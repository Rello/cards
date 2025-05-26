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

// Aktuelle Karte laden
if (isset($cards[$cardIndex])) {
	$currentCard = $cards[$cardIndex];

	// Bild generieren
	include 'generate_image.php';
	$imageData = generateImage($currentCard, $userDir);

	// Bild ausgeben
	header('Content-Type: image/png');
	echo $imageData;
} else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<p class="error">Karte nicht gefunden. ';
        echo '<a href="index.php">Zurück</a></p>';
}
