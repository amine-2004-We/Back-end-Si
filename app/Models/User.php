<?php

namespace App\Models;

use App\Constants\Department;
use App\Constants\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Traits\HasRoles;


/**
 * class User
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

// Implémentation de JWTSubject

    /**
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * @param $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
      /**
     * Get the collaborator associated with the user.
     */
    public function collaborator(): HasOne
    {
        return $this->hasOne(Collaborator::class, 'user_id');
    }

    /**
     * Vérifie si l'utilisateur appartient au département Partenariat.
     */
    public function belongsToPartnershipDept(): bool
    {
        $departmentName = optional(optional($this->collaborator)->department)->name;
        \Log::info('Department Name: ' . $departmentName); 

        return $departmentName === Department::DEV_PARTENARIAT_COMM;
    }

  
}
