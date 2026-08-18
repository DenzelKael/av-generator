<?php

use App\Models\MaterialMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

uses(RefreshDatabase::class);

it('imports a correlated assignment from a csv file', function () {
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([
        ['Movimiento(s): 4813214'],
        ['RESPONSABLE', 'Anely Caceres'],
        ['OFICINA', 'Brigada Móvil Distrital Santa Cruz'],
        ['N°', 'Descripción', 'UM', 'Lote', 'Desde', 'Hasta', 'Cantidad', 'Estado'],
        [1, 'CÉDULA DE IDENTIDAD DS4924', 'PIEZA', 'BO-1', '328812', '328832', 21, 'MATERIAL SIN UTILIZAR'],
    ]);
    $path = tempnam(sys_get_temp_dir(), 'report-');
    (new Xls($spreadsheet))->save($path);
    $file = new UploadedFile($path, 'VerReporteIngresoAsignacionMaterial(12)', 'application/vnd.ms-excel', null, true);

    $response = $this->post(route('material-movements.store'), [
        'document' => $file,
    ]);

    $response->assertRedirect(route('material-movements.index'));
    $movement = MaterialMovement::where('movement_number', '4813214')->firstOrFail();
    expect($movement->has_correlation)->toBeTrue()
        ->and($movement->items)->toHaveCount(1)
        ->and($movement->items->first()->serial_from)->toBe('328812')
        ->and($movement->items->first()->quantity)->toBe(21);
});

it('links a return to its matching assignment', function () {
    $assignment = MaterialMovement::factory()->create([
        'movement_number' => '4813223',
        'type' => 'assignment',
        'has_correlation' => true,
    ]);
    $file = UploadedFile::fake()->createWithContent('VerReporteDevolucionMaterialOperadorPorIdMovimiento(12).csv', implode("\n", [
        'Movimiento(s): 4813223',
        'RESPONSABLE,Reineri Lopez Herrera',
        'OFICINA,Brigada Móvil Distrital Santa Cruz',
        'Descripción,Cantidad',
        'CÉDULA DE IDENTIDAD DS4924,21',
    ]));

    $this->post(route('material-movements.store'), [
        'document' => $file,
    ])->assertRedirect(route('material-movements.index'));

    expect(MaterialMovement::where('movement_number', '4813223')->where('type', 'return')->value('assignment_id'))->toBe($assignment->id);
});
