@extends('layouts.adminlte')

@section('title', 'Detalle de Lote')
@section('page-title', 'Lote ' . $lote->identificador . ' - ' . $lote->nombre)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
  <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-4">
    
    <!-- Tarjeta de Info del Lote -->
    <div class="card card-success card-outline">
      <div class="card-body box-profile">
        <div class="text-center mb-3">
          <span class="img-circle elevation-2 bg-success d-inline-flex align-items-center justify-content-center text-white font-weight-bold"
                style="width:80px;height:80px;font-size:2.5rem;">
            {{ $lote->identificador }}
          </span>
        </div>

        <h3 class="profile-username text-center">{{ $lote->nombre }}</h3>
        <p class="text-muted text-center">
          @if($lote->es_alternativo)
            <span class="badge badge-info">Alternativo</span>
          @else
            <span class="badge badge-primary">Principal</span>
          @endif
          @if(!$lote->activo)
            <span class="badge badge-secondary">Inactivo</span>
          @endif
        </p>

        <ul class="list-group list-group-unbordered mb-3">
          <li class="list-group-item">
            <b>Estado</b> 
            <a class="float-right text-{{ $lote->estado == 'disponible' ? 'success' : ($lote->estado == 'ocupado' ? 'warning' : 'danger') }}">
              {{ ucfirst($lote->estado) }}
            </a>
          </li>
          <li class="list-group-item">
            <b>Área</b> <a class="float-right text-dark">{{ number_format($lote->area_ha, 2) }} ha</a>
          </li>
          <li class="list-group-item">
            <b>Cultivo Preferido</b> 
            <a class="float-right text-dark">{{ $lote->tipoPreferido->nombre ?? 'Ninguno' }}</a>
          </li>
        </ul>

        <strong><i class="fas fa-map-marker-alt mr-1 text-danger"></i> Ubicación</strong>
        <p class="text-muted">{{ $lote->ubicacion }}</p>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-primary btn-block"><b>Editar Lote</b></a>
        @endif
      </div>
    </div>
    
    <!-- Trabajadores Asignados -->
    <div class="card card-info">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Trabajadores Asignados</h3>
      </div>
      <div class="card-body p-0">
        @if($lote->trabajadores->isEmpty())
          <div class="text-center py-3 text-muted">
            <small>No hay trabajadores asignados.</small>
          </div>
        @else
          <ul class="list-group list-group-flush">
            @foreach($lote->trabajadores as $trabajador)
            <li class="list-group-item">
              <i class="fas fa-user-circle text-info mr-1"></i>
              {{ $trabajador->nombre }}
            </li>
            @endforeach
          </ul>
        @endif
      </div>
      @if(auth()->user()->isAdmin())
      <div class="card-footer text-center">
        <button class="btn btn-sm btn-outline-info">Gestionar Asignaciones</button>
      </div>
      @endif
    </div>
    
  </div>
  
  <div class="col-md-8">
    <div class="card">
      <div class="card-header p-2">
        <ul class="nav nav-pills">
          <li class="nav-item"><a class="nav-link active" href="#actual" data-toggle="tab">Cultivo Actual</a></li>
          <li class="nav-item"><a class="nav-link" href="#historial" data-toggle="tab">Historial</a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">
          
          <!-- Tab Cultivo Actual -->
          <div class="active tab-pane" id="actual">
            @if($lote->cultivoActivo)
              <div class="post">
                <div class="user-block mb-3">
                  <span class="username ml-0">
                    <a href="{{ route('cultivos.show', $lote->cultivoActivo) }}" class="text-success" style="font-size: 1.2rem;">
                      <i class="fas fa-seedling mr-1"></i> {{ $lote->cultivoActivo->codigo }}
                    </a>
                  </span>
                  <span class="description ml-0">Sembrado el {{ $lote->cultivoActivo->fecha_siembra->format('d/m/Y') }}</span>
                </div>
                
                <div class="row">
                  <div class="col-sm-6">
                    <dl class="row mb-0">
                      <dt class="col-sm-5">Variedad:</dt>
                      <dd class="col-sm-7">{{ $lote->cultivoActivo->variedad->tipoCultivo->nombre ?? '' }} - {{ $lote->cultivoActivo->variedad->nombre ?? '' }}</dd>
                      
                      <dt class="col-sm-5">Estado:</dt>
                      <dd class="col-sm-7">
                        <span class="badge badge-info">{{ ucfirst($lote->cultivoActivo->estado) }}</span>
                      </dd>
                      
                      <dt class="col-sm-5">Cosecha est.:</dt>
                      <dd class="col-sm-7 text-warning font-weight-bold">
                        {{ $lote->cultivoActivo->fecha_cosecha_estimada->format('d/m/Y') }}
                      </dd>
                    </dl>
                  </div>
                  <div class="col-sm-6 text-center">
                    <!-- Progreso aproximado de la cosecha -->
                    @php
                       $inicio = $lote->cultivoActivo->fecha_siembra;
                       $fin = $lote->cultivoActivo->fecha_cosecha_estimada;
                       $hoy = now();
                       $totalDias = $inicio->diffInDays($fin) ?: 1;
                       $diasPasados = $inicio->diffInDays($hoy);
                       $porcentaje = min(100, max(0, round(($diasPasados / $totalDias) * 100)));
                    @endphp
                    <p class="mb-1 text-muted">Progreso hacia cosecha</p>
                    <div class="progress progress-sm" style="height: 10px;">
                      <div class="progress-bar bg-success" style="width: {{ $porcentaje }}%"></div>
                    </div>
                    <small>{{ $porcentaje }}% completado</small>
                    
                    <div class="mt-3">
                      <a href="{{ route('cultivos.show', $lote->cultivoActivo) }}" class="btn btn-sm btn-outline-success">
                        Ver Cultivo Completo
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            @else
              <div class="text-center py-5">
                <i class="fas fa-leaf fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Este lote está disponible</h5>
                <p class="text-muted">No hay ningún cultivo activo sembrado actualmente en este lote.</p>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('cultivos.create', ['lote' => $lote->id]) }}" class="btn btn-success mt-2">
                  <i class="fas fa-plus mr-1"></i> Registrar Siembra
                </a>
                @endif
              </div>
            @endif
          </div>
          
          <!-- Tab Historial -->
          <div class="tab-pane" id="historial">
            @if($lote->cultivos->isEmpty())
              <div class="text-center py-4 text-muted">
                <small>No hay registro histórico de cultivos para este lote.</small>
              </div>
            @else
              <div class="table-responsive">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Fecha Siembra</th>
                      <th>Estado</th>
                      <th>Cosecha (Kg)</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($lote->cultivos as $cultivoHist)
                    <tr>
                      <td>{{ $cultivoHist->codigo }}</td>
                      <td>{{ $cultivoHist->fecha_siembra->format('d/m/Y') }}</td>
                      <td>
                        <span class="badge badge-{{ $cultivoHist->estado == 'cosechado' ? 'success' : 'secondary' }}">
                          {{ ucfirst($cultivoHist->estado) }}
                        </span>
                      </td>
                      <td>{{ $cultivoHist->cantidad_cosechada_kg ? number_format($cultivoHist->cantidad_cosechada_kg, 2) : '—' }}</td>
                      <td class="text-right">
                        <a href="{{ route('cultivos.show', $cultivoHist) }}" class="btn btn-xs btn-default">
                          Ver
                        </a>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
          
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
