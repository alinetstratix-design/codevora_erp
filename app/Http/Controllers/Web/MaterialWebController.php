<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaterialWebController extends Controller
{
    public function index()
    {
        $materials = Material::latest()->paginate(15);
        return view('materials.index', compact('materials'));
    }

    public function create()
    {
        return view('materials.create');
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', Rule::unique('materials', 'sku')->where('company_id', $companyId)],
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'uom' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'waste_percent' => 'nullable|numeric|min:0',
        ]);

        $validated['company_id'] = $companyId;
        $validated['is_active'] = true;

        Material::create($validated);

        return redirect()->route('materials.index')->with('success', 'Material created successfully.');
    }

    public function edit(Material $material)
    {
        return view('materials.edit', compact('material'));
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', Rule::unique('materials', 'sku')->ignore($material->id)->where('company_id', $material->company_id)],
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'uom' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'waste_percent' => 'nullable|numeric|min:0',
        ]);

        $material->update($validated);

        return redirect()->route('materials.index')->with('success', 'Material updated successfully.');
    }

    public function destroy(Material $material)
    {
        $material->delete();
        return redirect()->route('materials.index')->with('success', 'Material deleted successfully.');
    }
}
