<?php

namespace App\Models;

use App\Models\Enums\UserTransactionOperationType;
use App\Models\Enums\UserTransactionStatus;
use App\Models\Enums\UserTransactionType;
use App\Models\Traits\HasEnumStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTransaction extends Model
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
        'type',
        'operation',
        'description',
        'current_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'amount' => 'float',
        'operation' => UserTransactionOperationType::class,
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
        return $this->current_status === UserTransactionStatus::PENDING;
    }

    public function scopeWhereIsPurchases($query)
    {
        return $query->where('operation', UserTransactionOperationType::DEBIT) && $query->where('type', UserTransactionType::PURCHASE);
    }

    public function scopeWhereIsDeposits($query)
    {
        return $query->where('operation', UserTransactionOperationType::CREDIT) && $query->where('type', UserTransactionType::DEPOSIT);
    }
}
