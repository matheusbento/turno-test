<?php

namespace App\Models\Enums;

interface ModelStatusInterface
{
    public const TYPE_PRIMARY = 'primary';
    public const TYPE_SECONDARY = 'secondary';

    public function description(): string;

    public function isPrimary(): bool;
    public function isSecondary(): bool;
}
