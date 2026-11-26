@php
    $painsChoix = \App\Models\Produit::orderBy('id')->get();
    $painChoisi = old('produit_id', $painActuel ?? $painsChoix->first()?->id);
@endphp
@if($painsChoix->count() > 1)
    <div style="margin-bottom:18px">
        <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Pain *</label>
        <select name="produit_id" required
                style="width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none">
            @foreach($painsChoix as $pain)
                <option value="{{ $pain->id }}" @selected((int) $painChoisi === $pain->id)>{{ $pain->nom }}</option>
            @endforeach
        </select>
    </div>
@endif
