<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // <- uncomment interface below if you use email verification
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable /* implements MustVerifyEmail */
{
    use HasFactory, Notifiable;

    /**
     * Mass assignable attributes (keeps your old fields).
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',      // from your old model
        'phone_verified', // from your old model
    ];

    /**
     * Hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casts (Laravel 10+ uses 'hashed' for password).
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'phone_verified'    => 'boolean', // helpful if this is 0/1 in DB
        ];
    }

    /**
     * Relations (your old hasMany to Applicant).
     * Other models remain under the App\ namespace as we planned.
     */
    public function applicant()
    {
        return $this->hasMany(\App\Applicant::class);
    }

    /**
     * Keep your old role helper, but use $this (not Auth::user()).
     */
    public function hasAnyRole($roles): bool
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if (($this->user_type ?? null) === $role) {
                    return true;
                }
            }
            return false;
        }

        // single string case
        return (($this->user_type ?? null) === $roles);
    }
}
