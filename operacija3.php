<?php
session_start();
require_once 'include/nustatymai.php';

if (empty($_SESSION['user']) ||
    empty($_SESSION['userid']) ||
    !in_array((int)($_SESSION['ulevel'] ?? -1), [5, 9], true)) {
    header('Location: index.php');
    exit;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
$db->set_charset('utf8mb4');

// Patikriname rolę ir duomenų bazėje
$manoId = (string)$_SESSION['userid'];

$stmt = $db->prepare(
    "SELECT userlevel FROM users WHERE userid = ? LIMIT 1"
);
$stmt->bind_param('s', $manoId);
$stmt->execute();
$naudotojas = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$naudotojas ||
    !in_array((int)$naudotojas['userlevel'], [5, 9], true)) {
    http_response_code(403);
    exit('Neturite teisės valdyti skelbimų.');
}

// Apsaugos kodas formoms
if (empty($_SESSION['kontroles_token'])) {
    $_SESSION['kontroles_token'] = bin2hex(random_bytes(32));
}

// Skelbimo pašalinimas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['kontroles_token'] ?? '';
    $id = filter_var(
        $_POST['skelbimas_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if (!is_string($token) ||
        !hash_equals($_SESSION['kontroles_token'], $token) ||
        !$id) {
        http_response_code(400);
        exit('Neteisinga užklausa.');
    }

    $stmt = $db->prepare(
        "UPDATE skelbimas
         SET busena = 'pasalintas'
         WHERE skelbimas_id = ? AND busena = 'aktyvus'"
    );

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $pakeista = $stmt->affected_rows;
    $stmt->close();

    $_SESSION['kontroles_pranesimas'] = $pakeista
        ? 'Skelbimas sėkmingai pašalintas.'
        : 'Skelbimas nerastas arba jau neaktyvus.';

    header('Location: operacija3.php');
    exit;
}

// Skelbimų ir peržiūrų informacija
$sql = "SELECT
            s.skelbimas_id,
            s.pavadinimas,
            s.tipas,
            s.rusis,
            s.vieta,
            s.busena,
            s.galioja_iki,
            s.sukurtas_at,
            u.username AS autorius,
            (
                SELECT COUNT(*)
                FROM perziura p
                WHERE p.skelbimas_id = s.skelbimas_id
            ) AS perziuru_skaicius
        FROM skelbimas s
        LEFT JOIN users u ON s.autorius_id = u.userid
        ORDER BY s.sukurtas_at DESC, s.skelbimas_id DESC";

$result = $db->query($sql);
$skelbimai = $result->fetch_all(MYSQLI_ASSOC);

$db->close();
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Skelbimų kontrolė</title>
    <link href="include/styles.css" rel="stylesheet">
</head>
<body>

<h1>Skelbimų kontrolė</h1>

<?php include 'include/meniu.php'; ?>

<p><a href="index.php">Grįžti į pradžią</a></p>

<?php if (!empty($_SESSION['kontroles_pranesimas'])): ?>
    <p style="color:green">
        <?= h($_SESSION['kontroles_pranesimas']) ?>
    </p>
    <?php unset($_SESSION['kontroles_pranesimas']); ?>
<?php endif; ?>

<h2>Visi gyvūnų skelbimai</h2>

<?php if (empty($skelbimai)): ?>

    <p>Skelbimų nėra.</p>

<?php else: ?>

    <table class="center" border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Pavadinimas</th>
            <th>Tipas</th>
            <th>Rūšis</th>
            <th>Vieta</th>
            <th>Autorius</th>
            <th>Peržiūros</th>
            <th>Būsena</th>
            <th>Veiksmas</th>
        </tr>

        <?php foreach ($skelbimai as $s): ?>
            <tr>
                <td><?= (int)$s['skelbimas_id'] ?></td>
                <td><?= h($s['pavadinimas']) ?></td>

                <td>
                    <?= $s['tipas'] === 'dinges'
                        ? 'Dingęs' : 'Rastas' ?>
                </td>

                <td><?= h($s['rusis']) ?></td>
                <td><?= h($s['vieta']) ?></td>
                <td><?= h($s['autorius'] ?? 'Nežinomas') ?></td>

                <td>
                    <?= (int)$s['perziuru_skaicius'] ?>
                </td>

                <td>
                    <?php
                    if ($s['busena'] === 'aktyvus' &&
                        $s['galioja_iki'] !== null &&
                        $s['galioja_iki'] < date('Y-m-d')) {
                        echo 'Pasibaigęs galiojimas';
                    } else {
                        echo h($s['busena']);
                    }
                    ?>
                </td>

                <td>
                    <?php if ($s['busena'] === 'aktyvus'): ?>

                        <form method="post">
                            <input type="hidden"
                                   name="kontroles_token"
                                   value="<?= h($_SESSION['kontroles_token']) ?>">

                            <input type="hidden"
                                   name="skelbimas_id"
                                   value="<?= (int)$s['skelbimas_id'] ?>">

                            <button type="submit"
                                    onclick="return confirm('Ar tikrai pašalinti skelbimą?')">
                                Pašalinti
                            </button>
                        </form>

                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php endif; ?>

<p>
    <small>
        Pašalinti skelbimai lieka duomenų bazėje,
        bet nebėra rodomi viešame skelbimų sąraše.
    </small>
</p>

</body>
</html>