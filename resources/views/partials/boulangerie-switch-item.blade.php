{{-- Un item de la liste "Mes boulangeries", identique en sidebar desktop et tiroir mobile --}}
<form method="POST" action="{{ route('boulangeries.switch', $b) }}">
    @csrf
    <button type="submit" class="boulangerie-switch-item {{ $b->id === $activeBoulangerieId ? 'active' : '' }}">
        <span class="boulangerie-switch-item-left">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span>{{ $b->nom }}</span>
        </span>
        @if($b->id === $activeBoulangerieId)
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        @endif
    </button>
</form>
