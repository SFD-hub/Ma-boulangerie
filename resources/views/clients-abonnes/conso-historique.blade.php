@extends('layouts.app')

@section('title', 'Historique consommations')

@section('content')

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('clients-abonnes.show', $clientAbonne) }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Historique consommations</span>
        <div style="width:36px"></div>
    </div>

    {{-- Sous-titre abonné --}}
    <div style="font-size:13px;color:#9CA3AF;margin-bottom:16px;text-align:center">
        {{ $clientAbonne->prenom }} {{ $clientAbonne->nom }}
    </div>

    @if($consommations->isEmpty())

        <div style="text-align:center;padding:60px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">🍞</div>
            Aucune consommation enregistrée.
        </div>

    @else

        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">

            @foreach($consommations as $conso)
                <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}position:relative">

                    {{-- Icône --}}
                    <div style="width:44px;height:44px;border-radius:11px;background:#ECFDF5;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                        🍞
                    </div>

                    {{-- Infos --}}
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:700;color:#111827">
                            {{ $conso->date_consommation->format('d/m/Y') }}
                        </div>
                        <div style="font-size:13px;font-weight:700;color:#F97316;margin-top:2px">
                            {{ $conso->quantite }}&nbsp;pain{{ $conso->quantite > 1 ? 's' : '' }}
                        </div>
                    </div>

                    {{-- Menu ··· --}}
                    <details style="position:relative;flex-shrink:0">
                        <summary style="list-style:none;cursor:pointer;color:#9CA3AF;padding:8px 4px;font-size:20px;font-weight:700;letter-spacing:2px;line-height:1;user-select:none">
                            ···
                        </summary>
                        <div style="position:absolute;right:0;top:36px;background:#FFFFFF;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.14);z-index:50;min-width:148px;overflow:hidden;border:1px solid #F3F4F6">
                            <a href="{{ route('consommations-abonnes.edit', $conso) }}"
                               style="display:flex;align-items:center;gap:10px;padding:13px 16px;font-size:14px;font-weight:500;color:#111827;text-decoration:none">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Modifier
                            </a>
                            <div style="height:1px;background:#F3F4F6"></div>
                            <form method="POST" action="{{ route('consommations-abonnes.destroy', $conso) }}"
                                  onsubmit="return confirm('Supprimer cette consommation ?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        style="display:flex;align-items:center;gap:10px;padding:13px 16px;font-size:14px;font-weight:500;color:#EF4444;background:none;border:none;cursor:pointer;width:100%;text-align:left">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </details>

                </div>
            @endforeach

        </div>

        {{-- ── Total ── --}}
        <div style="background:#F97316;border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:15px;font-weight:600;color:#FFFFFF">Total pains consommés</span>
            <span style="font-size:16px;font-weight:800;color:#FFFFFF">{{ number_format($totalPains, 0, ',', ' ') }}</span>
        </div>

    @endif

@endsection
