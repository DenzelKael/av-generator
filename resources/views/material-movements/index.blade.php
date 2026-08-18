<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control de material valorado</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:py-12">
        <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-2 text-sm font-semibold tracking-[0.18em] text-blue-700">SUMINISTROS · AV-03</p>
                <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Importar movimientos de material</h1>
                <p class="mt-3 max-w-2xl text-slate-600">Carga el Excel de asignación o devolución. Las devoluciones se enlazan automáticamente con su asignación mediante el número de movimiento.</p>
            </div>
            <div class="rounded-xl bg-blue-700 px-4 py-3 text-sm text-white shadow-sm">
                <strong>4 formatos admitidos</strong><br>Asignación / devolución · Con / sin correlativo
            </div>
        </header>

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1.3fr_.7fr]">
            <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-7">
                <div class="mb-6 flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">1</span>
                    <div><h2 class="font-bold">Importación automática</h2><p class="text-sm text-slate-500">Solo sube el reporte; todos los datos se leen del archivo.</p></div>
                </div>

                <form action="{{ route('material-movements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" data-upload-form>
                    @csrf
                    <div>
                        <span class="label">Archivo Excel *</span>
                        <label class="mt-2 flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 text-center transition hover:border-blue-500 hover:bg-blue-50" data-drop-zone>
                            <input type="file" name="document" class="sr-only" accept=".xlsx,.xls,.csv" required data-file-input>
                            <svg class="mb-3 h-9 w-9 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 16V4m0 0L8 8m4-4 4 4M5 15v4a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-4"/></svg>
                            <strong data-file-name>Arrastra el archivo aquí o selecciónalo</strong>
                            <span class="mt-1 text-sm text-slate-500">Excel, CSV o reporte Excel sin extensión · máximo 20 MB</span>
                        </label>
                        @error('document')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    @if ($errors->any() && !$errors->has('document'))
                        <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">El reporte no contiene todos los datos necesarios para importarlo automáticamente.</p>
                    @endif
                    <button class="w-full rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">Importar y registrar movimiento</button>
                </form>
            </section>

            <aside class="space-y-6">
                <section class="rounded-2xl bg-slate-900 p-6 text-slate-100 shadow-sm">
                    <h2 class="font-bold">Columnas que debe tener el Excel</h2>
                    <p class="mt-2 text-sm text-slate-300">El sistema reconoce los encabezados aunque estén en mayúsculas o con acentos.</p>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><strong>Obligatorios:</strong> Descripción, Cantidad</li>
                        <li><strong>Opcionales:</strong> N°, UM, Lote, Estado</li>
                        <li><strong>Con correlativo:</strong> Desde, Hasta</li>
                    </ul>
                </section>
                <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-950">
                    <h2 class="font-bold">Sobre los PDF</h2>
                    <p class="mt-2">Los reportes sin extensión que entrega SUMINISTROS se detectan como Excel automáticamente. Los PDF escaneados aún requieren OCR y validación visual.</p>
                </section>
            </aside>
        </div>

        <section class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-7"><div><h2 class="font-bold">Últimos movimientos</h2><p class="text-sm text-slate-500">Las devoluciones enlazadas muestran su asignación.</p></div></div>
            <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3 font-medium">Movimiento</th><th class="px-5 py-3 font-medium">Tipo</th><th class="px-5 py-3 font-medium">Correlativo</th><th class="px-5 py-3 font-medium">Ítems</th><th class="px-5 py-3 font-medium">Asignación</th></tr></thead><tbody class="divide-y divide-slate-100">
                @forelse ($movements as $movement)
                    <tr><td class="px-5 py-4 font-semibold">{{ $movement->movement_number }}</td><td class="px-5 py-4">{{ $movement->type === 'assignment' ? 'Asignación' : 'Devolución' }}</td><td class="px-5 py-4">{{ $movement->has_correlation ? 'Con' : 'Sin' }}</td><td class="px-5 py-4">{{ $movement->items_count }}</td><td class="px-5 py-4 text-slate-600">{{ $movement->assignment_reference ?: '—' }}</td></tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Aún no hay movimientos importados.</td></tr>
                @endforelse
            </tbody></table></div>
        </section>
    </main>
</body>
</html>
