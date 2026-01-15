<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\PatientDeletionRequest;
use App\Models\Hospital\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DeletionRequestController extends Controller
{
    /**
     * Display a listing of deletion requests
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?? Auth::user()->branch_id;

            $requests = PatientDeletionRequest::with(['patient', 'initiator', 'approver'])
                ->where('company_id', $companyId);
            
            // Show both branch-specific and company-wide (null) requests
            if ($branchId) {
                $requests->where(function($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                });
            } else {
                $requests->whereNull('branch_id');
            }

            // Filter by status if provided
            if ($request->has('status') && $request->status !== '') {
                $requests->where('status', $request->status);
            }

            return DataTables::of($requests)
                ->addIndexColumn()
                ->addColumn('patient_name', function ($request) {
                    return $request->patient ? $request->patient->first_name . ' ' . $request->patient->last_name : 'N/A';
                })
                ->addColumn('patient_mrn', function ($request) {
                    return $request->patient ? $request->patient->mrn : 'N/A';
                })
                ->addColumn('reason', function ($request) {
                    return \Str::limit($request->reason, 50);
                })
                ->addColumn('initiator_name', function ($request) {
                    return $request->initiator ? $request->initiator->name : 'N/A';
                })
                ->addColumn('status_badge', function ($request) {
                    if ($request->status === 'pending') {
                        return '<span class="badge bg-warning">Pending</span>';
                    } elseif ($request->status === 'approved') {
                        return '<span class="badge bg-success">Approved</span>';
                    } elseif ($request->status === 'rejected') {
                        return '<span class="badge bg-danger">Rejected</span>';
                    }
                    return '<span class="badge bg-secondary">' . ucfirst($request->status) . '</span>';
                })
                ->addColumn('created_at_formatted', function ($request) {
                    return $request->created_at->format('Y-m-d H:i');
                })
                ->addColumn('action', function ($request) {
                    $actions = '';
                    if ($request->status === 'pending') {
                        $actions .= '<a href="' . route('hospital.admin.deletion-requests.show', $request->id) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bx bx-show"></i></a>';
                        $actions .= '<button class="btn btn-sm btn-outline-success approve-btn me-1" data-id="' . $request->id . '"><i class="bx bx-check"></i></button>';
                        $actions .= '<button class="btn btn-sm btn-outline-danger reject-btn" data-id="' . $request->id . '"><i class="bx bx-x"></i></button>';
                    } else {
                        $actions .= '<a href="' . route('hospital.admin.deletion-requests.show', $request->id) . '" class="btn btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>';
                    }
                    return $actions;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('hospital.admin.deletion-requests.index');
    }

    /**
     * Show a specific deletion request
     */
    public function show($id)
    {
        $request = PatientDeletionRequest::with(['patient', 'initiator', 'approver', 'company', 'branch'])
            ->findOrFail($id);

        // Verify access - allow access if same company and (same branch or null branch)
        $user = Auth::user();
        $branchId = session('branch_id') ?? $user->branch_id;
        
        if ($request->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to deletion request.');
        }
        
        // Check branch access - allow if branch_id is null (company-wide) or matches user's branch
        if ($request->branch_id !== null && $request->branch_id !== $branchId) {
            abort(403, 'Unauthorized access to deletion request.');
        }

        return view('hospital.admin.deletion-requests.show', compact('request'));
    }

    /**
     * Approve a deletion request
     */
    public function approve(Request $request, $id)
    {
        $deletionRequest = PatientDeletionRequest::findOrFail($id);

        // Verify access
        if ($deletionRequest->company_id !== Auth::user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        // Check if already processed
        if ($deletionRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been processed.'
            ], 400);
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Update deletion request
            $deletionRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_notes' => $validated['approval_notes'] ?? null,
            ]);

            // Soft delete the patient
            $patient = Patient::findOrFail($deletionRequest->patient_id);
            $patient->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deletion request approved and patient deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve deletion request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a deletion request
     */
    public function reject(Request $request, $id)
    {
        $deletionRequest = PatientDeletionRequest::findOrFail($id);

        // Verify access
        if ($deletionRequest->company_id !== Auth::user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        // Check if already processed
        if ($deletionRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been processed.'
            ], 400);
        }

        $validated = $request->validate([
            'approval_notes' => 'required|string|min:10|max:1000',
        ]);

        try {
            $deletionRequest->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_notes' => $validated['approval_notes'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deletion request rejected successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject deletion request: ' . $e->getMessage()
            ], 500);
        }
    }
}
