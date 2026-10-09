<?php
// Genere les icones PNG de V-STORE a partir de la MEME geometrie que
// images/logo.svg et images/favicon.svg : tuile arrondie en degrade,
// V et S au trait, et le chariot pour la version complete.
//
// Aucune police n'est utilisee : le resultat est identique partout et
// les PNG ne peuvent pas se desynchroniser des SVG.
//
// Rendu en 4x puis reduction : c'est ce qui donne les bords lisses,
// GD ne sait pas dessiner en anti-crenele.
//
//   php outils/faire_icones.php            (ecrit dans images/)
//   php outils/faire_icones.php <dossier>

// par defaut : le dossier images/ du projet (ce script est dans outils/)
$sortie = ($argv[1] ?? dirname(__DIR__)) . "/images";

const BOITE  = 64;   // repere des SVG
const RAYON  = 12;   // arrondi des coins
const SURECH = 4;    // facteur de surechantillonnage

// degrade de la tuile (haut-gauche -> bas-droite)
const C1 = [0x4a, 0x6a, 0x7d];
const C2 = [0x2b, 0x41, 0x50];

// laiton du chariot
const LAITON = [0xa9, 0x83, 0x4f];


// ------------------------------------------------------------
// LES DEUX VARIANTES
// ------------------------------------------------------------

// marque complete (images/logo.svg) : VS + chariot
$complete = [
    "epaisseur" => 8,
    "v" => [[13, 17], [22, 41], [31, 17]],
    "s" => [
        [[50, 22], [50, 16.5], [38, 16.5], [38, 23]],
        [[38, 23], [38, 29], [50, 29], [50, 35]],
        [[50, 35], [50, 41.5], [38, 41.5], [38, 35.5]],
    ],
    "chariot" => [[38, 47.5], [41.2, 47.5], [43.5, 54.1], [54.3, 54.1], [56.6, 48.7], [42.8, 48.7]],
    "chariot_epaisseur" => 2,
    "roues" => [[46.5, 57.2, 1.5], [53.8, 57.2, 1.5]],
];

// version compacte (images/favicon.svg) : VS seul, plus gros
// a 16 px le chariot ne serait qu'une tache
$compacte = [
    "epaisseur" => 9,
    "v" => [[12, 18], [22, 46], [32, 18]],
    "s" => [
        [[52, 24], [52, 16.5], [38, 16.5], [38, 24.5]],
        [[38, 24.5], [38, 32], [52, 32], [52, 39.5]],
        [[52, 39.5], [52, 47.5], [38, 47.5], [38, 40]],
    ],
    "chariot" => null,
    "roues" => [],
];


// ------------------------------------------------------------
// ECHANTILLONNAGE DES TRACES
// ------------------------------------------------------------

function points_ligne(array $sommets, float $k): array
{
    $pts = [];
    for ($i = 0; $i < count($sommets) - 1; $i++) {
        [$x1, $y1] = $sommets[$i];
        [$x2, $y2] = $sommets[$i + 1];
        $n = max(2, (int) ceil(hypot($x2 - $x1, $y2 - $y1) * $k));
        for ($j = 0; $j <= $n; $j++) {
            $t = $j / $n;
            $pts[] = [($x1 + ($x2 - $x1) * $t) * $k, ($y1 + ($y2 - $y1) * $t) * $k];
        }
    }
    return $pts;
}


function points_bezier(array $courbes, float $k): array
{
    $pts = [];
    foreach ($courbes as [$p0, $p1, $p2, $p3]) {
        $approx = hypot($p1[0] - $p0[0], $p1[1] - $p0[1])
                + hypot($p2[0] - $p1[0], $p2[1] - $p1[1])
                + hypot($p3[0] - $p2[0], $p3[1] - $p2[1]);
        $n = max(8, (int) ceil($approx * $k));
        for ($j = 0; $j <= $n; $j++) {
            $t = $j / $n;
            $u = 1 - $t;
            $pts[] = [
                ($u*$u*$u*$p0[0] + 3*$u*$u*$t*$p1[0] + 3*$u*$t*$t*$p2[0] + $t*$t*$t*$p3[0]) * $k,
                ($u*$u*$u*$p0[1] + 3*$u*$u*$t*$p1[1] + 3*$u*$t*$t*$p2[1] + $t*$t*$t*$p3[1]) * $k,
            ];
        }
    }
    return $pts;
}


