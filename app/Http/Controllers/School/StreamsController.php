<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hashids\Hashids;
use Yajra\DataTables\Facades\DataTables;

class StreamsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('school.streams.index');
    }

    /**
     * Get streams data for DataTables.
     */
    public function data(Request $request)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Stream::query()
            ->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
            });

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('created_at_formatted', function ($stream) {
                return $stream->created_at->format('M d, Y');
            })
            ->addColumn('classes_count', function ($stream) {
                $count = $stream->classes()->count();
                $stream->classes_count = $count; // Add to stream object for actions
                return $count;
            })
            ->addColumn('actions', function ($stream) {
                return view('school.streams.partials.actions', compact('stream'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('school.streams.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'streams' => 'required|array|min:1',
            'streams.*.name' => 'required|string|max:255',
            'streams.*.description' => 'nullable|string'
        ]);

        // Check for duplicate names within the submitted streams
        $names = array_column($request->streams, 'name');
        if (count($names) !== count(array_unique($names))) {
            return back()->withErrors(['streams' => 'Duplicate stream names are not allowed.'])->withInput();
        }

        // Check for existing streams with the same names
        $existingStreams = Stream::whereIn('name', $names)->pluck('name')->toArray();
        if (!empty($existingStreams)) {
            return back()->withErrors(['streams' => 'The following stream names already exist: ' . implode(', ', $existingStreams)])->withInput();
        }

        $createdCount = 0;
        foreach ($request->streams as $streamData) {
            if (!empty(trim($streamData['name']))) {
                Stream::create([
                    'name' => trim($streamData['name']),
                    'description' => trim($streamData['description'] ?? ''),
                    'company_id' => Auth::user()->company_id,
                    'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
                ]);
                $createdCount++;
            }
        }

        return redirect()->route('school.streams.index')
            ->with('success', $createdCount . ' stream' . ($createdCount > 1 ? 's' : '') . ' created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $encodedId)
    {
        $hashids = new Hashids();
        $id = $hashids->decode($encodedId)[0] ?? null;

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $stream = Stream::where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })->with('classes')->findOrFail($id);

        return view('school.streams.show', compact('stream'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $encodedId)
    {
        $hashids = new Hashids();
        $id = $hashids->decode($encodedId)[0] ?? null;

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $stream = Stream::where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })->findOrFail($id);

        return view('school.streams.edit', compact('stream'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $encodedId)
    {
        $hashids = new Hashids();
        $id = $hashids->decode($encodedId)[0] ?? null;

        $request->validate([
            'name' => 'required|string|max:255|unique:streams,name,' . $id,
            'description' => 'nullable|string'
        ]);

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $stream = Stream::where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })->findOrFail($id);

        $stream->update($request->only(['name', 'description']));

        return redirect()->route('school.streams.index')
            ->with('success', 'Stream updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $encodedId)
    {
        $hashids = new Hashids();
        $id = $hashids->decode($encodedId)[0] ?? null;

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $stream = Stream::where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })->findOrFail($id);

        // Check if stream has any classes assigned
        if ($stream->classes()->count() > 0) {
            return redirect()->route('school.streams.index')
                ->with('error', 'Cannot delete stream "' . $stream->name . '" because it has classes assigned to it. Please remove all class assignments first.');
        }

        $stream->delete();

        return redirect()->route('school.streams.index')
            ->with('success', 'Stream deleted successfully.');
    }

    /**
     * Check if a stream name already exists.
     */
    public function checkName(Request $request)
    {
        $name = $request->query('name');
        $excludeId = $request->query('exclude_id');

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $query = Stream::where('name', $name)
            ->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }
}
