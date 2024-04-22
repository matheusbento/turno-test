<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property UserType $type
 *
 * @property-read UserDeposit[] $deposits
 *
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'type' => UserType::class,
        ];
    }

    /**
     * Determine if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->type === UserType::ADMIN;
    }

    /**
     * Determine if the user is a customer.
     *
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this->type === UserType::CUSTOMER;
    }


    public function deposits(): HasMany
    {
        return $this->hasMany(UserDeposit::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(UserPurchase::class);
    }
}
