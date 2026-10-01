<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    const UPDATED_AT = null;

    protected static function booted(): void
    {
        static::creating(function (ActivityLog $log) {
            $log->ip_address ??= request()?->ip();
        });
    }

    // Columns intentionally not in the slim table — ignore what the package sets.
    public function setDescriptionAttribute($value): void {}
    public function setBatchUuidAttribute($value): void {}

    public function applicant() { return $this->belongsTo(Applicant::class); }
}
