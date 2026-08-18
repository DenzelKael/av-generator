<?php

namespace App\Http\Controllers;

use App\Imports\MaterialRowsImport;
use App\Models\MaterialMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class MaterialMovementController extends Controller
{
    public function index()
    {
        return view('material-movements.index', [
            'movements' => MaterialMovement::query()->withCount('items')->latest()->take(10)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'document' => ['required', 'file', 'max:20480'],
        ]);

        try {
            $readerType = IOFactory::identify($data['document']->getRealPath());
        } catch (\Throwable) {
            return back()->withInput()->withErrors(['document' => 'El archivo no es un Excel o CSV legible.']);
        }

        if (! in_array($readerType, ['Xls', 'Xlsx', 'Csv'], true)) {
            return back()->withInput()->withErrors(['document' => 'Solo se admiten archivos Excel (.xls, .xlsx) o CSV.']);
        }

        $rows = $this->normalizeRows(Excel::toArray(new MaterialRowsImport, $data['document'], null, $readerType)[0] ?? []);
        $data = array_merge($data, $this->detectDocumentData($rows, $data['document']->getClientOriginalName()));

        Validator::make($data, [
            'document_type' => ['required', 'in:assignment,return'],
            'correlation_type' => ['required', 'in:with_correlation,without_correlation'],
            'movement_number' => ['required', 'string', 'max:100'],
            'movement_at' => ['nullable', 'date'],
            'responsible' => ['required', 'string', 'max:255'],
            'office' => ['required', 'string', 'max:255'],
        ], [
            'document_type.required' => 'No se pudo identificar si el reporte es una asignación o devolución.',
            'movement_number.required' => 'No se encontró el número de movimiento dentro del reporte.',
            'responsible.required' => 'No se encontró el responsable dentro del reporte.',
            'office.required' => 'No se encontró la oficina o almacén dentro del reporte.',
        ])->validate();
        $items = $this->itemsFromRows($rows);

        if ($items === []) {
            return back()->withInput()->withErrors([
                'document' => 'No se encontraron filas de material. El Excel debe contener encabezados como Descripción y Cantidad.',
            ]);
        }

        $file = $data['document'];
        $extension = strtolower($readerType);
        $path = $file->storeAs('material-movements', Str::uuid().'.'.$extension);

        DB::transaction(function () use ($data, $items, $path) {
            $assignmentId = null;
            if ($data['document_type'] === 'return') {
                $assignmentId = MaterialMovement::query()
                    ->where('type', 'assignment')
                    ->where('movement_number', $data['movement_number'])
                    ->value('id');
            }

            $movement = MaterialMovement::create([
                'movement_number' => $data['movement_number'],
                'type' => $data['document_type'],
                'has_correlation' => $data['correlation_type'] === 'with_correlation',
                'movement_at' => $data['movement_at'] ?? null,
                'responsible' => $data['responsible'] ?? null,
                'office' => $data['office'] ?? null,
                'source_file' => $path,
                'assignment_reference' => $data['document_type'] === 'return' ? $data['movement_number'] : null,
                'assignment_id' => $assignmentId,
            ]);

            $movement->items()->createMany($items);

            if ($movement->type === 'assignment') {
                MaterialMovement::query()
                    ->where('type', 'return')
                    ->whereNull('assignment_id')
                    ->where('assignment_reference', $movement->movement_number)
                    ->update(['assignment_id' => $movement->id]);
            }
        });

        return to_route('material-movements.index')->with('success', 'Documento importado y movimiento registrado correctamente.');
    }

    private function itemsFromRows(array $rows): array
    {
        $headerIndex = null;
        foreach ($rows as $index => $row) {
            $cells = collect($row)->map(fn ($value) => $this->key($value));
            if ($cells->contains('descripcion') && $cells->contains('cantidad')) {
                $headerIndex = $index;
                break;
            }
        }

        if ($headerIndex === null) {
            return [];
        }

        $headers = collect($rows[$headerIndex])->mapWithKeys(
            fn ($value, $index) => [$this->key($value) => $index]
        );

        return collect(array_slice($rows, $headerIndex + 1))
            ->map(function ($row) use ($headers) {
                $value = fn (array $names) => collect($names)
                    ->map(fn ($name) => $headers->has($name) ? ($row[$headers[$name]] ?? null) : null)
                    ->first(fn ($cell) => filled($cell));

                $description = $value(['descripcion', 'material']);
                if (blank($description)) {
                    return null;
                }

                return [
                    'line_number' => $this->integer($value(['n', 'numero'])),
                    'description' => trim((string) $description),
                    'unit' => $value(['um', 'unidad', 'unidadmedida']),
                    'lot' => $value(['lote']),
                    'serial_from' => $value(['desde', 'correlativodesde']),
                    'serial_to' => $value(['hasta', 'correlativohasta']),
                    'quantity' => $this->integer($value(['cantidad'])) ?? 0,
                    'status' => $value(['estado']),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function detectDocumentData(array $rows, string $fileName): array
    {
        $cells = collect($rows)->flatten()->filter(fn ($value) => filled($value))->map(fn ($value) => (string) $value);
        $text = $cells->implode(' ');
        $normalized = $this->key($text.' '.$fileName);
        $movement = null;

        if (preg_match('/movimiento\s*(?:\(\s*s\s*\)|s)?\s*:?\s*(\d+)/iu', $text, $matches)) {
            $movement = $matches[1];
        }

        $hasCorrelation = $cells->contains(fn ($value) => in_array($this->key($value), ['desde', 'hasta', 'correlativo', 'correlativodesde', 'correlativohasta'], true));

        return [
            'document_type' => str_contains($normalized, 'devolucion') ? 'return'
                : (str_contains($normalized, 'asignacion') ? 'assignment' : null),
            'correlation_type' => $hasCorrelation ? 'with_correlation' : 'without_correlation',
            'movement_number' => $movement,
            'responsible' => $this->valueFollowingLabel($rows, ['responsableorigen', 'responsable']),
            'office' => $this->valueFollowingLabel($rows, ['almacenorigen', 'oficina', 'almacen']),
            'movement_at' => $this->dateFromReport($rows),
        ];
    }

    private function dateFromReport(array $rows): ?string
    {
        foreach ($rows as $row) {
            $line = implode(' ', array_filter($row, fn ($value) => filled($value)));
            $label = $this->key($line);

            if (! str_contains($label, 'fecha') || str_contains($label, 'fechaimpresion')) {
                continue;
            }

            if (preg_match('/(\d{2}\/\d{2}\/\d{4})(?:\s+(\d{1,2}:\d{2}:\d{2}))?/', $line, $matches)) {
                return Carbon::createFromFormat('d/m/Y'.(isset($matches[2]) ? ' H:i:s' : ''), $matches[1].(isset($matches[2]) ? ' '.$matches[2] : ''))->format('Y-m-d H:i:s');
            }
        }

        return null;
    }

    private function valueFollowingLabel(array $rows, array $labels): ?string
    {
        foreach ($rows as $row) {
            foreach ($row as $index => $cell) {
                $value = trim((string) $cell);
                $key = $this->key($value);

                if (! collect($labels)->contains(fn ($label) => str_contains($key, $label))) {
                    continue;
                }

                $afterColon = trim((string) Str::of($value)->after(':'));
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

    private function normalizeRows(array $rows): array
    {
        return array_map(function (array $row) {
            $values = array_values(array_filter($row, fn ($value) => $value !== null && $value !== ''));

            return count($values) === 1 && is_string($values[0]) && str_contains($values[0], ',')
                ? str_getcsv($values[0])
                : $row;
        }, $rows);
    }

    private function key(mixed $value): string
    {
        return Str::of((string) $value)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '')->toString();
    }

    private function integer(mixed $value): ?int
    {
        $value = preg_replace('/[^0-9]/', '', (string) $value);

        return $value === '' ? null : (int) $value;
    }
}
