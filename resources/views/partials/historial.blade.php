@forelse($actividades as $actividad)
<article class="elemento-lista">
    <div class="fila">
        <strong>{{ $actividad->asunto }}</strong>
        <span class="insignia {{ $actividad->completada ? 'verde' : 'ambar' }}">{{ $actividad->completada ? 'Completada' : 'Pendiente' }}</span>
    </div>
    <p class="con-saltos">{{ $actividad->descripcion }}</p>
    <small>{{ $actividad->tipo }} · {{ $actividad->autor->name }} · {{ $actividad->created_at->format('d/m/Y H:i') }}</small>
    @if($actividad->programada_para)
    <small>Programada: {{ $actividad->programada_para->format('d/m/Y H:i') }}</small>
    @endif
</article>
@empty
<p class="vacio">Todavía no hay actividades registradas.</p>
@endforelse
