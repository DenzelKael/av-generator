<?php

namespace App\Services\MaterialMovement;

class MaterialExcelNormalizer
{
    public function normalize(array $rows): array
    {
        return array_map(function (array $row) {
            $values = array_values(
                array_filter(
                    $row,
                    fn($value) => $value !== null && $value !== ''
                )
            );

            return count($values) === 1
                && is_string($values[0])
                && str_contains($values[0], ',')
                ? str_getcsv($values[0])
                : $row;
        }, $rows);
    }
}
