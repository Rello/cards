<?php
// update_card.php

session_start();

// Einstellungen für Fehlerberichterstattung
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['card_index'])) {
	$cardIndex = intval($_POST['card_index']);

	// Speichern des Kartenindex in der Session
	if ($cardIndex >= 0) {
		$_SESSION['card_index'] = $cardIndex;
	} else {
		// Neue Karte erstellen
		$_SESSION['card_index'] = count($_SESSION['cards']);
	}
}

// Zurück zur index.php
header('Location: index.php');
exit();
