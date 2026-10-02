<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A row means "this attachment type is required".
 * degree_id NULL = required for every degree of that application type.
 */
class AttachmentRequirement extends Model
{
    protected $guarded = [];

    public function attachmentType() { return $this->belongsTo(AttachmentType::class); }
    public function applicationtype() { return $this->belongsTo(Applicationtype::class); }
    public function degree()          { return $this->belongsTo(Degree::class); }

    /** Attachment type ids the applicant must upload before final submit. */
    public static function requiredTypeIdsFor(Applicant $applicant): array
    {
        return static::where('applicationtype_id', $applicant->applicationtype_id)
            ->where(function ($q) use ($applicant) {
                $q->whereNull('degree_id')->orWhere('degree_id', $applicant->degree_id);
            })
            ->distinct()
            ->pluck('attachment_type_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
