<?php
// app/Models/Attachment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attachment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function type()
    {
        return $this->belongsTo(AttachmentType::class, 'attachment_type_id');
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
