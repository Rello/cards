<?php
// display.php

session_start();

// Prüfen, ob Daten vorhanden sind
if (!isset($_SESSION['cards'])) {
	echo "Keine Karte generiert.";
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
	echo "Karte nicht gefunden.";
}
