<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends CrudController
{
    protected $modelClass = User::class;

    protected function storeRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string',
            'status' => 'nullable|string'
        ];
    }

    protected function updateRules($id): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|string',
            'status' => 'nullable|string'
        ];
    }

    protected function preStore(array $data): array
    {
        $data['password'] = Hash::make($data['password']);
        return $data;
    }

    protected function preUpdate(array $data, $id): array
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        return $data;
    }
}
