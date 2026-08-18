<?php

namespace App\Services\MaterialMovement;

use App\DTOs\MaterialMovementData;
use App\Models\MaterialMovement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Imports\MaterialRowsImport;

class MaterialMovementImportService
{
    public function __construct(
        private readonly MaterialExcelNormalizer $normalizer,
        private readonly MaterialDocumentParser $documentParser,
        private readonly MaterialRowParser $rowParser,
        private readonly MaterialMovementRelationService $relationService,
    ) {}

    public function import(UploadedFile $file): MaterialMovement
    {
        $readerType = $this->detectReaderType($file);

        $rows = Excel::toArray(
            new MaterialRowsImport,
            $file,
            null,
            $readerType
        )[0] ?? [];

        $rows = $this->normalizer->normalize($rows);

        $document = $this->documentParser->parse(
            $rows,
            $file->getClientOriginalName()
        );

        $items = $this->rowParser->parse($rows);

        $this->validateDocumentData(
            $document,
            $items
        );

        $path = $file->storeAs(
            'material-movements',
            Str::uuid() . '.' . strtolower($readerType)
        );

        $data = new MaterialMovementData(
            movementNumber: $document['movement_number'],
            type: $document['document_type'],
            correlationType: $document['correlation_type'],
            movementAt: $document['movement_at'],
            responsible: $document['responsible'],
            office: $document['office'],
            items: $items,
        );

        return DB::transaction(
            fn() => $this->persist($data, $path)
        );
    }

    private function persist(
        MaterialMovementData $data,
        string $path
    ): MaterialMovement {
        $assignmentId = $this->relationService
            ->findAssignmentId($data);

        $movement = MaterialMovement::create([
            'movement_number' => $data->movementNumber,
            'type' => $data->type,
            'has_correlation' => $data->hasCorrelation(),
            'movement_at' => $data->movementAt,
            'responsible' => $data->responsible,
            'office' => $data->office,
            'source_file' => $path,

            'assignment_reference' =>
            $data->type === 'return'
                ? $data->movementNumber
                : null,

            'assignment_id' => $assignmentId,
        ]);

        $movement->items()->createMany(
            $data->items
        );

        $this->relationService->syncPendingReturns(
            $movement
        );

        return $movement;
    }

    private function detectReaderType(
        UploadedFile $file
    ): string {
        try {
            $readerType = IOFactory::identify(
                $file->getRealPath()
            );
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'document' =>
                'El archivo no es un Excel o CSV legible.',
            ]);
        }

        if (! in_array(
            $readerType,
            ['Xls', 'Xlsx', 'Csv'],
            true
        )) {
            throw ValidationException::withMessages([
                'document' =>
                'Solo se admiten archivos Excel (.xls, .xlsx) o CSV.',
            ]);
        }

        return $readerType;
    }

    private function validateDocumentData(
        array $document,
        array $items
    ): void {
        $errors = [];

        if (! in_array(
            $document['document_type'],
            ['assignment', 'return'],
            true
        )) {
            $errors['document_type'] =
                'No se pudo identificar si el reporte es una asignación o devolución.';
        }

        if (blank($document['movement_number'])) {
            $errors['movement_number'] =
                'No se encontró el número de movimiento dentro del reporte.';
        }

        if (blank($document['responsible'])) {
            $errors['responsible'] =
                'No se encontró el responsable dentro del reporte.';
        }

        if (blank($document['office'])) {
            $errors['office'] =
                'No se encontró la oficina o almacén dentro del reporte.';
        }

        if ($items === []) {
            $errors['document'] =
                'No se encontraron filas de material. El Excel debe contener encabezados como Descripción y Cantidad.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
