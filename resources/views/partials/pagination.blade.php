{{-- Pagination Précédent / Suivant. Variable : $paginator --}}
@if($paginator->hasPages())
    <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 0 16px">

        @if($paginator->onFirstPage())
            <span style="font-size:14px;font-weight:600;color:#D1D5DB;padding:10px 16px;background:#F9FAFB;border-radius:10px">
                ← Précédent
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               style="font-size:14px;font-weight:600;color:#F97316;padding:10px 16px;background:#FFF7ED;border-radius:10px;text-decoration:none">
                ← Précédent
            </a>
        @endif

        <span style="font-size:13px;color:#9CA3AF;font-weight:500">
            Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
        </span>

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
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
