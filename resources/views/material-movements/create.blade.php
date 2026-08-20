@extends('adminlte::page')

@section('title', 'Subir AV-3')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-0">Subir AV-3</h3>
            <small class="text-muted">
                Importación de movimientos de material valorado.
            </small>
        </div>
    </div>
@stop

@section('content')
    <x-material-movements.alerts />
    <div class="row">
        {{-- Importar archivo --}}
        <div class="col-lg-5">
            <x-material-movements.upload-card />
        </div>
        {{-- Últimos movimientos --}}
        <div class="col-lg-7">
            <x-material-movements.movements-card :movements="$movements" />
        </div>
    </div>
@stop
