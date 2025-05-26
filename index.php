<?php
// index.php

session_start();

header('Content-Type: text/html; charset=utf-8');

// Einstellungen für Fehlerberichterstattung
ini_set('display_errors', 1);
error_reporting(E_ALL);

$cookieName = 'session_code';

// Wenn kein Code in der Session vorhanden ist, versuchen wir ihn aus dem Cookie zu laden
if (!isset($_SESSION['code'])) {
        if (isset($_COOKIE[$cookieName]) && preg_match('/^[A-Z0-9]{6}$/', $_COOKIE[$cookieName])) {
                $_SESSION['code'] = $_COOKIE[$cookieName];
        } else {
                // Generieren eines 6-stelligen alphanumerischen Codes
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $generated = '';
                for ($i = 0; $i < 6; $i++) {
                        $generated .= $characters[rand(0, strlen($characters) - 1)];
                }
                $_SESSION['code'] = $generated;
                setcookie($cookieName, $generated, time() + 60*60*24*30, '/');
        }
}

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
<div class="container">
    <div class="form-container">
        <h2>Karte <?php echo $cardIndex + 1; ?> von <?php echo count($cards); ?></h2>
        <form action="process.php" method="post" enctype="multipart/form-data" target="hiddenFrame">
            <input type="hidden" name="card_index" value="<?php echo $cardIndex; ?>">
            <div class="form-grid">
                <div class="field">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo isset($currentCard['name']) ? htmlspecialchars($currentCard['name']) : ''; ?>">
                </div>

                <div class="field">
                    <label for="seltenheitsform">Seltenheitsform</label>
                    <select id="seltenheitsform" name="seltenheitsform">
                        <?php
                        $seltenheitsformen = [ ' ','Gewöhnlich', 'Ungewöhnlich', 'Episch', 'Heroisch', 'Legendär'];
                        foreach ($seltenheitsformen as $form) {
                            $selected = (isset($currentCard['seltenheitsform']) && $currentCard['seltenheitsform'] == $form) ? 'selected' : '';
                            echo "<option value=\"$form\" $selected>$form</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="field">
                    <label for="kosten">Kosten</label>
                    <input type="text" id="kosten" name="kosten" value="<?php echo isset($currentCard['kosten']) ? htmlspecialchars($currentCard['kosten']) : ''; ?>" size="5">
                </div>

                <div class="field">
                    <label for="reichweite">Reichweite</label>
                    <select id="reichweite" name="reichweite">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            $selected = (isset($currentCard['reichweite']) && $currentCard['reichweite'] == $i) ? 'selected' : '';
                            echo "<option value=\"$i\" $selected>$i</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="field">
                    <label for="leben">Leben</label>
                    <input type="text" id="leben" name="leben" value="<?php echo isset($currentCard['leben']) ? htmlspecialchars($currentCard['leben']) : ''; ?>">
                </div>

                <div class="field">
                    <label for="superangriff">Superangriff</label>
                    <input type="text" id="superangriff" name="superangriff" value="<?php echo isset($currentCard['superangriff']) ? htmlspecialchars($currentCard['superangriff']) : ''; ?>">
                </div>

                <div class="field">
                    <label for="schaden">Schaden</label>
                    <input type="text" id="schaden" name="schaden" value="<?php echo isset($currentCard['schaden']) ? htmlspecialchars($currentCard['schaden']) : ''; ?>">
                </div>

                <div class="field full-width">
                    <label for="bild">Bild</label>
                    <input type="file" id="bild" name="bild" accept="image/*">
                </div>

                <div class="field full-width">
                    <label>Modus</label>
                    <div>
                        Vorschau: <input type="radio" name="mode" value="0" checked>
                        Download: <input type="radio" name="mode" value="1">
                    </div>
                </div>

                <div class="field full-width">
                    <input type="submit" value="Speichern & Generieren">
                </div>
            </div>
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
<p class="back-link"><a href="welcome.php">Zurück zur Startseite</a></p>

<!-- Unsichtbares iframe zum Verarbeiten des Formulars -->
<iframe name="hiddenFrame" style="display:none;"></iframe>
</body>
</html>
