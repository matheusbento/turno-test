<?php

namespace App\Models\Traits;

use App\Exceptions\InvalidStatusReversal;
use App\Models\Enums\ModelStatusInterface;
use App\Models\ModelEnumStatus;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasEnumStatuses
{
    public function initializeHasEnumStatuses(): void
    {
        $enumClass = ModelEnumStatus::getEnumTypeFromModelClass(self::class);

        if (isset($this->currentStatusColumn)) {
            $this->mergeCasts([
                $this->currentStatusColumn => $enumClass,
            ]);
        }
    }

    public static function bootHasEnumStatuses(): void
    {
        static::created(function (Model $model) {
            if ($model->currentStatusColumn && $model->{$model->currentStatusColumn}) {
                $model->setStatus($model->{$model->currentStatusColumn});
            }
        });
    }

    /**
     * @param ModelStatusInterface $status
     * @param string|null $reason
     * @return ModelEnumStatus
     * @throws Exception
     */
    public function setStatus(ModelStatusInterface $status, ?string $reason = null): ModelEnumStatus
    {
        if (!$this->exists) {
            throw new Exception('Model must be saved before setting statuses.');
        }

        $enumType = ModelEnumStatus::getEnumTypeFromModelClass(self::class);

        if (get_class($status) !== $enumType) {
            throw new \InvalidArgumentException("status {$status->value} status is of wrong enum type " . get_class($status) . " provided, {$enumType} expected");
        }

        if ($status->isPrimary() === $status->isSecondary()) {
            throw new \InvalidArgumentException("Status {$status->value} is incorrectly configured, check isPrimary() and isSecondary() methods");
        }

        if ($this->currentStatusColumn) {
            $this->{$this->currentStatusColumn} = $status;
            $this->save();
        }

        /** @var ModelEnumStatus $newStatus */
        $newStatus = $this->rawStatuses()->create([
            'type' => $status->isPrimary()
                ? ModelStatusInterface::TYPE_PRIMARY
                : ModelStatusInterface::TYPE_SECONDARY,
            'status' => $status,
            'reason' => $reason,
        ]);

        $relationsToReload = [
            'rawStatuses',
            $status->isPrimary() ? 'currentPrimaryStatus' : 'currentSecondaryStatus',
        ];

        //Refresh relationship that are already loaded and got out of sync
        $this->load(
            collect($relationsToReload)->filter(fn (string $relationship) => $this->relationLoaded($relationship))->all()
        );

        return $newStatus;
    }

    public function rawStatuses(): MorphMany
    {
        return $this->morphMany(ModelEnumStatus::class, 'model')
            ->latest('id');
    }

    public function currentPrimaryStatus(): MorphOne
    {
        return $this->morphOne(ModelEnumStatus::class, 'model')
            ->where('type', ModelStatusInterface::TYPE_PRIMARY)
            ->ofMany(
                ['id' => 'max'],
                fn (Builder $query) => $query->where('type', ModelStatusInterface::TYPE_PRIMARY)
            );
    }

    public function currentSecondaryStatus(): MorphOne
    {
        return $this->morphOne(ModelEnumStatus::class, 'model')
            ->where('type', ModelStatusInterface::TYPE_SECONDARY)
            ->ofMany(
                ['id' => 'max'],
                fn (Builder $query) => $query->where('type', ModelStatusInterface::TYPE_SECONDARY)
            );
    }

    protected function statuses(): Attribute
    {
        $attr = new Attribute(
            get: fn () => $this->rawStatuses->whereNotNull('status')->pluck('status')->all()
        );
        $attr->withoutObjectCaching();
        return $attr;
    }

    protected function status(): Attribute
    {
        $attr = new Attribute(
            get: fn (): ?ModelStatusInterface => $this->primary_status
        );
        $attr->withoutObjectCaching();
        return $attr;
    }

    protected function primaryStatus(): Attribute
    {
        $attr = new Attribute(
            get: fn (): ?ModelStatusInterface => $this->currentPrimaryStatus ? $this->currentPrimaryStatus->status : null,
        );
        $attr->withoutObjectCaching();
        return $attr;
    }

    protected function secondaryStatus(): Attribute
    {
        $attr = new Attribute(
            get: fn (): ?ModelStatusInterface => $this->currentSecondaryStatus ? $this->currentSecondaryStatus->status : null,
        );
        $attr->withoutObjectCaching();
        return $attr;
    }

    public function getPreviousStatus(): ?ModelStatusInterface
    {
        $previousStatus = $this->rawStatuses()
            ->whereNotNull('status')
            ->take(1)
            ->skip(1)
            ->first();

        return $previousStatus !== null
            ? $previousStatus->status
            : $this->status;
    }

    /**
     * @throws InvalidStatusReversal
     * @throws Exception
     */
    public function revertLastPrimaryStatus(): self
    {
        $previousPrimaryStatus = $this->rawStatuses()
            ->whereType(ModelStatusInterface::TYPE_PRIMARY)
            ->take(1)
            ->skip(1)
            ->first();

        if ($previousPrimaryStatus !== null) {
            $this->setStatus($previousPrimaryStatus->status);
        } else {
            throw new InvalidStatusReversal();
        }

        return $this;
    }

    /**
     * @param ModelStatusInterface $status
     * @return HasEnumStatuses
     * @throws InvalidStatusReversal
     */
    public function revertIfCurrentStatus(ModelStatusInterface $status): self
    {
        if ($status->isPrimary()) {
            if ($this->primary_status !== $status) {
                return $this;
            }

            $this->revertLastPrimaryStatus();
        }

        if ($status->isSecondary()) {
            if ($this->secondary_status !== $status) {
                return $this;
            }

            $previousStatus = $this->getPreviousStatus();

            $this->rawStatuses()->create([
                'type' => ModelStatusInterface::TYPE_SECONDARY,
                'status' => null,
            ]);

            $this->setStatus($previousStatus);

            if ($this->relationLoaded('currentSecondaryStatus')) {
                $this->load('currentSecondaryStatus');
            }
        }

        return $this;
    }

    public function scopeWhereCurrentPrimaryStatus(Builder $builder, ModelStatusInterface $status)
    {
        $builder->whereHas('currentPrimaryStatus', fn (Builder $q) => $q->where('status', $status));
    }

    public function scopeWhereCurrentSecondaryStatus(Builder $builder, ModelStatusInterface $status)
    {
        $builder->whereHas('currentSecondaryStatus', fn (Builder $q) => $q->where('status', $status));
    }

    public function scopeWhereCurrentStatus($query, ModelStatusInterface $status)
    {
        if (!$this->currentStatusColumn) {
            throw new Exception('HasTrait currentStatusColumn is required to use this method.');
        }

        $query->where($this->currentStatusColumn, '=', $status);
    }

    public function scopeWhereCurrentStatusIn($query, array $statuses)
    {
        if (!$this->currentStatusColumn) {
            throw new Exception('HasTrait currentStatusColumn is required to use this method.');
        }

        return $query->whereIn($this->currentStatusColumn, $statuses);
    }

    public function scopeWhereCurrentStatusNot($query, ModelStatusInterface $status)
    {
        if (!$this->currentStatusColumn) {
            throw new Exception('HasTrait currentStatusColumn is required to use this method.');
        }

        return $query->where($this->currentStatusColumn, '!=', $status);
    }

    public function scopeWhereCurrentStatusNotIn($query, array $statuses)
    {
        if (!$this->currentStatusColumn) {
            throw new Exception('HasTrait currentStatusColumn is required to use this method.');
        }

        return $query->whereNotIn($this->currentStatusColumn, $statuses);
    }
}
