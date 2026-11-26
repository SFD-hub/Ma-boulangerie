{{-- Ligne d'achat (ou de stock initial) d'un produit, avec le menu ··· Modifier / Supprimer. Variables : $achat, $mp, $unite, $loop. --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">
                    <div style="min-width:0">
                        <div style="font-size:14px;font-weight:600;color:#111827">
                            {{ \App\Support\Nombre::qte($achat->quantite) }}&nbsp;{{ $mp->uniteLibelle($achat->quantite) }}
                        </div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">{{ $achat->date_achat->format('d/m/Y') }}{{ $achat->depart ? ' · Stock de départ' : '' }}</div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:14px;font-weight:700;color:#111827">{{ number_format($achat->montant, 0, ',', ' ') }}&nbsp;FCFA</div>
                        @if((float) $achat->quantite > 0)
                            <div style="font-size:12px;color:#9CA3AF;margin-top:2px">{{ number_format($achat->montant / $achat->quantite, 0, ',', ' ') }}&nbsp;/&nbsp;{{ $unite }}</div>
                        @endif
                    </div>

                    {{-- Menu ··· : modifier ou supprimer l'achat (on revient ensuite sur cette page) --}}
                    <details style="position:relative;flex-shrink:0">
                        <summary style="list-style:none;cursor:pointer;color:#9CA3AF;padding:8px 4px;font-size:20px;font-weight:700;letter-spacing:2px;line-height:1;user-select:none">
                            ···
                        </summary>
                        <div style="position:absolute;right:0;top:36px;background:#FFFFFF;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.14);z-index:50;min-width:148px;overflow:hidden;border:1px solid #F3F4F6">
                            <a href="{{ route('achats-matieres-premieres.edit', ['achat_matiere_premiere' => $achat, 'depuis_produit' => 1]) }}"
                               style="display:flex;align-items:center;gap:10px;padding:13px 16px;font-size:14px;font-weight:500;color:#111827;text-decoration:none">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Modifier
                            </a>
                            <div style="height:1px;background:#F3F4F6"></div>
                            <form method="POST" action="{{ route('achats-matieres-premieres.destroy', $achat) }}"
                                  onsubmit="return confirm('Supprimer cet achat ? Le stock sera ajusté.')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="depuis_produit" value="1">
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
