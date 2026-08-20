<div class="card card-default h-100">

    <div class="card-header">

        <h3 class="card-title">
            <i class="fas fa-history mr-2"></i>
            Últimos movimientos
        </h3>

        <div class="card-tools">

            <span class="text-muted">
                Las devoluciones enlazadas muestran su asignación.
            </span>

        </div>

    </div>

    <div class="card-body p-0">

        <x-material-movements.movements-table
            :movements="$movements"
        />

    </div>

</div>
