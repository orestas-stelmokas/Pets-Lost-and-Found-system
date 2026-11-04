<?php
session_start();
require_once 'include/nustatymai.php';

if (empty($_SESSION['user']) ||
    empty($_SESSION['userid']) ||
    !in_array((int)($_SESSION['ulevel'] ?? -1), [4, 5, 9], true)) {
    header('Location: index.php');
    exit;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$manoId = (string)$_SESSION['userid'];
$db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
$db->set_charset('utf8mb4');

// Patikriname, ar paskyra vis dar aktyvi
$stmt = $db->prepare(
    "SELECT userid FROM users
     WHERE userid = ? AND userlevel IN (4,5,9)"
);
$stmt->bind_param('s', $manoId);
$stmt->execute();
$aktyvus = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$aktyvus) {
    http_response_code(403);
    exit('Paskyra neaktyvi.');
}

if (empty($_SESSION['zinutes_token'])) {
    $_SESSION['zinutes_token'] = bin2hex(random_bytes(32));
}

$klaida = '';
$tekstas = '';
$pokalbis = [];
$pokalbiai = [];
$partneris = null;
$skelbimas = null;

// Ar atidarome konkretų skelbimą?
$atidarytas = isset($_GET['skelbimas_id']);
$skelbimoId = filter_input(
    INPUT_GET,
    'skelbimas_id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($atidarytas) {
    if (!$skelbimoId) {
        http_response_code(404);
        exit('Skelbimas nerastas.');
    }

    $stmt = $db->prepare(
        "SELECT skelbimas_id, pavadinimas, autorius_id
         FROM skelbimas WHERE skelbimas_id = ?"
    );
    $stmt->bind_param('i', $skelbimoId);
    $stmt->execute();
    $skelbimas = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$skelbimas) {
        http_response_code(404);
        exit('Skelbimas nerastas.');
    }

    $esuAutorius = $skelbimas['autorius_id'] === $manoId;

    if ($esuAutorius) {
        // Autorius atsakinėja pasirinktam naudotojui
        $partnerioId = $_GET['partner_id'] ?? '';

        if (!is_string($partnerioId) ||
            !preg_match('/^[a-f0-9]{32}$/i', $partnerioId)) {
            header('Location: zinutes.php');
            exit;
        }

        // Autorius gali atsakyti tik jau parašiusiam naudotojui
        $stmt = $db->prepare(
            "SELECT zinute_id FROM zinute
             WHERE skelbimas_id = ?
               AND (
                 (siuntejas_id = ? AND gavejas_id = ?)
                 OR
                 (siuntejas_id = ? AND gavejas_id = ?)
               )
             LIMIT 1"
        );
        $stmt->bind_param(
            'issss',
            $skelbimoId,
            $manoId,
            $partnerioId,
            $partnerioId,
            $manoId
        );
        $stmt->execute();
        $egzistuoja = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$egzistuoja) {
            http_response_code(403);
            exit('Šis pokalbis neegzistuoja.');
        }
    } else {
        // Kitas naudotojas rašo skelbimo autoriui
        $partnerioId = $skelbimas['autorius_id'];
    }

    // Gauname pašnekovą
    $stmt = $db->prepare(
        "SELECT userid, username
         FROM users
         WHERE userid = ? AND userlevel IN (4,5,9)"
    );
    $stmt->bind_param('s', $partnerioId);
    $stmt->execute();
    $partneris = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$partneris || $partnerioId === $manoId) {
        http_response_code(404);
        exit('Pašnekovas nerastas.');
    }

    // Naujos žinutės siuntimas
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['zinutes_token'] ?? '';
        $ivestis = $_POST['tekstas'] ?? '';

        $tekstas = is_string($ivestis) ? trim($ivestis) : '';

        if (!is_string($token) ||
            !hash_equals($_SESSION['zinutes_token'], $token)) {
            $klaida = 'Neteisinga užklausa.';
        } elseif (
            preg_match('/\A.{1,2000}\z/us', $tekstas) !== 1
        ) {
            $klaida = 'Įveskite žinutę iki 2000 simbolių.';
        } else {
            $stmt = $db->prepare(
                "INSERT INTO zinute
                 (skelbimas_id, siuntejas_id, gavejas_id, tekstas)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                'isss',
                $skelbimoId,
                $manoId,
                $partnerioId,
                $tekstas
            );

            $stmt->execute();
            $stmt->close();

            $_SESSION['zinutes_sekme'] = 'Žinutė išsiųsta.';

            $url = 'zinutes.php?skelbimas_id=' . $skelbimoId;

            if ($esuAutorius) {
                $url .= '&partner_id=' . urlencode($partnerioId);
            }

            header('Location: ' . $url);
            exit;
        }
    }

    // Pokalbio istorija
    $stmt = $db->prepare(
        "SELECT siuntejas_id, tekstas, issiusta_at
         FROM zinute
         WHERE skelbimas_id = ?
           AND (
             (siuntejas_id = ? AND gavejas_id = ?)
             OR
             (siuntejas_id = ? AND gavejas_id = ?)
           )
         ORDER BY issiusta_at, zinute_id"
    );

    $stmt->bind_param(
        'issss',
        $skelbimoId,
        $manoId,
        $partnerioId,
        $partnerioId,
        $manoId
    );

    $stmt->execute();
    $pokalbis = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

} else {
    // Mano pokalbių sąrašas
    $stmt = $db->prepare(
        "SELECT z.skelbimas_id, z.siuntejas_id,
                z.gavejas_id, z.issiusta_at,
                s.pavadinimas,
                us.username AS siuntejas_vardas,
                ug.username AS gavejas_vardas
         FROM zinute z
         JOIN skelbimas s
           ON s.skelbimas_id = z.skelbimas_id
         LEFT JOIN users us
           ON us.userid = z.siuntejas_id
         LEFT JOIN users ug
           ON ug.userid = z.gavejas_id
         WHERE z.siuntejas_id = ? OR z.gavejas_id = ?
         ORDER BY z.issiusta_at DESC, z.zinute_id DESC"
    );

    $stmt->bind_param('ss', $manoId, $manoId);
    $stmt->execute();
    $rezultatai = $stmt->get_result();
    $matyti = [];

    while ($z = $rezultatai->fetch_assoc()) {
        $esuSiuntejas = $z['siuntejas_id'] === $manoId;

        $kitoId = $esuSiuntejas
            ? $z['gavejas_id']
            : $z['siuntejas_id'];

        $kitoVardas = $esuSiuntejas
            ? $z['gavejas_vardas']
            : $z['siuntejas_vardas'];

        $raktas = $z['skelbimas_id'] . ':' . $kitoId;

        if (isset($matyti[$raktas])) {
            continue;
        }

        $matyti[$raktas] = true;

        $pokalbiai[] = [
            'skelbimas_id' => $z['skelbimas_id'],
            'pavadinimas' => $z['pavadinimas'],
            'partnerio_id' => $kitoId,
            'partnerio_vardas' => $kitoVardas ?? 'Nežinomas',
            'laikas' => $z['issiusta_at']
        ];
    }

    $stmt->close();
}

