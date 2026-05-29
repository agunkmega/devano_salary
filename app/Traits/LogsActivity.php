<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    protected function logActivity(string $type, string $action, string $description, ?string $modelType = null, ?int $modelId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'log_type' => $type,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