// un disque a chaque point du trace : c'est l'equivalent d'un trait
// a bouts ronds
function tracer(GdImage $img, array $pts, int $diametre, int $couleur): void
{
    foreach ($pts as [$x, $y]) {
        imagefilledellipse($img, (int) round($x), (int) round($y), $diametre, $diametre, $couleur);
    }
}


// ------------------------------------------------------------
// FABRICATION D'UNE ICONE
// ------------------------------------------------------------

function fabriquer(int $taille, array $m): GdImage
{
    $g = $taille * SURECH;     // toile de travail
    $k = $g / BOITE;           // repere SVG -> pixels

    $img = imagecreatetruecolor($g, $g);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));

    // --- degrade : une ligne anti-diagonale par pas ---
    $max = 2 * ($g - 1);
    for ($d = 0; $d <= $max; $d++) {
        $t = $d / $max;
        $c = imagecolorallocate($img,
            (int) round(C1[0] + (C2[0] - C1[0]) * $t),
            (int) round(C1[1] + (C2[1] - C1[1]) * $t),
            (int) round(C1[2] + (C2[2] - C1[2]) * $t));
        $x1 = min($d, $g - 1);
        $y2 = min($d, $g - 1);
        imageline($img, $x1, $d - $x1, $d - $y2, $y2, $c);
    }

    // --- coins arrondis : on rend transparent ce qui depasse ---
    $r = RAYON * $k;
    $vide = imagecolorallocatealpha($img, 0, 0, 0, 127);
    $coins = [[0, 0, $r, $r], [$g - 1, 0, $g - 1 - $r, $r],
              [0, $g - 1, $r, $g - 1 - $r], [$g - 1, $g - 1, $g - 1 - $r, $g - 1 - $r]];
    foreach ($coins as [$cx, $cy, $ox, $oy]) {
        for ($x = (int) min($cx, $ox); $x <= (int) max($cx, $ox); $x++) {
            for ($y = (int) min($cy, $oy); $y <= (int) max($cy, $oy); $y++) {
                if (hypot($x - $ox, $y - $oy) > $r) {
                    imagesetpixel($img, $x, $y, $vide);
                }
            }
        }
    }

    imagealphablending($img, true);

    // --- le chariot, sous les lettres ---
    if ($m["chariot"]) {
        $laiton = imagecolorallocate($img, LAITON[0], LAITON[1], LAITON[2]);
        tracer($img, points_ligne($m["chariot"], $k),
               (int) round($m["chariot_epaisseur"] * $k), $laiton);
        foreach ($m["roues"] as [$x, $y, $r_roue]) {
            $d_roue = (int) round($r_roue * 2 * $k);
            imagefilledellipse($img, (int) round($x * $k), (int) round($y * $k),
                               $d_roue, $d_roue, $laiton);
        }
    }

    // --- le V et le S ---
    $blanc = imagecolorallocate($img, 255, 255, 255);
    $e = (int) round($m["epaisseur"] * $k);
    tracer($img, points_ligne($m["v"], $k), $e, $blanc);
    tracer($img, points_bezier($m["s"], $k), $e, $blanc);

    // --- reduction : c'est elle qui lisse les bords ---
    $fin = imagecreatetruecolor($taille, $taille);
    imagealphablending($fin, false);
    imagesavealpha($fin, true);
    imagefill($fin, 0, 0, imagecolorallocatealpha($fin, 0, 0, 0, 127));
    imagecopyresampled($fin, $img, 0, 0, 0, 0, $taille, $taille, $g, $g);
    imagedestroy($img);

    return $fin;
}


// ------------------------------------------------------------

$a_faire = [
    // nom                taille  marque
    ["favicon-32.png",     32,    $compacte],   // onglet du navigateur
    ["favicon-180.png",   180,    $complete],   // ecran d'accueil iOS
];

foreach ($a_faire as [$nom, $taille, $marque]) {
    $img = fabriquer($taille, $marque);
    imagepng($img, $sortie . "/" . $nom, 9);
    imagedestroy($img);
    printf("%-18s %3d x %-3d  %s  %d octets\n", $nom, $taille, $taille,
           $marque["chariot"] ? "avec chariot" : "VS seul     ",
           filesize($sortie . "/" . $nom));
}
