<?php
session_start();
require_once("include/nustatymai.php");

if (empty($_SESSION['user']) ||
    !in_array((int)($_SESSION['ulevel'] ?? -1), [0,4,5,9], true)) {
    header("Location: index.php");
    exit;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$skelbimai = [];
$dbKlaida = false;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset("utf8mb4");

    $sql = "SELECT s.*, u.username AS autorius
            FROM skelbimas s
            LEFT JOIN users u ON s.autorius_id = u.userid
            WHERE s.busena = 'aktyvus'
              AND (s.galioja_iki IS NULL
                   OR s.galioja_iki >= CURDATE())
            ORDER BY s.sukurtas_at DESC, s.skelbimas_id DESC";

    $result = $db->query($sql);

    while ($row = $result->fetch_assoc()) {
        $skelbimai[] = $row;
    }

    $db->close();

} catch (mysqli_sql_exception $e) {
    error_log("Skelbimų nuskaitymo klaida: " . $e->getMessage());
    $dbKlaida = true;
}
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Gyvūnų skelbimai</title>
    <link href="include/styles.css" rel="stylesheet">
</head>
<body>

    <h1>Dingusių ir rastų gyvūnų skelbimai</h1>

    <?php include("include/meniu.php"); ?>

    <p><a href="index.php">Grįžti į pradžią</a></p>

    <?php if (!empty($_SESSION['skelbimas_sekme'])): ?>
        <p style="color:green">
            <?= h($_SESSION['skelbimas_sekme']) ?>
        </p>
        <?php unset($_SESSION['skelbimas_sekme']); ?>
    <?php endif; ?>

    <?php if ($dbKlaida): ?>

        <p style="color:red">
            Nepavyko nuskaityti skelbimų iš duomenų bazės.
        </p>

    <?php elseif (empty($skelbimai)): ?>

        <p>Šiuo metu aktyvių skelbimų nėra.</p>

    <?php else: ?>

        <h2>Aktyvūs skelbimai</h2>

        <table border="1" cellpadding="8"
               style="margin:20px auto;border-collapse:collapse">

            <tr>
                <th>Tipas</th>
                <th>Pavadinimas</th>
                <th>Rūšis</th>
                <th>Lytis</th>
                <th>Amžius</th>
                <th>Vieta</th>
                <th>Aprašymas</th>
                <th>Autorius</th>
                <th>Galioja iki</th>
            </tr>

            <?php foreach ($skelbimai as $s): ?>
                <tr>
                    <td>
                        <?= $s['tipas'] === 'dinges'
                            ? 'Dingo' : 'Rastas' ?>
                    </td>

                    <td><?= h($s['pavadinimas']) ?></td>
                    <td><?= h($s['rusis']) ?></td>
                    <td><?= h($s['lytis']) ?></td>

                    <td>
                        <?= $s['amzius_men'] === null
                            ? 'Nenurodyta'
                            : h($s['amzius_men']) . ' mėn.' ?>
                    </td>

                    <td><?= h($s['vieta']) ?></td>
                    <td><?= nl2br(h($s['aprasymas'])) ?></td>
                    <td><?= h($s['autorius'] ?? 'Nežinomas') ?></td>

                    <td>
                        <?= h($s['galioja_iki'] ?? 'Nenurodyta') ?>
                    </td>
                </tr>
            <?php endforeach; ?>

        </table>

    <?php endif; ?>

</body>
</html>