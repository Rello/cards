<?php
// display.php

session_start();

// Prüfen, ob Daten vorhanden sind
if (!isset($_SESSION['cards']) || empty($_SESSION['cards'])) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<p class="error">Keine Karte generiert. '; 
        echo '<a href="index.php">Zum Generator</a></p>';
        exit();
}

$cards = $_SESSION['cards'];
$cardIndex = $_SESSION['card_index'];
$code = $_SESSION['code'];
$userDir = __DIR__ . '/sessions/' . $code;

// Gewählten Index aus GET oder aktuellen Index verwenden
$index = isset($_GET['index']) ? intval($_GET['index']) : $cardIndex;

// Aktuelle Karte laden
if (isset($cards[$index])) {
        $currentCard = $cards[$index];

        // Bild generieren
        include 'generate_image.php';
        $imageData = generateImage($currentCard, $userDir);

        if (isset($_GET['thumb'])) {
                $img = imagecreatefromstring($imageData);
                if ($img) {
                        $thumb = imagescale($img, 150);
                        ob_start();
                        imagepng($thumb);
                        $imageData = ob_get_clean();
                        imagedestroy($thumb);
                        imagedestroy($img);
                }
        }

        // Bild ausgeben
        header('Content-Type: image/png');
        echo $imageData;
} else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<p class="error">Karte nicht gefunden. ';
        echo '<a href="index.php">Zurück</a></p>';
}
