<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelStatus extends Model
{
    protected $table = 'model_statuses';
    protected $fillable = [
        'type',
        'status',
        'reason',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeWhereType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
