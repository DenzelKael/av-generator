<?php

namespace App\DTOs;

class MaterialMovementData
{
    public function __construct(
        public readonly string $movementNumber,
        public readonly string $type,
        public readonly string $correlationType,
        public readonly ?string $movementAt,
        public readonly ?string $responsible,
        public readonly ?string $office,
        public readonly array $items,
    ) {}

    public function hasCorrelation(): bool
    {
        return $this->correlationType === 'with_correlation';
    }
}
