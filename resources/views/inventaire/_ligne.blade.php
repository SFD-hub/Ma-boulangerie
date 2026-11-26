{{-- Ligne d'un écart d'inventaire. Variables : $inv, $loop. --}}
                @php $perte = (float) $inv->ecart < 0; @endphp
                <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:700;color:#111827">{{ $inv->matierePremiere->nom ?? '—' }}</div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">
                            {{ $inv->date_inventaire->format('d/m/Y') }} · {{ \App\Support\Nombre::qte($inv->quantite_avant) }} → {{ \App\Support\Nombre::qte($inv->quantite_comptee) }}
                            @if($inv->note) · {{ $inv->note }} @endif
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:14px;font-weight:800;color:{{ $perte ? '#DC2626' : '#059669' }}">
                            {{ (float) $inv->ecart > 0 ? '+' : '' }}{{ \App\Support\Nombre::qte($inv->ecart) }}
                        </div>
                    </div>
                </div>
