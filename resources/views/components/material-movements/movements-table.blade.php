<div class="table-responsive">

    <table class="table table-hover table-striped mb-0">

        <thead>
            <tr>
                <th>Movimiento</th>
                <th>Tipo</th>
                <th>Correlativo</th>
                <th>Ítems</th>
                <th>Asignación</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($movements as $movement)

                <tr>

                    <td>
                        <strong>
                            {{ $movement->movement_number }}
                        </strong>
                    </td>

                    <td>

                        @if ($movement->type === 'assignment')

                            <span class="badge badge-primary">
                                <i class="fas fa-sign-in-alt mr-1"></i>
                                Asignación
                            </span>

                        @else

                            <span class="badge badge-warning">
                                <i class="fas fa-undo mr-1"></i>
                                Devolución
                            </span>

                        @endif

                    </td>

                    <td>

                        @if ($movement->has_correlation)

                            <span class="badge badge-success">
                                Con
                            </span>

                        @else

                            <span class="badge badge-secondary">
                                Sin
                            </span>

                        @endif

                    </td>

                    <td>
                        {{ $movement->items_count }}
                    </td>

                    <td>

                        @if ($movement->assignment_reference)

                            <span class="text-primary">
                                {{ $movement->assignment_reference }}
                            </span>

                        @else

                            <span class="text-muted">
                                —
                            </span>

                        @endif

                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="5"
                        class="text-center text-muted py-4"
                    >

                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>

                        Aún no hay movimientos importados.

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>
