<?php
session_start();
require_once("include/nustatymai.php");
date_default_timezone_set("Europe/Vilnius");

if (empty($_SESSION['user']) ||
    empty($_SESSION['userid']) ||
    !in_array((int)($_SESSION['ulevel'] ?? -1), [4,5,9], true)) {
    header("Location: index.php");
    exit;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$duomenys = [
    'tipas' => '',
    'pavadinimas' => '',
    'rusis' => '',
    'lytis' => '',
    'amzius' => '',
    'vieta' => '',
    'aprasymas' => '',
    'galioja_iki' => ''
];

$klaidos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Paimame formos duomenis
    foreach ($duomenys as $laukas => $reiksme) {
        $ivestis = $_POST[$laukas] ?? '';
        $duomenys[$laukas] = is_string($ivestis)
            ? trim($ivestis) : '';
    }

    // Patikriname įvestus duomenis
    if (!in_array($duomenys['tipas'], ['dinges', 'rastas'], true)) {
        $klaidos[] = "Pasirinkite skelbimo tipą.";
    }

    if (!in_array($duomenys['lytis'],
        ['patinas', 'patele', 'nezinoma'], true)) {
        $klaidos[] = "Pasirinkite gyvūno lytį.";
    }

    foreach (['pavadinimas', 'rusis', 'vieta', 'aprasymas'] as $laukas) {
        if ($duomenys[$laukas] === '') {
            $klaidos[] = "Užpildykite visus privalomus laukus.";
            break;
        }
    }

    $ribos = [
        'pavadinimas' => 120,
        'rusis' => 60,
        'vieta' => 150
    ];

    foreach ($ribos as $laukas => $maksimumas) {
        if (preg_match('/^.{0,' . $maksimumas . '}$/us',
            $duomenys[$laukas]) !== 1) {
            $klaidos[] = "Laukas „$laukas“ per ilgas.";
        }
    }

    if (strlen($duomenys['aprasymas']) > 4000) {
        $klaidos[] = "Aprašymas per ilgas.";
    }

    if ($duomenys['amzius'] !== '' &&
        (!ctype_digit($duomenys['amzius']) ||
         (int)$duomenys['amzius'] > 360)) {
        $klaidos[] = "Amžius turi būti nuo 0 iki 360 mėnesių.";
    }

    if ($duomenys['galioja_iki'] !== '') {
        $data = DateTime::createFromFormat(
            '!Y-m-d', $duomenys['galioja_iki']
        );

        if (!$data ||
            $data->format('Y-m-d') !== $duomenys['galioja_iki'] ||
            $duomenys['galioja_iki'] < date('Y-m-d')) {
            $klaidos[] = "Nurodykite tinkamą galiojimo datą.";
        }
    }

    // Jei klaidų nėra, įrašome į MySQL
    if (empty($klaidos)) {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $db = new mysqli(
                DB_SERVER, DB_USER, DB_PASS, DB_NAME
            );
            $db->set_charset("utf8mb4");

            $sql = "INSERT INTO skelbimas
                (autorius_id, tipas, pavadinimas, rusis,
                 lytis, amzius_men, vieta, aprasymas, galioja_iki)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($sql);

            $autoriusId = (string)$_SESSION['userid'];
            $tipas = $duomenys['tipas'];
            $pavadinimas = $duomenys['pavadinimas'];
            $rusis = $duomenys['rusis'];
            $lytis = $duomenys['lytis'];
            $amzius = $duomenys['amzius'] === ''
                ? null : (int)$duomenys['amzius'];
            $vieta = $duomenys['vieta'];
            $aprasymas = $duomenys['aprasymas'];
            $galiojaIki = $duomenys['galioja_iki'] === ''
                ? null : $duomenys['galioja_iki'];

            $stmt->bind_param(
                "sssssisss",
                $autoriusId,
                $tipas,
                $pavadinimas,
                $rusis,
                $lytis,
                $amzius,
                $vieta,
                $aprasymas,
                $galiojaIki
            );

            $stmt->execute();
            $stmt->close();
            $db->close();

            $_SESSION['skelbimas_sekme'] =
                "Skelbimas sėkmingai išsaugotas!";

            header("Location: operacija1.php");
            exit;

        } catch (mysqli_sql_exception $e) {
            error_log("Skelbimo įrašymo klaida: " . $e->getMessage());
            $klaidos[] = "Nepavyko išsaugoti skelbimo. Bandykite dar kartą.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Naujas skelbimas</title>
    <link href="include/styles.css" rel="stylesheet">
</head>
<body>
    <h1>Naujo gyvūno skelbimo įvedimas</h1>

    <?php include("include/meniu.php"); ?>

    <p><a href="index.php">Grįžti į pradžią</a></p>

    <?php foreach ($klaidos as $klaida): ?>
        <p style="color:red"><?= h($klaida) ?></p>
    <?php endforeach; ?>

    <form method="post" style="display:inline-block;text-align:left">
        <p>
            Skelbimo tipas:<br>
            <select name="tipas" required>
                <option value="">Pasirinkite</option>
                <option value="dinges" <?= $duomenys['tipas'] === 'dinges' ? 'selected' : '' ?>>
                    Dingo gyvūnas
                </option>
                <option value="rastas" <?= $duomenys['tipas'] === 'rastas' ? 'selected' : '' ?>>
                    Rastas gyvūnas
                </option>
            </select>
        </p>

        <p>
            Pavadinimas:<br>
            <input name="pavadinimas" required
                   value="<?= h($duomenys['pavadinimas']) ?>">
        </p>

        <p>
            Gyvūno rūšis:<br>
            <input name="rusis" required
                   placeholder="Pvz., šuo, katė"
                   value="<?= h($duomenys['rusis']) ?>">
        </p>

        <p>
            Lytis:<br>
            <select name="lytis" required>
                <option value="">Pasirinkite</option>
                <?php foreach (['patinas' => 'Patinas',
                                'patele' => 'Patelė',
                                'nezinoma' => 'Nežinoma'] as $kodas => $pavadinimas): ?>
                    <option value="<?= $kodas ?>"
                        <?= $duomenys['lytis'] === $kodas ? 'selected' : '' ?>>
                        <?= $pavadinimas ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            Amžius (mėnesiais, jei žinomas):<br>
            <input type="number" name="amzius" min="0" max="360"
                   value="<?= h($duomenys['amzius']) ?>">
        </p>

        <p>
            Dingimo / radimo vieta:<br>
            <input name="vieta" required
                   value="<?= h($duomenys['vieta']) ?>">
        </p>

        <p>
            Aprašymas:<br>
            <textarea name="aprasymas" rows="5" cols="40"
                      required><?= h($duomenys['aprasymas']) ?></textarea>
        </p>

        <p>
            Skelbimo galiojimo pabaiga (neprivaloma):<br>
            <input type="date" name="galioja_iki"
                   min="<?= date('Y-m-d') ?>"
                   value="<?= h($duomenys['galioja_iki']) ?>">
        </p>

        <button type="submit">Išsaugoti skelbimą</button>
    </form>
</body>
</html>