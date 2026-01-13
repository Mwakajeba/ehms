<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Imports\CollegeStudentSheetImport;
use App\Models\College\Student;
use App\Models\College\Program;
use App\Models\College\CollegeGuardian;

class StudentController extends Controller
{
    public function index()
    {
        // Get data for filters
        $programs = Program::active()
            ->forCompany(Auth::user()->company_id)
            ->when(session('branch_id'), function ($query) {
                return $query->forBranch(session('branch_id'));
            })
            ->get();

        $departments = \App\Models\College\Department::active()
            ->forCompany(Auth::user()->company_id)
            ->when(session('branch_id'), function ($query) {
                return $query->forBranch(session('branch_id'));
            })
            ->get();

        $academicYears = \App\Models\School\AcademicYear::forCompany(Auth::user()->company_id)
            ->orderBy('start_date', 'desc')
            ->get();

        $statuses = ['active', 'inactive', 'graduated', 'suspended', 'transferred'];
        $levels = [1, 2, 3, 4];

        return view('college.students.index', compact('programs', 'departments', 'academicYears', 'statuses', 'levels'));
    }

    public function create()
    {
        $programs = Program::active()
            ->forCompany(Auth::user()->company_id)
            ->when(session('branch_id'), function ($query) {
                return $query->forBranch(session('branch_id'));
            })
            ->get();

        $academicYears = \App\Models\School\AcademicYear::forCompany(Auth::user()->company_id)
            ->orderBy('start_date', 'desc')
            ->get();

        $activeAcademicYear = \App\Models\School\AcademicYear::active()
            ->forCompany(Auth::user()->company_id)
            ->first();

        $defaultEnrollmentYear = null;
        if ($activeAcademicYear) {
            $defaultEnrollmentYear = intval(explode('-', $activeAcademicYear->year_name)[0]);
        }

        $courses = \App\Models\College\Course::where('status', 'active')
            ->orderBy('name')
            ->get();

        $semesters = \App\Models\College\Semester::where('status', 'active')
            ->orderBy('id')
            ->get();

        return view('college.students.create', compact('programs', 'academicYears', 'activeAcademicYear', 'defaultEnrollmentYear', 'courses', 'semesters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_number' => 'required|string|max:50|unique:college_students,student_number',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:college_students,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'required|in:male,female,other',
            'nationality' => 'nullable|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'program_id' => 'required|exists:college_programs,id',
            'enrollment_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'graduation_year' => 'nullable|integer|min:2000|max:' . (date('Y') + 10),
            'admission_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,graduated,suspended,transferred',
            'admission_level' => 'required|in:1,2,3,4',
            'permanent_address' => 'nullable|string|max:500',
            'current_address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|in:parent,guardian,sibling,spouse,relative,friend,other',
            'previous_school' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'grade_score' => 'nullable|string|max:100',
            'completion_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'student_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $photoPath = null;
            if ($request->hasFile('student_photo')) {
                $photoPath = $request->file('student_photo')->store('students/photos', 'public');
            }

            $student = Student::create([
                'student_number' => $request->student_number,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make(strtolower($request->first_name)),
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'nationality' => $request->nationality,
                'id_number' => $request->id_number,
                'program_id' => $request->program_id,
                'enrollment_year' => $request->enrollment_year,
                'graduation_year' => $request->graduation_year,
                'admission_date' => $request->admission_date,
                'status' => $request->status,
                'admission_level' => $request->admission_level,
                'permanent_address' => $request->permanent_address ?: 'Not provided',
                'current_address' => $request->current_address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relationship' => $request->emergency_contact_relationship,
                'previous_school' => $request->previous_school,
                'qualification' => $request->qualification,
                'grade_score' => $request->grade_score,
                'completion_year' => $request->completion_year,
                'student_photo' => $photoPath,
                'company_id' => Auth::user()->company_id,
                'branch_id' => session('branch_id')
            ]);

            // Enroll student in selected courses
            if ($request->has('courses') && is_array($request->courses)) {
                // Get or create active academic year
                $activeAcademicYear = \App\Models\College\AcademicYear::where('status', 'active')->first();
                if (!$activeAcademicYear) {
                    $activeAcademicYear = \App\Models\College\AcademicYear::first();
                    if (!$activeAcademicYear) {
                        // Create a default academic year
                        $activeAcademicYear = \App\Models\College\AcademicYear::create([
                            'name' => date('Y') . '-' . (date('Y') + 1),
                            'start_date' => date('Y') . '-01-01',
                            'end_date' => (date('Y') + 1) . '-12-31',
                            'status' => 'active'
                        ]);
                    }
                }

                // Get or create active semester
                $activeSemester = \App\Models\College\Semester::where('status', 'active')->first();
                if (!$activeSemester) {
                    $activeSemester = \App\Models\College\Semester::first();
                    if (!$activeSemester) {
                        // Create a default semester
                        $activeSemester = \App\Models\College\Semester::create([
                            'name' => 'Semester 1',
                            'number' => 1,
                            'description' => 'First Semester',
                            'status' => 'active'
                        ]);
                    }
                }

                foreach ($request->courses as $courseId) {
                    \App\Models\College\CourseEnrollment::create([
                        'student_id' => $student->id,
                        'course_id' => $courseId,
                        'academic_year_id' => $activeAcademicYear->id,
                        'semester_id' => $activeSemester->id,
                        'enrolled_date' => now(),
                        'status' => 'enrolled'
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('college.students.index')
                ->with('success', 'Student created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create student: ' . $e->getMessage());
        }
    }

    public function show($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        $id = $decoded[0] ?? null;

        if (!$id) {
            abort(404, 'Student not found.');
        }

        $student = Student::with(['program', 'program.department', 'parents', 'courseEnrollments.course'])->findOrFail($id);

        // Check if student belongs to user's branch
        if (session('branch_id') && $student->branch_id !== session('branch_id')) {
            abort(403, 'Unauthorized access to student record.');
        }

        // Get enrollment year name
        $enrollmentYearName = null;
        if ($student->enrollment_year) {
            $academicYear = \App\Models\School\AcademicYear::forCompany(Auth::user()->company_id)
                ->when(session('branch_id'), function ($query) {
                    return $query->forBranch(session('branch_id'));
                })
                ->where('year_name', 'LIKE', $student->enrollment_year . '-%')
                ->first();
            $enrollmentYearName = $academicYear ? $academicYear->year_name : $student->enrollment_year;
        }

        // Calculate Year of Study based on admission level and current academic year
        $yearOfStudy = $this->calculateYearOfStudy($student);

        return view('college.students.show', compact('student', 'enrollmentYearName', 'yearOfStudy'));
    }

    /**
     * Calculate the current year of study based on admission level and academic year
     */
    private function calculateYearOfStudy($student)
    {
        $currentYear = date('Y');
        $currentMonth = date('n'); // 1-12

        // Get the current academic year
        $currentAcademicYear = \App\Models\School\AcademicYear::active()
            ->forCompany(Auth::user()->company_id)
            ->when(session('branch_id'), function ($query) {
                return $query->forBranch(session('branch_id'));
            })
            ->first();

        if (!$currentAcademicYear) {
            // Fallback: assume academic year starts in September (month 9)
            $academicYearStart = $currentMonth >= 9 ? $currentYear : $currentYear - 1;
        } else {
            // Parse academic year name like "2024-2025"
            $yearParts = explode('-', $currentAcademicYear->year_name);
            $academicYearStart = intval($yearParts[0] ?? $currentYear);
        }

        // Calculate years since enrollment
        $yearsSinceEnrollment = $academicYearStart - $student->enrollment_year;

        // Calculate current year of study
        $currentYearOfStudy = (int)$student->admission_level + $yearsSinceEnrollment;

        // Ensure it's within valid range (1-4)
        $currentYearOfStudy = max(1, min(4, $currentYearOfStudy));

        // Map to year names
        $yearNames = [
            1 => 'First Year',
            2 => 'Second Year',
            3 => 'Third Year',
            4 => 'Fourth Year'
        ];

        return $yearNames[$currentYearOfStudy] ?? 'Unknown';
    }

    public function edit($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404, 'Student not found.');
        }

        $student = Student::findOrFail($id);

        // Check if student belongs to user's branch
        if (session('branch_id') && $student->branch_id !== session('branch_id')) {
            abort(403, 'Unauthorized access to student record.');
        }

        $programs = Program::active()
            ->forCompany(Auth::user()->company_id)
            ->when(session('branch_id'), function ($query) {
                return $query->forBranch(session('branch_id'));
            })
            ->get();

        $activeAcademicYear = \App\Models\School\AcademicYear::active()
            ->forCompany(Auth::user()->company_id)
            ->first();

        $courses = \App\Models\College\Course::where('status', 'active')
            ->orderBy('name')
            ->get();

        $semesters = \App\Models\College\Semester::where('status', 'active')
            ->orderBy('id')
            ->get();

        $enrolledCourseIds = $student->courseEnrollments()->where('status', 'enrolled')->pluck('course_id')->toArray();

        return view('college.students.edit', compact('student', 'programs', 'activeAcademicYear', 'courses', 'semesters', 'enrolledCourseIds'));
    }

    public function update(Request $request, $encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404, 'Student not found.');
        }

        $student = Student::findOrFail($id);

        // Check if student belongs to user's branch
        if (session('branch_id') && $student->branch_id !== session('branch_id')) {
            abort(403, 'Unauthorized access to student record.');
        }

        $request->validate([
            'student_number' => 'required|string|max:50|unique:college_students,student_number,' . $id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:college_students,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'required|in:male,female,other',
            'nationality' => 'nullable|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'program_id' => 'required|exists:college_programs,id',
            'enrollment_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'graduation_year' => 'nullable|integer|min:2000|max:' . (date('Y') + 10),
            'admission_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,graduated,suspended,transferred',
            'admission_level' => 'required|in:1,2,3,4',
            'permanent_address' => 'nullable|string|max:500',
            'current_address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|in:parent,guardian,sibling,spouse,relative,friend,other',
            'previous_school' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'grade_score' => 'nullable|string|max:100',
            'completion_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'student_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $photoPath = $student->student_photo; // Keep existing photo by default
            if ($request->hasFile('student_photo')) {
                // Delete old photo if exists
                if ($student->student_photo && \Storage::disk('public')->exists($student->student_photo)) {
                    \Storage::disk('public')->delete($student->student_photo);
                }
                $photoPath = $request->file('student_photo')->store('students/photos', 'public');
            }

            $student->update([
                'student_number' => $request->student_number,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'nationality' => $request->nationality,
                'id_number' => $request->id_number,
                'program_id' => $request->program_id,
                'enrollment_year' => $request->enrollment_year,
                'graduation_year' => $request->graduation_year,
                'admission_date' => $request->admission_date,
                'status' => $request->status,
                'admission_level' => $request->admission_level,
                'permanent_address' => $request->permanent_address ?: 'Not provided',
                'current_address' => $request->current_address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relationship' => $request->emergency_contact_relationship,
                'previous_school' => $request->previous_school,
                'qualification' => $request->qualification,
                'grade_score' => $request->grade_score,
                'completion_year' => $request->completion_year,
                'student_photo' => $photoPath
            ]);

            // Sync course enrollments
            if ($request->has('courses')) {
                // Get current enrollments
                $currentEnrollments = $student->courseEnrollments()->where('status', 'enrolled')->pluck('course_id')->toArray();
                $newCourses = $request->courses ?? [];

                // Find courses to add and remove
                $coursesToAdd = array_diff($newCourses, $currentEnrollments);
                $coursesToRemove = array_diff($currentEnrollments, $newCourses);

                // Remove enrollments
                if (!empty($coursesToRemove)) {
                    $student->courseEnrollments()
                        ->whereIn('course_id', $coursesToRemove)
                        ->where('status', 'enrolled')
                        ->update(['status' => 'dropped', 'unassigned_date' => now()]);
                }

                // Add new enrollments
                if (!empty($coursesToAdd)) {
                    // Get or create active academic year
                    $activeAcademicYear = \App\Models\College\AcademicYear::where('status', 'active')->first();
                    if (!$activeAcademicYear) {
                        $activeAcademicYear = \App\Models\College\AcademicYear::first();
                        if (!$activeAcademicYear) {
                            $activeAcademicYear = \App\Models\College\AcademicYear::create([
                                'name' => date('Y') . '-' . (date('Y') + 1),
                                'start_date' => date('Y') . '-01-01',
                                'end_date' => (date('Y') + 1) . '-12-31',
                                'status' => 'active'
                            ]);
                        }
                    }

                    // Get or create active semester
                    $activeSemester = \App\Models\College\Semester::where('status', 'active')->first();
                    if (!$activeSemester) {
                        $activeSemester = \App\Models\College\Semester::first();
                        if (!$activeSemester) {
                            $activeSemester = \App\Models\College\Semester::create([
                                'name' => 'Semester 1',
                                'number' => 1,
                                'description' => 'First Semester',
                                'status' => 'active'
                            ]);
                        }
                    }

                    foreach ($coursesToAdd as $courseId) {
                        \App\Models\College\CourseEnrollment::create([
                            'student_id' => $student->id,
                            'course_id' => $courseId,
                            'academic_year_id' => $activeAcademicYear->id,
                            'semester_id' => $activeSemester->id,
                            'enrolled_date' => now(),
                            'status' => 'enrolled'
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('college.students.index')
                ->with('success', 'Student updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update student: ' . $e->getMessage());
        }
    }

    public function destroy($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404, 'Student not found.');
        }

        $student = Student::findOrFail($id);

        // Check if student belongs to user's branch
        if (session('branch_id') && $student->branch_id !== session('branch_id')) {
            abort(403, 'Unauthorized access to student record.');
        }

        try {
            $student->delete();
            return redirect()->route('college.students.index')
                ->with('success', 'Student deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete student: ' . $e->getMessage());
        }
    }

    /**
     * Add courses to student
     */
    public function addCourses(Request $request, $encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404, 'Student not found.');
        }

        $student = Student::findOrFail($id);

        // Check if student belongs to user's branch
        if (session('branch_id') && $student->branch_id !== session('branch_id')) {
            abort(403, 'Unauthorized access to student record.');
        }

        $request->validate([
            'courses' => 'required|array|min:1',
            'courses.*' => 'exists:courses,id',
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'semester_id' => 'required|exists:college_semesters,id'
        ]);

        try {
            DB::beginTransaction();

            $enrolledCount = 0;
            foreach ($request->courses as $courseId) {
                // Check if already enrolled in this course for this academic year and semester
                $existingEnrollment = \App\Models\College\CourseEnrollment::where('student_id', $student->id)
                    ->where('course_id', $courseId)
                    ->where('academic_year_id', $request->academic_year_id)
                    ->where('semester_id', $request->semester_id)
                    ->first();

                if (!$existingEnrollment) {
                    \App\Models\College\CourseEnrollment::create([
                        'student_id' => $student->id,
                        'course_id' => $courseId,
                        'academic_year_id' => $request->academic_year_id,
                        'semester_id' => $request->semester_id,
                        'enrolled_date' => now(),
                        'status' => 'enrolled'
                    ]);
                    $enrolledCount++;
                }
            }

            DB::commit();

            return redirect()->route('college.students.show', $encodedId)
                ->with('success', $enrolledCount . ' course(s) added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to add courses: ' . $e->getMessage());
        }
    }

    /**
     * Change student program
     */
    public function changeProgram(Request $request, $encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404, 'Student not found.');
        }

        $student = Student::findOrFail($id);

        // Check if student belongs to user's branch
        if (session('branch_id') && $student->branch_id !== session('branch_id')) {
            abort(403, 'Unauthorized access to student record.');
        }

        $request->validate([
            'program_id' => 'required|exists:college_programs,id',
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $oldProgramId = $student->program_id;
            $student->update([
                'program_id' => $request->program_id
            ]);

            // Optional: Log the program change if you have a history table
            // You can create a program_history table to track changes
            
            DB::commit();

            return redirect()->route('college.students.show', $encodedId)
                ->with('success', 'Program changed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to change program: ' . $e->getMessage());
        }
    }

    /**
     * Update course enrollment status
     */
    public function updateEnrollment(Request $request, $enrollmentId)
    {
        $enrollment = \App\Models\College\CourseEnrollment::findOrFail($enrollmentId);

        $request->validate([
            'status' => 'required|in:enrolled,completed,dropped,failed',
            'grade' => 'nullable|string|max:10',
            'remarks' => 'nullable|string|max:500'
        ]);

        try {
            $enrollment->update([
                'status' => $request->status,
                'grade' => $request->grade,
                'remarks' => $request->remarks
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Enrollment updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update enrollment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete course enrollment
     */
    public function deleteEnrollment($enrollmentId)
    {
        $enrollment = \App\Models\College\CourseEnrollment::findOrFail($enrollmentId);

        try {
            $enrollment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Enrollment deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete enrollment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assignParents($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404, 'Student not found.');
        }

        $student = Student::with(['program', 'program.department', 'parents'])->findOrFail($id);

        // Check if student belongs to user's branch
        if (session('branch_id') && $student->branch_id !== session('branch_id')) {
            abort(403, 'Unauthorized access to student record.');
        }

        return view('college.students.assign-parents', compact('student'));
    }

    public function storeAssignedParents(Request $request, $encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404, 'Student not found.');
        }

        $student = Student::findOrFail($id);

        // Check if student belongs to user's branch
        if (session('branch_id') && $student->branch_id !== session('branch_id')) {
            abort(403, 'Unauthorized access to student record.');
        }

        $request->validate([
            'parents' => 'required|array|min:1',
            'parents.*.name' => 'required|string|max:255',
            'parents.*.relationship' => 'required|string|max:100',
            'parents.*.phone' => 'required|string|max:20',
            'parents.*.alt_phone' => 'nullable|string|max:20',
            'parents.*.email' => 'nullable|email',
            'parents.*.occupation' => 'nullable|string|max:255',
            'parents.*.address' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Create parents/guardians and attach to student
            foreach ($request->parents as $parentData) {
                // Check if parent with this email already exists
                if (!empty($parentData['email'])) {
                    $guardian = CollegeGuardian::where('email', $parentData['email'])
                        ->where('company_id', Auth::user()->company_id)
                        ->when(session('branch_id'), function ($query) {
                            return $query->where('branch_id', session('branch_id'));
                        })
                        ->first();
                } else {
                    $guardian = null;
                }

                // If parent doesn't exist, create new one
                if (!$guardian) {
                    $guardian = CollegeGuardian::create([
                        'name' => $parentData['name'],
                        'phone' => $parentData['phone'],
                        'alt_phone' => $parentData['alt_phone'] ?? null,
                        'email' => $parentData['email'] ?? null,
                        'occupation' => $parentData['occupation'] ?? null,
                        'address' => $parentData['address'],
                        'company_id' => Auth::user()->company_id,
                        'branch_id' => session('branch_id')
                    ]);
                } else {
                    // Update existing parent's information if needed
                    $guardian->update([
                        'name' => $parentData['name'],
                        'phone' => $parentData['phone'],
                        'alt_phone' => $parentData['alt_phone'] ?? null,
                        'occupation' => $parentData['occupation'] ?? null,
                        'address' => $parentData['address'],
                    ]);
                }

                // Check if this parent is already attached to the student
                if (!$student->parents()->where('parent_id', $guardian->id)->exists()) {
                    // Create the relationship
                    $student->parents()->attach($guardian->id, ['relationship' => $parentData['relationship']]);
                }
            }

            DB::commit();

            return redirect()->route('college.students.show', $encodedId)
                ->with('success', 'Parents assigned to student successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to assign parents: ' . $e->getMessage());
        }
    }

    public function assignExistingParents(Request $request, $encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Student not found.'], 404);
        }

        try {
            $student = Student::findOrFail($id);

            // Check if student belongs to user's branch
            if (session('branch_id') && $student->branch_id !== session('branch_id')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access to student record.'], 403);
            }

            $request->validate([
                'parents' => 'required|array|min:1',
                'parents.*.id' => 'required|exists:college_guardians,id',
                'parents.*.relationship' => 'required|string|max:100',
            ]);

            $assignedCount = 0;
            $alreadyAssignedCount = 0;

            foreach ($request->parents as $parentData) {
                $guardian = CollegeGuardian::findOrFail($parentData['id']);

                // Check if this parent is already attached to the student
                if (!$student->parents()->where('parent_id', $guardian->id)->exists()) {
                    // Create the relationship
                    $student->parents()->attach($guardian->id, ['relationship' => $parentData['relationship']]);
                    $assignedCount++;
                } else {
                    $alreadyAssignedCount++;
                }
            }

            $message = '';
            if ($assignedCount > 0) {
                $message .= "Successfully assigned {$assignedCount} parent(s) to the student.";
            }
            if ($alreadyAssignedCount > 0) {
                if ($message) $message .= ' ';
                $message .= "{$alreadyAssignedCount} parent(s) were already assigned.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'assigned_count' => $assignedCount,
                'already_assigned_count' => $alreadyAssignedCount
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->errors()['parents'] ?? ['Invalid parent data']),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Parent assignment error: ' . $e->getMessage(), [
                'student_id' => $id,
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while assigning parents. Please try again.'
            ], 500);
        }
    }

    public function removeParent($encodedStudentId, $parentId)
    {
        $studentId = Hashids::decode($encodedStudentId)[0] ?? null;
        if (!$studentId) {
            abort(404, 'Student not found.');
        }

        $student = Student::findOrFail($studentId);

        // Check if student belongs to user's branch
        if (session('branch_id') && $student->branch_id !== session('branch_id')) {
            abort(403, 'Unauthorized access to student record.');
        }

        $student->parents()->detach($parentId);

        return redirect()->back()
            ->with('success', 'Parent removed from student successfully.');
    }

    public function searchParents(Request $request, $encodedStudentId)
    {
        $studentId = Hashids::decode($encodedStudentId)[0] ?? null;
        if (!$studentId) {
            return response()->json(['parents' => []]);
        }

        $student = Student::find($studentId);

        if (!$student) {
            return response()->json(['parents' => []]);
        }

        // Check if student belongs to user's branch
        if (session('branch_id') && $student->branch_id !== session('branch_id')) {
            return response()->json(['parents' => []]);
        }

        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json(['parents' => []]);
        }

        // Get IDs of parents already assigned to this student
        $assignedParentIds = $student->parents()->pluck('parent_id')->toArray();

        $parents = CollegeGuardian::where(function($q) use ($query) {
            $q->where('name', 'LIKE', '%' . $query . '%')
              ->orWhere('phone', 'LIKE', '%' . $query . '%')
              ->orWhere('alt_phone', 'LIKE', '%' . $query . '%')
              ->orWhere('email', 'LIKE', '%' . $query . '%');
        })
        ->where('company_id', Auth::user()->company_id)
        ->when(session('branch_id'), function ($query) {
            return $query->where('branch_id', session('branch_id'));
        })
        ->whereNotIn('id', $assignedParentIds) // Exclude already assigned parents
        ->select('id', 'name', 'phone', 'alt_phone', 'email', 'address', 'occupation')
        ->orderBy('name')
        ->limit(20)
        ->get();

        return response()->json(['parents' => $parents]);
    }

    public function data(Request $request)
    {
        $query = Student::with(['program', 'program.department', 'courseEnrollments.course']);

        // Only filter by company if user is authenticated
        if (Auth::check()) {
            $query->forCompany(Auth::user()->company_id);
        }

        // Only filter by branch if branch_id is set in session
        if (session('branch_id')) {
            $query->forBranch(session('branch_id'));
        }

        // Apply filters
        if ($request->has('program_id') && !empty($request->program_id)) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->has('department_id') && !empty($request->department_id)) {
            $query->whereHas('program', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->has('academic_year_id') && !empty($request->academic_year_id)) {
            $academicYear = \App\Models\School\AcademicYear::find($request->academic_year_id);
            if ($academicYear) {
                $query->where('enrollment_year', intval(explode('-', $academicYear->year_name)[0]));
            }
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('admission_level') && !empty($request->admission_level)) {
            $query->where('admission_level', $request->admission_level);
        }

        $students = $query->select(['id', 'student_number', 'first_name', 'last_name', 'email', 'phone', 'gender', 'program_id', 'enrollment_year', 'status', 'created_at']);

        return DataTables::of($students)
            ->addColumn('full_name', function ($student) {
                return $student->first_name . ' ' . $student->last_name;
            })
            ->addColumn('program', function ($student) {
                return $student->program ? $student->program->name : 'Not Assigned';
            })
            ->addColumn('department', function ($student) {
                return $student->program && $student->program->department ? $student->program->department->name : 'N/A';
            })
            ->addColumn('courses_list', function ($student) {
                $enrolledCourses = $student->courseEnrollments->where('status', 'enrolled');
                
                if ($enrolledCourses->isEmpty()) {
                    return '<span class="badge bg-secondary">No courses</span>';
                }
                
                $courseNames = $enrolledCourses->map(function ($enrollment) {
                    return $enrollment->course ? $enrollment->course->name : 'Unknown';
                });
                
                $coursesHtml = '';
                foreach ($courseNames as $courseName) {
                    $coursesHtml .= '<span class="badge bg-primary me-1 mb-1">' . $courseName . '</span>';
                }
                
                return $coursesHtml;
            })
            ->addColumn('enrollment_year', function ($student) {
                // Find the corresponding academic year name based on the enrollment_year integer
                $academicYear = \App\Models\School\AcademicYear::forCompany(Auth::user()->company_id)
                    ->when(session('branch_id'), function ($query) {
                        return $query->forBranch(session('branch_id'));
                    })
                    ->where('year_name', 'LIKE', $student->enrollment_year . '-%')
                    ->first();

                return $academicYear ? $academicYear->year_name : $student->enrollment_year;
            })
            ->addColumn('status_badge', function ($student) {
                $badges = [
                    'active' => '<span class="badge bg-success">Active</span>',
                    'inactive' => '<span class="badge bg-secondary">Inactive</span>',
                    'graduated' => '<span class="badge bg-info">Graduated</span>',
                    'suspended' => '<span class="badge bg-warning">Suspended</span>',
                    'transferred' => '<span class="badge bg-primary">Transferred</span>'
                ];
                return $badges[$student->status] ?? '<span class="badge bg-secondary">Unknown</span>';
            })
            ->addColumn('actions', function ($student) {
                $encodedId = \Vinkla\Hashids\Facades\Hashids::encode($student->id);
                $name = addslashes($student->first_name . ' ' . $student->last_name);
                return '<div class="btn-group" role="group">
                    <a href="' . route('college.students.show', $encodedId) . '" class="btn btn-sm btn-outline-info" title="View Details">
                        <i class="bx bx-show"></i>
                    </a>
                    <a href="' . route('college.students.edit', $encodedId) . '" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bx bx-edit"></i>
                    </a>
                    <a href="' . route('college.students.assign-parents', $encodedId) . '" class="btn btn-sm btn-outline-success" title="Assign Parents">
                        <i class="bx bx-user-plus"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                            onclick="confirmDelete(\'' . $encodedId . '\', \'' . $name . '\')">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['status_badge', 'courses_list', 'actions'])
            ->make(true);
    }

    /**
     * Export students to Excel
     */
    public function exportExcel(Request $request, string $hashId = null)
    {
        // HashId is used for URL uniqueness but not validated for security
        // since it's generated client-side for cache-busting purposes

        $query = Student::with(['program', 'program.department']);

        // Apply company/branch filters
        $query->forCompany(Auth::user()->company_id);
        if (session('branch_id')) {
            $query->forBranch(session('branch_id'));
        }

        // Apply filters
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('program', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('academic_year_id')) {
            $academicYear = \App\Models\School\AcademicYear::find($request->academic_year_id);
            if ($academicYear) {
                $query->where('enrollment_year', intval(explode('-', $academicYear->year_name)[0]));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('admission_level')) {
            $query->where('admission_level', $request->admission_level);
        }

        $students = $query->orderBy('first_name')->orderBy('last_name')->get();

        return Excel::download(new class($students) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping {
            private $students;

            public function __construct($students)
            {
                $this->students = $students;
            }

            public function collection()
            {
                return $this->students;
            }

            public function headings(): array
            {
                return [
                    'Student Number',
                    'First Name',
                    'Last Name',
                    'Email',
                    'Phone',
                    'Date of Birth',
                    'Gender',
                    'Nationality',
                    'ID Number',
                    'Program',
                    'Department',
                    'Enrollment Year',
                    'Graduation Year',
                    'Admission Date',
                    'Status',
                    'Admission Level',
                    'Permanent Address',
                    'Current Address',
                    'Emergency Contact Name',
                    'Emergency Contact Phone',
                    'Emergency Contact Relationship',
                    'Previous School',
                    'Qualification',
                    'Grade Score',
                    'Completion Year'
                ];
            }

            public function map($student): array
            {
                return [
                    $student->student_number,
                    $student->first_name,
                    $student->last_name,
                    $student->email,
                    $student->phone,
                    $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : '',
                    ucfirst($student->gender ?? ''),
                    $student->nationality,
                    $student->id_number,
                    $student->program->name ?? '',
                    $student->program->department->name ?? '',
                    $student->enrollment_year,
                    $student->graduation_year,
                    $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('Y-m-d') : '',
                    ucfirst($student->status),
                    $student->admission_level,
                    $student->permanent_address,
                    $student->current_address,
                    $student->emergency_contact_name,
                    $student->emergency_contact_phone,
                    $student->emergency_contact_relationship,
                    $student->previous_school,
                    $student->qualification,
                    $student->grade_score,
                    $student->completion_year
                ];
            }
        }, 'college_students_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Export students to PDF
     */
    public function exportPdf(Request $request, string $hashId = null)
    {
        // HashId is used for URL uniqueness but not validated for security
        // since it's generated client-side for cache-busting purposes

        $query = Student::with(['program', 'program.department']);

        // Apply company/branch filters
        $query->forCompany(Auth::user()->company_id);
        if (session('branch_id')) {
            $query->forBranch(session('branch_id'));
        }

        // Apply filters
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('program', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('academic_year_id')) {
            $academicYear = \App\Models\School\AcademicYear::find($request->academic_year_id);
            if ($academicYear) {
                $query->where('enrollment_year', intval(explode('-', $academicYear->year_name)[0]));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('admission_level')) {
            $query->where('admission_level', $request->admission_level);
        }

        $students = $query->orderBy('first_name')->orderBy('last_name')->get();

        $data = [
            'students' => $students,
            'company' => \App\Models\Company::find(Auth::user()->company_id),
            'title' => 'College Students Report',
            'filters' => [
                'program' => $request->filled('program_id') ? Program::find($request->program_id)->name ?? 'All' : 'All',
                'department' => $request->filled('department_id') ? \App\Models\College\Department::find($request->department_id)->name ?? 'All' : 'All',
                'academic_year' => $request->filled('academic_year_id') ? \App\Models\School\AcademicYear::find($request->academic_year_id)->year_name ?? 'All' : 'All',
                'status' => $request->filled('status') ? ucfirst($request->status) : 'All',
                'admission_level' => $request->filled('admission_level') ? $request->admission_level : 'All',
            ],
            'generated_at' => now(),
            'logo_path' => null, // Will be set below
        ];

        // Check if company logo exists and set the correct path for DomPDF
        if ($data['company'] && $data['company']->logo) {
            $logoFullPath = public_path('storage/' . $data['company']->logo);
            if (file_exists($logoFullPath)) {
                $data['logo_path'] = $logoFullPath;
            }
        }

        $pdf = Pdf::loadView('college.students.exports.pdf', $data);
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions(['isRemoteEnabled' => false, 'isHtml5ParserEnabled' => true]);

        return $pdf->download('college_students_report_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    public function import()
    {
        \Log::info('College Student Import method called');
        
        // For testing without authentication, use default company_id
        $companyId = Auth::check() ? Auth::user()->company_id : 1;
        $branchId = Auth::check() ? session('branch_id') : null;

        \Log::info('Company ID: ' . $companyId . ', Branch ID: ' . $branchId);

        $programs = Program::active()
            ->forCompany($companyId)
            ->when($branchId, function ($query) use ($branchId) {
                return $query->forBranch($branchId);
            })
            ->get();

        // Get all academic years for selection
        $academicYears = \App\Models\School\AcademicYear::forCompany($companyId)
            ->orderBy('start_date', 'desc')
            ->get();

        // Get current academic year for default selection
        $currentAcademicYear = \App\Models\School\AcademicYear::active()
            ->forCompany($companyId)
            ->first();

        \Log::info('Found ' . $programs->count() . ' programs');
        \Log::info('Found ' . $academicYears->count() . ' academic years');
        \Log::info('Current Academic Year: ' . ($currentAcademicYear ? $currentAcademicYear->year_name : 'None'));

        return view('college.students.import', compact('programs', 'academicYears', 'currentAcademicYear'));
    }

    public function previewImport(Request $request)
    {
        \Log::info('Preview Import method called', [
            'has_file' => $request->hasFile('excel_file'),
            'program_id' => $request->program_id,
            'branch_id' => $request->branch_id,
            'csrf_token' => $request->_token
        ]);

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
            'program_id' => 'required|exists:college_programs,id',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        $program = Program::findOrFail($request->program_id);

        // For testing without authentication, use default company_id
        $companyId = Auth::check() ? Auth::user()->company_id : 1;
        $branchId = $request->branch_id; // Use selected branch from form

        \Log::info('Preview Import validation passed', [
            'program' => $program->name,
            'company_id' => $companyId,
            'branch_id' => $branchId
        ]);

        try {
            $import = new CollegeStudentSheetImport($program, true, $companyId, $branchId);
            Excel::import($import, $request->file('excel_file'));

            $previewData = $import->getPreviewData();
            $errors = $import->getErrors();
            $duplicates = $import->getDuplicates();

            \Log::info('Preview Import completed', [
                'preview_count' => count($previewData),
                'error_count' => count($errors),
                'duplicate_count' => count($duplicates)
            ]);

            return response()->json([
                'success' => true,
                'total_rows' => count($previewData) + count($duplicates) + count($errors),
                'valid_count' => count($previewData),
                'duplicates_count' => count($duplicates),
                'errors_count' => count($errors),
                'preview_data' => $previewData,
                'duplicates' => $duplicates,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            \Log::error('Preview Import error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
            'program_id' => 'required|exists:college_programs,id',
            'academic_year' => 'required|string',
            'admission_level' => 'required|in:1,2,3,4'
        ]);

        $program = Program::findOrFail($request->program_id);

        // For testing without authentication, use default company_id
        $companyId = Auth::check() ? Auth::user()->company_id : 1;
        $branchId = Auth::check() ? session('branch_id') : null;

        // Parse academic year to get enrollment year
        $enrollmentYear = intval(explode('-', $request->academic_year)[0]);

        DB::beginTransaction();
        try {
            $import = new CollegeStudentSheetImport($program, false, $companyId, $branchId, $enrollmentYear, $request->admission_level);
            Excel::import($import, $request->file('excel_file'));

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();
            $duplicates = $import->getDuplicates();

            DB::commit();

            $message = "Import completed! {$successCount} students imported successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} rows had errors.";
            }
            if (count($duplicates) > 0) {
                $message .= " " . count($duplicates) . " duplicates found.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'duplicate_count' => count($duplicates)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadTemplate(Request $request)
    {
        $programId = $request->get('program_id');
        $program = null;

        if ($programId) {
            $program = Program::find($programId);
        }

        $filename = $program ? "college_students_template_{$program->name}.xlsx" : "college_students_template.xlsx";

        return Excel::download(new class($program) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
            private $program;

            public function __construct($program)
            {
                $this->program = $program;
            }

            public function headings(): array
            {
                return [
                    'Student Number',
                    'First Name',
                    'Last Name',
                    'Gender'
                ];
            }

            public function array(): array
            {
                return [
                    ['STU001', 'John', 'Doe', 'Male'],
                    ['STU002', 'Jane', 'Smith', 'Female'],
                    ['STU003', 'Robert', 'Johnson', 'Male'],
                    ['STU004', 'Emily', 'Williams', 'Female'],
                    ['STU005', 'Michael', 'Brown', 'Male'],
                    ['STU006', 'Sarah', 'Jones', 'Female'],
                    ['STU007', 'David', 'Garcia', 'Male'],
                    ['STU008', 'Lisa', 'Miller', 'Female'],
                    ['STU009', 'James', 'Davis', 'Male'],
                    ['STU010', 'Patricia', 'Rodriguez', 'Female']
                ];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                // Style the header row
                $sheet->getStyle('A1:D1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '007BFF'],
                    ],
                ]);

                // Auto-size columns
                foreach (range('A', 'D') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Add instructions below the sample data (after row 12)
                $sheet->setCellValue('A14', 'INSTRUCTIONS:');
                $sheet->getStyle('A14')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
                ]);

                $instructions = [
                    '• Fill in all required fields',
                    '• Student Number must be unique',
                    '• Gender: Male or Female',
                    '• Do not modify the header row',
                    '• Status will be set to "active" automatically',
                    '• Branch will be set from your session automatically',
                    '• Academic Year and Admission Level will be set from the import form'
                ];

                for ($i = 0; $i < count($instructions); $i++) {
                    $sheet->setCellValue('A' . (15 + $i), $instructions[$i]);
                }

                return $sheet;
            }
        }, $filename);
    }
}