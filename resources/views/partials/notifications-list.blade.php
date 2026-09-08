@php
    $meta = \App\Models\ActivityLog::categories();
@endphp

@forelse($activites as $item)
    @php
        $cat  = $meta[$item->categorie] ?? ['label' => 'Activité', 'icon' => '🔔'];
        $isRead = in_array($item->id, $readIds, true);
    @endphp
    <div class="notif-item {{ $isRead ? 'read' : '' }}" data-id="{{ $item->id }}">
        <div class="notif-item-icon">{{ $cat['icon'] }}</div>
        <div class="notif-item-body">
            <div class="notif-item-title">
                {{ $cat['label'] }}
                @if(!$isRead)<span class="notif-dot"></span>@endif
            </div>
            <div class="notif-item-desc">{{ $item->description }}</div>
            <div class="notif-item-meta">
                {{ $item->user->name ?? 'Système' }} · {{ $item->created_at->locale('fr')->diffForHumans() }}
            </div>
        </div>
        <div class="notif-item-actions">
            @if(!$isRead)
                <button type="button" class="notif-action-btn notif-read-btn" title="Marquer comme lu" onclick="notifMarkRead({{ $item->id }}, this)">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </button>
            @endif
            <button type="button" class="notif-action-btn notif-dismiss-btn" title="Masquer" onclick="notifDismiss({{ $item->id }}, this)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
@empty
    <div class="notif-empty">
        <div style="font-size:32px;margin-bottom:8px">🔔</div>
        Aucune activité pour l'instant.
    </div>
@endforelse

@if($activites->hasPages())
    <div class="notif-pagination">
        <button type="button" class="notif-page-btn" {{ $activites->onFirstPage() ? 'disabled' : '' }}
                onclick="notifLoadPage({{ $activites->currentPage() - 1 }})">
            ← Précédent
        </button>
        <span class="notif-page-info">Page {{ $activites->currentPage() }} / {{ $activites->lastPage() }}</span>
        <button type="button" class="notif-page-btn" {{ $activites->hasMorePages() ? '' : 'disabled' }}
                onclick="notifLoadPage({{ $activites->currentPage() + 1 }})">
            Suivant →
        </button>
    </div>
@endif
