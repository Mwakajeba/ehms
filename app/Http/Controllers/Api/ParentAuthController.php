<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\ActivityLog;
use App\Models\School\Guardian;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ParentAuthController extends Controller
{
    /**
     * Parent login API endpoint
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        // Check if this specific user (phone) is locked out
        if (LoginAttempt::isLockedOut($request->phone)) {
            $remainingTime = LoginAttempt::getRemainingLockoutTime($request->phone);

            ActivityLog::create([
                'user_id'     => null,
                'model'       => 'Auth',
                'action'      => 'login_failed',
                'description' => "Parent login blocked - too many attempts for {$request->phone}",
                'ip_address'  => $request->ip(),
                'device'      => $request->userAgent(),
                'activity_time' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "Account is temporarily locked. Please try again in {$remainingTime} minutes.",
            ], 429);
        }

        // Find guardian by phone
        $guardian = find_guardian_by_phone($request->phone);

        if (!$guardian) {
            LoginAttempt::record($request->phone, $request->ip(), $request->userAgent(), false);

            ActivityLog::create([
                'user_id'     => null,
                'model'       => 'Auth',
                'action'      => 'login_failed',
                'description' => "Parent login failed - phone not found ({$request->phone})",
                'ip_address'  => $request->ip(),
                'device'      => $request->userAgent(),
                'activity_time' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number or password.',
            ], 401);
        }

        // Check if guardian has password set
        if (!$guardian->password) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not set up. Please contact support.',
            ], 403);
        }

        // Verify password
        if (!Hash::check($request->password, $guardian->password)) {
            LoginAttempt::record($request->phone, $request->ip(), $request->userAgent(), false);

            ActivityLog::create([
                'user_id'     => null,
                'model'       => 'Auth',
                'action'      => 'login_failed',
                'description' => 'Parent login failed - wrong password',
                'ip_address'  => $request->ip(),
                'device'      => $request->userAgent(),
                'activity_time' => now(),
            ]);

            if (LoginAttempt::isLockedOut($request->phone)) {
                $remainingTime = LoginAttempt::getRemainingLockoutTime($request->phone);

                return response()->json([
                    'success' => false,
                    'message' => "Too many failed attempts. Account is locked for {$remainingTime} minutes.",
                ], 429);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number or password.',
            ], 401);
        }

        // Record successful login attempt
        LoginAttempt::record($guardian->phone, $request->ip(), $request->userAgent(), true);
        LoginAttempt::clearOldAttempts();

        // Create API token using Sanctum
        $token = $guardian->createToken('parent-api-token', ['parent'])->plainTextToken;

        // Log successful login
        ActivityLog::create([
            'user_id'     => null,
            'model'       => 'Auth',
            'action'      => 'login_success',
            'description' => "Parent (Guardian ID: {$guardian->id}) logged in via API",
            'ip_address'  => $request->ip(),
            'device'      => $request->userAgent(),
            'activity_time' => now(),
        ]);

        // Get students for this guardian to check if multiple students exist
        $students = $guardian->students()->with(['class', 'stream', 'academicYear'])->get();

        // Return success response with token and guardian data including students
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $guardian->id,
                    'name' => $guardian->name,
                    'phone' => $guardian->phone,
                    'email' => $guardian->email,
                    'role' => 'parent',
                    'students' => $students->map(function ($student) {
                        return [
                            'id' => $student->id,
                            'name' => $student->first_name . ' ' . $student->last_name,
                            'admission_number' => $student->admission_number,
                            'class' => $student->class ? [
                                'id' => $student->class->id,
                                'name' => $student->class->name,
                            ] : null,
                            'stream' => $student->stream ? [
                                'id' => $student->stream->id,
                                'name' => $student->stream->name,
                            ] : null,
                            'academic_year' => $student->academicYear ? [
                                'id' => $student->academicYear->id,
                                'year_name' => $student->academicYear->year_name,
                            ] : null,
                        ];
                    }),
                ],
            ],
        ], 200);
    }

    /**
     * Logout parent user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $guardian = $request->user();

        if ($guardian) {
            // Revoke current access token
            $request->user()->currentAccessToken()->delete();

            ActivityLog::create([
                'user_id'     => null,
                'model'       => 'Auth',
                'action'      => 'logout',
                'description' => "Parent (Guardian ID: {$guardian->id}) logged out via API",
                'ip_address'  => $request->ip(),
                'device'      => $request->userAgent(),
                'activity_time' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Get authenticated parent user information
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $guardian = $request->user();

        // Get students for this guardian
        $students = $guardian->students()->with(['class', 'stream', 'academicYear'])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $guardian->id,
                'name' => $guardian->name,
                'phone' => $guardian->phone,
                'email' => $guardian->email,
                'address' => $guardian->address,
                'occupation' => $guardian->occupation,
                'alt_phone' => $guardian->alt_phone,
                'role' => 'parent',
                'students' => $students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->first_name . ' ' . $student->last_name,
                        'admission_number' => $student->admission_number,
                        'class' => $student->class ? $student->class->name : null,
                        'stream' => $student->stream ? $student->stream->name : null,
                        'academic_year' => $student->academicYear ? $student->academicYear->year_name : null,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Update parent profile
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $guardian = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:20',
            'alt_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'occupation' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $updateData = $request->only(['name', 'email', 'phone', 'alt_phone', 'address', 'occupation']);

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $guardian->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $guardian->id,
                'name' => $guardian->name,
                'phone' => $guardian->phone,
                'email' => $guardian->email,
                'address' => $guardian->address,
                'occupation' => $guardian->occupation,
                'alt_phone' => $guardian->alt_phone,
            ],
        ], 200);
    }

    /**
     * Get student details
     * 
     * @param Request $request
     * @param int $studentId
     * @return JsonResponse
     */
    public function getStudent(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        // Verify the student belongs to this guardian
        $student = $guardian->students()
            ->where('students.id', $studentId)
            ->with([
                'class', 
                'stream', 
                'academicYear', 
                'route.buses', 
                'busStop.bus'
            ])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'full_name' => $student->first_name . ' ' . $student->last_name,
                'admission_number' => $student->admission_number,
                'date_of_birth' => $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : null,
                'gender' => $student->gender,
                'email' => $student->email,
                'address' => $student->address,
                'status' => $student->status,
                'admission_date' => $student->admission_date ? $student->admission_date->format('Y-m-d') : null,
                'boarding_type' => $student->boarding_type,
                'has_transport' => $student->has_transport,
                'class' => $student->class ? [
                    'id' => $student->class->id,
                    'name' => $student->class->name,
                ] : null,
                'stream' => $student->stream ? [
                    'id' => $student->stream->id,
                    'name' => $student->stream->name,
                ] : null,
                'academic_year' => $student->academicYear ? [
                    'id' => $student->academicYear->id,
                    'year_name' => $student->academicYear->year_name,
                ] : null,
                'route' => $student->route ? [
                    'id' => $student->route->id,
                    'name' => $student->route->route_name,
                    'code' => $student->route->route_code,
                    'description' => $student->route->description ?? null,
                    'buses' => $student->route->buses->map(function ($bus) {
                        return [
                            'id' => $bus->id,
                            'bus_number' => $bus->bus_number,
                            'driver_name' => $bus->driver_name,
                            'driver_phone' => $bus->driver_phone,
                            'capacity' => $bus->capacity,
                            'model' => $bus->model,
                            'registration_number' => $bus->registration_number,
                        ];
                    })->toArray(),
                ] : null,
                'bus_stop' => $student->busStop ? [
                    'id' => $student->busStop->id,
                    'name' => $student->busStop->stop_name,
                    'code' => $student->busStop->stop_code,
                    'description' => $student->busStop->description ?? null,
                    'fare' => $student->busStop->fare ?? null,
                    'latitude' => $student->busStop->latitude ?? null,
                    'longitude' => $student->busStop->longitude ?? null,
                    'bus' => $student->busStop->bus ? [
                        'id' => $student->busStop->bus->id,
                        'bus_number' => $student->busStop->bus->bus_number,
                        'driver_name' => $student->busStop->bus->driver_name,
                        'driver_phone' => $student->busStop->bus->driver_phone,
                        'capacity' => $student->busStop->bus->capacity,
                        'model' => $student->busStop->bus->model,
                        'registration_number' => $student->busStop->bus->registration_number,
                    ] : null,
                ] : null,
            ],
        ], 200);
    }

    /**
     * Get student subjects/courses
     * 
     * @param Request $request
     * @param int $studentId
     * @return JsonResponse
     */
    public function getStudentSubjects(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        // Verify the student belongs to this guardian
        $student = $guardian->students()
            ->where('students.id', $studentId)
            ->with(['class'])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied.',
            ], 404);
        }

        // Get subjects for the student's class through subject groups
        $subjects = [];
        if ($student->class_id) {
            // Get all active subject groups for this class
            $subjectGroups = \App\Models\School\SubjectGroup::where('class_id', $student->class_id)
                ->where('is_active', true)
                ->with(['subjects' => function($query) {
                    $query->orderBy('name');
                }])
                ->get();
            
            // Collect all unique subjects from all subject groups
            $subjectsMap = [];
            foreach ($subjectGroups as $subjectGroup) {
                foreach ($subjectGroup->subjects as $subject) {
                    // Use subject ID as key to avoid duplicates
                    if (!isset($subjectsMap[$subject->id])) {
                        $subjectsMap[$subject->id] = [
                            'id' => $subject->id,
                            'name' => $subject->name,
                            'code' => $subject->code ?? '',
                            'short_name' => $subject->short_name ?? $subject->name,
                            'subject_type' => $subject->subject_type ?? null,
                            'subject_group' => [
                                'id' => $subjectGroup->id,
                                'name' => $subjectGroup->name,
                                'code' => $subjectGroup->code ?? '',
                            ],
                        ];
                    }
                }
            }
            
            // Convert map to array and sort by name
            $subjects = collect($subjectsMap)->values()->sortBy('name')->values()->all();
        }

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ], 200);
    }

    /**
     * Get active academic year for a company
     */
    private function getActiveAcademicYear($companyId)
    {
        return \App\Models\School\AcademicYear::where('company_id', $companyId)
            ->where('is_current', true)
            ->first();
    }

    /**
     * Get student exams
     * 
     * @param Request $request
     * @param int $studentId
     * @return JsonResponse
     */
    public function getStudentExams(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        // Verify the student belongs to this guardian
        $student = $guardian->students()
            ->where('students.id', $studentId)
            ->with(['class', 'stream'])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied.',
            ], 404);
        }

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);

        // Get exam registrations with marks - group by exam type and academic year
        // Filter by active academic year only and published exam types only
        $query = \App\Models\SchoolExamRegistration::where('student_id', $studentId)
            ->with([
                'examClassAssignment.subject', 
                'examClassAssignment.examType', 
                'examClassAssignment.academicYear',
                'examClassAssignment.classe',
                'examClassAssignment.stream'
            ]);

        // Filter by active academic year and published exam types
        if ($activeAcademicYear) {
            $query->whereHas('examClassAssignment', function($q) use ($activeAcademicYear) {
                $q->where('academic_year_id', $activeAcademicYear->id)
                  ->whereHas('examType', function($examTypeQ) {
                      $examTypeQ->where('is_published', true);
                  });
            });
        } else {
            // Even if no active academic year, still filter by published exam types
            $query->whereHas('examClassAssignment', function($q) {
                $q->whereHas('examType', function($examTypeQ) {
                    $examTypeQ->where('is_published', true);
                });
            });
        }

        $examRegistrations = $query->get();

        $exams = $examRegistrations->groupBy(function ($registration) {
            $assignment = $registration->examClassAssignment;
            $examType = $assignment && $assignment->examType ? $assignment->examType->name : 'Standard';
            $academicYear = $assignment && $assignment->academicYear ? $assignment->academicYear->year_name : 'Unknown';
            $examTypeId = $assignment && $assignment->examType ? $assignment->examType->id : null;
            $academicYearId = $assignment && $assignment->academicYear ? $assignment->academicYear->id : null;
            return $examTypeId . '|' . $examType . '|' . $academicYearId . '|' . $academicYear;
        })->map(function ($registrations, $key) use ($student) {
            $parts = explode('|', $key);
            $examTypeId = $parts[0];
            $examType = $parts[1];
            $academicYearId = $parts[2];
            $academicYear = $parts[3] ?? 'Unknown';
            
            $firstReg = $registrations->first();
            $assignment = $firstReg->examClassAssignment;
            $classId = $assignment ? $assignment->class_id : $student->class_id;
            $streamId = $assignment ? $assignment->stream_id : $student->stream_id;
            
            // Get all students in the same class/stream for position calculation
            $classStudents = \App\Models\School\Student::where('class_id', $classId)
                ->where('status', 'active')
                ->where('company_id', $student->company_id);
            
            if ($streamId) {
                $classStudents->where('stream_id', $streamId);
            }
            
            $allStudents = $classStudents->pluck('id')->toArray();
            
            // Get grade scale for this academic year
            $gradeScale = \App\Models\SchoolGradeScale::where('academic_year_id', $academicYearId)
                ->where('is_active', true)
                ->first();
            
            // Get max_marks from grade scale (fallback if exam_class_assignment doesn't have it)
            $gradeScaleMaxMarks = $gradeScale ? (float) $gradeScale->max_marks : 100.0; // Default to 100 if no grade scale
            
            $subjects = [];
            $studentTotal = 0;
            $maxTotal = 0; // Will accumulate max_marks for ALL subjects
            $subjectCount = 0; // Count of subjects with marks (for average calculation like Examination Results Report)
            
            foreach ($registrations as $reg) {
                $ass = $reg->examClassAssignment;
                $subject = $ass ? $ass->subject : null;
                
                if (!$subject) continue;
                
                $marks = \App\Models\SchoolExamMark::where('student_id', $reg->student_id)
                    ->where('exam_class_assignment_id', $reg->exam_class_assignment_id)
                    ->first();
                
                $status = $reg->status ?? 'present';
                $marksObtained = null;
                $percentage = 0;
                $grade = null;
                $classRank = '-';
                
                // Get max_marks: use exam_class_assignment max_marks if available, otherwise use grade scale max_marks
                $maxMarks = 0;
                if ($ass && $ass->max_marks && $ass->max_marks > 0) {
                    $maxMarks = (float) $ass->max_marks;
                } else {
                    $maxMarks = $gradeScaleMaxMarks;
                }
                
                // Always add max_marks to total (for ALL subjects, even if absent)
                $maxTotal += $maxMarks;
                
                if ($status === 'absent') {
                    $grade = 'ABS';
                } elseif ($status === 'exempted') {
                    $grade = 'EXEMPT';
                } elseif ($marks) {
                    $marksObtained = (float) $marks->marks_obtained;
                    $percentage = $maxMarks > 0 ? round(($marksObtained / $maxMarks) * 100, 1) : 0;
                    
                    // Calculate grade using raw marks (like Examination Results Report)
                    // The grade scale uses raw marks, not percentage
                    $grade = $marks->grade ?? $this->calculateGrade($marksObtained, $gradeScale);
                    
                    // Calculate class rank for this subject
                    $classRank = $this->calculateSubjectRank($ass->id, $student->id, $allStudents);
                    
                    $studentTotal += $marksObtained;
                    $subjectCount++; // Count subjects with marks
                }
                
                $subjects[] = [
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->name,
                    'subject_short_name' => $subject->short_name ?? $subject->name,
                    'marks_obtained' => $marksObtained,
                    'max_marks' => $maxMarks, // Always include max_marks from grade scale or assignment
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'class_rank' => $classRank,
                    'status' => $status,
                ];
            }
            
            // Calculate overall average, grade, and position
            // Use same calculation as Examination Results Report: average of raw marks, not percentage
            $averageRawMarks = $subjectCount > 0 ? round($studentTotal / $subjectCount, 2) : 0;
            $averagePercentage = $maxTotal > 0 ? round(($studentTotal / $maxTotal) * 100, 1) : 0;
            
            // Calculate grade using raw marks average (like Examination Results Report)
            $overallGrade = $this->calculateGrade($averageRawMarks, $gradeScale);
            // Get remark from grade scale based on the grade (from marks grade, not calculated)
            $remark = $this->getRemark($overallGrade, $gradeScale);
            
            // Calculate overall position in class
            $position = $this->calculateOverallPosition(
                $examTypeId, 
                $academicYearId, 
                $classId, 
                $streamId, 
                $student->id,
                $studentTotal
            );
            
            // Get exam date if available
            $examDate = $assignment && $assignment->exam_date 
                ? $assignment->exam_date->format('Y-m-d') 
                : null;
            
            // Calculate additional statistics
            $totalStudentsInClass = count($allStudents);
            $positionNumber = is_string($position) && strpos($position, '/') !== false 
                ? (int) explode('/', $position)[0] 
                : (is_numeric($position) ? (int) $position : null);
            $totalStudents = is_string($position) && strpos($position, '/') !== false 
                ? (int) explode('/', $position)[1] 
                : $totalStudentsInClass;
            
            return [
                'exam_id' => $examTypeId,
                'exam_type' => $examType,
                'exam_type_id' => $examTypeId,
                'academic_year' => $academicYear,
                'academic_year_id' => $academicYearId,
                'exam_date' => $examDate,
                'class' => $assignment && $assignment->classe ? $assignment->classe->name : ($student->class ? $student->class->name : null),
                'stream' => $assignment && $assignment->stream ? $assignment->stream->name : ($student->stream ? $student->stream->name : null),
                'average' => $averagePercentage, // Percentage for display
                'average_raw_marks' => $averageRawMarks, // Raw marks average (like Examination Results Report)
                'total_marks' => (float) $studentTotal,
                'max_marks' => (float) $maxTotal,
                'grade' => $overallGrade,
                'remark' => $remark,
                'position' => $position, // Format: "1/30" or just number
                'position_number' => $positionNumber, // Just the position number (e.g., 1)
                'total_students' => $totalStudents, // Total students in class/stream
                'subjects' => $subjects,
                'subjects_count' => count($subjects),
                'performance_summary' => [
                    'total_subjects' => count($subjects),
                    'subjects_passed' => count(array_filter($subjects, function($s) {
                        $grade = $s['grade'] ?? '';
                        return !in_array($grade, ['ABS', 'EXEMPT', 'E', 'F']);
                    })),
                    'subjects_failed' => count(array_filter($subjects, function($s) {
                        $grade = $s['grade'] ?? '';
                        return in_array($grade, ['E', 'F']);
                    })),
                    'subjects_absent' => count(array_filter($subjects, function($s) {
                        return ($s['status'] ?? '') === 'absent' || ($s['grade'] ?? '') === 'ABS';
                    })),
                ],
            ];
        })->sortByDesc(function ($exam) {
            // Sort by academic year and exam type
            return ($exam['academic_year'] ?? '') . '|' . ($exam['exam_type'] ?? '');
        })->values();

        return response()->json([
            'success' => true,
            'data' => $exams,
        ], 200);
    }
    
    /**
     * Calculate grade from marks (raw marks, not percentage)
     * This matches how Examination Results Report calculates grades
     * The Examination Results Report uses: average = total / subjectCount (raw marks)
     * Then uses grade scale to determine grade based on raw marks average
     */
    private function calculateGrade($marks, $gradeScale = null)
    {
        // Use grade scale if available (same as Examination Results Report)
        // Grade scale uses getGradeForMark which checks min_marks <= mark <= max_marks
        if ($gradeScale) {
            $grade = $gradeScale->getGradeForMark($marks);
            if ($grade) {
                return $grade->grade_letter;
            }
        }
        
        // Fallback to default grading (using raw marks thresholds)
        // This matches the default calculateGrade in SchoolReportsController
        if ($marks >= 90) return 'A';
        if ($marks >= 80) return 'B';
        if ($marks >= 70) return 'C';
        if ($marks >= 60) return 'D';
        return 'E';
    }
    
    /**
     * Get remark from grade scale based on grade letter (from marks grade)
     * Do not calculate/fix remarks - get them directly from the grade scale
     */
    private function getRemark($grade, $gradeScale = null)
    {
        // Get remarks directly from grade scale based on the grade letter
        if ($gradeScale && $gradeScale->grades) {
            $gradeObj = $gradeScale->grades->where('grade_letter', $grade)->first();
            if ($gradeObj && $gradeObj->remarks) {
                return $gradeObj->remarks;
            }
        }
        
        // Only use fallback if grade scale doesn't have the grade or remarks
        $remarks = [
            'A' => 'EXCELLENT',
            'B' => 'VERY GOOD',
            'C' => 'AVERAGE',
            'D' => 'BELOW AVERAGE',
            'E' => 'UNSATISFACTORY',
            'ABS' => 'ABSENT',
            'EXEMPT' => 'EXEMPTED',
        ];
        
        return $remarks[$grade] ?? 'UNKNOWN';
    }
    
    /**
     * Calculate subject rank in class
     */
    private function calculateSubjectRank($assignmentId, $studentId, $allStudents)
    {
        $marks = \App\Models\SchoolExamMark::where('exam_class_assignment_id', $assignmentId)
            ->whereIn('student_id', $allStudents)
            ->get();
        
        $registrations = \App\Models\SchoolExamRegistration::where('exam_class_assignment_id', $assignmentId)
            ->whereIn('student_id', $allStudents)
            ->where('status', '!=', 'absent')
            ->pluck('student_id')
            ->toArray();
        
        $validMarks = [];
        foreach ($marks as $mark) {
            if (in_array($mark->student_id, $registrations)) {
                $percentage = $mark->max_marks > 0 
                    ? round(($mark->marks_obtained / $mark->max_marks) * 100, 1) 
                    : 0;
                $validMarks[] = [
                    'student_id' => $mark->student_id,
                    'percentage' => $percentage,
                ];
            }
        }
        
        usort($validMarks, function($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });
        
        foreach ($validMarks as $index => $markData) {
            if ($markData['student_id'] == $studentId) {
                return ($index + 1) . '/' . count($validMarks);
            }
        }
        
        return '-';
    }
    
    /**
     * Calculate overall position in class
     */
    private function calculateOverallPosition($examTypeId, $academicYearId, $classId, $streamId, $studentId, $studentTotal)
    {
        // Get all assignments for this exam type, academic year, class, and stream
        // Handle NULL stream_id in assignments - if assignment has NULL stream_id, it applies to all streams
        $assignments = \App\Models\ExamClassAssignment::where('exam_type_id', $examTypeId)
            ->where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->where(function($query) use ($streamId) {
                $query->whereNull('stream_id')
                      ->orWhere('stream_id', $streamId);
            });
        
        $assignmentIds = $assignments->pluck('id')->toArray();
        
        if (empty($assignmentIds)) {
            return '-';
        }
        
        // Get all students in class/stream
        $students = \App\Models\School\Student::where('class_id', $classId)
            ->where('status', 'active');
        
        if ($streamId) {
            $students->where('stream_id', $streamId);
        }
        
        $allStudentIds = $students->pluck('id')->toArray();
        
        // Calculate total marks for each student
        $studentTotals = [];
        foreach ($allStudentIds as $sid) {
            $marks = \App\Models\SchoolExamMark::whereIn('exam_class_assignment_id', $assignmentIds)
                ->where('student_id', $sid)
                ->get();
            
            $total = $marks->sum('marks_obtained');
            
            // Check if student was absent from all exams
            $registrations = \App\Models\SchoolExamRegistration::whereIn('exam_class_assignment_id', $assignmentIds)
                ->where('student_id', $sid)
                ->where('status', 'absent')
                ->count();
            
            // Only include students who took at least one exam
            if ($registrations < count($assignmentIds)) {
                $studentTotals[] = [
                    'student_id' => $sid,
                    'total' => $total,
                ];
            }
        }
        
        // Sort by total descending
        usort($studentTotals, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        // Find position
        foreach ($studentTotals as $index => $data) {
            if ($data['student_id'] == $studentId) {
                return ($index + 1) . '/' . count($studentTotals);
            }
        }
        
        return '-';
    }

    /**
     * Calculate performance summary by gender
     */
    private function calculatePerformanceSummaryByGender($examTypeId, $academicYearId, $classId, $streamId)
    {
        // Get all assignments for this exam
        $assignments = \App\Models\ExamClassAssignment::where('exam_type_id', $examTypeId)
            ->where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->where(function($query) use ($streamId) {
                $query->whereNull('stream_id')
                      ->orWhere('stream_id', $streamId);
            })
            ->pluck('id')
            ->toArray();

        if (empty($assignments)) {
            return [
                'girls' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'total' => 0],
                'boys' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'total' => 0],
                'total' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'total' => 0],
            ];
        }

        // Get all students in class/stream
        $students = \App\Models\School\Student::where('class_id', $classId)
            ->where('status', 'active');
        
        if ($streamId) {
            $students->where('stream_id', $streamId);
        }
        
        $allStudents = $students->get();
        
        // Get grade scale
        $gradeScale = \App\Models\SchoolGradeScale::where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->first();

        $summary = [
            'girls' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'total' => 0],
            'boys' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'total' => 0],
            'total' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'total' => 0],
        ];

        foreach ($allStudents as $student) {
            // Calculate student's total marks
            $marks = \App\Models\SchoolExamMark::whereIn('exam_class_assignment_id', $assignments)
                ->where('student_id', $student->id)
                ->get();
            
            $totalMarks = $marks->sum('marks_obtained');
            $totalMaxMarks = $marks->sum('max_marks');
            
            // Check if student took the exam
            $registrations = \App\Models\SchoolExamRegistration::whereIn('exam_class_assignment_id', $assignments)
                ->where('student_id', $student->id)
                ->where('status', '!=', 'absent')
                ->count();
            
            if ($registrations == 0) continue; // Skip if didn't take exam
            
            // Calculate average
            $subjectCount = $marks->count();
            $average = $subjectCount > 0 ? ($totalMarks / $subjectCount) : 0;
            
            // Get grade
            $grade = $this->calculateGrade($average, $gradeScale);
            
            // Determine gender
            $gender = strtolower($student->gender ?? 'unknown');
            $genderKey = ($gender == 'female' || $gender == 'f') ? 'girls' : 'boys';
            
            // Count by grade (only A, B, C, D)
            if (in_array($grade, ['A', 'B', 'C', 'D'])) {
                $summary[$genderKey][$grade]++;
                $summary[$genderKey]['total']++;
                $summary['total'][$grade]++;
                $summary['total']['total']++;
            }
        }

        return $summary;
    }

    /**
     * Get student assignments/homework
     * 
     * @param Request $request
     * @param int $studentId
     * @return JsonResponse
     */
    public function getStudentAssignments(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        // Verify the student belongs to this guardian
        $student = $guardian->students()
            ->where('students.id', $studentId)
            ->with(['class', 'stream', 'academicYear'])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied.',
            ], 404);
        }

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);
        $academicYearId = $activeAcademicYear ? $activeAcademicYear->id : $student->academic_year_id;

        // Get assignments for the student's class and stream using Assignment model
        $query = \App\Models\School\Assignment::where('academic_year_id', $academicYearId)
            ->where('status', 'published') // Only published assignments
            ->whereHas('assignmentClasses', function($q) use ($student) {
                $q->where('class_id', $student->class_id)
                  ->where(function($streamQ) use ($student) {
                      $streamQ->whereNull('stream_id')
                              ->orWhere('stream_id', $student->stream_id);
                  });
            })
            ->whereNotNull('due_date') // Only assignments with due dates
            ->with([
                'subject', 
                'teacher', 
                'creator', 
                'assignmentClasses.classe', 
                'assignmentClasses.stream', 
                'attachments' => function($q) {
                    $q->orderBy('sort_order', 'asc');
                }
            ])
            ->orderBy('due_date', 'asc');

        $allAssignments = $query->get();

        $now = now();
        $tomorrow = now()->addDay();
        $twoDaysFromNow = now()->addDays(2);

        $upcoming = [];
        $dueSoon = [];
        $submitted = [];
        $marked = [];
        $overdue = [];

        foreach ($allAssignments as $assignment) {
            $dueDate = $assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date) : null;
            if (!$dueDate) continue;

            // Combine due_date and due_time if available
            if ($assignment->due_time) {
                $timeParts = explode(':', $assignment->due_time);
                if (count($timeParts) >= 2) {
                    $dueDate->setTime((int)$timeParts[0], (int)$timeParts[1]);
                }
            }

            // Check if student has submitted (check assignment submission)
            $isSubmitted = false;
            $isMarked = false;
            $score = null;
            $grade = null;
            $feedback = null;
            $marksObtained = null;
            $totalMarks = $assignment->total_marks ?? 0;

            // Check if there's a submission
            $submission = \App\Models\School\AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('student_id', $studentId)
                ->first();

            if ($submission) {
                $isSubmitted = true;
                
                // Check if marked (has marks)
                if ($submission->marks_obtained !== null) {
                    $isMarked = true;
                    $marksObtained = $submission->marks_obtained;
                    $score = $marksObtained . '/' . $totalMarks;
                    
                    // Calculate grade based on percentage
                    $percentage = $submission->percentage ?? (($marksObtained / $totalMarks) * 100);
                    if ($percentage >= 90) $grade = 'A';
                    elseif ($percentage >= 80) $grade = 'B';
                    elseif ($percentage >= 70) $grade = 'C';
                    elseif ($percentage >= 60) $grade = 'D';
                    else $grade = 'F';
                    
                    $feedback = $submission->teacher_comments ?? $assignment->instructions;
                }
            }

            // Get all attachments with URLs - ensure relationship is loaded
            $attachments = [];
            // Reload attachments if not already loaded
            if (!$assignment->relationLoaded('attachments')) {
                $assignment->load('attachments');
            }
            
            if ($assignment->attachments && $assignment->attachments->isNotEmpty()) {
                foreach ($assignment->attachments as $attachment) {
                    // Get full URL for mobile app
                    $fileUrl = null;
                    if ($attachment->file_path) {
                        // Ensure we have a proper file path
                        $filePath = $attachment->file_path;
                        // Remove leading slash if exists
                        $filePath = ltrim($filePath, '/');
                        // Remove 'storage/' prefix if it exists (we'll add it back)
                        $filePath = preg_replace('#^storage/#', '', $filePath);
                        // Generate full URL
                        $fileUrl = url('storage/' . $filePath);
                    } elseif (method_exists($attachment, 'getUrlAttribute')) {
                        // Try using the model's URL accessor if available
                        $fileUrl = $attachment->url;
                    }
                    
                    if ($fileUrl) {
                        $attachments[] = [
                            'id' => $attachment->id,
                            'name' => $attachment->original_name ?? $attachment->file_name ?? 'Document',
                            'url' => $fileUrl,
                            'size' => $attachment->file_size ?? 0,
                        ];
                    }
                }
            }

            // Format assignment data
            $assignmentData = [
                'id' => $assignment->id,
                'assignment_id' => $assignment->assignment_id,
                'subject' => $assignment->subject ? $assignment->subject->name : 'Unknown',
                'title' => $assignment->title ?: ($assignment->subject ? $assignment->subject->name . ' Assignment' : 'Assignment'),
                'description' => $assignment->description,
                'instructions' => $assignment->instructions,
                'due' => $dueDate->format('d M Y'),
                'due_date' => $dueDate->format('Y-m-d'), // Date only (without time)
                'due_datetime' => $dueDate->format('Y-m-d H:i:s'), // Full datetime
                'due_time' => $assignment->due_time, // Time string (HH:mm)
                'due_timestamp' => $dueDate->timestamp, // Unix timestamp for accurate parsing
                'assigned_date' => $assignment->date_assigned ? \Carbon\Carbon::parse($assignment->date_assigned)->format('Y-m-d') : null,
                'status' => $assignment->status,
                'type' => $assignment->type,
                'total_marks' => $totalMarks,
                'attachments' => $attachments,
                'marks_obtained' => $marksObtained,
                'score' => $isMarked ? $score : null,
                'grade' => $isMarked ? $grade : null,
                'feedback' => $isMarked ? $feedback : null,
            ];

            // Calculate days left
            $daysLeft = $now->diffInDays($dueDate, false);
            $hoursLeft = $now->diffInHours($dueDate, false);

            if ($isMarked) {
                // Marked assignments
                $marked[] = array_merge($assignmentData, [
                    'score' => $score,
                    'grade' => $grade,
                    'feedback' => $feedback,
                ]);
            } elseif ($isSubmitted) {
                // Submitted but not marked
                $submitted[] = array_merge($assignmentData, [
                    'score' => 'Awaiting Mark',
                ]);
            } elseif ($dueDate->isPast()) {
                // Overdue
                $daysOverdue = $now->diffInDays($dueDate);
                $overdue[] = array_merge($assignmentData, [
                    'days' => $daysOverdue == 0 ? 'Due today' : ($daysOverdue == 1 ? '1 day overdue' : "$daysOverdue days overdue"),
                    'action' => 'Submit now',
                ]);
            } elseif ($hoursLeft <= 12) {
                // Due soon (within 12 hours)
                $dueSoon[] = array_merge($assignmentData, [
                    'dueIn' => $hoursLeft <= 1 ? 'Due in less than 1 hour' : "Due in $hoursLeft hours",
                ]);
            } elseif ($daysLeft <= 7) {
                // Upcoming (within 7 days)
                $upcoming[] = array_merge($assignmentData, [
                    'daysLeft' => $daysLeft == 0 ? 'Due today' : ($daysLeft == 1 ? '1 day left' : "$daysLeft days left"),
                ]);
            } else {
                // Still upcoming but more than 7 days
                $upcoming[] = array_merge($assignmentData, [
                    'daysLeft' => "$daysLeft days left",
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'upcoming' => $upcoming,
                'due_soon' => $dueSoon,
                'submitted' => $submitted,
                'marked' => $marked,
                'overdue' => $overdue,
            ],
        ], 200);
    }

    /**
     * Get student fees
     * 
     * @param Request $request
     * @param int $studentId
     * @return JsonResponse
     */
    public function getStudentFees(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        // Verify the student belongs to this guardian
        $student = $guardian->students()->where('students.id', $studentId)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied.',
            ], 404);
        }

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);

        // Get fee invoices for the student - filter by active academic year only
        $query = \App\Models\FeeInvoice::where('student_id', $studentId)
            ->with(['feeGroup', 'academicYear']);

        // Filter by active academic year if available
        if ($activeAcademicYear) {
            $query->where('academic_year_id', $activeAcademicYear->id);
        }

        $feeInvoices = $query->orderBy('issue_date', 'desc')->get();

        $totalAmount = $feeInvoices->sum('total_amount');
        $paidAmount = $feeInvoices->sum('paid_amount');
        $dueAmount = $totalAmount - $paidAmount;

        // Check if LIPISHA is enabled
        $lipishaEnabled = \App\Services\LipishaService::isEnabled();

        $fees = $feeInvoices->map(function ($invoice) use ($lipishaEnabled) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'fee_group' => $invoice->feeGroup ? $invoice->feeGroup->name : null,
                'period' => $invoice->period,
                'academic_year' => $invoice->academicYear ? $invoice->academicYear->year_name : null,
                'subtotal' => (float) $invoice->subtotal,
                'transport_fare' => (float) $invoice->transport_fare,
                'discount_amount' => (float) $invoice->discount_amount,
                'total_amount' => (float) $invoice->total_amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'due_amount' => (float) ($invoice->total_amount - $invoice->paid_amount),
                'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                'issue_date' => $invoice->issue_date ? $invoice->issue_date->format('Y-m-d') : null,
                'status' => $invoice->status,
                'lipisha_control_number' => ($lipishaEnabled && $invoice->lipisha_control_number) ? $invoice->lipisha_control_number : null,
            ];
        });

        // Get opening balance
        $openingBalance = null;
        if ($activeAcademicYear) {
            $openingBalanceRecord = \App\Models\School\StudentFeeOpeningBalance::where('student_id', $studentId)
                ->where('academic_year_id', $activeAcademicYear->id)
                ->first();
            
            if ($openingBalanceRecord) {
                $openingBalance = [
                    'amount' => (float) $openingBalanceRecord->amount,
                    'paid_amount' => (float) $openingBalanceRecord->paid_amount,
                    'balance_due' => (float) $openingBalanceRecord->balance_due,
                    'opening_date' => $openingBalanceRecord->opening_date ? $openingBalanceRecord->opening_date->format('Y-m-d') : null,
                    'lipisha_control_number' => ($lipishaEnabled && $openingBalanceRecord->lipisha_control_number) ? $openingBalanceRecord->lipisha_control_number : null,
                ];
            }
        }

        // Get prepaid account balance - always return a value (0 if no account exists)
        $prepaidAccount = \App\Models\School\StudentPrepaidAccount::where('student_id', $studentId)->first();
        $prepaidBalance = $prepaidAccount ? (float) $prepaidAccount->credit_balance : 0.0; // Always return a number, 0 if no account

        return response()->json([
            'success' => true,
            'data' => [
                'invoices' => $fees,
                'summary' => [
                    'total_amount' => (float) $totalAmount,
                    'paid_amount' => (float) $paidAmount,
                    'due_amount' => (float) $dueAmount,
                    'payment_percentage' => $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 2) : 0,
                    'opening_balance' => $openingBalance,
                    'prepaid_balance' => $prepaidBalance,
                ],
                'lipisha_enabled' => $lipishaEnabled,
            ],
        ], 200);
    }

    /**
     * Get prepaid account transactions for a student
     */
    public function getPrepaidAccountTransactions(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        // Verify the student belongs to this guardian
        $student = $guardian->students()->where('students.id', $studentId)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied.',
            ], 404);
        }

        // Get prepaid account
        $prepaidAccount = \App\Models\School\StudentPrepaidAccount::where('student_id', $studentId)->first();

        if (!$prepaidAccount) {
            return response()->json([
                'success' => true,
                'data' => [
                    'account' => null,
                    'transactions' => [],
                ],
            ], 200);
        }

        // Get transactions
        $transactions = $prepaidAccount->transactions()
            ->with(['feeInvoice'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type, // 'deposit', 'withdrawal', 'invoice_application'
                    'type_label' => $this->getTransactionTypeLabel($transaction->type),
                    'amount' => (float) $transaction->amount,
                    'balance_before' => (float) $transaction->balance_before,
                    'balance_after' => (float) $transaction->balance_after,
                    'reference' => $transaction->reference,
                    'invoice_number' => $transaction->feeInvoice ? $transaction->feeInvoice->invoice_number : null,
                    'notes' => $transaction->notes,
                    'created_at' => $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : null,
                    'created_at_formatted' => $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'account' => [
                    'id' => $prepaidAccount->id,
                    'credit_balance' => (float) $prepaidAccount->credit_balance,
                    'total_deposited' => (float) $prepaidAccount->total_deposited,
                    'total_used' => (float) $prepaidAccount->total_used,
                ],
                'transactions' => $transactions,
            ],
        ], 200);
    }

    /**
     * Get transaction type label in Swahili
     */
    private function getTransactionTypeLabel(string $type): string
    {
        return match($type) {
            'deposit' => 'Amana',
            'withdrawal' => 'Kutoa',
            'invoice_application' => 'Malipo ya Ada',
            default => $type,
        };
    }
    
    /**
     * Get detailed exam results for a specific exam
     * 
     * @param Request $request
     * @param int $studentId
     * @param int $examTypeId
     * @param int $academicYearId
     * @return JsonResponse
     */
    public function getExamDetails(Request $request, $studentId, $examTypeId, $academicYearId): JsonResponse
    {
        $guardian = $request->user();

        // Verify the student belongs to this guardian
        $student = $guardian->students()
            ->where('students.id', $studentId)
            ->with(['class', 'stream'])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied.',
            ], 404);
        }

        // Get exam data using the same logic as getStudentExams but for specific exam
        // Only show if exam type is published
        $examType = \App\Models\SchoolExamType::find($examTypeId);
        if (!$examType || !$examType->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Exam not found or not published.',
            ], 404);
        }

        $examRegistrations = \App\Models\SchoolExamRegistration::where('student_id', $studentId)
            ->whereHas('examClassAssignment', function($query) use ($examTypeId, $academicYearId) {
                $query->where('exam_type_id', $examTypeId)
                      ->where('academic_year_id', $academicYearId);
            })
            ->with([
                'examClassAssignment.subject', 
                'examClassAssignment.examType', 
                'examClassAssignment.academicYear',
                'examClassAssignment.classe',
                'examClassAssignment.stream'
            ])
            ->get();

        if ($examRegistrations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Exam not found.',
            ], 404);
        }

        $firstReg = $examRegistrations->first();
        $assignment = $firstReg->examClassAssignment;
        $classId = $assignment ? $assignment->class_id : $student->class_id;
        $streamId = $assignment ? $assignment->stream_id : $student->stream_id;
        
        // Get grade scale
        $gradeScale = \App\Models\SchoolGradeScale::where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->first();
        
        // Get max_marks from grade scale (fallback if exam_class_assignment doesn't have it)
        $gradeScaleMaxMarks = $gradeScale ? (float) $gradeScale->max_marks : 100.0; // Default to 100 if no grade scale
        
        $subjects = [];
        $studentTotal = 0;
        $maxTotal = 0; // Will accumulate max_marks for ALL subjects
        $subjectCount = 0; // Count of subjects with marks (for average calculation like Examination Results Report)
        
        foreach ($examRegistrations as $reg) {
            $ass = $reg->examClassAssignment;
            $subject = $ass ? $ass->subject : null;
            
            if (!$subject) continue;
            
            $marks = \App\Models\SchoolExamMark::where('student_id', $reg->student_id)
                ->where('exam_class_assignment_id', $reg->exam_class_assignment_id)
                ->first();
            
            $status = $reg->status ?? 'present';
            $marksObtained = null;
            $percentage = 0;
            $grade = null;
            $classRank = '-';
            
            // Get max_marks: use exam_class_assignment max_marks if available, otherwise use grade scale max_marks
            $maxMarks = 0;
            if ($ass && $ass->max_marks && $ass->max_marks > 0) {
                $maxMarks = (float) $ass->max_marks;
            } else {
                $maxMarks = $gradeScaleMaxMarks;
            }
            
            // Always add max_marks to total (for ALL subjects, even if absent)
            $maxTotal += $maxMarks;
            
            if ($status === 'absent') {
                $grade = 'ABS';
            } elseif ($status === 'exempted') {
                $grade = 'EXEMPT';
            } elseif ($marks) {
                $marksObtained = (float) $marks->marks_obtained;
                $percentage = $maxMarks > 0 ? round(($marksObtained / $maxMarks) * 100, 1) : 0;
                
                // Calculate grade using raw marks (like Examination Results Report)
                $grade = $marks->grade ?? $this->calculateGrade($marksObtained, $gradeScale);
                
                $allStudentIds = \App\Models\School\Student::where('class_id', $classId)
                    ->where('status', 'active')
                    ->when($streamId, function($q) use ($streamId) {
                        $q->where('stream_id', $streamId);
                    })
                    ->pluck('id')->toArray();
                
                $classRank = $this->calculateSubjectRank($ass->id, $student->id, $allStudentIds);
                
                $studentTotal += $marksObtained;
                $subjectCount++; // Count subjects with marks
            }
            
            $subjects[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->name,
                'subject_short_name' => $subject->short_name ?? $subject->name,
                'marks_obtained' => $marksObtained,
                'max_marks' => $maxMarks, // Always include max_marks from grade scale or assignment
                'percentage' => $percentage,
                'grade' => $grade,
                'class_rank' => $classRank,
                'status' => $status,
            ];
        }
        
        // Calculate overall average, grade, and position
        // Use same calculation as Examination Results Report: average of raw marks, not percentage
        $averageRawMarks = $subjectCount > 0 ? round($studentTotal / $subjectCount, 2) : 0;
        $averagePercentage = $maxTotal > 0 ? round(($studentTotal / $maxTotal) * 100, 1) : 0;
        
        // Calculate grade using raw marks average (like Examination Results Report)
        $overallGrade = $this->calculateGrade($averageRawMarks, $gradeScale);
        $remark = $this->getRemark($overallGrade, $gradeScale);
        $position = $this->calculateOverallPosition(
            $examTypeId, 
            $academicYearId, 
            $classId, 
            $streamId, 
            $student->id,
            $studentTotal
        );
        
        $examDate = $assignment && $assignment->exam_date 
            ? $assignment->exam_date->format('Y-m-d') 
            : null;
        
        // Calculate additional statistics
        $allStudentIds = \App\Models\School\Student::where('class_id', $classId)
            ->where('status', 'active')
            ->when($streamId, function($q) use ($streamId) {
                $q->where('stream_id', $streamId);
            })
            ->pluck('id')->toArray();
        $totalStudentsInClass = count($allStudentIds);
        $positionNumber = is_string($position) && strpos($position, '/') !== false 
            ? (int) explode('/', $position)[0] 
            : (is_numeric($position) ? (int) $position : null);
        $totalStudents = is_string($position) && strpos($position, '/') !== false 
            ? (int) explode('/', $position)[1] 
            : $totalStudentsInClass;
        
        // Calculate performance summary by gender
        $performanceSummary = $this->calculatePerformanceSummaryByGender(
            $examTypeId,
            $academicYearId,
            $classId,
            $streamId
        );
        
        return response()->json([
            'success' => true,
            'data' => [
                'exam_id' => $examTypeId,
                'exam_type' => $assignment && $assignment->examType ? $assignment->examType->name : 'Standard',
                'exam_type_id' => $examTypeId,
                'academic_year' => $assignment && $assignment->academicYear ? $assignment->academicYear->year_name : 'Unknown',
                'academic_year_id' => $academicYearId,
                'exam_date' => $examDate,
                'class' => $assignment && $assignment->classe ? $assignment->classe->name : ($student->class ? $student->class->name : null),
                'stream' => $assignment && $assignment->stream ? $assignment->stream->name : ($student->stream ? $student->stream->name : null),
                'average' => $averagePercentage, // Percentage for display
                'average_raw_marks' => $averageRawMarks, // Raw marks average (like Examination Results Report)
                'total_marks' => (float) $studentTotal,
                'max_marks' => (float) $maxTotal,
                'grade' => $overallGrade,
                'remark' => $remark,
                'position' => $position, // Format: "1/30" or just number
                'position_number' => $positionNumber, // Just the position number (e.g., 1)
                'total_students' => $totalStudents, // Total students in class/stream
                'subjects' => $subjects,
                'subjects_count' => count($subjects),
                'performance_summary' => [
                    'total_subjects' => count($subjects),
                    'subjects_passed' => count(array_filter($subjects, function($s) {
                        $grade = $s['grade'] ?? '';
                        return !in_array($grade, ['ABS', 'EXEMPT', 'E', 'F']);
                    })),
                    'subjects_failed' => count(array_filter($subjects, function($s) {
                        $grade = $s['grade'] ?? '';
                        return in_array($grade, ['E', 'F']);
                    })),
                    'subjects_absent' => count(array_filter($subjects, function($s) {
                        return ($s['status'] ?? '') === 'absent' || ($s['grade'] ?? '') === 'ABS';
                    })),
                ],
                'performance_by_gender' => $performanceSummary,
            ],
        ], 200);
    }

    /**
     * Get student attendance records
     * 
     * @param Request $request
     * @param int $studentId
     * @return JsonResponse
     */
    public function getStudentAttendance(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        // Verify the student belongs to this guardian
        $student = $guardian->students()->where('students.id', $studentId)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied.',
            ], 404);
        }

        // Get query parameters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $limit = $request->input('limit', 30); // Default to 30 records

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);

        // Build query for attendance records - filter by active academic year only
        $query = \App\Models\School\StudentAttendance::where('student_id', $studentId)
            ->with(['attendanceSession' => function($q) use ($activeAcademicYear) {
                $q->with(['class', 'stream', 'academicYear']);
                // Filter by active academic year if available
                if ($activeAcademicYear) {
                    $q->where('academic_year_id', $activeAcademicYear->id);
                }
            }])
            ->orderBy('created_at', 'desc');

        // Apply date filters if provided
        if ($startDate) {
            $query->whereHas('attendanceSession', function($q) use ($startDate) {
                $q->where('session_date', '>=', $startDate);
            });
        }

        if ($endDate) {
            $query->whereHas('attendanceSession', function($q) use ($endDate) {
                $q->where('session_date', '<=', $endDate);
            });
        }

        // If no date filters, get recent records (last 30 days)
        if (!$startDate && !$endDate) {
            $query->whereHas('attendanceSession', function($q) {
                $q->where('session_date', '>=', now()->subDays(30));
            });
        }

        $attendances = $query->limit($limit)->get();

        // Format attendance data
        $attendanceData = $attendances->map(function ($attendance) {
            $session = $attendance->attendanceSession;
            $status = $attendance->status;
            
            // Determine badge and color based on status
            $badge = null;
            $badgeColor = null;
            $borderColor = ['r' => 19, 'g' => 127, 'b' => 236]; // Blue
            
            if ($status === 'present') {
                $badge = null; // No badge for present
                $borderColor = ['r' => 19, 'g' => 127, 'b' => 236]; // Blue
            } elseif ($status === 'absent') {
                $badge = 'Absent';
                $badgeColor = ['r' => 255, 'g' => 152, 'b' => 0]; // Orange
                $borderColor = ['r' => 255, 'g' => 152, 'b' => 0]; // Orange
            } elseif ($status === 'late') {
                $badge = 'Late';
                $badgeColor = ['r' => 255, 'g' => 235, 'b' => 59]; // Yellow
                $borderColor = ['r' => 255, 'g' => 235, 'b' => 59]; // Yellow
            } elseif ($status === 'sick') {
                $badge = 'Sick';
                $badgeColor = ['r' => 255, 'g' => 152, 'b' => 0]; // Orange
                $borderColor = ['r' => 255, 'g' => 152, 'b' => 0]; // Orange
            }

            // Format time display
            $timeDisplay = '';
            if ($attendance->time_in) {
                $timeIn = \Carbon\Carbon::parse($attendance->time_in);
                $timeDisplay = 'Arrived at ' . $timeIn->format('g:i A');
            } elseif ($status === 'absent' && $attendance->notes) {
                $timeDisplay = $attendance->notes;
            } else {
                $timeDisplay = ucfirst($status);
            }

            // Determine section (Today, Yesterday, Earlier this week)
            $sessionDate = $session ? \Carbon\Carbon::parse($session->session_date) : null;
            $section = 'Earlier this week';
            $daysDiff = 0;
            if ($sessionDate) {
                $daysDiff = now()->startOfDay()->diffInDays($sessionDate->startOfDay(), false);
                if ($daysDiff == 0) {
                    $section = 'Today';
                } elseif ($daysDiff == 1) {
                    $section = 'Yesterday';
                } elseif ($daysDiff <= 7) {
                    $section = 'Earlier this week';
                }
            }

            return [
                'id' => $attendance->id,
                'date' => $sessionDate ? $sessionDate->format('d') : '',
                'month' => $sessionDate ? $sessionDate->format('M') : '',
                'full_date' => $sessionDate ? $sessionDate->format('Y-m-d') : null,
                'status' => ucfirst($status),
                'time' => $timeDisplay,
                'time_in' => $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i:s') : null,
                'time_out' => $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i:s') : null,
                'badge' => $badge,
                'badge_color' => $badgeColor,
                'border_color' => $borderColor,
                'section' => $section,
                'icon' => $status === 'present' && $daysDiff > 1 ? 'check_circle' : null,
                'note' => $attendance->notes,
                'class' => $session && $session->class ? $session->class->name : null,
                'stream' => $session && $session->stream ? $session->stream->name : null,
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => $attendanceData,
        ], 200);
    }

    /**
     * Get student attendance statistics
     * 
     * @param Request $request
     * @param int $studentId
     * @return JsonResponse
     */
    public function getStudentAttendanceStats(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        // Verify the student belongs to this guardian
        $student = $guardian->students()
            ->where('students.id', $studentId)
            ->with(['class', 'stream', 'academicYear'])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied.',
            ], 404);
        }

        // Get date range (default to current academic year or last 30 days)
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // If no dates provided, use current academic year or last 30 days
        if (!$startDate || !$endDate) {
            if ($student->academic_year_id) {
                $academicYear = \App\Models\School\AcademicYear::find($student->academic_year_id);
                if ($academicYear) {
                    // Use academic year dates if available
                    $startDate = $academicYear->start_date ?? now()->subDays(30)->format('Y-m-d');
                    $endDate = $academicYear->end_date ?? now()->format('Y-m-d');
                } else {
                    $startDate = now()->subDays(30)->format('Y-m-d');
                    $endDate = now()->format('Y-m-d');
                }
            } else {
                $startDate = now()->subDays(30)->format('Y-m-d');
                $endDate = now()->format('Y-m-d');
            }
        }

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);

        // Get all attendance records for the date range - filter by active academic year only
        $query = \App\Models\School\StudentAttendance::where('student_id', $studentId)
            ->whereHas('attendanceSession', function($q) use ($startDate, $endDate, $activeAcademicYear) {
                $q->whereBetween('session_date', [$startDate, $endDate]);
                // Filter by active academic year if available
                if ($activeAcademicYear) {
                    $q->where('academic_year_id', $activeAcademicYear->id);
                }
            });

        $attendances = $query->get();

        // Calculate statistics
        $totalDays = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $sickCount = $attendances->where('status', 'sick')->count();

        // Calculate attendance rate
        $attendanceRate = $totalDays > 0 ? round(($presentCount / $totalDays) * 100, 1) : 0.0;

        // Get unexcused absences (absences without notes)
        $unexcusedAbsences = $attendances->where('status', 'absent')
            ->where(function($q) {
                $q->whereNull('notes')->orWhere('notes', '');
            })
            ->count();

        // Get last tardy date
        $lastTardy = $attendances->where('status', 'late')
            ->sortByDesc(function($attendance) {
                return $attendance->attendanceSession ? $attendance->attendanceSession->session_date : null;
            })
            ->first();

        $lastTardyDay = null;
        if ($lastTardy && $lastTardy->attendanceSession) {
            $lastTardyDay = \Carbon\Carbon::parse($lastTardy->attendanceSession->session_date)->format('D');
        }

        // Get weekly data for chart (last 7 days or current week)
        $weeklyStart = now()->startOfWeek();
        $weeklyEnd = now()->endOfWeek();
        
        $weeklyAttendances = \App\Models\School\StudentAttendance::where('student_id', $studentId)
            ->whereHas('attendanceSession', function($q) use ($weeklyStart, $weeklyEnd) {
                $q->whereBetween('session_date', [$weeklyStart->format('Y-m-d'), $weeklyEnd->format('Y-m-d')]);
            })
            ->with('attendanceSession')
            ->get()
            ->groupBy(function($attendance) {
                return $attendance->attendanceSession 
                    ? \Carbon\Carbon::parse($attendance->attendanceSession->session_date)->format('D')
                    : '';
            });

        // Map to days of week
        $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $weeklyData = [];
        
        foreach ($daysOfWeek as $day) {
            $dayAttendances = $weeklyAttendances->get($day, collect());
            $dayAttendance = $dayAttendances->first();
            
            if ($dayAttendance) {
                $status = $dayAttendance->status;
                $height = $status === 'present' ? 100.0 : ($status === 'late' ? 60.0 : 0.0);
                $color = $status === 'present' ? 'blue' : ($status === 'late' ? 'yellow' : 'orange');
            } else {
                // No attendance record for this day
                $height = 0.0;
                $color = 'orange';
            }
            
            $weeklyData[] = [
                'day' => $day,
                'height' => $height,
                'color' => $color,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'rate' => $attendanceRate,
                    'absences' => $absentCount,
                    'unexcused' => $unexcusedAbsences,
                    'tardies' => $lateCount,
                    'last_tardy' => $lastTardyDay,
                    'sick' => $sickCount,
                    'total_days' => $totalDays,
                    'present_count' => $presentCount,
                ],
                'weekly_data' => $weeklyData,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ],
        ], 200);
    }

    /**
     * Get all students for the authenticated parent
     */
    public function getStudents(Request $request): JsonResponse
    {
        $guardian = $request->user();
        
        $students = $guardian->students()
            ->with(['class', 'stream', 'academicYear'])
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'hash_id' => $student->hash_id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'full_name' => $student->first_name . ' ' . $student->last_name,
                    'admission_number' => $student->admission_number,
                    'class' => $student->class->name ?? 'N/A',
                    'stream' => $student->stream->name ?? 'N/A',
                    'academic_year' => $student->academicYear->year_name ?? 'N/A',
                    'gender' => $student->gender,
                    'date_of_birth' => $student->date_of_birth,
                    'photo' => $student->photo,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $students,
        ], 200);
    }

    /**
     * Get assignment details
     */
    public function getAssignmentDetails(Request $request, $studentId, $assignmentId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        $assignment = \App\Models\School\Assignment::with(['subject', 'teacher', 'assignmentClasses.classe', 'assignmentClasses.stream'])
            ->where('id', $assignmentId)
            ->first();

        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found'], 404);
        }

        $submission = \App\Models\School\AssignmentSubmission::where('assignment_id', $assignmentId)
            ->where('student_id', $studentId)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'assignment' => [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'instructions' => $assignment->instructions,
                    'type' => $assignment->type,
                    'subject' => $assignment->subject->name ?? 'N/A',
                    'teacher' => $assignment->teacher ? $assignment->teacher->first_name . ' ' . $assignment->teacher->last_name : 'N/A',
                    'date_assigned' => $assignment->date_assigned,
                    'due_date' => $assignment->due_date,
                    'due_time' => $assignment->due_time,
                    'total_marks' => $assignment->total_marks,
                    'submission_type' => $assignment->submission_type,
                ],
                'submission' => $submission ? [
                    'id' => $submission->id,
                    'submitted_at' => $submission->submitted_at,
                    'is_late' => $submission->is_late,
                    'status' => $submission->status,
                    'marks_obtained' => $submission->marks_obtained,
                    'percentage' => $submission->percentage,
                    'grade' => $submission->grade,
                    'remarks' => $submission->remarks,
                    'teacher_comments' => $submission->teacher_comments,
                ] : null,
            ],
        ], 200);
    }

    /**
     * Submit an assignment
     */
    public function submitAssignment(Request $request, $studentId, $assignmentId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        $request->validate([
            'submission_type' => 'required|in:written,online_upload,photo_upload',
            'submission_content' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $assignment = \App\Models\School\Assignment::find($assignmentId);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found'], 404);
        }

        // Check if already submitted
        $existingSubmission = \App\Models\School\AssignmentSubmission::where('assignment_id', $assignmentId)
            ->where('student_id', $studentId)
            ->first();

        if ($existingSubmission && !$assignment->resubmission_allowed) {
            return response()->json(['success' => false, 'message' => 'Assignment already submitted and resubmission is not allowed'], 400);
        }

        $isLate = now() > $assignment->due_date;

        $submission = \App\Models\School\AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
            ],
            [
                'class_id' => $student->class_id,
                'stream_id' => $student->stream_id,
                'submission_type' => $request->submission_type,
                'submission_content' => $request->submission_content,
                'submitted_at' => now(),
                'is_late' => $isLate,
                'status' => 'submitted',
                'notes' => $request->notes,
                'company_id' => $student->company_id,
                'branch_id' => $student->branch_id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Assignment submitted successfully',
            'data' => [
                'submission_id' => $submission->id,
                'is_late' => $isLate,
            ],
        ], 201);
    }

    /**
     * Get attendance calendar
     */
    public function getStudentAttendanceCalendar(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Get attendance records - filter by active academic year only
        $query = \App\Models\School\StudentAttendance::where('student_id', $studentId)
            ->whereHas('attendanceSession', function($q) use ($startDate, $endDate, $activeAcademicYear) {
                $q->whereBetween('session_date', [$startDate, $endDate]);
                // Filter by active academic year if available
                if ($activeAcademicYear) {
                    $q->where('academic_year_id', $activeAcademicYear->id);
                }
            })
            ->with('attendanceSession');

        $attendances = $query->get()
            ->map(function ($attendance) {
                return [
                    'date' => $attendance->attendanceSession->session_date->format('Y-m-d'),
                    'status' => $attendance->status,
                    'time_in' => $attendance->time_in,
                    'time_out' => $attendance->time_out,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $attendances,
        ], 200);
    }

    /**
     * Get student results
     */
    public function getStudentResults(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);
        $academicYearId = $activeAcademicYear ? $activeAcademicYear->id : ($request->get('academic_year_id', $student->academic_year_id));

        // Get all published exam types and results only
        $examTypes = \App\Models\SchoolExamType::where('company_id', $student->company_id)
            ->where('is_published', true)
            ->get();
        $results = [];

        foreach ($examTypes as $examType) {
            $examDetails = $this->getExamDetails($request, $studentId, $examType->id, $academicYearId);
            $examData = json_decode($examDetails->getContent(), true);
            
            if ($examData['success']) {
                $results[] = [
                    'exam_type' => $examType->name,
                    'exam_type_id' => $examType->id,
                    'data' => $examData['data'],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ], 200);
    }

    /**
     * Get results by exam type
     */
    public function getResultsByExamType(Request $request, $studentId, $examTypeId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);
        $academicYearId = $activeAcademicYear ? $activeAcademicYear->id : ($request->get('academic_year_id', $student->academic_year_id));

        return $this->getExamDetails($request, $studentId, $examTypeId, $academicYearId);
    }

    /**
     * Get student invoices
     */
    public function getStudentInvoices(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);

        // Get invoices - filter by active academic year only
        $query = \App\Models\FeeInvoice::where('student_id', $studentId)
            ->with(['feeGroup', 'academicYear']);

        // Filter by active academic year if available
        if ($activeAcademicYear) {
            $query->where('academic_year_id', $activeAcademicYear->id);
        }

        $invoices = $query->orderBy('issue_date', 'desc')->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'fee_group' => $invoice->feeGroup->name ?? 'N/A',
                    'academic_year' => $invoice->academicYear->year_name ?? 'N/A',
                    'issue_date' => $invoice->issue_date,
                    'due_date' => $invoice->due_date,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount ?? 0,
                    'balance_due' => $invoice->total_amount - ($invoice->paid_amount ?? 0),
                    'status' => $invoice->status,
                    'period' => $invoice->period,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ], 200);
    }

    /**
     * Get student payments
     */
    public function getStudentPayments(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);

        // Get payments - filter by active academic year only
        $query = \App\Models\Payment::whereHas('feeInvoice', function($q) use ($studentId, $activeAcademicYear) {
                $q->where('student_id', $studentId);
                // Filter by active academic year if available
                if ($activeAcademicYear) {
                    $q->where('academic_year_id', $activeAcademicYear->id);
                }
            })
            ->with(['feeInvoice']);

        $payments = $query->orderBy('payment_date', 'desc')->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_number' => $payment->feeInvoice->invoice_number ?? 'N/A',
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'reference' => $payment->reference,
                    'notes' => $payment->notes,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $payments,
        ], 200);
    }

    /**
     * Get student fee balance
     */
    public function getStudentFeeBalance(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);

        // Get fee totals - filter by active academic year only
        $invoiceQuery = \App\Models\FeeInvoice::where('student_id', $studentId);
        if ($activeAcademicYear) {
            $invoiceQuery->where('academic_year_id', $activeAcademicYear->id);
        }

        $totalInvoiced = $invoiceQuery->sum('total_amount');
        
        $paidQuery = \App\Models\FeeInvoice::where('student_id', $studentId);
        if ($activeAcademicYear) {
            $paidQuery->where('academic_year_id', $activeAcademicYear->id);
        }
        $totalPaid = $paidQuery->sum('paid_amount');

        $balance = $totalInvoiced - ($totalPaid ?? 0);

        // Get prepaid account balance if exists
        $prepaidAccount = \App\Models\School\StudentPrepaidAccount::where('student_id', $studentId)->first();
        $prepaidBalance = $prepaidAccount ? $prepaidAccount->credit_balance : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_invoiced' => $totalInvoiced,
                'total_paid' => $totalPaid ?? 0,
                'balance_due' => $balance,
                'prepaid_balance' => $prepaidBalance,
                'net_balance' => $balance - $prepaidBalance,
            ],
        ], 200);
    }

    /**
     * Make a payment
     */
    public function makePayment(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        $request->validate([
            'invoice_id' => 'required|exists:fee_invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:mpesa,bank,cash,other',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $invoice = \App\Models\FeeInvoice::where('id', $request->invoice_id)
            ->where('student_id', $studentId)
            ->first();

        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
        }

        // Payment creation logic would go here
        // This is a simplified version - you may need to integrate with payment gateways

        return response()->json([
            'success' => true,
            'message' => 'Payment request received. Processing...',
        ], 200);
    }

    /**
     * Get notifications
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $guardian = $request->user();
        $studentId = $request->query('student_id');

        $query = \App\Models\ParentNotification::where('parent_id', $guardian->id)
            ->orderBy('created_at', 'desc');

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $notifications = $query->paginate($request->get('per_page', 20));

        $notifications->getCollection()->transform(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'data' => $notification->data,
                'is_read' => $notification->is_read,
                'read_at' => $notification->read_at?->toIso8601String(),
                'created_at' => $notification->created_at->toIso8601String(),
                'student' => $notification->student ? [
                    'id' => $notification->student->id,
                    'name' => $notification->student->first_name . ' ' . $notification->student->last_name,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ], 200);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotifications(Request $request): JsonResponse
    {
        $guardian = $request->user();
        $studentId = $request->query('student_id');

        $query = \App\Models\ParentNotification::where('parent_id', $guardian->id)
            ->where('is_read', false);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $unreadCount = $query->count();

        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $unreadCount],
        ], 200);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(Request $request, $notificationId): JsonResponse
    {
        $guardian = $request->user();

        $notification = \App\Models\ParentNotification::where('id', $notificationId)
            ->where('parent_id', $guardian->id)
            ->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ], 200);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead(Request $request): JsonResponse
    {
        $guardian = $request->user();
        $studentId = $request->query('student_id');

        $query = \App\Models\ParentNotification::where('parent_id', $guardian->id)
            ->where('is_read', false);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $query->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ], 200);
    }

    /**
     * Get academic information
     */
    public function getAcademicInfo(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()
            ->where('students.id', $studentId)
            ->with(['class', 'stream', 'academicYear'])
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'class' => $student->class->name ?? 'N/A',
                'stream' => $student->stream->name ?? 'N/A',
                'academic_year' => $student->academicYear->year_name ?? 'N/A',
                'admission_number' => $student->admission_number,
                'enrollment_date' => $student->created_at,
            ],
        ], 200);
    }

    /**
     * Get timetable
     */
    public function getTimetable(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()
            ->where('students.id', $studentId)
            ->with(['class', 'stream', 'academicYear'])
            ->first();
            
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);
        $academicYearId = $activeAcademicYear ? $activeAcademicYear->id : $student->academic_year_id;

        // Find timetable for the student's class and stream
        // Try master timetable first, then fall back to class timetable
        $timetable = \App\Models\School\Timetable::where('company_id', $student->company_id)
            ->where(function ($query) use ($student) {
                $query->where('branch_id', $student->branch_id)
                      ->orWhereNull('branch_id');
            })
            ->where('academic_year_id', $academicYearId)
            ->where('class_id', $student->class_id)
            ->where(function ($query) use ($student) {
                $query->whereNull('stream_id')
                      ->orWhere('stream_id', $student->stream_id);
            })
            ->where('status', 'published')
            ->where(function ($query) {
                $query->where('timetable_type', 'master')
                      ->orWhere('timetable_type', 'class');
            })
            ->orderByRaw("CASE WHEN timetable_type = 'master' THEN 1 ELSE 2 END")
            ->first();

        if (!$timetable) {
            return response()->json([
                'success' => true,
                'data' => (object)[], // Return empty object instead of array for mobile app compatibility
                'message' => 'No timetable found for this student',
            ], 200);
        }

        // Get all timetable entries
        $entries = \App\Models\School\TimetableEntry::where('timetable_id', $timetable->id)
            ->with(['subject', 'teacher', 'period', 'room'])
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get();

        // Group by day of week
        $timetableData = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        foreach ($days as $day) {
            $dayEntries = $entries->where('day_of_week', $day)->sortBy('period_number');
            
            if ($dayEntries->isNotEmpty()) {
                $timetableData[$day] = $dayEntries->map(function ($entry) {
                    return [
                        'period_number' => $entry->period_number,
                        'period_name' => $entry->period ? $entry->period->name : "Period {$entry->period_number}",
                        'start_time' => $entry->period ? $entry->period->start_time : null,
                        'end_time' => $entry->period ? $entry->period->end_time : null,
                        'subject' => $entry->subject ? [
                            'id' => $entry->subject->id,
                            'name' => $entry->subject->name,
                            'short_name' => $entry->subject->short_name ?? $entry->subject->name,
                        ] : null,
                        'teacher' => $entry->teacher ? [
                            'id' => $entry->teacher->id,
                            'name' => $entry->teacher->first_name . ' ' . $entry->teacher->last_name,
                        ] : null,
                        'room' => $entry->room ? [
                            'id' => $entry->room->id,
                            'name' => $entry->room->name,
                        ] : null,
                        'is_double_period' => $entry->is_double_period,
                        'is_practical' => $entry->is_practical,
                        'notes' => $entry->notes,
                    ];
                })->values()->toArray();
            }
        }

        return response()->json([
            'success' => true,
            'data' => $timetableData, // Return days directly for mobile app compatibility
        ], 200);
    }

    /**
     * Get events
     */
    public function getEvents(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()->where('students.id', $studentId)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Events logic would go here
        // This depends on your events implementation

        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Events feature coming soon',
        ], 200);
    }

    /**
     * Get library materials for student
     */
    public function getLibraryMaterials(Request $request, $studentId): JsonResponse
    {
        $guardian = $request->user();

        $student = $guardian->students()
            ->where('students.id', $studentId)
            ->with(['class', 'stream', 'academicYear'])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied.',
            ], 404);
        }

        // Get active academic year
        $activeAcademicYear = $this->getActiveAcademicYear($student->company_id);
        $academicYearId = $activeAcademicYear ? $activeAcademicYear->id : $student->academic_year_id;

        // Get library materials
        $query = \App\Models\School\LibraryMaterial::where('status', 'published')
            ->where('is_active', true)
            ->where('company_id', $student->company_id)
            ->where(function ($query) use ($student) {
                $query->where('branch_id', $student->branch_id)
                      ->orWhereNull('branch_id');
            })
            ->where(function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId)
                      ->orWhereNull('academic_year_id');
            })
            ->where(function ($query) use ($student) {
                $query->where('class_id', $student->class_id)
                      ->orWhereNull('class_id');
            });

        // Filter by subject if provided
        if ($request->has('subject_id') && $request->subject_id) {
            $query->where(function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id)
                  ->orWhereNull('subject_id');
            });
        }

        // Filter by type if provided
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $materials = $query->orderBy('created_at', 'desc')->get();

        $materialsData = $materials->map(function ($material) {
            return [
                'id' => $material->id,
                'title' => $material->title,
                'type' => $material->type,
                'type_label' => $material->type_label,
                'description' => $material->description,
                'file_url' => $material->url,
                'file_name' => $material->original_name,
                'file_size' => $material->formatted_file_size,
                'class_name' => $material->classe ? $material->classe->name : 'All Classes',
                'subject_name' => $material->subject ? $material->subject->name : 'All Subjects',
                'created_at' => $material->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $materialsData,
        ], 200);
    }

    /**
     * Get invoice details and payment history
     */
    public function getInvoiceDetails(Request $request, $studentId, $invoiceId): JsonResponse
    {
        $guardian = $request->user();

        // Verify the student belongs to this guardian
        $student = $guardian->students()->where('students.id', $studentId)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied.',
            ], 404);
        }

        // Get the invoice
        $invoice = \App\Models\FeeInvoice::where('id', $invoiceId)
            ->where('student_id', $studentId)
            ->with(['feeGroup', 'academicYear', 'items', 'student'])
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found or access denied.',
            ], 404);
        }

        // Check if LIPISHA is enabled
        $lipishaEnabled = \App\Services\LipishaService::isEnabled();

        // Get payment history
        $payments = \App\Models\Payment::where('reference', $invoice->invoice_number)
            ->where('reference_type', 'fee_invoice')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'payment_date' => $payment->date ? $payment->date->format('Y-m-d H:i:s') : null,
                    'payment_method' => $payment->payment_method ?? 'N/A',
                    'reference_number' => $payment->reference_number ?? null,
                    'description' => $payment->description ?? null,
                ];
            });

        // Get invoice items
        $items = $invoice->items->map(function ($item) {
            return [
                'id' => $item->id,
                'fee_name' => $item->fee_name ?? 'N/A',
                'amount' => (float) $item->amount,
                'category' => $item->category ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'fee_group' => $invoice->feeGroup ? $invoice->feeGroup->name : null,
                    'period' => $invoice->period,
                    'academic_year' => $invoice->academicYear ? $invoice->academicYear->year_name : null,
                    'subtotal' => (float) $invoice->subtotal,
                    'transport_fare' => (float) $invoice->transport_fare,
                    'discount_type' => $invoice->discount_type,
                    'discount_value' => (float) $invoice->discount_value,
                    'discount_amount' => (float) $invoice->discount_amount,
                    'total_amount' => (float) $invoice->total_amount,
                    'paid_amount' => (float) $invoice->paid_amount,
                    'due_amount' => (float) ($invoice->total_amount - $invoice->paid_amount),
                    'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                    'issue_date' => $invoice->issue_date ? $invoice->issue_date->format('Y-m-d') : null,
                    'status' => $invoice->status,
                    'lipisha_control_number' => ($lipishaEnabled && $invoice->lipisha_control_number) ? $invoice->lipisha_control_number : null,
                ],
                'items' => $items,
                'payments' => $payments,
                'lipisha_enabled' => $lipishaEnabled,
            ],
        ], 200);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $guardian = $request->user();

        if (!Hash::check($request->current_password, $guardian->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        $guardian->password = Hash::make($request->new_password);
        $guardian->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ], 200);
    }
}

