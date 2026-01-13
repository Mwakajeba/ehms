<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School\Student;
use App\Models\School\Classe;
use App\Models\School\Route;
use App\Models\School\Stream;
use App\Models\School\BusStop;
use App\Models\School\Guardian;
use App\Models\School\AcademicYear;
use App\Models\Company;
use Yajra\DataTables\Facades\DataTables;
use Hashids\Hashids;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class StudentSheetImport implements \Maatwebsite\Excel\Concerns\ToModel, \Maatwebsite\Excel\Concerns\WithStartRow
{
    private $class;
    private $stream;
    private $currentAcademicYear;
    private $errors = [];
    private $successCount = 0;
    private $errorCount = 0;

    public function __construct($class, $stream, $currentAcademicYear)
    {
        $this->class = $class;
        $this->stream = $stream;
        $this->currentAcademicYear = $currentAcademicYear;
    }

    public function startRow(): int
    {
        return 1; // Start from row 1 (no headers, data starts immediately)
    }

    public function model(array $row)
    {
        try {
            // Skip empty rows
            if (empty(array_filter($row))) {
                return null;
            }

            // Skip header row if detected (check if first column contains header-like text)
            $firstCell = trim($row[0] ?? '');
            if (strtolower($firstCell) === 'admission no' || strtolower($firstCell) === 'admission_no') {
                return null; // Skip header row
            }

            // Skip instruction rows
            if (strtolower($firstCell) === 'instructions:') {
                return null;
            }

            // Skip dropdown metadata rows (from Excel template)
            $dropdownIndicators = [
                'gender options', 'transport options', 'boarding options', 
                'bus stop options', 'bus stops options'
            ];
            if (in_array(strtolower($firstCell), $dropdownIndicators)) {
                return null;
            }

            // Skip rows that contain template dropdown values in multiple columns
            $secondCell = trim($row[1] ?? '');
            $thirdCell = trim($row[2] ?? '');
            if (in_array(strtolower($secondCell), ['transport options', 'boarding options']) ||
                in_array(strtolower($thirdCell), ['boarding options'])) {
                return null;
            }

            // Skip rows that look like Excel dropdown data (single words that are dropdown options)
            $singleWordOptions = ['male', 'female', 'yes', 'no', 'day', 'boarding'];
            if (count(array_filter($row)) <= 5 && in_array(strtolower($firstCell), $singleWordOptions)) {
                // Additional check: if most columns are empty or contain single dropdown values
                $nonEmptyCells = array_filter($row, function($cell) {
                    return !empty(trim($cell ?? ''));
                });
                $dropdownWords = ['male', 'female', 'yes', 'no', 'day', 'boarding'];
                $dropdownCount = 0;
                foreach ($nonEmptyCells as $cell) {
                    if (in_array(strtolower(trim($cell)), $dropdownWords)) {
                        $dropdownCount++;
                    }
                }
                if ($dropdownCount >= 2) {
                    return null; // Skip rows that are mostly dropdown options
                }
            }

            // Define expected column order (matching the template)
            $expectedColumns = [
                'admission_no',      // Column A
                'first_name',        // Column B
                'last_name',         // Column C
                'gender',            // Column D
                'date_of_birth',     // Column E
                'admission_date',    // Column F
                'address',           // Column G
                'boarding_type',     // Column H
                'has_transport',     // Column I
                'bus_stop',          // Column J (optional)
                'guardian_name',     // Column K
                'guardian_phone',    // Column L
                'guardian_email',    // Column M
                'discount_type',     // Column N
                'discount_value',    // Column O
            ];

            // Map the row data to expected columns
            $normalizedRow = [];
            foreach ($expectedColumns as $index => $columnName) {
                $normalizedRow[$columnName] = $row[$index] ?? null;
            }

            // Validate required fields
            $firstName = $normalizedRow['first_name'] ?? '';
            if (empty(trim($firstName))) {
                $this->errors[] = "Row " . ($this->successCount + $this->errorCount + 1) . ": Missing required field (First Name) in column B";
                $this->errorCount++;
                return null;
            }

            // Check for duplicate admission number
            $admissionNumber = null;
            if (!empty($normalizedRow['admission_no'])) {
                $admissionNumber = trim($normalizedRow['admission_no']);
            }

            if ($admissionNumber && Student::where('admission_number', $admissionNumber)->exists()) {
                $this->errors[] = "Row " . ($this->successCount + $this->errorCount + 1) . ": Admission number '{$admissionNumber}' already exists";
                $this->errorCount++;
                return null;
            }

            // Validate and parse dates
            $dateOfBirth = null;
            $admissionDate = null;

            if (!empty($normalizedRow['date_of_birth'])) {
                try {
                    $dateOfBirth = $this->parseDate(trim($normalizedRow['date_of_birth']));
                } catch (\Exception $e) {
                    $this->errors[] = "Row " . ($this->successCount + $this->errorCount + 1) . ": Invalid date format for Date of Birth: '{$normalizedRow['date_of_birth']}'. Use YYYY-MM-DD format or Excel date format.";
                    $this->errorCount++;
                    return null;
                }
            }

            if (!empty($normalizedRow['admission_date'])) {
                try {
                    $admissionDate = $this->parseDate(trim($normalizedRow['admission_date']));
                } catch (\Exception $e) {
                    $this->errors[] = "Row " . ($this->successCount + $this->errorCount + 1) . ": Invalid date format for Admission Date: '{$normalizedRow['admission_date']}'. Use YYYY-MM-DD format or Excel date format.";
                    $this->errorCount++;
                    return null;
                }
            } else {
                // Default to today if admission date is not provided
                $admissionDate = now()->format('Y-m-d');
            }

            // Validate discount fields if provided
            $discountType = null;
            $discountValue = null;

            if (!empty($normalizedRow['discount_type'])) {
                $discountTypeInput = strtolower(trim($normalizedRow['discount_type']));
                if (in_array($discountTypeInput, ['fixed', 'percentage'])) {
                    $discountType = $discountTypeInput;
                } else {
                    $this->errors[] = "Row " . ($this->successCount + $this->errorCount + 1) . ": Invalid discount type '{$normalizedRow['discount_type']}'. Must be 'fixed' or 'percentage'";
                    $this->errorCount++;
                    return null;
                }
            }

            if (!empty($normalizedRow['discount_value'])) {
                $discountValueInput = trim($normalizedRow['discount_value']);
                if (is_numeric($discountValueInput)) {
                    $discountValue = (float) $discountValueInput;

                    // Validate discount value ranges
                    if ($discountType === 'percentage' && ($discountValue < 0 || $discountValue > 100)) {
                        $this->errors[] = "Row " . ($this->successCount + $this->errorCount + 1) . ": Percentage discount must be between 0 and 100";
                        $this->errorCount++;
                        return null;
                    } elseif ($discountType === 'fixed' && $discountValue < 0) {
                        $this->errors[] = "Row " . ($this->successCount + $this->errorCount + 1) . ": Fixed discount cannot be negative";
                        $this->errorCount++;
                        return null;
                    }
                } else {
                    $this->errors[] = "Row " . ($this->successCount + $this->errorCount + 1) . ": Discount value must be a valid number";
                    $this->errorCount++;
                    return null;
                }
            }

            // Validate that both discount fields are provided together
            if (($discountType && !$discountValue) || (!$discountType && $discountValue)) {
                $this->errors[] = "Row " . ($this->successCount + $this->errorCount + 1) . ": Both discount type and discount value must be provided together";
                $this->errorCount++;
                return null;
            }

            // Prepare student data
            $studentData = [
                'admission_number' => $admissionNumber,
                'first_name' => trim($firstName),
                'last_name' => !empty($normalizedRow['last_name']) ? trim($normalizedRow['last_name']) : null,
                'gender' => !empty($normalizedRow['gender']) ? strtolower(trim($normalizedRow['gender'])) : null,
                'date_of_birth' => $dateOfBirth,
                'admission_date' => $admissionDate,
                'address' => !empty($normalizedRow['address']) ? trim($normalizedRow['address']) : null,
                'boarding_type' => !empty($normalizedRow['boarding_type']) ? strtolower(trim($normalizedRow['boarding_type'])) : 'day',
                'has_transport' => !empty($normalizedRow['has_transport']) ? strtolower(trim($normalizedRow['has_transport'])) : 'no',
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'class_id' => $this->class->id,
                'stream_id' => $this->stream->id,
                'academic_year_id' => $this->currentAcademicYear->id,
                'company_id' => Auth::user()->company_id,
                'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
            ];

            // Create student
            $student = new Student($studentData);

            // Handle bus stop if provided and has_transport is yes
            if (!empty($normalizedRow['bus_stop']) && strtolower(trim($normalizedRow['has_transport'] ?? 'no')) === 'yes') {
                $busStopName = trim($normalizedRow['bus_stop']);

                // First try exact case-insensitive match
                $busStop = \App\Models\School\BusStop::whereRaw('LOWER(stop_name) = ?', [strtolower($busStopName)])->first();

                // If no exact match, try partial match (contains the name)
                if (!$busStop) {
                    $busStop = \App\Models\School\BusStop::whereRaw('LOWER(stop_name) LIKE ?', ['%' . strtolower($busStopName) . '%'])->first();
                }

                // If still no match, try soundex matching for similar sounding names
                if (!$busStop) {
                    $soundex = soundex($busStopName);
                    $busStop = \App\Models\School\BusStop::whereRaw('SOUNDEX(stop_name) = ?', [$soundex])->first();
                }

                if ($busStop) {
                    $student->bus_stop_id = $busStop->id;
                } else {
                    // Get all available bus stops for error message
                    $availableStops = \App\Models\School\BusStop::pluck('stop_name')->toArray();
                    $this->errors[] = "Row " . ($this->successCount + $this->errorCount + 1) . ": Bus stop '{$busStopName}' not found. Available bus stops: " . implode(', ', $availableStops);
                    $this->errorCount++;
                    return null;
                }
            }

            $student->save();

            // Dispatch job to create LIPISHA customer (runs after student is saved)
            // Only if LIPISHA integration is enabled
            if (\App\Services\LipishaService::isEnabled()) {
                // Get phone and email from guardians if available
                $phoneNumber = null;
                $email = null;
                
                if ($student->guardians && $student->guardians->isNotEmpty()) {
                    $firstGuardian = $student->guardians->first();
                    $phoneNumber = $firstGuardian->phone ?? null;
                    $email = $firstGuardian->email ?? null;
                }
                
                // CRITICAL: Always dispatch job for EVERY student, even if it fails
                // This ensures no student is skipped
                try {
                    // Add small delay based on student ID to stagger job processing
                    // This prevents all jobs from running simultaneously
                    $delaySeconds = ($student->id % 5); // 0-4 seconds delay
                    
                    \App\Jobs\CreateLipishaCustomerForStudent::dispatch(
                        $student->id,
                        $phoneNumber,
                        $email
                    )->onQueue('default')
                     ->delay(now()->addSeconds($delaySeconds));
                    
                    \Log::info('LIPISHA customer creation job dispatched during import', [
                        'student_id' => $student->id,
                        'has_phone' => !empty($phoneNumber),
                        'has_email' => !empty($email),
                        'delay_seconds' => $delaySeconds
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Exception dispatching LIPISHA customer creation job during import', [
                        'student_id' => $student->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Don't fail the import if job dispatch fails - job will retry automatically
                }
            } else {
                \Log::info('LIPISHA integration is disabled - skipping customer creation job during import', [
                    'student_id' => $student->id
                ]);
            }

            // Handle guardian if provided
            if (!empty($normalizedRow['guardian_name'])) {
                // Normalize phone number (handle scientific notation)
                $guardianPhone = null;
                if (!empty($normalizedRow['guardian_phone'])) {
                    $originalPhone = trim($normalizedRow['guardian_phone']);
                    $guardianPhone = $this->normalizePhoneNumber($originalPhone);

                    // Check if the phone number was in scientific notation and couldn't be properly converted
                    if (preg_match('/^(\d+\.?\d*)E\+(\d+)$/', $originalPhone) && !preg_match('/^\+?\d{7,15}$/', $guardianPhone)) {
                        $this->errors[] = "Row " . ($this->successCount + $this->errorCount + 1) . ": Phone number '{$originalPhone}' appears to be in Excel scientific notation. Please format the phone number column as 'Text' in Excel before importing.";
                        $this->errorCount++;
                        return null;
                    }
                }

                // Check if guardian already exists by phone or email
                $guardian = null;
                if ($guardianPhone) {
                    $guardian = Guardian::where('phone', $guardianPhone)->first();
                }
                if (!$guardian && !empty($normalizedRow['guardian_email'])) {
                    $guardian = Guardian::where('email', trim($normalizedRow['guardian_email']))->first();
                }

                // If guardian doesn't exist, create new one with password
                if (!$guardian) {
                    $defaultPassword = '12345'; // Default password for guardians
                    // Guardian model has 'password' => 'hashed' cast, so we pass plain text
                    $guardian = Guardian::create([
                        'name' => trim($normalizedRow['guardian_name']),
                        'phone' => $guardianPhone,
                        'email' => !empty($normalizedRow['guardian_email']) ? trim($normalizedRow['guardian_email']) : null,
                        'address' => !empty($normalizedRow['address']) ? trim($normalizedRow['address']) : null,
                        'password' => $defaultPassword, // Will be hashed automatically by model cast
                    ]);
                } else {
                    // Update existing guardian's information if needed
                    $updateData = [
                        'name' => trim($normalizedRow['guardian_name']),
                        'address' => !empty($normalizedRow['address']) ? trim($normalizedRow['address']) : null,
                    ];
                    
                    // Set password if it's null
                    if (!$guardian->password && $guardianPhone) {
                        $updateData['password'] = '12345'; // Will be hashed automatically by model cast
                    }
                    
                    $guardian->update($updateData);
                }

                // Attach guardian to student
                $student->guardians()->attach($guardian->id, ['relationship' => 'guardian']);
            }

            $this->successCount++;
            return $student;

        } catch (\Exception $e) {
            $this->errors[] = "Row " . ($this->successCount + $this->errorCount + 1) . ": " . $e->getMessage();
            $this->errorCount++;
            return null;
        }
    }

    private function normalizePhoneNumber(string $phone): ?string
    {
        // Clean the phone number first
        $cleanPhone = trim($phone);

        // Handle scientific notation only for reasonable phone number ranges
        if (preg_match('/^(\d+\.?\d*)E\+(\d+)$/', $cleanPhone, $matches)) {
            $base = (float) $matches[1];
            $exponent = (int) $matches[2];

            // Only convert if the result would be a reasonable phone number length (7-15 digits)
            $converted = bcmul($base, bcpow(10, $exponent));
            if (strlen($converted) >= 7 && strlen($converted) <= 15) {
                $cleanPhone = (string) $converted;
            }
            // If the converted number is too long, treat it as a string and try to extract a valid phone number
            elseif (strlen($converted) > 15) {
                // This might be Excel's way of storing a phone number - try to extract a valid phone number
                // For example, 2.55E+11 might represent +255xxxxxxxx
                $convertedStr = (string) $converted;
                if (preg_match('/255(\d{9})$/', $convertedStr, $phoneMatches)) {
                    $cleanPhone = '+255' . $phoneMatches[1];
                }
            }
        }

        // Remove any non-numeric characters except + at the beginning
        $cleanPhone = preg_replace('/[^\d+]/', '', $cleanPhone);

        // If it's a valid phone number, return it
        if (preg_match('/^\+?\d{7,15}$/', $cleanPhone)) {
            return $cleanPhone;
        }

        // If it's just numbers, try to format it as a Kenyan phone number
        if (preg_match('/^\d{9}$/', $cleanPhone)) {
            // Assume it's a Kenyan number without country code
            return '+254' . $cleanPhone;
        }

        if (preg_match('/^\d{12}$/', $cleanPhone) && str_starts_with($cleanPhone, '254')) {
            // Kenyan number with country code but missing +
            return '+' . $cleanPhone;
        }

        // Return as-is if we can't determine the format
        return $cleanPhone;
    }

    private function parseDate(string $dateString): string
    {
        $dateString = trim($dateString);

        // Check if it's an Excel serial date (typically 5-digit number)
        if (preg_match('/^\d{5,6}$/', $dateString)) {
            $serialDate = (int) $dateString;

            // Excel dates start from January 1, 1900 (serial date 1)
            // But Excel has a bug where it considers 1900-02-29 as a valid date (it wasn't a leap year)
            // So we need to adjust for that
            if ($serialDate > 60) {
                $serialDate--; // Adjust for the non-existent 1900-02-29
            }

            // Convert to Unix timestamp: Excel epoch is 1900-01-01
            $excelEpoch = strtotime('1900-01-01');
            $timestamp = $excelEpoch + ($serialDate - 1) * 86400; // 86400 seconds per day

            return date('Y-m-d', $timestamp);
        }

        // Try to parse as regular date format
        try {
            return \Carbon\Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception("Unable to parse date: {$dateString}");
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }
}

class StudentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get filter options
        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        // Check if company_id column exists in streams table
        $streamsQuery = Stream::query();
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('streams', 'company_id')) {
            $streamsQuery->where('company_id', $companyId);
        }
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('streams', 'branch_id')) {
            $streamsQuery->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });
        }
        
        $streams = $streamsQuery->orderBy('name')->get();

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->orderBy('year_name', 'desc')
            ->get();

        // Get selected filter values for form repopulation
        $selectedClass = $request->get('class_id');
        $selectedStream = $request->get('stream_id');
        $selectedAcademicYear = $request->get('academic_year_id');

        // Set current academic year as default if no academic year is selected
        if (!$selectedAcademicYear) {
            $currentAcademicYear = AcademicYear::current();
            if ($currentAcademicYear) {
                $selectedAcademicYear = $currentAcademicYear->id;
            }
        }

        return view('school.students.index', compact(
            'classes', 'streams', 'academicYears',
            'selectedClass', 'selectedStream', 'selectedAcademicYear'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $classes = Classe::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->orderBy('name')
            ->get();

        $streams = Stream::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $routes = Route::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->orderBy('route_name')
            ->get();

        return view('school.students.create', compact('classes', 'streams', 'routes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Decode class_id and stream_id if they are encoded (from AJAX dropdowns)
        $classId = $request->class_id;
        $streamId = $request->stream_id;

        try {
            if ($classId && !is_numeric($classId)) {
                $decoded = \Vinkla\Hashids\Facades\Hashids::decode($classId);
                $classId = $decoded[0] ?? $classId;
            }
        } catch (\Exception $e) {
            // If decode fails, try as regular ID
        }

        try {
            if ($streamId && !is_numeric($streamId)) {
                $decoded = \Vinkla\Hashids\Facades\Hashids::decode($streamId);
                $streamId = $decoded[0] ?? $streamId;
            }
        } catch (\Exception $e) {
            // If decode fails, try as regular ID
        }

        // Merge decoded IDs back into request for validation
        $request->merge(['class_id' => $classId, 'stream_id' => $streamId]);

        // Build validation rules dynamically based on discount type
        $validationRules = [
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'required|exists:streams,id',
            'admission_number' => 'nullable|string|max:50|unique:students,admission_number',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'required|string|max:500',
            'admission_date' => 'nullable|date',
            'boarding_type' => 'nullable|in:day,boarding',
            'has_transport' => 'nullable|in:yes,no',
            'bus_stop_id' => 'nullable|exists:bus_stops,id',
            'discount_type' => 'nullable|in:fixed,percentage',
            'passport_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        // Add discount_value validation based on discount_type
        if ($request->discount_type === 'percentage') {
            $validationRules['discount_value'] = 'nullable|numeric|min:0|max:100';
        } else {
            $validationRules['discount_value'] = 'nullable|numeric|min:0';
        }

        // Custom validation messages
        $validationMessages = [
            'admission_number.unique' => 'Namba ya uandikishaji :input tayari ipo. Tafadhali tumia namba tofauti.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'class_id.required' => 'Class is required.',
            'stream_id.required' => 'Stream is required.',
            'address.required' => 'Address is required.',
            'discount_value.max' => 'Percentage discount cannot exceed 100%.',
        ];

        // Additional validation: if discount_type is set, discount_value must be provided and vice versa
        $request->validate($validationRules, $validationMessages);

        // Custom validation for discount fields
        if ($request->discount_type && !$request->discount_value) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['discount_value' => 'Discount value is required when discount type is selected.']);
        }

        if ($request->discount_value && !$request->discount_type) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['discount_type' => 'Discount type is required when discount value is provided.']);
        }

        // Validate that class and stream belong to user's company/branch
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Use decoded IDs (already decoded above)
        $classId = $request->class_id;
        $streamId = $request->stream_id;

        $class = Classe::where('id', $classId)
            ->where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->first();

        if (!$class) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['class_id' => 'The selected class is invalid.']);
        }

        $stream = Stream::where('id', $streamId)
            ->where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->first();

        if (!$stream) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['stream_id' => 'The selected stream is invalid.']);
        }

        // Automatically assign current academic year
        $currentAcademicYear = AcademicYear::current();
        if (!$currentAcademicYear) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['academic_year' => 'No current academic year is set. Please set a current academic year first.']);
        }

        // Create student
        $studentData = $request->only([
            'class_id', 'stream_id', 'admission_number', 'first_name', 'last_name',
            'date_of_birth', 'gender', 'address', 'admission_date',
            'boarding_type', 'has_transport', 'bus_stop_id', 'discount_type', 'discount_value'
        ]);

        // Automatically assign company and branch from authenticated user
        $studentData['company_id'] = Auth::user()->company_id;
        $studentData['branch_id'] = session('branch_id') ?: Auth::user()->branch_id;

        // Set the current academic year
        $studentData['academic_year_id'] = $currentAcademicYear->id;

        // Handle passport photo upload
        if ($request->hasFile('passport_photo')) {
            $photoPath = $request->file('passport_photo')->store('students/photos', 'public');
            $studentData['passport_photo'] = $photoPath;
        }

        $student = Student::create($studentData);

        // Dispatch job to create LIPISHA customer (runs after student is saved)
        // Only if LIPISHA integration is enabled
        if (\App\Services\LipishaService::isEnabled()) {
            try {
                \App\Jobs\CreateLipishaCustomerForStudent::dispatch(
                    $student->id,
                    $request->phone ?? null,
                    $request->email ?? null
                )->onQueue('default');
                
                \Log::info('LIPISHA customer creation job dispatched for new student', [
                    'student_id' => $student->id,
                    'has_phone' => !empty($request->phone),
                    'has_email' => !empty($request->email)
                ]);
            } catch (\Exception $e) {
                \Log::error('Exception dispatching LIPISHA customer creation job for new student', [
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't fail the student creation if job dispatch fails - job will retry automatically
            }
        } else {
            \Log::info('LIPISHA integration is disabled - skipping customer creation job for new student', [
                'student_id' => $student->id
            ]);
        }

        return redirect()->route('school.students.index')
            ->with('success', 'Student created successfully and assigned to current academic year: ' . $currentAcademicYear->year_name);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

        $student = Student::with([
            'class.students',
            'stream',
            'route',
            'busStop.bus',
            'busStop.routes.buses',
            'academicYear',
            'guardians',
            'feeInvoices.feeGroup',
            'feeInvoices.academicYear',
            'feeInvoices.classe'
        ])->findOrFail($id);

        return view('school.students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

        $student = Student::with(['class', 'stream', 'busStop', 'academicYear', 'guardians'])->findOrFail($id);
        
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $classes = Classe::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->orderBy('name')
            ->get();

        $streams = Stream::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $routes = Route::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->orderBy('route_name')
            ->get();

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->orderBy('year_name', 'desc')
            ->get();

        return view('school.students.edit', compact('student', 'classes', 'streams', 'routes', 'academicYears'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

        $student = Student::findOrFail($id);

        // Check if this is a discount-only update (from fee invoice page)
        $isDiscountUpdate = $request->has('discount_type') || $request->has('discount_value');

        if ($isDiscountUpdate && !$request->has('first_name')) {
            // Partial update for discount only
            $request->validate([
                'discount_type' => 'nullable|in:fixed,percentage',
                'discount_value' => 'nullable|numeric|min:0' . ($request->discount_type === 'percentage' ? '|max:100' : '|max:100000'),
            ]);

            // Update only discount fields
            $student->update([
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
            ]);

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student discount updated successfully.',
                    'student' => $student
                ]);
            }

            return redirect()->back()
                ->with('success', 'Student discount updated successfully.');
        }

        // Decode Hash IDs if they are provided
        $classId = $request->class_id;
        $streamId = $request->stream_id;
        $academicYearId = $request->academic_year_id;
        
        // Try to decode if they are Hash IDs
        try {
            $decodedClassId = \Vinkla\Hashids\Facades\Hashids::decode($classId);
            if (!empty($decodedClassId)) {
                $classId = $decodedClassId[0];
            }
        } catch (\Exception $e) {
            // Not a Hash ID, use as is
        }
        
        try {
            $decodedStreamId = \Vinkla\Hashids\Facades\Hashids::decode($streamId);
            if (!empty($decodedStreamId)) {
                $streamId = $decodedStreamId[0];
            }
        } catch (\Exception $e) {
            // Not a Hash ID, use as is
        }
        
        try {
            $decodedAcademicYearId = \Vinkla\Hashids\Facades\Hashids::decode($academicYearId);
            if (!empty($decodedAcademicYearId)) {
                $academicYearId = $decodedAcademicYearId[0];
            }
        } catch (\Exception $e) {
            // Not a Hash ID, use as is
        }
        
        // Verify that class, stream, and academic year exist and belong to user's company/branch
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        
        $class = Classe::where('id', $classId)
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->first();
        
        if (!$class) {
            return redirect()->back()
                ->withErrors(['class_id' => 'The selected class is invalid.'])
                ->withInput();
        }
        
        $stream = Stream::where('id', $streamId)
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->first();
        
        if (!$stream) {
            return redirect()->back()
                ->withErrors(['stream_id' => 'The selected stream is invalid.'])
                ->withInput();
        }
        
        $academicYear = AcademicYear::where('id', $academicYearId)
            ->where('company_id', $companyId)
            ->first();
        
        if (!$academicYear) {
            return redirect()->back()
                ->withErrors(['academic_year_id' => 'The selected academic year is invalid.'])
                ->withInput();
        }
        
        // Full update validation (without exists checks for IDs we've already validated)
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'admission_number' => 'required|string|max:50|unique:students,admission_number,' . $id,
            'admission_date' => 'nullable|date',
            'address' => 'required|string|max:500',
            'boarding_type' => 'nullable|in:day,boarding',
            'has_transport' => 'nullable|in:yes,no',
            'bus_stop_id' => 'nullable|exists:bus_stops,id',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0|max:100000',
            'passport_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'admission_number.unique' => 'Admission number :input already exists. Please use a different number.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'academic_year_id.required' => 'Academic year is required.',
            'class_id.required' => 'Class is required.',
            'stream_id.required' => 'Stream is required.',
            'address.required' => 'Address is required.',
        ]);

        // Update student data (use decoded IDs)
        $studentData = [
            'academic_year_id' => $academicYearId,
            'class_id' => $classId,
            'stream_id' => $streamId,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'admission_number' => $request->admission_number,
            'admission_date' => $request->admission_date,
            'address' => $request->address,
            'boarding_type' => $request->boarding_type,
            'has_transport' => $request->has_transport,
            'bus_stop_id' => $request->bus_stop_id,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
        ];

        // Handle passport photo upload
        if ($request->hasFile('passport_photo')) {
            // Delete old photo if exists
            if ($student->passport_photo && \Storage::disk('public')->exists($student->passport_photo)) {
                \Storage::disk('public')->delete($student->passport_photo);
            }

            $photoPath = $request->file('passport_photo')->store('students/photos', 'public');
            $studentData['passport_photo'] = $photoPath;
        }

        $student->update($studentData);

        return redirect()->route('school.students.index')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('school.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * Show the form for assigning parents to a student.
     */
    public function assignParents(string $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

        $student = Student::with(['class', 'stream', 'guardians'])->findOrFail($id);
        return view('school.students.assign-parents', compact('student'));
    }

    /**
     * Store assigned parents for a student.
     */
    public function storeAssignedParents(Request $request, string $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

        $student = Student::findOrFail($id);

        $request->validate([
            'guardians' => 'required|array|min:1',
            'guardians.*.name' => 'required|string|max:255',
            'guardians.*.relationship' => 'required|string|max:100',
            'guardians.*.phone' => 'required|string|max:20',
            'guardians.*.alt_phone' => 'nullable|string|max:20',
            'guardians.*.email' => 'nullable|email',
            'guardians.*.occupation' => 'nullable|string|max:255',
            'guardians.*.address' => 'required|string|max:500',
        ]);

        // Create parents/guardians and attach to student
        foreach ($request->guardians as $guardianData) {
            // Check if guardian with this email already exists
            if (!empty($guardianData['email'])) {
                $guardian = Guardian::where('email', $guardianData['email'])->first();
            } else {
                $guardian = null;
            }

            // If guardian doesn't exist, create new one with password
            if (!$guardian) {
                // Guardian model has 'password' => 'hashed' cast, so we pass plain text
                $guardian = Guardian::create([
                    'name' => $guardianData['name'],
                    'phone' => $guardianData['phone'],
                    'alt_phone' => $guardianData['alt_phone'] ?? null,
                    'email' => $guardianData['email'] ?? null,
                    'occupation' => $guardianData['occupation'] ?? null,
                    'address' => $guardianData['address'],
                    'password' => '12345', // Will be hashed automatically by model cast
                ]);
            } else {
                // Update existing guardian's information if needed
                $updateData = [
                    'name' => $guardianData['name'],
                    'phone' => $guardianData['phone'],
                    'alt_phone' => $guardianData['alt_phone'] ?? null,
                    'occupation' => $guardianData['occupation'] ?? null,
                    'address' => $guardianData['address'],
                ];

                // Set password if it's null
                if (!$guardian->password) {
                    $updateData['password'] = Hash::make('12345');
                }
                
                $guardian->update($updateData);
            }

            // Check if this guardian is already attached to the student
            if (!$student->guardians()->where('guardian_id', $guardian->id)->exists()) {
                // Create the relationship
                $student->guardians()->attach($guardian->id, ['relationship' => $guardianData['relationship']]);
            }
        
        }

        return redirect()->route('school.students.index', ['refresh' => '1'])
            ->with('success', 'Guardians assigned to student successfully.');
    }

    /**
     * Remove a parent from a student.
     */
    public function removeParent(string $encodedId, string $parentId)
    {
        $studentId = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

        $student = Student::findOrFail($studentId);
        $student->guardians()->detach($parentId);

        return redirect()->back()
            ->with('success', 'Parent removed from student successfully.');
    }

    /**
     * Get streams for a specific class via AJAX.
     */
    public function getStreamsByClass(Request $request)
    {
        $classId = $request->get('class_id');
        $search = $request->get('q', '');

        if (!$classId) {
            return response()->json(['streams' => []]);
        }

        // Decode hash ID if needed
        try {
            if (!is_numeric($classId)) {
                $decoded = \Vinkla\Hashids\Facades\Hashids::decode($classId);
                $classId = $decoded[0] ?? $classId;
            }
        } catch (\Exception $e) {
            // If decode fails, try as regular ID
        }

        $class = Classe::find($classId);

        if (!$class) {
            return response()->json(['streams' => []]);
        }

        // Get authenticated user info
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Ensure the class belongs to user's company/branch
        if ($class->company_id !== $companyId ||
            ($branchId && $class->branch_id !== $branchId && $class->branch_id !== null)) {
            return response()->json(['streams' => []]);
        }

        $streamsQuery = $class->streams()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->select('streams.id', 'streams.name');

        if (!empty($search)) {
            $streamsQuery->where('streams.name', 'LIKE', '%' . $search . '%');
        }

        $streams = $streamsQuery->get();

        // Encode stream IDs
        $streams = $streams->map(function($stream) {
            return [
                'id' => \Vinkla\Hashids\Facades\Hashids::encode($stream->id),
                'name' => $stream->name
            ];
        });

        return response()->json(['streams' => $streams]);
    }

    /**
     * Get bus stops via AJAX.
     */
    public function getBusStops(Request $request)
    {
        $search = $request->get('q', '');
        $routeId = $request->get('route_id');

        $query = BusStop::query();

        if (!empty($routeId)) {
            $query->whereHas('routes', function($q) use ($routeId) {
                $q->where('routes.id', $routeId);
            });
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('stop_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('stop_code', 'LIKE', '%' . $search . '%');
            });
        }

        $busStops = $query->active()
                          ->orderBy('stop_name')
                          ->get(['id', 'stop_name', 'stop_code', 'fare']);

        return response()->json(['bus_stops' => $busStops]);
    }

    /**
     * Get students data for DataTables.
     */
    public function data(Request $request)
    {
        $query = Student::with(['class', 'stream', 'guardians', 'academicYear'])
            ->forCompany(Auth::user()->company_id);

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId) {
            $query->forBranch($branchId);
        }

        // Apply default filter for current academic year if no academic year is selected
        if (!$request->filled('academic_year_id')) {
            $currentAcademicYear = AcademicYear::current();
            if ($currentAcademicYear) {
                $query->where('academic_year_id', $currentAcademicYear->id);
            }
        }

        // Apply filters if provided
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('stream_id')) {
            $query->where('stream_id', $request->stream_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $searchValue = $request->search['value'];

                    $query->where(function ($q) use ($searchValue) {
                        $q->where('first_name', 'LIKE', '%' . $searchValue . '%')
                          ->orWhere('last_name', 'LIKE', '%' . $searchValue . '%');
                    });
                }
            })
            ->addIndexColumn()
            ->addColumn('full_name', function ($student) {
                return $student->first_name . ' ' . $student->last_name;
            })
            ->addColumn('class_name', function ($student) {
                return $student->class->name ?? 'N/A';
            })
            ->addColumn('stream_name', function ($student) {
                return $student->stream->name ?? 'N/A';
            })
            ->addColumn('boarding_badge', function ($student) {
                $type = $student->boarding_type ?? 'day';
                $badgeClass = $type === 'boarding' ? 'warning' : 'secondary';
                return '<span class="badge bg-' . $badgeClass . '">' . ucfirst($type) . '</span>';
            })
            ->addColumn('gender_badge', function ($student) {
                $gender = $student->gender;
                $badgeClass = match($gender) {
                    'male' => 'primary',
                    'female' => 'success',
                    default => 'info'
                };
                return '<span class="badge bg-' . $badgeClass . '">' . ucfirst($gender) . '</span>';
            })
            ->addColumn('guardian_info', function ($student) {
                if ($student->guardians && $student->guardians->isNotEmpty()) {
                    $guardian = $student->guardians->first();
                    return '<div><div class="fw-bold">' . $guardian->name . '</div><small class="text-muted">' . $guardian->phone . '</small></div>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('discount_info', function ($student) {
                if ($student->discount_type && $student->discount_value) {
                    $type = ucfirst($student->discount_type);
                    $value = $student->discount_value;
                    $unit = $student->discount_type === 'percentage' ? '%' : 'Amount';
                    return '<div><div class="fw-bold">' . $value . ' ' . $unit . '</div><small class="text-muted">' . $type . '</small></div>';
                }
                return '<span class="text-muted">No Discount</span>';
            })
            ->addColumn('actions', function ($student) {
                return view('school.students.partials.actions', compact('student'))->render();
            })
            ->rawColumns(['boarding_badge', 'gender_badge', 'guardian_info', 'discount_info', 'actions'])
            ->make(true);
    }

    /**
     * Update a guardian for a student.
     */
    public function updateGuardian(Request $request, string $encodedId, string $guardianId)
    {
        $studentId = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

        $student = Student::findOrFail($studentId);
        $guardian = Guardian::findOrFail($guardianId);

        $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'alt_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'required|string|max:500',
        ]);

        // Update guardian information
        $guardian->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'alt_phone' => $request->alt_phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        // Update the relationship if it changed
        $student->guardians()->updateExistingPivot($guardian->id, [
            'relationship' => $request->relationship
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Guardian updated successfully.'
        ]);
    }

    /**
     * Remove a guardian from a student.
     */
    public function removeGuardian(string $encodedId, string $guardianId)
    {
        $studentId = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

        $student = Student::findOrFail($studentId);
        $guardian = Guardian::findOrFail($guardianId);

        // Remove the relationship
        $student->guardians()->detach($guardian->id);

        return response()->json([
            'success' => true,
            'message' => 'Guardian removed successfully.'
        ]);
    }

    /**
     * Search parents via AJAX.
     */
    public function searchParents(Request $request, string $encodedId)
    {
        $studentId = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

        if (!$studentId) {
            return response()->json(['parents' => []]);
        }

        $student = Student::find($studentId);

        if (!$student) {
            return response()->json(['parents' => []]);
        }

        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json(['parents' => []]);
        }

        // Get IDs of parents already assigned to this student
        $assignedGuardianIds = $student->guardians()->pluck('guardian_id')->toArray();

        $parents = Guardian::where(function($q) use ($query) {
            $q->where('name', 'LIKE', '%' . $query . '%')
              ->orWhere('phone', 'LIKE', '%' . $query . '%')
              ->orWhere('alt_phone', 'LIKE', '%' . $query . '%')
              ->orWhere('email', 'LIKE', '%' . $query . '%');
        })
        ->whereNotIn('id', $assignedGuardianIds) // Exclude already assigned guardians
        ->select('id', 'name', 'phone', 'alt_phone', 'email', 'address', 'occupation')
        ->orderBy('name')
        ->limit(20)
        ->get();

        return response()->json(['parents' => $parents]);
    }

    /**
     * Assign existing parents to a student.
     */
    public function assignExistingParents(Request $request, string $encodedId)
    {
        try {
            $studentId = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid student ID.'
                ], 400);
            }

            $student = Student::findOrFail($studentId);

            $request->validate([
                'guardians' => 'required|array|min:1',
                'guardians.*.id' => 'required|exists:guardians,id',
                'guardians.*.relationship' => 'required|string|max:100',
            ]);

            $assignedCount = 0;
            $alreadyAssignedCount = 0;

            foreach ($request->guardians as $guardianData) {
                $guardian = Guardian::findOrFail($guardianData['id']);

                // Check if this guardian is already attached to the student
                if (!$student->guardians()->where('guardian_id', $guardian->id)->exists()) {
                    // Create the relationship
                    $student->guardians()->attach($guardian->id, ['relationship' => $guardianData['relationship']]);
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
                'student_id' => $encodedId,
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while assigning parents. Please try again.'
            ], 500);
        }
    }

    /**
     * Export students to Excel
     */
    public function exportExcel(Request $request, string $hashId = null)
    {
        // HashId is used for URL uniqueness but not validated for security
        // since it's generated client-side for cache-busting purposes

        $query = Student::with(['class', 'stream', 'guardians', 'academicYear'])
            ->forCompany(Auth::user()->company_id);

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId) {
            $query->forBranch($branchId);
        }

        // Apply filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('stream_id')) {
            $query->where('stream_id', $request->stream_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        } else {
            // Default to current academic year
            $currentAcademicYear = AcademicYear::current();
            if ($currentAcademicYear) {
                $query->where('academic_year_id', $currentAcademicYear->id);
            }
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
                    'Admission No',
                    'First Name',
                    'Last Name',
                    'Class',
                    'Stream',
                    'Gender',
                    'Date of Birth',
                    'Admission Date',
                    'Address',
                    'Boarding Type',
                    'Has Transport',
                    'Bus Stop',
                    'Academic Year',
                    'Guardian Name',
                    'Guardian Phone',
                    'Guardian Email'
                ];
            }

            public function map($student): array
            {
                $guardian = $student->guardians->first();

                return [
                    $student->admission_number,
                    $student->first_name,
                    $student->last_name,
                    $student->class->name ?? 'N/A',
                    $student->stream->name ?? 'N/A',
                    ucfirst($student->gender ?? 'N/A'),
                    $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : 'N/A',
                    $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('Y-m-d') : 'N/A',
                    $student->address ?? 'N/A',
                    ucfirst($student->boarding_type ?? 'day'),
                    $student->has_transport === 'yes' ? 'Yes' : 'No',
                    $student->busStop->stop_name ?? 'N/A',
                    $student->academicYear->year_name ?? 'N/A',
                    $guardian->name ?? 'N/A',
                    $guardian->phone ?? 'N/A',
                    $guardian->email ?? 'N/A'
                ];
            }
        }, 'students_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Export students to PDF
     */
    public function exportPdf(Request $request, string $hashId = null)
    {
        // HashId is used for URL uniqueness but not validated for security
        // since it's generated client-side for cache-busting purposes

        $query = Student::with(['class', 'stream', 'guardians', 'academicYear'])
            ->forCompany(Auth::user()->company_id);

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId) {
            $query->forBranch($branchId);
        }

        // Apply filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('stream_id')) {
            $query->where('stream_id', $request->stream_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        } else {
            // Default to current academic year
            $currentAcademicYear = AcademicYear::current();
            if ($currentAcademicYear) {
                $query->where('academic_year_id', $currentAcademicYear->id);
            }
        }

        $students = $query->orderBy('first_name')->orderBy('last_name')->get();

        $data = [
            'students' => $students,
            'company' => Company::first(), // Get the first company (assuming single company system)
            'title' => 'Students Report',
            'filters' => [
                'class' => $request->filled('class_id') ? Classe::find($request->class_id)->name ?? 'All' : 'All',
                'stream' => $request->filled('stream_id') ? Stream::find($request->stream_id)->name ?? 'All' : 'All',
                'academic_year' => $request->filled('academic_year_id') ? AcademicYear::find($request->academic_year_id)->year_name ?? 'All' : (AcademicYear::current()->year_name ?? 'Current'),
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('school.students.exports.pdf', $data);
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions(['isRemoteEnabled' => false, 'isHtml5ParserEnabled' => true]);

        return $pdf->download('students_report_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Show the import form
     */
    public function import()
    {
        return view('school.students.import');
    }

    /**
     * Process the Excel import
     */
    public function processImport(Request $request)
    {
        // Decode hash IDs if they are encoded
        $classId = $request->class_id;
        $streamId = $request->stream_id;
        
        try {
            if ($classId && !is_numeric($classId)) {
                $decoded = \Vinkla\Hashids\Facades\Hashids::decode($classId);
                if (!empty($decoded)) {
                    $classId = $decoded[0];
                }
            }
            
            if ($streamId && !is_numeric($streamId)) {
                $decoded = \Vinkla\Hashids\Facades\Hashids::decode($streamId);
                if (!empty($decoded)) {
                    $streamId = $decoded[0];
                }
            }
        } catch (\Exception $e) {
            // If decoding fails, assume they are regular IDs
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        // Get authenticated user info
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get the selected class and stream, ensuring they belong to user's company/branch
        $class = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->find($classId);
            
        if (!$class) {
            return redirect()->back()->withErrors(['class_id' => 'The selected class is invalid.']);
        }

        // Check if company_id and branch_id columns exist in streams table
        $streamQuery = Stream::query();
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('streams', 'company_id')) {
            $streamQuery->where('company_id', $companyId);
        }
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('streams', 'branch_id')) {
            $streamQuery->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });
        }
        
        $stream = $streamQuery->find($streamId);
        
        if (!$stream) {
            return redirect()->back()->withErrors(['stream_id' => 'The selected stream is invalid.']);
        }

        // Get current academic year
        $currentAcademicYear = AcademicYear::current();
        if (!$currentAcademicYear) {
            return redirect()->back()->withErrors(['excel_file' => 'No current academic year is set. Please set a current academic year first.']);
        }

        try {
            $file = $request->file('excel_file');

            // Debug: Log file information
            \Log::info('Starting import', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_mime' => $file->getMimeType(),
                'class_id' => $classId,
                'stream_id' => $streamId,
                'decoded_class_id' => $class->id,
                'decoded_stream_id' => $stream->id,
                'academic_year_id' => $currentAcademicYear->id ?? null
            ]);

            // Import the data using the StudentSheetImport class directly
            // This will import from the first sheet regardless of name
            $import = new StudentSheetImport($class, $stream, $currentAcademicYear);

            Excel::import($import, $file);

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            // Debug logging
            \Log::info('Import completed', [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]);

            if ($successCount > 0) {
                $message = "Successfully imported {$successCount} student(s) into {$class->name} - {$stream->name}.";
                if ($errorCount > 0) {
                    $message .= " {$errorCount} row(s) had errors and were skipped.";
                }
                return redirect()->route('school.students.index')->with('success', $message);
            } else {
                $errorMessage = 'No students were imported. Please check your file format and data.';
                if (!empty($errors)) {
                    $errorMessage .= ' Errors: ' . implode('; ', array_slice($errors, 0, 5)); // Show first 5 errors
                    if (count($errors) > 5) {
                        $errorMessage .= '... (' . (count($errors) - 5) . ' more errors)';
                    }
                }
                return redirect()->back()->withErrors(['excel_file' => $errorMessage]);
            }

        } catch (\Exception $e) {
            \Log::error('Student import error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['excel_file' => 'An error occurred during import: ' . $e->getMessage()]);
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate(Request $request)
    {
        $classId = $request->get('class_id');
        $streamId = $request->get('stream_id');

        // Decode hash IDs if they are encoded
        try {
            if ($classId) {
                $decoded = \Vinkla\Hashids\Facades\Hashids::decode($classId);
                if (!empty($decoded)) {
                    $classId = $decoded[0];
                }
            }
            
            if ($streamId) {
                $decoded = \Vinkla\Hashids\Facades\Hashids::decode($streamId);
                if (!empty($decoded)) {
                    $streamId = $decoded[0];
                }
            }
        } catch (\Exception $e) {
            // If decoding fails, assume they are regular IDs
        }

        // Validate that class and stream exist if provided
        $class = null;
        $stream = null;

        if ($classId) {
            $class = Classe::where('company_id', Auth::user()->company_id)->find($classId);
            if (!$class) {
                return redirect()->back()->withErrors(['template' => 'Selected class not found.']);
            }
        }

        if ($streamId) {
            $stream = Stream::where('company_id', Auth::user()->company_id)->find($streamId);
            if (!$stream) {
                return redirect()->back()->withErrors(['template' => 'Selected stream not found.']);
            }
        }

        return Excel::download(new class($class, $stream) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithEvents, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
            private $class;
            private $stream;

            public function __construct($class, $stream)
            {
                $this->class = $class;
                $this->stream = $stream;
            }

            public function array(): array
            {
                $className = $this->class ? $this->class->name : 'Class One';
                $streamName = $this->stream ? $this->stream->name : 'RED';

                return [
                    [
                        'admission_no' => 'ADM001',
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'gender' => 'male',
                        'date_of_birth' => '2005-01-15',
                        'admission_date' => date('Y-m-d'),
                        'address' => '123 Main Street, Nairobi',
                        'boarding_type' => 'day',
                        'has_transport' => 'yes',
                        'bus_stop' => 'Westlands',
                        'guardian_name' => 'Jane Doe',
                        'guardian_phone' => '+254712345678',
                        'guardian_email' => 'jane.doe@example.com',
                        'discount_type' => 'percentage',
                        'discount_value' => '10.00',
                    ],
                    [
                        'admission_no' => 'ADM002',
                        'first_name' => 'Mary',
                        'last_name' => 'Smith',
                        'gender' => 'female',
                        'date_of_birth' => '2004-03-20',
                        'admission_date' => date('Y-m-d'),
                        'address' => '456 Oak Avenue, Nairobi',
                        'boarding_type' => 'boarding',
                        'has_transport' => 'no',
                        'bus_stop' => '',
                        'guardian_name' => 'Robert Smith',
                        'guardian_phone' => '+254798765432',
                        'guardian_email' => 'robert.smith@example.com',
                        'discount_type' => 'fixed',
                        'discount_value' => '5000.00',
                    ],
                ];
            }

            public function headings(): array
            {
                return [
                    'Admission No',
                    'First Name',
                    'Last Name',
                    'Gender',
                    'Date of Birth',
                    'Admission Date',
                    'Address',
                    'Boarding Type',
                    'Has Transport',
                    'Bus Stop',
                    'Guardian Name',
                    'Guardian Phone',
                    'Guardian Email',
                    'Discount Type',
                    'Discount Value',
                ];
            }

            public function registerEvents(): array
            {
                return [
                    \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $workbook = $event->sheet->getDelegate()->getParent();

                        // Set the main sheet title
                        $sheet->setTitle('Students');

                        // Get bus stops for dropdown
                        $busStops = \App\Models\School\BusStop::active()->orderBy('stop_name')->pluck('stop_name')->toArray();
                        if (empty($busStops)) {
                            $busStops = ['Westlands', 'CBD', 'Karen', 'Kilimani', 'Parklands'];
                        }

                        // Add dropdown data to a hidden sheet
                        $dropdownSheet = $workbook->createSheet();
                        $dropdownSheet->setTitle('DropdownData');

                        // Gender options (row 1)
                        $dropdownSheet->setCellValue('A1', 'Gender Options');
                        $dropdownSheet->setCellValue('A2', 'male');
                        $dropdownSheet->setCellValue('A3', 'female');

                        // Has Transport options (row 5)
                        $dropdownSheet->setCellValue('B1', 'Transport Options');
                        $dropdownSheet->setCellValue('B2', 'yes');
                        $dropdownSheet->setCellValue('B3', 'no');

                        // Boarding Type options (row 7)
                        $dropdownSheet->setCellValue('C1', 'Boarding Options');
                        $dropdownSheet->setCellValue('C2', 'day');
                        $dropdownSheet->setCellValue('C3', 'boarding');

                        // Bus Stops (starting from row 9)
                        $dropdownSheet->setCellValue('D1', 'Bus Stop Options');
                        foreach ($busStops as $index => $busStop) {
                            $dropdownSheet->setCellValue('D' . ($index + 2), $busStop);
                        }

                        // Discount Type options (starting from row 9 + bus stops count + 2)
                        $discountTypeRow = count($busStops) + 11;
                        $dropdownSheet->setCellValue('E1', 'Discount Type Options');
                        $dropdownSheet->setCellValue('E2', 'fixed');
                        $dropdownSheet->setCellValue('E3', 'percentage');

                        // Hide the dropdown data sheet
                        $dropdownSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

                        // Create data validation for Gender column (D)
                        $validation = $sheet->getDataValidation('D2:D1000');
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(false);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Input error');
                        $validation->setError('Value is not in list.');
                        $validation->setPromptTitle('Pick from list');
                        $validation->setPrompt('Please pick a value from the drop-down list.');
                        $validation->setFormula1('DropdownData!$A$2:$A$3');

                        // Create data validation for Has Transport column (I)
                        $validation = $sheet->getDataValidation('I2:I1000');
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(false);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Input error');
                        $validation->setError('Value is not in list.');
                        $validation->setPromptTitle('Pick from list');
                        $validation->setPrompt('Please pick a value from the drop-down list.');
                        $validation->setFormula1('DropdownData!$B$2:$B$3');

                        // Create data validation for Boarding Type column (H)
                        $validation = $sheet->getDataValidation('H2:H1000');
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(false);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Input error');
                        $validation->setError('Value is not in list.');
                        $validation->setPromptTitle('Pick from list');
                        $validation->setPrompt('Please pick a value from the drop-down list.');
                        $validation->setFormula1('DropdownData!$C$2:$C$3');

                        // Create data validation for Bus Stop column (J)
                        $busStopRange = 'DropdownData!$D$2:$D$' . (count($busStops) + 1);
                        $validation = $sheet->getDataValidation('J2:J1000');
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(true); // Allow blank for bus stops
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Input error');
                        $validation->setError('Value is not in list.');
                        $validation->setPromptTitle('Pick from list');
                        $validation->setPrompt('Please pick a bus stop from the drop-down list.');
                        $validation->setFormula1($busStopRange);

                        // Create data validation for Discount Type column (N)
                        $validation = $sheet->getDataValidation('N2:N1000');
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(true); // Allow blank for discount type
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Input error');
                        $validation->setError('Value is not in list.');
                        $validation->setPromptTitle('Pick from list');
                        $validation->setPrompt('Please pick a discount type from the drop-down list.');
                        $validation->setFormula1('DropdownData!$E$2:$E$3');

                        // Create Excel Table
                        $tableRange = 'A1:O3'; // Headers + 2 sample rows (now 15 columns)
                        $table = new \PhpOffice\PhpSpreadsheet\Worksheet\Table($tableRange, 'StudentImportTable');
                        $table->setShowHeaderRow(true);
                        $table->setShowTotalsRow(false);
                        $table->setAllowFilter(true);
                        $sheet->addTable($table);

                        // Style the header row
                        $headerStyle = $sheet->getStyle('A1:O1');
                        $headerStyle->getFont()->setBold(true);
                        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                        $headerStyle->getFill()->getStartColor()->setRGB('E6E6FA'); // Light lavender
                        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                        // Add data formatting for date columns
                        $sheet->getStyle('E2:E1000')->getNumberFormat()->setFormatCode('yyyy-mm-dd');
                        $sheet->getStyle('F2:F1000')->getNumberFormat()->setFormatCode('yyyy-mm-dd');

                        // Set column widths
                        $sheet->getColumnDimension('A')->setWidth(15); // Admission No
                        $sheet->getColumnDimension('B')->setWidth(15); // First Name
                        $sheet->getColumnDimension('C')->setWidth(15); // Last Name
                        $sheet->getColumnDimension('D')->setWidth(10); // Gender
                        $sheet->getColumnDimension('E')->setWidth(12); // Date of Birth
                        $sheet->getColumnDimension('F')->setWidth(12); // Admission Date
                        $sheet->getColumnDimension('G')->setWidth(25); // Address
                        $sheet->getColumnDimension('H')->setWidth(12); // Boarding Type
                        $sheet->getColumnDimension('I')->setWidth(12); // Has Transport
                        $sheet->getColumnDimension('J')->setWidth(15); // Bus Stop
                        $sheet->getColumnDimension('K')->setWidth(15); // Guardian Name
                        $sheet->getColumnDimension('L')->setWidth(15); // Guardian Phone
                        $sheet->getColumnDimension('M')->setWidth(20); // Guardian Email
                        $sheet->getColumnDimension('N')->setWidth(12); // Discount Type
                        $sheet->getColumnDimension('O')->setWidth(12); // Discount Value

                        // Add instructions
                        $sheet->setCellValue('A5', 'INSTRUCTIONS:');
                        $sheet->setCellValue('A6', '1. IMPORTANT: Delete rows 2-3 (sample data) before filling your data');
                        $sheet->setCellValue('A7', '2. Start entering your student data from row 2');
                        $sheet->setCellValue('A8', '3. Use dropdown lists for Gender, Boarding Type, Has Transport, and Bus Stop columns');
                        $sheet->setCellValue('A9', '4. Date format should be YYYY-MM-DD (e.g., 2005-01-15) or Excel date format');
                        $sheet->setCellValue('A10', '5. Phone numbers should include country code (e.g., +254712345678)');
                        $sheet->setCellValue('A11', '6. Bus Stop is only required if Has Transport is "yes"');
                        $sheet->setCellValue('A12', '7. Discount Type can be "fixed" (amount in KES) or "percentage" (0-100)');
                        $sheet->setCellValue('A13', '8. Discount Value should be a number (e.g., 5000 for fixed, 10 for 10% percentage)');
                        $sheet->setCellValue('A14', '9. Leave discount fields blank if no discount applies');
                        $sheet->setCellValue('A15', '10. Do NOT include the header row when importing - only data rows');
                        $sheet->setCellValue('A16', '11. The system will import from the first sheet in your Excel file');

                        // Style the instructions
                        $instructionStyle = $sheet->getStyle('A5:A16');
                        $instructionStyle->getFont()->setItalic(true);
                        $instructionStyle->getFont()->getColor()->setRGB('666666');
                    },
                ];
            }
        }, 'students_import_template.xlsx');
    }
}
