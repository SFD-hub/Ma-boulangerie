@props(['categorie', 'size' => 44])

@php
/**
 * Système d'icônes centralisé pour les catégories de dépenses.
 *
 * Pour remplacer une icône SVG par un fichier PNG :
 *   → déposer le fichier dans public/images/depenses/{categorie}.png
 *   → le composant détecte automatiquement le fichier et l'affiche à la place du SVG.
 *
 * Exemple : public/images/depenses/eau.png
 */

$pngPath = public_path("images/depenses/{$categorie}.png");
$usePng  = file_exists($pngPath);

// Couleurs de fond + couleur du trait SVG par catégorie
$couleurs = [
    'achat_farine'   => ['bg' => '#FFF3E0', 'ic' => '#EA7820'],
    'achat_levure'   => ['bg' => '#FDF4FF', 'ic' => '#A855F7'],
    'salaire'        => ['bg' => '#EFF6FF', 'ic' => '#3B82F6'],
    'salaire_gerant' => ['bg' => '#EFF6FF', 'ic' => '#3B82F6'],
    'salaire_employe'=> ['bg' => '#F0F9FF', 'ic' => '#0284C7'],
    'eau'            => ['bg' => '#E0F7FA', 'ic' => '#0288D1'],
    'electricite'    => ['bg' => '#FEFCE8', 'ic' => '#CA8A04'],
    'transport'      => ['bg' => '#F5F3FF', 'ic' => '#7C3AED'],
    'carburant'      => ['bg' => '#FFF3E0', 'ic' => '#EA580C'],
    'reparation'     => ['bg' => '#F0FDF4', 'ic' => '#15803D'],
    'entretien'      => ['bg' => '#F0FDF4', 'ic' => '#15803D'],
    'huile'          => ['bg' => '#FFFBEB', 'ic' => '#B45309'],
    'sel'            => ['bg' => '#F8FAFC', 'ic' => '#475569'],
    'divers'         => ['bg' => '#F3F4F6', 'ic' => '#6B7280'],
    'autre'          => ['bg' => '#F3F4F6', 'ic' => '#6B7280'],
];

// Chemins SVG (24×24 viewBox, style Heroicons)
$svgs = [
    // Sac de farine : sac rectangulaire noué
    'achat_farine'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6l1 3H8L9 3zM7 6h10l1 14a1 1 0 01-1 1H7a1 1 0 01-1-1L7 6zm2 4h6m-6 4h6"/>',

    // Fiole : bécher de laboratoire
    'achat_levure'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6M10 3v5.5L6 16a2 2 0 001.8 2.8h8.4A2 2 0 0018 16l-4-7.5V3m-3 9h6"/>',

    // Gérant : silhouette personne seule
    'salaire_gerant' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 0115 0"/>',

    // Employés : deux silhouettes
    'salaire_employe'=> '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>',

    // Héritage "salaire" : même icône que gérant
    'salaire'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 0115 0"/>',

    // Eau : goutte d'eau
    'eau'            => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 2C9.5 5.5 6 10 6 14a6 6 0 0012 0c0-4-3.5-8.5-6-12z"/>',

    // Électricité : éclair
    'electricite'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>',

    // Transport : camionnette
    'transport'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 17H5a2 2 0 01-2-2V9l3-5h9v10H8zm0 0a2 2 0 104 0m-4 0a2 2 0 114 0M13 9v8m0-8h3l3 4v4h-3m-3 0a2 2 0 104 0"/>',

    // Carburant : pompe à essence
    'carburant'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 22V5a2 2 0 012-2h7a2 2 0 012 2v17M4 22h11M4 10h11M19 8l2-2v14h-2V8zm0 4h2"/>',

    // Réparation : clé anglaise
    'reparation'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',

    // Héritage "entretien" : même clé
    'entretien'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',

    // Huile : flacon avec goutte
    'huile'          => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6v3l2 3v9a2 2 0 01-2 2H9a2 2 0 01-2-2V9l2-3V3zm0 0h6M7 9h10m-7 4a2 2 0 100 4 2 2 0 000-4z"/>',

    // Sel : cube cristal
    'sel'            => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',

    // Divers (héritage) : points de suspension
    'divers'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/>',

    // Autre : étiquette prix
    'autre'          => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M3 3h8.5L21 12.5 12.5 21 3 11.5V3zm0 0v8.5"/>',
];

$c      = $couleurs[$categorie] ?? $couleurs['autre'];
$svg    = $svgs[$categorie]     ?? $svgs['autre'];
$radius = round($size * 0.25);
$inner  = round($size * 0.53);
@endphp

<div style="width:{{ $size }}px;height:{{ $size }}px;border-radius:{{ $radius }}px;background:{{ $c['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
    @if($usePng)
        <img src="{{ asset('images/depenses/'.$categorie.'.png') }}"
             alt="{{ $categorie }}"
             style="width:{{ $inner }}px;height:{{ $inner }}px;object-fit:contain">
    @else
        <svg width="{{ $inner }}" height="{{ $inner }}" fill="none"
             stroke="{{ $c['ic'] }}" stroke-width="1.75" viewBox="0 0 24 24">
            {!! $svg !!}
        </svg>
    @endif
</div>
