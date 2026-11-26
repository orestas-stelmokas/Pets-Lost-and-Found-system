<?php

require_once __DIR__ . '/nustatymai.php';

// Neprisijungusio naudotojo netikriname
if (empty($_SESSION['user'])) {
    return;
}

// Svečio netikriname duomenų bazėje
if ($_SESSION['user'] === 'guest' &&
    (int)$_SESSION['ulevel'] === 0) {
    return;
}

$db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

$stmt = $db->prepare(
    "SELECT userlevel FROM users WHERE userid = ?"
);

$userid = (string)($_SESSION['userid'] ?? '');

$stmt->bind_param('s', $userid);
$stmt->execute();

$naudotojas = $stmt->get_result()->fetch_assoc();

$stmt->close();
$db->close();

// Paskyra pašalinta, užblokuota arba netinkama rolė
if (!$naudotojas ||
    !in_array((int)$naudotojas['userlevel'], [4, 5, 9], true)) {

    $_SESSION = [];
    session_regenerate_id(true);

    $_SESSION['prev'] = 'blocked';
    $_SESSION['message'] = 'Jūsų paskyra neaktyvi arba užblokuota.';

    header('Location: /home');
    exit;
}

// Atnaujiname rolę pagal duomenų bazę
$_SESSION['ulevel'] = (int)$naudotojas['userlevel'];