<?php
// process.php

session_start();

// Einstellungen für Fehlerberichterstattung
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Pfad zum Benutzerordner
$code = $_SESSION['code'];
$userDir = __DIR__ . '/sessions/' . $code;

// Laden der vorhandenen Karten
$cards = [];
if (file_exists($userDir . '/cards.json')) {
	$cards = json_decode(file_get_contents($userDir . '/cards.json'), true);
}

// Eingabedaten erfassen
$cardIndex = isset($_POST['card_index']) ? intval($_POST['card_index']) : 0;
$name = $_POST['name'];
$seltenheitsform = $_POST['seltenheitsform'];
$kosten = $_POST['kosten'];
$reichweite = $_POST['reichweite'];
$leben = $_POST['leben'];
$superangriff = $_POST['superangriff'];
$schaden = $_POST['schaden'];
$mode = $_POST['mode'];

// Bild hochladen und speichern
$bildName = '';
if (isset($_FILES['bild']) && $_FILES['bild']['error'] == 0) {
	// Überprüfen des Dateityps
	$fileInfo = getimagesize($_FILES['bild']['tmp_name']);
	$mimeType = $fileInfo['mime'];

	if ($mimeType == 'image/png') {
		$extension = '.png';
	} elseif ($mimeType == 'image/jpeg') {
		$extension = '.jpg';
	} elseif ($mimeType == 'image/webp') {
		$extension = '.webp';
	} else {
		die('Ungültiger Bildtyp. Bitte laden Sie ein PNG-, JPG- oder WebP-Bild hoch.');
	}

	$bildName = 'bild_' . $cardIndex . $extension;
	move_uploaded_file($_FILES['bild']['tmp_name'], $userDir . '/' . $bildName);
} elseif (isset($cards[$cardIndex]['bild'])) {
	// Wenn kein neues Bild hochgeladen wurde, verwenden wir das alte
	$bildName = $cards[$cardIndex]['bild'];
}

// Aktuelle Karte aktualisieren oder hinzufügen
$cards[$cardIndex] = [
	'name' => $name,
	'seltenheitsform' => $seltenheitsform,
	'kosten' => $kosten,
	'reichweite' => $reichweite,
	'leben' => $leben,
	'superangriff' => $superangriff,
	'schaden' => $schaden,
	'bild' => $bildName,
];

// Kartenliste speichern
file_put_contents($userDir . '/cards.json', json_encode($cards));

// Speichern der Kartenliste in der Session
$_SESSION['cards'] = $cards;
$_SESSION['card_index'] = $cardIndex;

// Download-Modus
if ($mode == '1') {
	// Bild generieren
	include 'generate_image.php';
	$imageData = generateImage($cards[$cardIndex], $userDir);
	// Header setzen und Bild ausgeben
	header('Content-Type: image/png');
	header('Content-Disposition: attachment; filename="karte.png"');
	echo $imageData;
	exit();
}

// Wenn nicht im Download-Modus, aktualisiere das iframe
// Header setzen, um dem Browser mitzuteilen, dass wir HTML ausgeben
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verarbeitung abgeschlossen</title>
    <script>
        // Aktualisiere das iframe mit der generierten Karte
        if (parent && parent.document) {
            parent.document.getElementById('imageFrame').src = 'display.php?' + new Date().getTime();
        }
    </script>
</head>
<body>
</body>
</html>
