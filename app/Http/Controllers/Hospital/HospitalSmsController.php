<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Helpers\SmsHelper;
use App\Models\Hospital\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HospitalSmsController extends Controller
{
    public function searchPatients(Request $request)
    {
        $term = $request->get('q', $request->get('term', ''));
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        $patients = Patient::byCompany($companyId)
            ->byBranch($branchId)
            ->active()
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->search($term)
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $patients->map(fn (Patient $patient) => [
                'id' => $patient->id,
                'text' => trim("{$patient->mrn} — {$patient->full_name} ({$patient->phone})"),
            ])->values(),
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'send_mode' => 'required|in:single,bulk',
            'patient_id' => 'required_if:send_mode,single|nullable|integer|exists:patients,id',
            'message' => 'required|string|min:1|max:918',
        ]);

        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;
        $message = trim($request->message);

        if ($request->send_mode === 'single') {
            $patient = Patient::byCompany($companyId)
                ->byBranch($branchId)
                ->active()
                ->findOrFail($request->patient_id);

            if (empty($patient->phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected patient has no phone number.',
                ], 422);
            }

            $patients = collect([$patient]);
        } else {
            $patients = Patient::byCompany($companyId)
                ->byBranch($branchId)
                ->active()
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get();
        }

        if ($patients->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No patients with valid phone numbers found for this branch.',
            ], 422);
        }

        $sent = 0;
        $failed = 0;
        $skipped = 0;
        $sentNumbers = [];

        foreach ($patients as $patient) {
            $rawPhone = $patient->phone;
            $phone = function_exists('normalize_phone_number')
                ? normalize_phone_number($rawPhone)
                : preg_replace('/[^0-9+]/', '', $rawPhone);

            if (empty($phone) || strlen($phone) < 9) {
                $skipped++;
                continue;
            }

            if (in_array($phone, $sentNumbers, true)) {
                $skipped++;
                continue;
            }

            $sentNumbers[] = $phone;

            try {
                $smsResponse = SmsHelper::send($phone, $message);
                $responsePayload = is_array($smsResponse)
                    ? json_encode($smsResponse)
                    : (string) $smsResponse;

                if (is_array($smsResponse) && empty($smsResponse['success'])) {
                    $failed++;
                } else {
                    $sent++;
                }

                DB::table('sms_logs')->insert([
                    'customer_id' => null,
                    'phone_number' => $phone,
                    'message' => $message,
                    'response' => $responsePayload,
                    'sent_by' => $user->id,
                    'sent_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Hospital patient SMS failed', [
                    'patient_id' => $patient->id,
                    'phone' => $phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($sent === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No messages were sent. Check patient phone numbers and SMS configuration.',
                'sent' => $sent,
                'failed' => $failed,
                'skipped' => $skipped,
            ], 422);
        }

        $label = $request->send_mode === 'single' ? 'SMS sent to patient.' : "Bulk SMS sent to {$sent} patient(s).";

        if ($failed > 0 || $skipped > 0) {
            $label .= " Failed: {$failed}. Skipped: {$skipped}.";
        }

        return response()->json([
            'success' => true,
            'message' => $label,
            'sent' => $sent,
            'failed' => $failed,
            'skipped' => $skipped,
        ]);
    }
}
