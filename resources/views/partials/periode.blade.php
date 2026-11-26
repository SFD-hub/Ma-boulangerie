{{-- Sélecteur de période (jusqu'à 1 an) partagé par l'accueil Dépenses et le détail d'une catégorie.
     Variables : $action (URL), $du, $au, $periodeLimitee --}}
<form method="GET" action="{{ $action }}"
      style="background:#FFFFFF;border-radius:16px;padding:14px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">
    <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
        <div style="flex:1;min-width:130px">
            <label style="display:block;font-size:12px;font-weight:600;color:#6B7280;margin-bottom:4px">Du</label>
            <input class="form-input" type="date" name="du" value="{{ $du }}" max="{{ date('Y-m-d') }}">
        </div>
        <div style="flex:1;min-width:130px">
            <label style="display:block;font-size:12px;font-weight:600;color:#6B7280;margin-bottom:4px">Au</label>
            <input class="form-input" type="date" name="au" value="{{ $au }}" max="{{ date('Y-m-d') }}">
        </div>
        <button type="submit" class="btn btn-primary" style="height:44px">Afficher</button>
    </div>
    @php
        $raccourcis = [
            'Ce mois'          => [now()->startOfMonth()->toDateString(), now()->toDateString()],
            'Mois dernier'     => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            '12 derniers mois' => [now()->subYear()->addDay()->toDateString(), now()->toDateString()],
        ];
    @endphp
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
        @foreach($raccourcis as $nom => [$d1, $d2])
            <a href="{{ $action }}?du={{ $d1 }}&au={{ $d2 }}"
               style="font-size:12px;font-weight:600;padding:6px 12px;border-radius:20px;text-decoration:none;{{ $du === $d1 && $au === $d2 ? 'background:#F97316;color:#FFFFFF' : 'background:#F3F4F6;color:#374151' }}">
                {{ $nom }}
            </a>
        @endforeach
    </div>
    @if($periodeLimitee)
        <div style="font-size:12px;color:#B45309;margin-top:10px">La période est limitée à 1 an : le début a été ajusté.</div>
    @endif
</form>
