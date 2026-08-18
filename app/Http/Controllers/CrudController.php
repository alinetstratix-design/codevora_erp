<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class CrudController extends Controller
{
    /**
     * The Eloquent Model class name.
     *
     * @var string
     */
    protected $modelClass;

    /**
     * Get the validation rules for store.
     *
     * @return array
     */
    abstract protected function storeRules(): array;

    /**
     * Get the validation rules for update.
     *
     * @param int $id
     * @return array
     */
    abstract protected function updateRules($id): array;

    /**
     * Hook to pre-process data before store creation.
     *
     * @param array $data
     * @return array
     */
    protected function preStore(array $data): array
    {
        return $data;
    }

    /**
     * Hook to pre-process data before update saving.
     *
     * @param array $data
     * @param int $id
     * @return array
     */
    protected function preUpdate(array $data, $id): array
    {
        return $data;
    }

    public function index()
    {
        $items = $this->modelClass::all();
        
        if (property_exists($this, 'resourceClass')) {
            return $this->resourceClass::collection($items);
        }
        
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate($this->storeRules());
        $processedData = $this->preStore($validatedData);
        $item = $this->modelClass::create($processedData);
        
        if (property_exists($this, 'resourceClass')) {
            return response()->json(new $this->resourceClass($item), 201);
        }
        
        return response()->json($item, 201);
    }

    public function show($id)
    {
        $item = $this->modelClass::findOrFail($id);
        
        if (property_exists($this, 'resourceClass')) {
            return new $this->resourceClass($item);
        }
        
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = $this->modelClass::findOrFail($id);
        $validatedData = $request->validate($this->updateRules($item->id));
        $processedData = $this->preUpdate($validatedData, $item->id);
        $item->update($processedData);
        
        if (property_exists($this, 'resourceClass')) {
            return new $this->resourceClass($item);
        }
        
        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = $this->modelClass::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
