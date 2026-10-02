<?php
// app/Models/AttachmentType.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttachmentType extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status'   => 'boolean',
    ];

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function applicationtypes()
    {
        return $this->belongsToMany(Applicationtype::class, 'applicationtype_attachment_type')->withTimestamps();
    }

    /** Active types offered in the upload dropdown for this applicant's application type. */
    public static function offeredFor(Applicant $applicant)
    {
        return static::where('status', 1)
            ->whereHas('applicationtypes', fn ($q) => $q->where('applicationtypes.id', $applicant->applicationtype_id))
            ->orderBy('id')
            ->get();
    }
}
