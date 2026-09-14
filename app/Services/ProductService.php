<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function createProduct(array $data, $imageFile = null)
    {
        return DB::transaction(function () use ($data, $imageFile) {
            $data['product_code'] = 'PRD-' . strtoupper(Str::random(6));
            $data['default_rate'] = $data['base_rate'] ?? 0;

            if (auth()->check() && auth()->user()->company_id && empty($data['company_id'])) {
                $data['company_id'] = auth()->user()->company_id;
            }

            if ($imageFile) {
                $path = $imageFile->store('public/products');
                $data['default_image'] = str_replace('public/', 'storage/', $path);
            }

            return Product::create($data);
        });
    }

    public function updateProduct(Product $product, array $data, $imageFile = null)
    {
        return DB::transaction(function () use ($product, $data, $imageFile) {
            if ($imageFile) {
                // Delete old image if it exists
                if ($product->default_image) {
                    $oldPath = str_replace('storage/', 'public/', $product->default_image);
                    Storage::delete($oldPath);
                }
                
                $path = $imageFile->store('public/products');
                $data['default_image'] = str_replace('public/', 'storage/', $path);
            }

            $product->update($data);
            return $product;
        });
    }

    public function deleteProduct(Product $product)
    {
        return DB::transaction(function () use ($product) {
            return $product->delete();
        });
    }
}
