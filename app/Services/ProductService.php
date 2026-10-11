<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function createProduct(array $data, int $companyId, $imageFile = null)
    {
        return DB::transaction(function () use ($data, $companyId, $imageFile) {
            $data['company_id'] = $companyId;
            $data['product_code'] = $this->nextProductCode($companyId);

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
            unset($data['company_id'], $data['product_code']);

            if ($imageFile) {
                if ($product->default_image) {
                    Storage::delete(str_replace('storage/', 'public/', $product->default_image));
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

    private function nextProductCode(int $companyId): string
    {
        do {
            $code = 'PRD-' . strtoupper(Str::random(6));
        } while (Product::withTrashed()->withoutGlobalScopes()->where('company_id', $companyId)->where('product_code', $code)->exists());

        return $code;
    }
}
