<?php

session_start();
require_once 'include/auth.php';
require_once 'include/nustatymai.php';

if (empty($_SESSION['userid']) ||
    !in_array((int)($_SESSION['ulevel'] ?? -1), [4,5,9], true)) {
    header('Location: index.php');
    exit;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
$db->set_charset('utf8mb4');

$manoId = (string)$_SESSION['userid'];

if (empty($_SESSION['mano_skelbimu_token'])) {
    $_SESSION['mano_skelbimu_token'] = bin2hex(random_bytes(32));
}

// Skelbimo šalinimas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['token'] ?? '';

    $id = filter_input(
        INPUT_POST,
        'skelbimas_id',
        FILTER_VALIDATE_INT
    );

    if (!is_string($token) ||
        !hash_equals($_SESSION['mano_skelbimu_token'], $token) ||
        !$id || $id < 1) {

        http_response_code(400);
        exit('Neteisinga užklausa.');
    }

    $stmt = $db->prepare(
        "UPDATE skelbimas
         SET busena = 'pasalintas'
         WHERE skelbimas_id = ?
           AND autorius_id = ?
           AND busena != 'pasalintas'"
    );

    $stmt->bind_param('is', $id, $manoId);
    $stmt->execute();

    $_SESSION['mano_skelbimu_pranesimas'] =
        $stmt->affected_rows > 0
        ? 'Skelbimas pašalintas.'
        : 'Skelbimo pašalinti nepavyko.';

    $stmt->close();

    header('Location: mano_skelbimai.php');
    exit;
}

// Gauname tik savo skelbimus
$stmt = $db->prepare(
    "SELECT *
     FROM skelbimas
     WHERE autorius_id = ?
     ORDER BY sukurtas_at DESC"
);

$stmt->bind_param('s', $manoId);
$stmt->execute();

$skelbimai = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$db->close();

?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Mano skelbimai</title>
    <link href="include/styles.css" rel="stylesheet">
</head>
<body>

<h1>Mano skelbimai</h1>

<?php include 'include/meniu.php'; ?>

<?php if (!empty($_SESSION['mano_skelbimu_pranesimas'])): ?>
    <p><?= h($_SESSION['mano_skelbimu_pranesimas']) ?></p>
    <?php unset($_SESSION['mano_skelbimu_pranesimas']); ?>
<?php endif; ?>

<?php if (empty($skelbimai)): ?>

    <p>Jūs dar neturite skelbimų.</p>

<?php else: ?>

    <table class="center" border="1" cellpadding="8">

        <tr>
            <th>Pavadinimas</th>
            <th>Tipas</th>
            <th>Rūšis</th>
            <th>Vieta</th>
            <th>Būsena</th>
            <th>Galioja iki</th>
            <th>Veiksmai</th>
        </tr>

        <?php foreach ($skelbimai as $s): ?>

            <tr>
                <td><?= h($s['pavadinimas']) ?></td>
                <td><?= h($s['tipas']) ?></td>
                <td><?= h($s['rusis']) ?></td>
                <td><?= h($s['vieta']) ?></td>
                <td><?= h($s['busena']) ?></td>
                <td><?= h($s['galioja_iki'] ?? 'Neribotai') ?></td>

                <td>
                    <?php if ($s['busena'] !== 'pasalintas'): ?>

                        <form method="post">

                            <input type="hidden"
                                   name="token"
                                   value="<?= h($_SESSION['mano_skelbimu_token']) ?>">

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

</body>
</html>