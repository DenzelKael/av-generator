<?php

namespace App\Services\MaterialMovement;

use Illuminate\Support\Str;

class MaterialRowParser
{
    public function parse(array $rows): array
    {
        $headerIndex = $this->findHeaderIndex($rows);

        if ($headerIndex === null) {
            return [];
        }

        $headers = collect($rows[$headerIndex])
            ->mapWithKeys(
                fn($value, $index) => [
                    $this->key($value) => $index
                ]
            );

        return collect(array_slice($rows, $headerIndex + 1))
            ->map(fn($row) => $this->parseRow($row, $headers))
            ->filter()
            ->values()
            ->all();
    }

    private function findHeaderIndex(array $rows): ?int
    {
        foreach ($rows as $index => $row) {
            $cells = collect($row)
                ->map(fn($value) => $this->key($value));

            if (
                $cells->contains('descripcion') &&
                $cells->contains('cantidad')
            ) {
                return $index;
            }
        }

        return null;
    }

    private function parseRow(array $row, $headers): ?array
    {
        $value = function (array $names) use ($headers, $row) {
            return collect($names)
                ->map(
                    fn($name) => $headers->has($name)
                        ? ($row[$headers[$name]] ?? null)
                        : null
                )
                ->first(fn($cell) => filled($cell));
        };

        $description = $value([
            'descripcion',
            'material',
        ]);

        if (blank($description)) {
            return null;
        }

        return [
            'line_number' => $this->integer(
                $value(['n', 'numero'])
            ),

            'description' => trim(
                (string) $description
            ),

            'unit' => $value([
                'um',
                'unidad',
                'unidadmedida',
            ]),

            'lot' => $value([
                'lote',
            ]),

            'serial_from' => $value([
                'desde',
                'correlativodesde',
            ]),

            'serial_to' => $value([
                'hasta',
                'correlativohasta',
            ]),

            'quantity' => $this->integer(
                $value(['cantidad'])
            ) ?? 0,

            'status' => $value([
                'estado',
            ]),
        ];
    }

    private function integer(mixed $value): ?int
    {
        $value = preg_replace(
            '/[^0-9]/',
            '',
            (string) $value
        );

        return $value === ''
            ? null
            : (int) $value;
    }

    private function key(mixed $value): string
    {
        return Str::of((string) $value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }
}
