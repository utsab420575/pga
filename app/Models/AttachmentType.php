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
        'required' => 'boolean',
    ];

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
