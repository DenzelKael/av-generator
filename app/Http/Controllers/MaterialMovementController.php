<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaterialMovementRequest;
use App\Models\MaterialMovement;
use App\Services\MaterialMovement\MaterialMovementImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Throwable;

class MaterialMovementController extends Controller
{
    public function __construct(
        private readonly MaterialMovementImportService $importService
    ) {}

    public function index(): View
    {
        $movements = MaterialMovement::query()
            ->withCount('items')
            ->latest()
            ->take(10)
            ->get();

        return view('material-movements.create', [
            'movements' => $movements,
        ]);
    }

    public function store(
        StoreMaterialMovementRequest $request
    ): RedirectResponse {
        try {

            $this->importService->import(
                $request->file('document')
            );

            return to_route('material-movements.index')
                ->with(
                    'success',
                    'Documento importado y movimiento registrado correctamente.'
                );
        } catch (ValidationException $e) {

            throw $e;
        } catch (Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'document' => 'Ocurrió un error al procesar el documento.',
                ]);
        }
    }
}
