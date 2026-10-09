<?php
// Genere les icones PNG de V-STORE a partir de la MEME geometrie que
// images/favicon.svg : tuile arrondie en degrade + V et S au trait.
// Aucune police n'est utilisee, donc le resultat est identique partout.
//
// Rendu en 4x puis reduction : c'est ce qui donne les bords lisses,
// GD ne sait pas dessiner en anti-crenele.

// par defaut : le dossier images/ du projet (ce script est dans outils/)
$sortie = ($argv[1] ?? dirname(__DIR__)) . "/images";

const BOITE   = 64;     // repere du SVG
const RAYON   = 12;     // arrondi des coins
const EPAISSEUR   = 8;      // epaisseur du V et du S
const SURECH  = 4;      // facteur de surechantillonnage

// --- couleurs du degrade (haut-gauche -> bas-droite) ---
const C1 = [0x4a, 0x6a, 0x7d];
const C2 = [0x2b, 0x41, 0x50];

// --- le V : une ligne brisee ---
$trace_v = [[12, 18], [22, 46], [32, 18]];

// --- le S : trois courbes de Bezier cubiques bout a bout ---
$trace_s = [
    [[52, 23.5], [52, 16.5], [38, 16.5], [38, 24.5]],
    [[38, 24.5], [38, 32.0], [52, 32.0], [52, 39.5]],
    [[52, 39.5], [52, 47.5], [38, 47.5], [38, 40.0]],
];


// ------------------------------------------------------------
// echantillonnage des traces
// ------------------------------------------------------------

function points_ligne(array $sommets, float $k): array
{
    $pts = [];
    for ($i = 0; $i < count($sommets) - 1; $i++) {
        [$x1, $y1] = $sommets[$i];
        [$x2, $y2] = $sommets[$i + 1];
        $d = hypot($x2 - $x1, $y2 - $y1) * $k;
        $n = max(2, (int) ceil($d));
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
        // longueur approchee pour choisir le nombre de points
        $approx = hypot($p1[0] - $p0[0], $p1[1] - $p0[1])
                + hypot($p2[0] - $p1[0], $p2[1] - $p1[1])
                + hypot($p3[0] - $p2[0], $p3[1] - $p2[1]);
        $n = max(8, (int) ceil($approx * $k));
        for ($j = 0; $j <= $n; $j++) {
            $t = $j / $n;
            $u = 1 - $t;
            $x = $u*$u*$u*$p0[0] + 3*$u*$u*$t*$p1[0] + 3*$u*$t*$t*$p2[0] + $t*$t*$t*$p3[0];
            $y = $u*$u*$u*$p0[1] + 3*$u*$u*$t*$p1[1] + 3*$u*$t*$t*$p2[1] + $t*$t*$t*$p3[1];
            $pts[] = [$x * $k, $y * $k];
        }
    }
    return $pts;
}


// ------------------------------------------------------------
// fabrication d'une icone
// ------------------------------------------------------------

function fabriquer(int $taille, array $trace_v, array $trace_s): GdImage
{
    $g = $taille * SURECH;           // toile de travail
    $k = $g / BOITE;                 // repere SVG -> pixels

    $img = imagecreatetruecolor($g, $g);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));

    // --- degrade en diagonale : une ligne anti-diagonale par pas ---
    $max = 2 * ($g - 1);
    for ($d = 0; $d <= $max; $d++) {
        $t = $d / $max;
        $c = imagecolorallocate($img,
            (int) round(C1[0] + (C2[0] - C1[0]) * $t),
            (int) round(C1[1] + (C2[1] - C1[1]) * $t),
            (int) round(C1[2] + (C2[2] - C1[2]) * $t));
        $x1 = min($d, $g - 1);
        $y1 = $d - $x1;
        $y2 = min($d, $g - 1);
        $x2 = $d - $y2;
        imageline($img, $x1, $y1, $x2, $y2, $c);
    }

    // --- coins arrondis : on rend transparent ce qui depasse ---
    $r = RAYON * $k;
    $vide = imagecolorallocatealpha($img, 0, 0, 0, 127);
    $coins = [[0, 0, $r, $r], [$g - 1, 0, $g - 1 - $r, $r],
              [0, $g - 1, $r, $g - 1 - $r], [$g - 1, $g - 1, $g - 1 - $r, $g - 1 - $r]];
    foreach ($coins as [$cx, $cy, $ox, $oy]) {
        $x0 = (int) min($cx, $ox); $x1 = (int) max($cx, $ox);
        $y0 = (int) min($cy, $oy); $y1 = (int) max($cy, $oy);
        for ($x = $x0; $x <= $x1; $x++) {
            for ($y = $y0; $y <= $y1; $y++) {
                if (hypot($x - $ox, $y - $oy) > $r) {
                    imagesetpixel($img, $x, $y, $vide);
                }
            }
        }
    }

    // --- le V et le S : un disque blanc a chaque point du trace ---
    imagealphablending($img, true);
    $blanc = imagecolorallocate($img, 255, 255, 255);
    $e = (int) round(EPAISSEUR * $k);
    foreach ([points_ligne($trace_v, $k), points_bezier($trace_s, $k)] as $pts) {
        foreach ($pts as [$x, $y]) {
            imagefilledellipse($img, (int) round($x), (int) round($y), $e, $e, $blanc);
        }
    }

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

foreach ([32 => "favicon-32.png", 180 => "favicon-180.png"] as $taille => $nom) {
    $img = fabriquer($taille, $trace_v, $trace_s);
    imagepng($img, $sortie . "/" . $nom, 9);
    imagedestroy($img);
    printf("%-18s %d x %d   %d octets\n", $nom, $taille, $taille,
           filesize($sortie . "/" . $nom));
}
