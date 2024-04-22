<?php

namespace App\Models;

use App\Models\Enums\UserDepositStatus;
use App\Models\Traits\HasEnumStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDeposit extends Model
{
    use HasFactory;
    use HasEnumStatuses;

    protected ?string $currentStatusColumn = 'current_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'file_id',
        'amount',
        'current_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'amount' => 'float',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending()
    {
        return $this->current_status === UserDepositStatus::PENDING;
    }
}
