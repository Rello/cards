<?php
session_start();

header('Content-Type: text/html; charset=utf-8');

ini_set('display_errors', 1);
error_reporting(E_ALL);

$cookieName = 'session_code';

function generateCode() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

$code = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = strtoupper(trim($_POST['code'] ?? ''));
    if (!preg_match('/^[A-Z0-9]{6}$/', $input)) {
        $error = 'Ungültiger Code. Bitte geben Sie einen 6-stelligen alphanumerischen Code ein.';
        $code = $input;
    } else {
        $code = $input;
        $_SESSION['code'] = $code;
        setcookie($cookieName, $code, time() + 60*60*24*30, '/');
        // reset session data
        $_SESSION['card_index'] = 0;
        unset($_SESSION['cards']);
        header('Location: index.php');
        exit();
    }
} else {
    if (isset($_COOKIE[$cookieName]) && preg_match('/^[A-Z0-9]{6}$/', $_COOKIE[$cookieName])) {
        $code = $_COOKIE[$cookieName];
    } else {
        $code = generateCode();
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Willkommen</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Karten-Generator</h1>
    <div class="form-container">
        <p>Willkommen zum Karten-Generator. Hier kannst du individuelle Spielkarten erstellen.</p>
        <form method="post" action="welcome.php">
            <label for="code">Session-Code</label>
            <input type="text" name="code" id="code" maxlength="6" value="<?php echo htmlspecialchars($code); ?>">
            <input type="submit" value="Code verwenden">
        </form>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
