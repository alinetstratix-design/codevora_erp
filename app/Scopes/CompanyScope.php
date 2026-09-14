<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user && $user->company_id) {
                $builder->where($model->getTable() . '.company_id', $user->company_id);
                return;
            }
        }

        if (session()->has('company_id')) {
            $builder->where($model->getTable() . '.company_id', session('company_id'));
            return;
        }

        // When unauthenticated, the authenticatable User model must be resolvable by auth guards.
        if ($model instanceof \App\Models\User) {
            return;
        }

        // Fail closed: No authenticated tenant context.
        // Unauthenticated queries must never return cross-tenant data.
        $builder->where($model->getTable() . '.company_id', '=', -1);
    }
}
