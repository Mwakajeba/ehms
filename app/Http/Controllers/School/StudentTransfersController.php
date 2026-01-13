<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\Student;
use App\Models\School\StudentTransfer;
use App\Models\School\Classe;
use App\Models\School\Stream;
use App\Models\School\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentTransfersController extends Controller
{
    /**
     * Display a listing of student transfers.
     */
    public function index()
    {
        $transfers = StudentTransfer::with(['student.class', 'student.stream', 'processedBy'])
            ->latest()
            ->paginate(25);

        $classes = Classe::all();
        $streams = Stream::all();
        $academicYears = AcademicYear::all();

        return view('school.student-transfers.index', compact('transfers', 'classes', 'streams', 'academicYears'));
    }

    /**
     * Show the form for creating a new transfer (transfer out).
     */
    public function create(Request $request)
    {
        $studentId = $request->get('student_id');
        $student = null;

        if ($studentId) {
            $student = Student::with(['class', 'stream', 'academicYear'])->find($studentId);
        }

        // Get students based on transfer type - active for transfer_out, transferred_out for re_admission
        $transferType = $request->get('transfer_type', 'transfer_out');

        if ($transferType === 're_admission') {
            $availableStudents = Student::where('status', 'transferred_out')
                ->with(['class', 'stream'])
                ->get();
        } else {
            $availableStudents = Student::where('status', 'active')
                ->with(['class', 'stream'])
                ->get();
        }

        $classes = Classe::all();
        $streams = Stream::all();
        $academicYears = AcademicYear::all();
        $currentAcademicYear = AcademicYear::current();

        return view('school.student-transfers.create', compact('student', 'availableStudents', 'classes', 'streams', 'academicYears', 'currentAcademicYear'));
    }

    /**
     * Show the form for re-admission (student returning after absence).
     */
    public function reAdmission(Request $request)
    {
        $transferredStudents = Student::where('status', 'transferred_out')
            ->with(['class', 'stream', 'academicYear', 'transfers' => function($query) {
                $query->latest()->first();
            }])
            ->get();

        $classes = Classe::all();
        $streams = Stream::all();
        $academicYears = AcademicYear::all();
        $currentAcademicYear = AcademicYear::current();

        return view('school.student-transfers.re-admission', compact(
            'transferredStudents',
            'classes',
            'streams',
            'academicYears',
            'currentAcademicYear'
        ));
    }

    /**
     * Store a transfer out record.
     */
    public function store(Request $request)
    {
        \Log::info('Student transfer store method called', [
            'request_data' => $request->all(),
            'files' => $request->hasFile('transfer_certificate') ? 'transfer_certificate uploaded' : 'no transfer_certificate',
            'academic_report' => $request->hasFile('academic_report') ? 'academic_report uploaded' : 'no academic_report',
            'user_id' => Auth::id()
        ]);

        try {
            $rules = [
                'student_id' => 'required|exists:App\Models\School\Student,id',
                'transfer_type' => 'required|in:transfer_out,re_admission',
                'new_school' => 'required_if:transfer_type,transfer_out|required_if:transfer_type,re_admission|string|max:255',
                'previous_school' => 'required_if:transfer_type,re_admission|string|max:255',
                'transfer_date' => 'required|date|before_or_equal:today',
                'reason' => 'nullable|string|max:500',
                'transfer_certificate_number' => 'nullable|string|max:100',
                'outstanding_fees' => 'nullable|numeric|min:0',
                'academic_records' => 'nullable|string',
                'notes' => 'nullable|string|max:1000',
                'transfer_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'academic_report' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ];

            // Add class and stream validation only for re_admission
            if ($request->transfer_type === 're_admission') {
                $rules['new_class_id'] = 'required|exists:classes,id';
                $rules['new_stream_id'] = 'required|exists:streams,id';
            } else {
                // For transfer_out, these fields are not required and should be nullable
                $rules['new_class_id'] = 'nullable';
                $rules['new_stream_id'] = 'nullable';
            }

            $request->validate($rules);

            \Log::info('Validation passed, proceeding with transfer creation');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        DB::transaction(function () use ($request) {
            $student = Student::findOrFail($request->student_id);
            \Log::info('Student found', ['student_id' => $student->id, 'student_name' => $student->first_name . ' ' . $student->last_name]);

            // Create transfer record
            $transferData = [
                'student_id' => $request->student_id,
                'transfer_type' => $request->transfer_type,
                'status' => 'completed', // transfer_out and re_admission are completed immediately
                'previous_school' => $request->previous_school,
                'new_school' => $request->new_school,
                'transfer_date' => $request->transfer_date,
                'reason' => $request->reason,
                'transfer_certificate_number' => $request->transfer_certificate_number,
                'outstanding_fees' => $request->outstanding_fees ?? 0,
                'academic_records' => $request->academic_records,
                'notes' => $request->notes,
                'processed_by' => Auth::id(),
            ];

            // Set current academic year for the transfer
            $currentAcademicYear = AcademicYear::current();
            if ($currentAcademicYear) {
                $transferData['academic_year_id'] = $currentAcademicYear->id;
                \Log::info('Current academic year set', ['academic_year_id' => $currentAcademicYear->id]);
            } else {
                \Log::info('No current academic year found');
            }

            // Handle file uploads
            if ($request->hasFile('transfer_certificate')) {
                $transferCertificatePath = $request->file('transfer_certificate')->store('transfers/certificates', 'public');
                $transferData['transfer_certificate'] = $transferCertificatePath;
                \Log::info('Transfer certificate uploaded', ['path' => $transferCertificatePath]);
            }

            if ($request->hasFile('academic_report')) {
                $academicReportPath = $request->file('academic_report')->store('transfers/reports', 'public');
                $transferData['academic_report'] = $academicReportPath;
                \Log::info('Academic report uploaded', ['path' => $academicReportPath]);
            }

            \Log::info('Creating transfer record', $transferData);
            $transfer = StudentTransfer::create($transferData);
            \Log::info('Transfer record created', ['transfer_id' => $transfer->id]);

            // Update student status based on transfer type
            if ($request->transfer_type === 'transfer_out') {
                $student->updateStatus('transferred_out', 'Transferred to: ' . $request->new_school);
                \Log::info('Student status updated to transferred_out');
            } elseif ($request->transfer_type === 're_admission') {
                $student->updateStatus('active', 'Re-admitted from: ' . $request->previous_school);
                // Assign new class and stream for re-admission
                $student->update([
                    'class_id' => $request->new_class_id,
                    'stream_id' => $request->new_stream_id,
                ]);
                \Log::info('Student status updated to active for re-admission');
            }
        });

        \Log::info('Transfer creation completed successfully');

        $message = $request->transfer_type === 'transfer_out'
            ? 'Student transfer out recorded successfully.'
            : 'Student re-admission recorded successfully.';

        return redirect()->route('school.student-transfers.index')
            ->with('success', $message);
    }

    /**
     * Display the specified transfer.
     */
    public function show($encodedId)
    {
        $transfer = StudentTransfer::resolveRouteBindingStatic($encodedId);
        $transfer->load(['student.class', 'student.stream', 'student.academicYear', 'processedBy']);
        $classes = Classe::all();

        // Debug: Check if transfer has valid ID and route key
        \Log::info('Transfer show method called', [
            'transfer_id' => $transfer->id,
            'transfer_route_key' => $transfer->getRouteKey(),
            'transfer_number' => $transfer->transfer_number
        ]);

        return view('school.student-transfers.view', compact('transfer', 'classes'));
    }

    /**
     * Show the form for editing the specified transfer.
     */
    public function edit($encodedId)
    {
        $transfer = StudentTransfer::resolveRouteBindingStatic($encodedId);

        // Debug: Check if transfer is properly loaded
        \Log::info('Edit method called', [
            'encodedId' => $encodedId,
            'transfer_id' => $transfer->id ?? 'null',
            'transfer_exists' => $transfer->exists ?? false,
            'transfer_route_key' => $transfer->getRouteKey() ?? 'null',
            'transfer_number' => $transfer->transfer_number ?? 'null'
        ]);

        if (!$transfer || !$transfer->exists) {
            return redirect()->route('school.student-transfers.index')
                ->with('error', 'Transfer record not found.');
        }

        $transfer->load('student');
        $activeStudents = Student::where('status', 'active')
            ->with(['class', 'stream'])
            ->get();

        // Include the transfer's student in the list if not already included
        if ($transfer->student && !$activeStudents->contains('id', $transfer->student->id)) {
            $transfer->student->load(['class', 'stream']);
            $activeStudents->push($transfer->student);
        }

        $academicYears = AcademicYear::all();
        $currentAcademicYear = AcademicYear::current();

        // Set default academic year if not set
        if (!$transfer->academic_year_id && $currentAcademicYear) {
            $transfer->academic_year_id = $currentAcademicYear->id;
        }

        return view('school.student-transfers.edit', compact('transfer', 'activeStudents', 'academicYears', 'currentAcademicYear'));
    }

    /**
     * Update the specified transfer.
     */
    public function update(Request $request, $encodedId)
    {
        $transfer = StudentTransfer::resolveRouteBindingStatic($encodedId);
        $request->validate([
            'transfer_type' => 'required|in:transfer_out,transfer_in,re_admission',
            'student_id' => 'required_if:transfer_type,transfer_out|required_if:transfer_type,re_admission|exists:students,id',
            'student_name' => 'required_if:transfer_type,transfer_in|string|max:255',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'new_school' => 'required_if:transfer_type,transfer_out|required_if:transfer_type,re_admission|string|max:255',
            'previous_school' => 'required_if:transfer_type,transfer_out|required_if:transfer_type,transfer_in|string|max:255',
            'transfer_date' => 'required|date|before_or_equal:today',
            'reason' => 'nullable|string|max:500',
            'transfer_certificate_number' => 'nullable|string|max:100',
            'academic_records' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
            'transfer_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'academic_report' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        DB::transaction(function () use ($request, $transfer) {
            $updateData = $request->only([
                'transfer_type', 'academic_year_id', 'previous_school', 'new_school', 'transfer_date',
                'reason', 'transfer_certificate_number', 'academic_records', 'notes'
            ]);

            // Handle student assignment based on transfer type
            if ($request->transfer_type === 'transfer_in') {
                // For transfer_in, we don't change the student_id as it's already set
                // The student_name field is just for display/reference
            } else {
                // For transfer_out and re_admission, update the student_id
                $updateData['student_id'] = $request->student_id;
            }

            // Handle file uploads
            if ($request->hasFile('transfer_certificate')) {
                // Delete old file if exists
                if ($transfer->transfer_certificate && \Storage::disk('public')->exists($transfer->transfer_certificate)) {
                    \Storage::disk('public')->delete($transfer->transfer_certificate);
                }

                $transferCertificatePath = $request->file('transfer_certificate')->store('transfers/certificates', 'public');
                $updateData['transfer_certificate'] = $transferCertificatePath;
            }

            if ($request->hasFile('academic_report')) {
                // Delete old file if exists
                if ($transfer->academic_report && \Storage::disk('public')->exists($transfer->academic_report)) {
                    \Storage::disk('public')->delete($transfer->academic_report);
                }

                $academicReportPath = $request->file('academic_report')->store('transfers/reports', 'public');
                $updateData['academic_report'] = $academicReportPath;
            }

            $transfer->update($updateData);

            // Update student status based on transfer type
            if ($transfer->student) {
                if ($request->transfer_type === 'transfer_out') {
                    $transfer->student->updateStatus('transferred_out', 'Transferred to: ' . $request->new_school);
                } elseif ($request->transfer_type === 're_admission') {
                    $transfer->student->updateStatus('active', 'Re-admitted from: ' . $request->previous_school);
                }
                // For transfer_in, status is managed separately via completeTransferIn
            }
        });

        return redirect()->route('school.student-transfers.show', $transfer->getRouteKey())
            ->with('success', 'Transfer record updated successfully.');
    }

    /**
     * Handle student transfer in (new student from another school).
     */
    public function transferIn(Request $request)
    {
        $classes = Classe::all();
        $streams = Stream::all();
        $academicYears = AcademicYear::all();

        if ($request->isMethod('post')) {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'date_of_birth' => 'required|date|before:today',
                'gender' => 'required|in:male,female,other',
                'admission_number' => 'required|string|max:50|unique:students',
                'admission_date' => 'required|date',
                'address' => 'required|string|max:500',
                'class_id' => 'required|exists:classes,id',
                'stream_id' => 'required|exists:streams,id',
                'previous_school' => 'required|string|max:255',
                'transfer_date' => 'required|date|before_or_equal:today',
                'reason' => 'nullable|string|max:500',
                'transfer_certificate_number' => 'nullable|string|max:100',
                'academic_records' => 'nullable|string',
                'passport_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'transfer_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'academic_report' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);

            DB::transaction(function () use ($request) {
                // Create the student
                $studentData = $request->only([
                    'first_name', 'last_name', 'date_of_birth', 'gender',
                    'admission_number', 'admission_date', 'address', 'class_id', 'stream_id'
                ]);

                // Set current academic year
                $currentAcademicYear = AcademicYear::current();
                if ($currentAcademicYear) {
                    $studentData['academic_year_id'] = $currentAcademicYear->id;
                }

                // Handle photo upload
                if ($request->hasFile('passport_photo')) {
                    $photoPath = $request->file('passport_photo')->store('students/photos', 'public');
                    $studentData['passport_photo'] = $photoPath;
                }

                $student = Student::create($studentData);

                // Create transfer in record
                $transferData = [
                    'student_id' => $student->id,
                    'transfer_type' => 'transfer_in',
                    'status' => 'pending',
                    'previous_school' => $request->previous_school,
                    'transfer_date' => $request->transfer_date,
                    'reason' => $request->reason,
                    'transfer_certificate_number' => $request->transfer_certificate_number,
                    'academic_records' => $request->academic_records,
                    'processed_by' => Auth::id(),
                ];

                // Set current academic year for the transfer
                if ($currentAcademicYear) {
                    $transferData['academic_year_id'] = $currentAcademicYear->id;
                }

                // Handle transfer document uploads
                if ($request->hasFile('transfer_certificate')) {
                    $transferCertificatePath = $request->file('transfer_certificate')->store('transfers/certificates', 'public');
                    $transferData['transfer_certificate'] = $transferCertificatePath;
                }

                if ($request->hasFile('academic_report')) {
                    $academicReportPath = $request->file('academic_report')->store('transfers/reports', 'public');
                    $transferData['academic_report'] = $academicReportPath;
                }

                StudentTransfer::create($transferData);
            });

            return redirect()->route('school.students.index')
                ->with('success', 'Student transfer in completed successfully.');
        }

        return view('school.student-transfers.transfer-in', compact('classes', 'streams', 'academicYears'));
    }

    /**
     * Complete a transfer in record by updating student details.
     */
    public function completeTransferIn(Request $request, $encodedId)
    {
        $transfer = StudentTransfer::resolveRouteBindingStatic($encodedId);
        // Ensure this is a transfer_in record and it's still pending
        if ($transfer->transfer_type !== 'transfer_in' || $transfer->status !== 'pending') {
            return redirect()->back()->with('error', 'Invalid transfer record or transfer already completed.');
        }

        $request->validate([
            'transfer_in_date' => 'required|date|after_or_equal:today',
            'admission_number' => 'required|string|max:50|unique:students',
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'required|exists:streams,id',
            'transfer_notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $transfer) {
            // Update the student with the transfer details
            $student = $transfer->student;
            if ($student) {
                $student->update([
                    'admission_number' => $request->admission_number,
                    'class_id' => $request->class_id,
                    'stream_id' => $request->stream_id,
                    'admission_date' => $request->transfer_in_date,
                ]);

                // Update student status to active
                $student->updateStatus('active', 'Completed transfer in on ' . $request->transfer_in_date);
            }

            // Update transfer record
            $transfer->update([
                'status' => 'completed',
                'notes' => ($transfer->notes ? $transfer->notes . "\n\n" : '') . 'Transfer completed on ' . $request->transfer_in_date . "\n" . ($request->transfer_notes ?? ''),
            ]);
        });

        return redirect()->route('school.student-transfers.show', $transfer->getRouteKey())
            ->with('success', 'Transfer in completed successfully.');
    }

    /**
     * Get data for DataTables server-side processing.
     */
    public function data(Request $request)
    {
        $query = StudentTransfer::with(['student.class', 'student.stream', 'processedBy']);

        // Apply filters
        if ($request->has('transfer_type') && $request->transfer_type) {
            $query->where('transfer_type', $request->transfer_type);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->whereRaw("CONCAT('TRF-', LPAD(id, 6, '0')) LIKE ?", ["%{$search}%"])
                  ->orWhere('previous_school', 'like', "%{$search}%")
                  ->orWhere('new_school', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($studentQuery) use ($search) {
                      $studentQuery->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%")
                                   ->orWhere('admission_number', 'like', "%{$search}%");
                  });
            });
        }

        // Ordering
        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $direction = $request->order[0]['dir'];

            $columns = ['id', 'transfer_number', 'student_name', 'transfer_type', 'previous_school', 'new_school', 'transfer_date', 'status'];
            $column = $columns[$columnIndex] ?? 'transfer_date';

            if ($column === 'student_name') {
                $query->join('students', 'student_transfers.student_id', '=', 'students.id')
                      ->orderByRaw("CONCAT(students.first_name, ' ', students.last_name) " . $direction);
            } elseif ($column === 'id') {
                $query->orderBy('student_transfers.id', $direction);
            } else {
                $query->orderBy($column, $direction);
            }
        }

        $totalRecords = $query->count();

        // Pagination
        $transfers = $query->skip($request->start ?? 0)
                          ->take($request->length ?? 25)
                          ->get();

        $data = $transfers->map(function ($transfer) {
            return [
                'DT_RowIndex' => $transfer->id,
                'transfer_number' => 'TRF-' . str_pad($transfer->id, 6, '0', STR_PAD_LEFT),
                'student_name' => $transfer->student_name . ($transfer->student ? ' (' . $transfer->student->admission_number . ')' : ''),
                'transfer_type' => ucwords(str_replace('_', ' ', $transfer->transfer_type)),
                'transfer_type_badge' => view('school.student-transfers.partials.transfer-type-badge', compact('transfer'))->render(),
                'from_school' => $transfer->previous_school ?? 'N/A',
                'to_school' => $transfer->new_school ?? 'N/A',
                'transfer_date' => $transfer->transfer_date->format('M d, Y'),
                'status' => ucfirst($transfer->status),
                'status_badge' => view('school.student-transfers.partials.status-badge', compact('transfer'))->render(),
                'actions' => view('school.student-transfers.partials.actions', compact('transfer'))->render()
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    /**
     * Remove the specified transfer from storage.
     */
    public function destroy($encodedId)
    {
        $transfer = StudentTransfer::resolveRouteBindingStatic($encodedId);

        // Handle student status restoration based on transfer type
        if ($transfer->student) {
            if ($transfer->transfer_type === 'transfer_out') {
                // Restore student to active status when transfer_out is deleted
                $transfer->student->updateStatus('active', 'Transfer record deleted - student restored to active status');
            } elseif ($transfer->transfer_type === 're_admission') {
                // For re_admission deletion, change status back to transferred_out
                $transfer->student->updateStatus('transferred_out', 'Re-admission record deleted - student returned to transferred out status');
            }
            // For transfer_in, the student will be deleted if pending, or status remains as is if completed
        }

        // For transfer_in records that are still pending, we should also delete the associated student
        if ($transfer->transfer_type === 'transfer_in' && $transfer->status === 'pending' && $transfer->student) {
            $student = $transfer->student;
            $transfer->delete();

            // Only delete the student if they have no other transfers
            if ($student->transfers()->count() === 0) {
                $student->delete();
            }
        } else {
            // Delete associated files
            if ($transfer->transfer_certificate && \Storage::disk('public')->exists($transfer->transfer_certificate)) {
                \Storage::disk('public')->delete($transfer->transfer_certificate);
            }

            if ($transfer->academic_report && \Storage::disk('public')->exists($transfer->academic_report)) {
                \Storage::disk('public')->delete($transfer->academic_report);
            }

            $transfer->delete();
        }

        return redirect()->route('school.student-transfers.index')
            ->with('success', 'Transfer record deleted successfully.');
    }

    /**
     * Show student transfer history.
     */
    public function studentHistory($encodedId)
    {
        $transfer = StudentTransfer::resolveRouteBindingStatic($encodedId);
        if (!$transfer->student) {
            return redirect()->route('school.student-transfers.index')
                ->with('error', 'No student associated with this transfer record.');
        }

        $student = $transfer->student->load(['class', 'stream', 'academicYear', 'transfers.processedBy']);
        $transferHistory = $student->transfers->sortByDesc('transfer_date');

        return view('school.student-transfers.student-history', compact('student', 'transferHistory', 'transfer'));
    }

    /**
     * Get students for transfer based on transfer type.
     */
    public function getStudents(Request $request)
    {
        $transferType = $request->get('transfer_type', 'transfer_out');

        \Log::info('getStudents called', ['transfer_type' => $transferType]);

        if ($transferType === 're_admission') {
            $students = Student::where('status', 'transferred_out')
                ->with(['class', 'stream'])
                ->get();
            \Log::info('Re-admission students count', ['count' => $students->count()]);
        } else {
            $students = Student::where('status', 'active')
                ->with(['class', 'stream'])
                ->get();
            \Log::info('Active students count', ['count' => $students->count()]);
        }

        return response()->json([
            'students' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'admission_number' => $student->admission_number,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'status' => $student->status,
                ];
            })
        ]);
    }

    /**
     * Show printable view of the transfer record.
     */
    public function print($encodedId)
    {
        $transfer = StudentTransfer::resolveRouteBindingStatic($encodedId);
        $transfer->load(['student.class', 'student.stream', 'student.academicYear', 'processedBy', 'academicYear']);

        // Get current user's branch and company information
        $branch = auth()->user()->branch;
        $company = auth()->user()->company;

        $pdf = \PDF::loadView('school.student-transfers.print_ultra_simple', compact('transfer', 'branch', 'company'))
            ->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'Times-Roman']);

        $filename = 'transfer_certificate_' . $transfer->transfer_number . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate and stream PDF of the transfer record for preview.
     */
    public function previewPdf($encodedId)
    {
        $transfer = StudentTransfer::resolveRouteBindingStatic($encodedId);
        $transfer->load(['student.class', 'student.stream', 'student.academicYear', 'processedBy', 'academicYear']);

        // Get current user's branch and company information
        $branch = auth()->user()->branch;
        $company = auth()->user()->company;

        $pdf = \PDF::loadView('school.student-transfers.print_ultra_simple', compact('transfer', 'branch', 'company'))
            ->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'Times-Roman']);

        $filename = 'transfer_certificate_' . $transfer->transfer_number . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Export student transfers to Excel.
     */
    public function exportExcel(Request $request)
    {
        $query = StudentTransfer::with(['student.class', 'student.stream', 'processedBy', 'academicYear']);

        // Apply the same filters as the index method
        if ($request->has('transfer_type') && $request->transfer_type) {
            $query->where('transfer_type', $request->transfer_type);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }

        $transfers = $query->orderBy('transfer_date', 'desc')->get();

        return \Excel::download(new class($transfers) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping {
            private $transfers;

            public function __construct($transfers)
            {
                $this->transfers = $transfers;
            }

            public function collection()
            {
                return $this->transfers;
            }

            public function headings(): array
            {
                return [
                    'Transfer ID',
                    'Student Name',
                    'Admission Number',
                    'Class',
                    'Stream',
                    'Transfer Type',
                    'From School',
                    'To School',
                    'Transfer Date',
                    'Status',
                    'Reason',
                    'Transfer Certificate Number',
                    'Outstanding Fees',
                    'Academic Records',
                    'Processed By',
                    'Academic Year',
                    'Created At'
                ];
            }

            public function map($transfer): array
            {
                return [
                    'TRF-' . str_pad($transfer->id, 6, '0', STR_PAD_LEFT),
                    $transfer->student ? $transfer->student->first_name . ' ' . $transfer->student->last_name : 'N/A',
                    $transfer->student ? $transfer->student->admission_number : 'N/A',
                    $transfer->student && $transfer->student->class ? $transfer->student->class->name : 'N/A',
                    $transfer->student && $transfer->student->stream ? $transfer->student->stream->name : 'N/A',
                    ucwords(str_replace('_', ' ', $transfer->transfer_type)),
                    $transfer->previous_school ?? 'N/A',
                    $transfer->new_school ?? 'N/A',
                    $transfer->transfer_date ? $transfer->transfer_date->format('M d, Y') : 'N/A',
                    ucfirst($transfer->status),
                    $transfer->reason ?? 'N/A',
                    $transfer->transfer_certificate_number ?? 'N/A',
                    $transfer->outstanding_fees ?? 0,
                    $transfer->academic_records ?? 'N/A',
                    $transfer->processedBy ? $transfer->processedBy->name : 'N/A',
                    $transfer->academicYear ? $transfer->academicYear->name : 'N/A',
                    $transfer->created_at ? $transfer->created_at->format('M d, Y H:i') : 'N/A'
                ];
            }
        }, 'student_transfers_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Export student transfers to PDF document.
     */
    public function exportPdf(Request $request)
    {
        $query = StudentTransfer::with(['student.class', 'student.stream', 'processedBy', 'academicYear']);

        // Apply the same filters as the index method
        if ($request->has('transfer_type') && $request->transfer_type) {
            $query->where('transfer_type', $request->transfer_type);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }

        $transfers = $query->orderBy('transfer_date', 'desc')->get();

        // Get current user's branch and company information
        $branch = auth()->user()->branch ?? null;
        $company = auth()->user()->company ?? null;

        $pdf = \PDF::loadView('school.student-transfers.export-pdf', compact('transfers', 'branch', 'company', 'request'))
            ->setPaper('a4', 'landscape')
            ->setOptions(['defaultFont' => 'Times-Roman']);

        $filename = 'student_transfers_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate HTML content for Word document export.
     */
    private function generateTransferDocHtml($transfers, Request $request)
    {
        $company = auth()->check() ? auth()->user()->company : null;
        $branch = auth()->check() ? auth()->user()->branch : null;

        $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Transfers Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 20px; }
        .company-name { font-size: 18pt; font-weight: bold; margin-bottom: 10px; }
        .report-title { font-size: 16pt; font-weight: bold; margin-bottom: 10px; }
        .filters { margin-bottom: 20px; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 10pt; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10pt; color: #666; }
        .summary { margin-bottom: 20px; font-size: 11pt; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">' . ($company ? $company->name : 'School Management System') . '</div>
        <div class="report-title">Student Transfers Report</div>
        <div>Generated on: ' . now()->format('F d, Y H:i') . '</div>
    </div>

    <div class="filters">
        <strong>Applied Filters:</strong><br>';

        if ($request->transfer_type) {
            $html .= 'Transfer Type: ' . ucwords(str_replace('_', ' ', $request->transfer_type)) . '<br>';
        }
        if ($request->status) {
            $html .= 'Status: ' . ucfirst($request->status) . '<br>';
        }
        if ($request->date_from) {
            $html .= 'From Date: ' . $request->date_from . '<br>';
        }
        if ($request->date_to) {
            $html .= 'To Date: ' . $request->date_to . '<br>';
        }

        $html .= '
    </div>

    <div class="summary">
        <strong>Total Records:</strong> ' . $transfers->count() . '<br>
        <strong>Generated By:</strong> ' . (auth()->check() ? auth()->user()->name : 'System') . '<br>
        <strong>Branch:</strong> ' . ($branch ? $branch->name : 'N/A') . '
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">#</th>
                <th style="width: 12%;">Transfer ID</th>
                <th style="width: 15%;">Student Name</th>
                <th style="width: 10%;">Admission No.</th>
                <th style="width: 8%;">Class</th>
                <th style="width: 10%;">Transfer Type</th>
                <th style="width: 12%;">From School</th>
                <th style="width: 12%;">To School</th>
                <th style="width: 10%;">Transfer Date</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($transfers as $index => $transfer) {
            $html .= '
            <tr>
                <td>' . ($index + 1) . '</td>
                <td>TRF-' . str_pad($transfer->id, 6, '0', STR_PAD_LEFT) . '</td>
                <td>' . ($transfer->student ? $transfer->student->first_name . ' ' . $transfer->student->last_name : 'N/A') . '</td>
                <td>' . ($transfer->student ? $transfer->student->admission_number : 'N/A') . '</td>
                <td>' . ($transfer->student && $transfer->student->class ? $transfer->student->class->name : 'N/A') . '</td>
                <td>' . ucwords(str_replace('_', ' ', $transfer->transfer_type)) . '</td>
                <td>' . ($transfer->previous_school ?? 'N/A') . '</td>
                <td>' . ($transfer->new_school ?? 'N/A') . '</td>
                <td>' . ($transfer->transfer_date ? $transfer->transfer_date->format('M d, Y') : 'N/A') . '</td>
                <td>' . ucfirst($transfer->status) . '</td>
            </tr>';
        }

        $html .= '
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated from the School Management System</p>
        <p>Confidential - For Internal Use Only</p>
    </div>
</body>
</html>';

        return $html;
    }
}
