<?php
// Gibt #fff oder #111 für gute Lesbarkeit auf beliebigem Hintergrund zurück
function getContrastTextColor($hexColor) {
    $hexColor = ltrim($hexColor, '#');
    if (strlen($hexColor) === 3) {
        $hexColor = $hexColor[0].$hexColor[0].$hexColor[1].$hexColor[1].$hexColor[2].$hexColor[2];
    }
    $r = hexdec(substr($hexColor, 0, 2));
    $g = hexdec(substr($hexColor, 2, 2));
    $b = hexdec(substr($hexColor, 4, 2));
    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    return $luminance > 0.5 ? '#111' : '#fff';
}
