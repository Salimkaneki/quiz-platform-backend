<?php

namespace App\Http\Controllers\Management;

use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class InstitutionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Institution::query();

        if($request->search){
            $query->search($request->search);
        }

        return $query->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:institutions',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email|unique:institutions',
            'website' => 'nullable|url',
            'timezone' => 'nullable|string',
            'settings' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $data['slug'] = Str::slug($data['code']);
        
        return Institution::create($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institution $institution)
    {
        return $institution;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Institution $institution)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:institutions,code,' . $institution->id,
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email|unique:institutions,email,' . $institution->id,
            'website' => 'nullable|url',
            'timezone' => 'nullable|string',
            'settings' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        if (isset($data['code'])) {
            $data['slug'] = Str::slug($data['code']);
        }
        
        $institution->update($data);
        
        return $institution;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institution $institution)
    {
        $institution->delete();
        return response()->json(['message' => 'Institution supprimée']);
    }
}