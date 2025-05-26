<?php
// index.php

session_start();

// Einstellungen für Fehlerberichterstattung
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Variable, um zu erkennen, ob ein neuer Code verwendet wurde
$newCodeUsed = false;

// Überprüfen, ob ein Code eingegeben wurde
if (isset($_POST['code'])) {
	$code = strtoupper(trim($_POST['code']));
	if (preg_match('/^[A-Z0-9]{6}$/', $code)) {
		$_SESSION['code'] = $code;
		$newCodeUsed = true; // Flag setzen, um später das iframe zu aktualisieren

		// Kartenindex zurücksetzen
		$_SESSION['card_index'] = 0;
		// Session-Karten zurücksetzen
		unset($_SESSION['cards']);
	} else {
		$error = "Ungültiger Code. Bitte geben Sie einen 6-stelligen alphanumerischen Code ein.";
	}
}

// Wenn kein Code in der Session vorhanden ist, generieren wir einen neuen
if (!isset($_SESSION['code'])) {
	// Generieren eines 6-stelligen alphanumerischen Codes
	$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$code = '';
	for ($i = 0; $i < 6; $i++) {
		$code .= $characters[rand(0, strlen($characters) - 1)];
	}
	$_SESSION['code'] = $code;
}

// Aktueller Code
$code = $_SESSION['code'];

// Pfad zum Benutzerordner
$userDir = __DIR__ . '/sessions/' . $code;

// Wenn der Benutzerordner nicht existiert, erstellen wir ihn
if (!file_exists($userDir)) {
	mkdir($userDir, 0777, true);
}

// Laden der vorhandenen Karten
$cards = [];
$cardIndex = 0;

// Prüfen, ob Karten bereits in der Session geladen sind
if (isset($_SESSION['cards'])) {
	$cards = $_SESSION['cards'];
	$cardIndex = isset($_SESSION['card_index']) ? $_SESSION['card_index'] : 0;
} else {
	// Karten aus der Datei laden
	if (file_exists($userDir . '/cards.json')) {
		$cards = json_decode(file_get_contents($userDir . '/cards.json'), true);
		$_SESSION['cards'] = $cards;
		$cardIndex = isset($_SESSION['card_index']) ? $_SESSION['card_index'] : 0;
	} else {
		// Neue Kartenliste erstellen
		$cards = [];
		$cardIndex = 0;
		$_SESSION['card_index'] = $cardIndex;
	}
}

// Aktuelle Karte
$currentCard = isset($cards[$cardIndex]) ? $cards[$cardIndex] : null;

// Fehlermeldungen
$error = isset($error) ? $error : '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Karten-Generator</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function updateCardIndex(index) {
            var form = document.createElement('form');
            form.method = 'post';
            form.action = 'update_card.php';

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'card_index';
            input.value = index;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }

        function refreshIframe() {
            document.getElementById('imageFrame').src = 'display.php?' + new Date().getTime();
        }
    </script>
</head>
<body>
<h1>Karten-Generator</h1>
<p>Ihr Session-Code: <strong><?php echo $code; ?></strong></p>
<form method="post" action="index.php">
    <label for="code">Vorhandenen Code eingeben:</label>
    <input type="text" name="code" id="code" maxlength="6" placeholder="6-stelliger Code">
    <input type="submit" value="Code verwenden">
</form>
<?php if ($error): ?>
    <p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<div class="container">
    <div class="form-container">
        <h2>Karte <?php echo $cardIndex + 1; ?> von <?php echo count($cards); ?></h2>
        <form action="process.php" method="post" enctype="multipart/form-data" target="hiddenFrame">
            <input type="hidden" name="card_index" value="<?php echo $cardIndex; ?>">
            <!-- Formularfelder -->
            Name:<br>
            <input type="text" name="name" value="<?php echo isset($currentCard['name']) ? htmlspecialchars($currentCard['name']) : ''; ?>" size="15"><br><br>

            Seltenheitsform:<br>
            <select name="seltenheitsform">
				<?php
				$seltenheitsformen = [ ' ','Gewöhnlich', 'Ungewöhnlich', 'Episch', 'Heroisch', 'Legendär'];
				foreach ($seltenheitsformen as $form) {
					$selected = (isset($currentCard['seltenheitsform']) && $currentCard['seltenheitsform'] == $form) ? 'selected' : '';
					echo "<option value=\"$form\" $selected>$form</option>";
				}
				?>
            </select><br><br>

            Kosten:<br>
            <input type="text" name="kosten" value="<?php echo isset($currentCard['kosten']) ? htmlspecialchars($currentCard['kosten']) : ''; ?>" size="5"> Juwelen<br><br>

            Reichweite:<br>
            <select name="reichweite">
				<?php
				for ($i = 1; $i <= 5; $i++) {
					$selected = (isset($currentCard['reichweite']) && $currentCard['reichweite'] == $i) ? 'selected' : '';
					echo "<option value=\"$i\" $selected>$i</option>";
				}
				?>
            </select><br><br>

            Leben:<br>
            <input type="text" name="leben" value="<?php echo isset($currentCard['leben']) ? htmlspecialchars($currentCard['leben']) : ''; ?>" size="15"><br><br>

            Superangriff:<br>
            <input type="text" name="superangriff" value="<?php echo isset($currentCard['superangriff']) ? htmlspecialchars($currentCard['superangriff']) : ''; ?>" size="15"><br><br>

            Schaden:<br>
            <input type="text" name="schaden" value="<?php echo isset($currentCard['schaden']) ? htmlspecialchars($currentCard['schaden']) : ''; ?>" size="15"><br><br>

            Bild:<br>
            <input type="file" name="bild" accept="image/*"><br><br>

            Modus:<br>
            Vorschau: <input type="radio" name="mode" value="0" checked><br>
            Download: <input type="radio" name="mode" value="1"><br><br>

            <input type="submit" value="Speichern & Generieren">
        </form>

        <div class="navigation">
            <button onclick="updateCardIndex(<?php echo max(0, $cardIndex - 1); ?>)" <?php if ($cardIndex == 0) echo 'disabled'; ?>>&laquo; Vorherige Karte</button>
            <button onclick="updateCardIndex(<?php echo min(count($cards) - 1, $cardIndex + 1); ?>)" <?php if ($cardIndex >= count($cards) - 1) echo 'disabled'; ?>>Nächste Karte &raquo;</button>
            <button onclick="updateCardIndex(-1)">Neue Karte</button>
        </div>
    </div>
    <div class="image-container">
        <h2>Generierte Karte:</h2>
        <iframe id="imageFrame" src="display.php" frameborder="0" width="100%" height="650px"></iframe>
    </div>
</div>

<!-- Unsichtbares iframe zum Verarbeiten des Formulars -->
<iframe name="hiddenFrame" style="display:none;"></iframe>

<?php if ($newCodeUsed): ?>
    <script>
        // Aktualisiere das iframe mit der generierten Karte
        refreshIframe();
    </script>
<?php endif; ?>
</body>
</html>
