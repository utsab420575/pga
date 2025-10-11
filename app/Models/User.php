<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // enable if you use email verification
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable /* implements MustVerifyEmail */
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'phone_verified',
        'phone',
         'plain_pass'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'phone_verified'    => 'boolean',
        ];
    }

    // keep method name from your original codebase
    public function applicant()
    {
        return $this->hasMany(\App\Models\Applicant::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }


    /**
     * Check if the user has any of the given role(s).
     * `user_type` is treated as the user's role.
     *
     *  roles  e.g. 'admin' or ['admin','head']
     * @return bool                  true if user_type matches
     */
    public function hasAnyRole($roles): bool
    {
        // If array given, return true on first match; otherwise false
        if (is_array($roles)) {
            //user_type working as roles
            foreach ($roles as $role) {
                if (($this->user_type ?? null) == $role) {
                    return true;
                }
            }
            return false;
        }
        return (($this->user_type ?? null) === $roles);
    }
}
