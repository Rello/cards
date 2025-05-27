<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['cards'], $_SESSION['code'])) {
    header('Location: index.php');
    exit();
}

$index = isset($_POST['card_index']) ? intval($_POST['card_index']) : -1;
$cards = $_SESSION['cards'];
$code = $_SESSION['code'];
$userDir = __DIR__ . '/sessions/' . $code;
$currentIndex = isset($_SESSION['card_index']) ? $_SESSION['card_index'] : 0;

if ($index >= 0 && isset($cards[$index])) {
    // remove image asset if exists
    if (!empty($cards[$index]['bild'])) {
        $filePath = $userDir . '/' . $cards[$index]['bild'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    // remove card from array
    array_splice($cards, $index, 1);

    // save updated list
    if (!empty($cards)) {
        file_put_contents($userDir . '/cards.json', json_encode($cards, JSON_UNESCAPED_UNICODE));
    } else {
        // remove file if no cards left
        if (file_exists($userDir . '/cards.json')) {
            unlink($userDir . '/cards.json');
        }
    }

    $_SESSION['cards'] = $cards;
    if ($currentIndex > $index) {
        $currentIndex--;
    } elseif ($currentIndex >= count($cards)) {
        $currentIndex = count($cards) - 1;
    }
    $_SESSION['card_index'] = max($currentIndex, 0);
}

header('Location: index.php');
exit();
?>
