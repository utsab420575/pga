<?php

namespace App\Models\Concerns;

use Carbon\Carbon;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Logs create/update/delete into activity_log.
 * Only changed fields are stored on update; timestamps are ignored.
 */
trait LogsApplicantActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->getTable())
            ->logAll()
            ->logExcept(['id', 'created_at', 'updated_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->applicant_id = $this->activityApplicantId();

        // Keep properties lean and readable: local dates, no empty fields on create
        $props = $activity->properties->toArray();
        foreach (['attributes', 'old'] as $key) {
            if (!isset($props[$key])) {
                continue;
            }
            $values = array_map([$this, 'activityReadableValue'], $props[$key]);
            if ($eventName === 'created') {
                $values = array_filter($values, fn ($v) => $v !== null && $v !== '');
            }
            $props[$key] = $values;
        }
        $activity->properties = collect($props);
    }

    protected function activityReadableValue($value)
    {
        // Date casts serialize as UTC ISO strings (e.g. 1999-01-01T18:00:00.000000Z)
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T[\d:.]+Z$/', $value)) {
            $date = Carbon::parse($value)->setTimezone(config('app.timezone'));
            return $date->format($date->isStartOfDay() ? 'Y-m-d' : 'Y-m-d H:i:s');
        }
        return $value;
    }

    /** Which application this row belongs to. Override where it differs. */
    protected function activityApplicantId(): ?int
    {
        return $this->applicant_id ?? null;
    }
}
