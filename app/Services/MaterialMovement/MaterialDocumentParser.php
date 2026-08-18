<?php

namespace App\Services\MaterialMovement;

use Carbon\Carbon;
use Illuminate\Support\Str;

class MaterialDocumentParser
{
    public function parse(array $rows, string $fileName): array
    {
        $cells = collect($rows)
            ->flatten()
            ->filter(fn($value) => filled($value))
            ->map(fn($value) => (string) $value);

        $text = $cells->implode(' ');
        $normalized = $this->key($text . ' ' . $fileName);

        return [
            'document_type' => $this->detectDocumentType($normalized),
            'correlation_type' => $this->detectCorrelationType($cells),
            'movement_number' => $this->detectMovementNumber($text),
            'responsible' => $this->valueFollowingLabel(
                $rows,
                ['responsableorigen', 'responsable']
            ),
            'office' => $this->valueFollowingLabel(
                $rows,
                ['almacenorigen', 'oficina', 'almacen']
            ),
            'movement_at' => $this->dateFromReport($rows),
        ];
    }

    private function detectDocumentType(string $text): ?string
    {
        if (str_contains($text, 'devolucion')) {
            return 'return';
        }

        if (str_contains($text, 'asignacion')) {
            return 'assignment';
        }

        return null;
    }

    private function detectCorrelationType($cells): string
    {
        $hasCorrelation = $cells->contains(
            fn($value) => in_array(
                $this->key($value),
                [
                    'desde',
                    'hasta',
                    'correlativo',
                    'correlativodesde',
                    'correlativohasta',
                ],
                true
            )
        );

        return $hasCorrelation
            ? 'with_correlation'
            : 'without_correlation';
    }

    private function detectMovementNumber(string $text): ?string
    {
        if (preg_match(
            '/movimiento\s*(?:\(\s*s\s*\)|s)?\s*:?\s*(\d+)/iu',
            $text,
            $matches
        )) {
            return $matches[1];
        }

        return null;
    }

    private function dateFromReport(array $rows): ?string
    {
        foreach ($rows as $row) {
            $line = implode(
                ' ',
                array_filter($row, fn($value) => filled($value))
            );

            $label = $this->key($line);

            if (
                ! str_contains($label, 'fecha') ||
                str_contains($label, 'fechaimpresion')
            ) {
                continue;
            }

            if (preg_match(
                '/(\d{2}\/\d{2}\/\d{4})(?:\s+(\d{1,2}:\d{2}:\d{2}))?/',
                $line,
                $matches
            )) {
                $format = 'd/m/Y';

                if (isset($matches[2])) {
                    $format .= ' H:i:s';
                }

                $value = $matches[1];

                if (isset($matches[2])) {
                    $value .= ' ' . $matches[2];
                }

                return Carbon::createFromFormat(
                    $format,
                    $value
                )->format('Y-m-d H:i:s');
            }
        }

        return null;
    }

    private function valueFollowingLabel(
        array $rows,
        array $labels
    ): ?string {
        foreach ($rows as $row) {
            foreach ($row as $index => $cell) {
                $value = trim((string) $cell);
                $key = $this->key($value);

                $matchesLabel = collect($labels)->contains(
                    fn($label) => str_contains($key, $label)
                );

                if (! $matchesLabel) {
                    continue;
                }

                $afterColon = trim(
                    (string) Str::of($value)->after(':')
                );

                if ($afterColon !== $value && $afterColon !== '') {
                    return $afterColon;
                }

                foreach (array_slice($row, $index + 1) as $next) {
                    if (filled($next)) {
                        return trim((string) $next);
                    }
                }
            }
        }

        return null;
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
