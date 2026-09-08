@extends('layouts.app')

@section('title', 'Historique des dépenses')

@section('content')

    @php
        $libelles = \App\Http\Controllers\DepenseController::CATEGORIES_LIBELLES;
    @endphp

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('depenses.index') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Historique des dépenses</span>
        <a href="{{ route('depenses.create') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none;font-size:24px;font-weight:300;line-height:1">
            +
        </a>
    </div>

    @if($depenses->isEmpty())

        <div style="text-align:center;padding:60px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">💸</div>
            Aucune dépense enregistrée.
        </div>

    @else

        {{-- ── Liste complète ── --}}
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">

            @foreach($depenses as $depense)
                @php
                    $isAuto  = in_array($depense->categorie, ['achat_farine', 'achat_levure']);
                    $libelle = $libelles[$depense->categorie] ?? $depense->categorie;
                @endphp
                <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}position:relative">

                    <x-depense-icone :categorie="$depense->categorie" />

                    {{-- Infos --}}
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $depense->libelle }}
                        </div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">
                            {{ $libelle }}
                        </div>
                    </div>

                    {{-- Montant + Date --}}
                    <div style="text-align:right;flex-shrink:0;margin-right:4px">
                        <div style="font-size:14px;font-weight:700;color:#111827">
                            {{ number_format($depense->montant, 0, ',', ' ') }}&nbsp;FCFA
                        </div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">
                            {{ $depense->date_depense->format('d/m/Y') }}
                        </div>
                    </div>

                    {{-- Menu ··· --}}
                    @if(!$isAuto)
                        <details style="position:relative;flex-shrink:0">
                            <summary style="list-style:none;cursor:pointer;color:#9CA3AF;padding:8px 4px;font-size:20px;font-weight:700;letter-spacing:2px;line-height:1;user-select:none">
                                ···
                            </summary>
                            <div style="position:absolute;right:0;top:36px;background:#FFFFFF;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.14);z-index:50;min-width:148px;overflow:hidden;border:1px solid #F3F4F6">
                                <a href="{{ route('depenses.edit', $depense) }}"
                                   style="display:flex;align-items:center;gap:10px;padding:13px 16px;font-size:14px;font-weight:500;color:#111827;text-decoration:none">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Modifier
                                </a>
                                <div style="height:1px;background:#F3F4F6"></div>
                                <form method="POST" action="{{ route('depenses.destroy', $depense) }}"
                                      onsubmit="return confirm('Supprimer cette dépense ?')">
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
                    @endif

                </div>
            @endforeach

        </div>

        {{-- ── Pagination ── --}}
        @if($depenses->hasPages())
            <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 0 8px">

                @if($depenses->onFirstPage())
                    <span style="font-size:14px;font-weight:600;color:#D1D5DB;padding:10px 16px;background:#F9FAFB;border-radius:10px">
                        ← Précédent
                    </span>
                @else
                    <a href="{{ $depenses->previousPageUrl() }}"
                       style="font-size:14px;font-weight:600;color:#F97316;padding:10px 16px;background:#FFF7ED;border-radius:10px;text-decoration:none">
                        ← Précédent
                    </a>
                @endif

                <span style="font-size:13px;color:#9CA3AF;font-weight:500">
                    Page {{ $depenses->currentPage() }} / {{ $depenses->lastPage() }}
                </span>

                @if($depenses->hasMorePages())
                    <a href="{{ $depenses->nextPageUrl() }}"
                       style="font-size:14px;font-weight:600;color:#F97316;padding:10px 16px;background:#FFF7ED;border-radius:10px;text-decoration:none">
                        Suivant →
                    </a>
                @else
                    <span style="font-size:14px;font-weight:600;color:#D1D5DB;padding:10px 16px;background:#F9FAFB;border-radius:10px">
                        Suivant →
                    </span>
                @endif

            </div>
        @endif

        {{-- ── Total dépenses ── --}}
        <div style="background:#F97316;border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:15px;font-weight:600;color:#FFFFFF">Total dépenses</span>
            <span style="font-size:16px;font-weight:800;color:#FFFFFF">{{ number_format($totalGlobal, 0, ',', ' ') }}&nbsp;FCFA</span>
        </div>

    @endif

@endsection
