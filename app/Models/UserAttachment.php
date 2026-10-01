<?php

namespace App\Models;

use App\Models\Concerns\LogsApplicantActivity;
use Illuminate\Database\Eloquent\Model;

class UserAttachment extends Model
{
    use LogsApplicantActivity;

    protected $guarded=[];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}