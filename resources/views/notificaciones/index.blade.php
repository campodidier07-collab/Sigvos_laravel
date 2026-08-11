@extends('layouts.adminlte')

@section('title', 'Notificaciones')
@section('page-title', 'Centro de Notificaciones')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
  <li class="breadcrumb-item active">Notificaciones</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">Historial de Notificaciones</h3>
        
        <div class="card-tools">
          <form action="{{ route('notificaciones.marcar_todas') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-success">
              <i class="fas fa-check-double mr-1"></i> Marcar todas como leídas
            </button>
          </form>
        </div>
      </div>
      
      <div class="card-body p-0">
        <table class="table table-hover table-striped">
          <tbody>
            @forelse($notificaciones as $noti)
              <tr class="{{ !$noti->leida ? 'bg-light font-weight-bold' : '' }}">
                <td style="width: 50px;" class="text-center">
                  @if(!$noti->leida)
                    <i class="fas fa-circle text-primary" style="font-size: 10px;"></i>
                  @else
                    <i class="far fa-circle text-muted" style="font-size: 10px;"></i>
                  @endif
                </td>
                <td>
                  <h6 class="mb-1 text-dark">{{ $noti->titulo }}</h6>
                  <p class="mb-0 text-muted">{{ $noti->mensaje }}</p>
                </td>
                <td class="text-right text-muted">
                  <small><i class="far fa-clock mr-1"></i> {{ $noti->creado_en->diffForHumans() }}</small>
                </td>
                <td class="text-right" style="width: 150px;">
                  @if(!$noti->leida)
                    <form action="{{ route('notificaciones.leida', $noti) }}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-primary">Ver detalle</button>
                    </form>
                  @elseif($noti->url)
                    <a href="{{ $noti->url }}" class="btn btn-sm btn-default">Ir al enlace</a>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center py-5 text-muted">
                  <i class="far fa-bell-slash fa-3x mb-3 text-light"></i>
                  <h5>No tienes notificaciones por el momento.</h5>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      
      @if($notificaciones->hasPages())
      <div class="card-footer clearfix">
        {{ $notificaciones->links('pagination::bootstrap-4') }}
      </div>
      @endif

    </div>
  </div>
</div>
@endsection
