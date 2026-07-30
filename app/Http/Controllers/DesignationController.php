<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesignationController extends Controller
{
    /**
     * Display a listing of designations.
     */
    public function index()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $designations = Designation::withCount('users')
            ->latest()
            ->paginate(10);

        return view('designations.index', compact('designations'));
    }

    /**
     * Store a newly created designation.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:designations,title'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Designation::create($validated);

        return redirect()->route('designations.index')
            ->with('success', 'Designation created successfully.');
    }

    /**
     * Update the specified designation.
     */
    public function update(Request $request, Designation $designation)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:designations,title,' . $designation->id],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $designation->update($validated);

        return redirect()->route('designations.index')
            ->with('success', 'Designation updated successfully.');
    }

    /**
     * Remove the specified designation.
     */
    public function destroy(Designation $designation)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if ($designation->users()->count() > 0) {
            return redirect()->route('designations.index')
                ->with('error', 'Cannot delete designation as it is currently assigned to users.');
        }

        $designation->delete();

        return redirect()->route('designations.index')
            ->with('success', 'Designation deleted successfully.');
    }
}
