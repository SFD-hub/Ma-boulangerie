{{-- Information sur le stock + bénéfice. Variables : $infoStock (ou null), $benefice (ou null).
     Ancien bilan enregistré (calculé sur les achats) : ni l'un ni l'autre. --}}
@if($infoStock)
    <div style="background:#FFFFFF;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:11px 16px;border-bottom:1px solid #F3F4F6">
            <span style="font-size:13px;color:#9CA3AF">Achats de stock du mois</span>
            <span style="font-size:13px;font-weight:600;color:#6B7280">{{ number_format($infoStock['achats'], 0, ',', ' ') }}&nbsp;FCFA</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:11px 16px">
            <span style="font-size:13px;color:#9CA3AF">Variation du stock</span>
            <span style="font-size:13px;font-weight:600;color:#6B7280">{{ $infoStock['variation'] > 0 ? '+' : '' }}{{ number_format($infoStock['variation'], 0, ',', ' ') }}&nbsp;FCFA</span>
        </div>
    </div>
@endif

@if($benefice !== null)
    <div style="border-radius:18px;padding:24px 20px;text-align:center;background:#F9FAFB;border:1.5px solid #E5E7EB;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px">
        <div style="font-size:13px;font-weight:700;color:#6B7280;margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em">
            Bénéfice
        </div>
        <div style="font-size:38px;font-weight:800;color:#111827;line-height:1">
            {{ number_format($benefice, 0, ',', ' ') }}&nbsp;<span style="font-size:20px">FCFA</span>
        </div>
    </div>
@endif
