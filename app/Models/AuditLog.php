<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Append-only activity record. Use AuditLog::record() to write; rows cannot be edited or removed.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

    public static function record(string $action, ?Model $subject = null, ?string $label = null, array $details = [], ?int $companyId = null): ?self
    {
        try {
            $user = Auth::user();

            return static::create([
                'company_id' => $companyId ?? $subject?->company_id ?? $user?->company_id,
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'user_role' => $user?->role,
                'action' => $action,
                'subject_type' => $subject ? strtolower(class_basename($subject)) : null,
                'subject_id' => $subject?->getKey(),
                'subject_label' => $label,
                'details' => $details ?: null,
                'ip_address' => request()?->ip(),
            ]);
        } catch (\Throwable $e) {
            // An audit failure must never break the user's action, but it must be visible to operators.
            Log::error('Audit log write failed', ['action' => $action, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
