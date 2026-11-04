<?php
session_start();

if (empty($_SESSION['user']) ||
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
$perziura = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($duomenys as $laukas => $reiksme) {
        $ivestis = $_POST[$laukas] ?? '';
        $duomenys[$laukas] = is_string($ivestis) ? trim($ivestis) : '';
    }

    if (!in_array($duomenys['tipas'], ['dinges', 'rastas'], true)) {
        $klaidos[] = "Pasirinkite skelbimo tipą.";
    }

    if (!in_array($duomenys['lytis'], ['patinas', 'patele', 'nezinoma'], true)) {
        $klaidos[] = "Pasirinkite gyvūno lytį.";
    }

    foreach (['pavadinimas', 'rusis', 'vieta', 'aprasymas'] as $laukas) {
        if ($duomenys[$laukas] === '') {
            $klaidos[] = "Užpildykite visus privalomus laukus.";
            break;
        }
    }

    if ($duomenys['amzius'] !== '' &&
        (!ctype_digit($duomenys['amzius']) ||
         (int)$duomenys['amzius'] > 360)) {
        $klaidos[] = "Amžius turi būti nuo 0 iki 360 mėnesių.";
    }

    if ($duomenys['galioja_iki'] !== '') {
        $data = DateTime::createFromFormat('!Y-m-d', $duomenys['galioja_iki']);

        if (!$data ||
            $data->format('Y-m-d') !== $duomenys['galioja_iki'] ||
            $duomenys['galioja_iki'] < date('Y-m-d')) {
            $klaidos[] = "Nurodykite galiojančią pabaigos datą.";
        }
    }

    $perziura = empty($klaidos);
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

        <button type="submit">Peržiūrėti įvestus duomenis</button>
    </form>

    <?php if ($perziura): ?>
        <hr>
        <h2>Skelbimo peržiūra</h2>
        <p><b>Duomenys patikrinti, bet dar neišsaugoti.</b></p>

        <?php foreach ($duomenys as $laukas => $reiksme): ?>
            <p>
                <b><?= h(ucfirst(str_replace('_', ' ', $laukas))) ?>:</b>
                <?= h($reiksme !== '' ? $reiksme : 'Nenurodyta') ?>
            </p>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>