$db->close();
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Mano žinutės</title>
    <link href="include/styles.css" rel="stylesheet">
</head>
<body>

<h1>Mano žinutės</h1>

<?php include 'include/meniu.php'; ?>

<p>
    <a href="index.php">Pradžia</a> |
    <a href="zinutes.php">Visi pokalbiai</a>
</p>

<?php if (!empty($_SESSION['zinutes_sekme'])): ?>
    <p style="color:green">
        <?= h($_SESSION['zinutes_sekme']) ?>
    </p>
    <?php unset($_SESSION['zinutes_sekme']); ?>
<?php endif; ?>

<?php if ($atidarytas): ?>

    <h2><?= h($skelbimas['pavadinimas']) ?></h2>
    <p>Susirašinėjate su: <b><?= h($partneris['username']) ?></b></p>

    <div style="max-width:650px;margin:20px auto;text-align:left">

        <?php if (empty($pokalbis)): ?>
            <p>Žinučių dar nėra. Parašykite pirmąją.</p>
        <?php endif; ?>

        <?php foreach ($pokalbis as $z): ?>
            <?php $mano = $z['siuntejas_id'] === $manoId; ?>

            <div style="border:1px solid #aaa;padding:10px;
                        margin:10px 0;background:<?= $mano ? '#e8f4ff' : '#f5f5f5' ?>">

                <b><?= $mano ? 'Jūs' : h($partneris['username']) ?></b>
                <small><?= h($z['issiusta_at']) ?></small>

                <p><?= nl2br(h($z['tekstas'])) ?></p>
            </div>
        <?php endforeach; ?>

        <?php if ($klaida !== ''): ?>
            <p style="color:red"><?= h($klaida) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="zinutes_token"
                   value="<?= h($_SESSION['zinutes_token']) ?>">

            <p>
                <textarea name="tekstas" rows="4" cols="55"
                          required><?= h($tekstas) ?></textarea>
            </p>

            <button type="submit">Siųsti žinutę</button>
        </form>
    </div>

<?php else: ?>

    <h2>Mano pokalbiai</h2>

    <?php if (empty($pokalbiai)): ?>
        <p>Žinučių dar neturite.</p>
    <?php else: ?>
        <table class="center" border="1" cellpadding="10">
            <tr>
                <th>Skelbimas</th>
                <th>Pašnekovas</th>
                <th>Paskutinė žinutė</th>
                <th>Veiksmas</th>
            </tr>

            <?php foreach ($pokalbiai as $p): ?>
                <tr>
                    <td><?= h($p['pavadinimas']) ?></td>
                    <td><?= h($p['partnerio_vardas']) ?></td>
                    <td><?= h($p['laikas']) ?></td>
                    <td>
                        <a href="zinutes.php?skelbimas_id=<?= (int)$p['skelbimas_id'] ?>&amp;partner_id=<?= h($p['partnerio_id']) ?>">
                            Atidaryti pokalbį
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

<?php endif; ?>

</body>
</html>