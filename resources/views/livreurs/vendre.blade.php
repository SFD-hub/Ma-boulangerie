@extends('layouts.app')

@section('title', 'Ventes du vendeur')

@section('content')

    @php
        $partsEnMain = max(0, $partsDonnees - $partsVendues);
        $estimation  = (int) round($partsVendues * $prixPain / 6);
    @endphp

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;margin-bottom:22px">
        <div style="flex:1">
            <a href="{{ route('livreurs.show', $livreur) }}"
               style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
        <span style="font-size:17px;font-weight:700;color:#111827">{{ trim($livreur->prenom . ' ' . $livreur->nom) }}</span>
        <div style="flex:1"></div>
    </div>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    @if(! $distribution)

        <div style="text-align:center;padding:48px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">🥖</div>
            Aucun pain en main.
            <div style="margin-top:16px">
                <a href="{{ route('livreurs.attribuer.form', $livreur) }}"
                   style="background:#F97316;color:#FFFFFF;border-radius:20px;padding:10px 18px;font-size:14px;font-weight:700;text-decoration:none">
                    Attribuer des pains
                </a>
            </div>
        </div>

    @else

        {{-- ── Ce qu'il a en main ── --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px">
            <div style="background:#DBEAFE;border-radius:16px;padding:14px 10px;text-align:center">
                <div style="font-size:11px;font-weight:600;color:#6B7280;margin-bottom:6px">Donnés</div>
                <div style="font-size:24px;font-weight:800;color:#111827;line-height:1">{{ \App\Support\Nombre::painsEntiers($partsDonnees / 6) }}</div>
            </div>
            <div style="background:#D1FAE5;border-radius:16px;padding:14px 10px;text-align:center">
                <div style="font-size:11px;font-weight:600;color:#6B7280;margin-bottom:6px">Vendus</div>
                <div style="font-size:24px;font-weight:800;color:#111827;line-height:1">{{ \App\Support\Nombre::painsEntiers($partsVendues / 6) }}</div>
                <div style="font-size:11px;color:#9CA3AF;margin-top:4px">{{ \App\Support\Nombre::painsLegende($partsVendues / 6) }}</div>
            </div>
            <div style="background:#FEF3C7;border-radius:16px;padding:14px 10px;text-align:center">
                <div style="font-size:11px;font-weight:600;color:#6B7280;margin-bottom:6px">En main</div>
                <div style="font-size:24px;font-weight:800;color:#111827;line-height:1">{{ \App\Support\Nombre::painsEntiers($partsEnMain / 6) }}</div>
                <div style="font-size:11px;color:#9CA3AF;margin-top:4px">{{ \App\Support\Nombre::painsLegende($partsEnMain / 6) }}</div>
            </div>
        </div>

        @if($prixPain > 0)
            <div style="font-size:12px;color:#6B7280;text-align:center;margin-bottom:16px">
                À verser pour l'instant : <strong style="color:#111827">{{ number_format($estimation, 0, ',', ' ') }}&nbsp;FCFA</strong>
                ({{ number_format($prixPain, 0, ',', ' ') }}&nbsp;FCFA le pain)
            </div>
        @endif

        {{-- ── Enregistrer une vente ── --}}
        <div style="background:#FFFFFF;border-radius:18px;padding:18px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">
            <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:6px">Enregistrer une vente</div>

            <form method="POST" action="{{ route('livreurs.vendre', $livreur) }}">
                @csrf

                @php
                    $lignes = [
                        ['id' => 'Entiers', 'name' => 'nombre_entiers', 'label' => 'Entier'],
                        ['id' => 'Moities', 'name' => 'nombre_moities', 'label' => 'Moitié'],
                        ['id' => 'Tiers',   'name' => 'nombre_tiers',   'label' => 'Tiers'],
                    ];
                @endphp
                @foreach($lignes as $l)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #F3F4F6">
                        <div style="font-size:15px;font-weight:600;color:#111827">{{ $l['label'] }}</div>
                        <div style="display:flex;align-items:center;gap:10px">
                            <button type="button" onclick="changerPiece('piece{{ $l['id'] }}', -1)"
                                    style="width:40px;height:40px;border-radius:12px;border:1px solid var(--border);background:#fff;font-size:22px;font-weight:700;color:#111827;cursor:pointer">−</button>
                            <input type="number" name="{{ $l['name'] }}" id="piece{{ $l['id'] }}" min="0" inputmode="numeric"
                                   value="{{ old($l['name'], 0) }}"
                                   style="width:56px;text-align:center;font-size:18px;font-weight:700;border:none;background:transparent;color:#111827">
                            <button type="button" onclick="changerPiece('piece{{ $l['id'] }}', 1)"
                                    style="width:40px;height:40px;border-radius:12px;border:none;background:#F97316;font-size:22px;font-weight:700;color:#fff;cursor:pointer">+</button>
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary btn-full" style="margin-top:16px" {{ $partsEnMain <= 0 ? 'disabled' : '' }}>
                    Enregistrer la vente
                </button>
            </form>
        </div>

        <script>
            function changerPiece(id, delta) {
                const el = document.getElementById(id);
                el.value = Math.max(0, (parseInt(el.value || 0) || 0) + delta);
            }
        </script>

        {{-- ── Ventes saisies sur cette distribution ── --}}
        <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:10px">Ventes saisies</div>

        @if($ventes->isEmpty())
            <div style="text-align:center;padding:24px 0;color:#9CA3AF;font-size:14px">Aucune vente saisie.</div>
        @else
            <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">
                @foreach($ventes as $vente)
                    <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">
                        <div style="flex:1;min-width:0">
                            <div style="font-size:14px;font-weight:600;color:#111827">{{ $vente->descriptionPieces() }}</div>
                            <div style="font-size:12px;color:#9CA3AF;margin-top:2px">{{ $vente->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                        <form method="POST" action="{{ route('ventes-vendeur.destroy', $vente) }}"
                              onsubmit="return confirm('Supprimer cette vente ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    style="font-size:13px;font-weight:600;color:#EF4444;background:none;border:none;cursor:pointer;padding:6px 4px">
                                Supprimer
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <a href="{{ route('livreurs.verser.form', $livreur) }}"
           style="display:flex;align-items:center;justify-content:center;gap:8px;background:#F97316;color:#FFFFFF;border-radius:14px;padding:15px 20px;font-size:16px;font-weight:700;text-decoration:none">
            💰 Enregistrer versement
        </a>

    @endif

@endsection
