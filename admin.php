<?php
session_start();
require_once 'include/nustatymai.php';
require_once 'include/functions.php';

if (empty($_SESSION['user']) ||
    (int)($_SESSION['ulevel'] ?? -1) !== (int)$user_roles[ADMIN_LEVEL]) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['leidimu_token'])) {
    $_SESSION['leidimu_token'] = bin2hex(random_bytes(32));
}

// Skelbimų publikavimo leidimo keitimas
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['keisti_skelbimo_leidima'])) {

    $token = $_POST['leidimu_token'] ?? '';
    $userid = $_POST['userid'] ?? '';
    $reiksme = $_POST['gali_skelbti'] ?? '';

    if (!is_string($token) ||
        !hash_equals($_SESSION['leidimu_token'], $token) ||
        !is_string($userid) ||
        !preg_match('/^[a-f0-9]{32}$/i', $userid) ||
        !in_array($reiksme, ['0', '1'], true)) {

        http_response_code(400);
        exit('Neteisinga užklausa.');
    }

    $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

    $stmt = $db->prepare(
        'UPDATE users
         SET gali_skelbti = ?
         WHERE userid = ? AND userlevel IN (4,5,9)'
    );

    $leisti = (int)$reiksme;

    $stmt->bind_param('is', $leisti, $userid);
    $stmt->execute();
    $stmt->close();
    $db->close();

    $_SESSION['message'] = $leisti
        ? 'Leidimas skelbti suteiktas.'
        : 'Leidimas skelbti atšauktas.';

    header('Location: admin.php');
    exit;
}

$_SESSION['prev'] = 'admin';
date_default_timezone_set('Europe/Vilnius');

$db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

$result = $db->query(
    'SELECT userid, username, userlevel, email,
            timestamp, gali_skelbti
     FROM users
     ORDER BY userlevel DESC, username'
);

function h($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Naudotojų valdymas</title>
    <link href="include/styles.css" rel="stylesheet">
</head>
<body>

    <h1>Gyvūnų paieškos ir radimo sistema</h1>
    <h2>Naudotojų valdymas</h2>

    <p>
        <a href="index.php">Atgal į pradžią</a>

        <?php if ($uregister !== 'self'): ?>
            | <a href="register.php">Registruoti naują naudotoją</a>
        <?php endif; ?>
    </p>

    <?php if (!empty($_SESSION['message'])): ?>
        <p style="color:green">
            <?= h($_SESSION['message']) ?>
        </p>
        <?php $_SESSION['message'] = ''; ?>
    <?php endif; ?>

    <!-- Rolių keitimui ir šalinimui skirta forma -->
    <form id="vartotojai"
          action="procadmin.php"
          method="post"></form>

    <table class="center" border="1"
           cellpadding="8" cellspacing="0">

        <tr>
            <th>Naudotojo vardas</th>
            <th>Rolė</th>
            <th>El. paštas</th>
            <th>Paskutinis aktyvumas</th>
            <th>Skelbimų publikavimas</th>
            <th>Šalinti?</th>
        </tr>

        <?php while ($u = $result->fetch_assoc()): ?>
            <?php
                $user = $u['username'];
                $level = (int)$u['userlevel'];
                $leista = (int)$u['gali_skelbti'] === 1;
            ?>

            <tr>
                <td><?= h($user) ?></td>

                <!-- Naudotojo rolė -->
                <td>
                    <select name="role_<?= h($user) ?>"
                            form="vartotojai">

                        <?php foreach ($user_roles as $role => $skaicius): ?>
                            <option value="<?= h($skaicius) ?>"
                                <?= $level === (int)$skaicius
                                    ? 'selected' : '' ?>>
                                <?= h($role) ?>
                            </option>
                        <?php endforeach; ?>

                        <option value="<?= UZBLOKUOTAS ?>"
                            <?= $level === (int)UZBLOKUOTAS
                                ? 'selected' : '' ?>>
                            Užblokuotas
                        </option>
                    </select>
                </td>

                <!-- El. paštas -->
                <td><?= h($u['email']) ?></td>

                <!-- Paskutinis aktyvumas -->
                <td>
                    <?= h(date(
                        'Y-m-d H:i',
                        strtotime($u['timestamp'])
                    )) ?>
                </td>

                <!-- Skelbimų publikavimo leidimas -->
                <td>
                    <?php if (in_array($level, [4,5,9], true)): ?>

                        <?= $leista ? 'Leista' : 'Neleista' ?>

                        <form method="post"
                              action="admin.php"
                              style="display:inline-block;margin-left:8px">

                            <input type="hidden"
                                   name="leidimu_token"
                                   value="<?= h($_SESSION['leidimu_token']) ?>">

                            <input type="hidden"
                                   name="userid"
                                   value="<?= h($u['userid']) ?>">

                            <input type="hidden"
                                   name="gali_skelbti"
                                   value="<?= $leista ? '0' : '1' ?>">

                            <button type="submit"
                                    name="keisti_skelbimo_leidima"
                                    value="1">
                                <?= $leista ? 'Atšaukti' : 'Suteikti' ?>
                            </button>
                        </form>

                    <?php else: ?>
                        Netaikoma
                    <?php endif; ?>
                </td>

                <!-- Naudotojo šalinimas -->
                <td>
                    <input type="checkbox"
                           name="naikinti_<?= h($user) ?>"
                           form="vartotojai">
                </td>
            </tr>

        <?php endwhile; ?>

    </table>

    <p>
        <button type="submit" form="vartotojai">
            Vykdyti rolių pakeitimus / šalinimą
        </button>
    </p>

    <p>
        <small>
            Leidimai skelbti keičiami iš karto.
            Rolių pakeitimams ir šalinimui naudokite
            apatinį mygtuką.
        </small>
    </p>

</body>
</html>

<?php $db->close(); ?>