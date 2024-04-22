<?php

namespace App\Models;

use App\Models\Enums\ModelStatusInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelEnumStatus extends Model
{
    protected static array $modelClassEnumMapping = [];

    protected $table = 'model_statuses';

    protected $fillable = [
        'type',
        'status',
        'reason',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            $enumType = static::getEnumTypeFromModelClass($model->model_type);

            if ($model->status !== null && !$model->status instanceof $enumType) {
                throw new \InvalidArgumentException("status {$model->status->value} status is of wrong enum type " . get_class($model->status) . " provided, {$enumType} expected");
            }
        });
    }

    protected function status(): Attribute
    {
        $attr = new Attribute(
            get: function (mixed $value, array $attributes): ?ModelStatusInterface {
                if ($value === null) {
                    return null;
                }

                if (empty($attributes['model_type'])) {
                    throw new \InvalidArgumentException('model_type is not set');
                }

                $enumType = $this->getEnumTypeFromModelClass($attributes['model_type']);

                return $enumType::from($value);
            },
            set: function (?ModelStatusInterface $status, array $attributes): array {
                if ($status === null) {
                    return ['status' => null];
                }

                if (!empty($attributes['model_type'])) {
                    $enumType = $this->getEnumTypeFromModelClass($attributes['model_type']);

                    if (!$status instanceof $enumType) {
                        $statusText = $status->value ?? 'N/A';
                        throw new \InvalidArgumentException("status {$statusText} status is of wrong enum type " . get_class($status) . " provided, {$enumType} expected");
                    }
                }

                return ['status' => $status->value];
            },
        );
        $attr->withoutObjectCaching();
        return $attr;
    }

    public static function getEnumTypeFromModelClass(string $modelClass): string
    {
        if (isset(static::$modelClassEnumMapping[$modelClass])) {
            return static::$modelClassEnumMapping[$modelClass];
        }

        $statusConfig = config('model-status.' . $modelClass);

        if (!is_a($statusConfig, ModelStatusInterface::class, true)) {
            throw new \Exception('Model ' . $modelClass . ' is not configured correctly on model-status.php config file. An enum implementing ModelStatusInterface should be provided.');
        }

        return static::$modelClassEnumMapping[$modelClass] = $statusConfig;
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeWhereType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
