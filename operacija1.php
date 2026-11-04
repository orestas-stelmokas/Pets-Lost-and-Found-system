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
$atidarytasSkelbimas = null;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset("utf8mb4");

    $sql = "SELECT s.*, u.username AS autorius,
			(
				SELECT n.failo_kelias
				FROM nuotrauka n
				WHERE n.skelbimas_id = s.skelbimas_id
				ORDER BY n.eiles_nr, n.nuotrauka_id
				LIMIT 1
			) AS nuotraukos_kelias
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

	// Skaičiuojame peržiūrą tik atidarius konkretų skelbimą
	$atidarymoId = filter_input(
		INPUT_GET,
		'id',
		FILTER_VALIDATE_INT,
		['options' => ['min_range' => 1]]
	);

	if (is_int($atidarymoId)) {

		foreach ($skelbimai as $s) {
			if ((int)$s['skelbimas_id'] === $atidarymoId) {
				$atidarytasSkelbimas = $s;
				break;
			}
		}

		if ($atidarytasSkelbimas !== null) {
			$ziuretojasId = (int)($_SESSION['ulevel'] ?? 0) === 0
				? null
				: (string)($_SESSION['userid'] ?? '');

			$stmt = $db->prepare(
				"INSERT INTO perziura (skelbimas_id, naudotojas_id)
				VALUES (?, ?)"
			);

			$stmt->bind_param(
				"is",
				$atidarymoId,
				$ziuretojasId
			);

			$stmt->execute();
			$stmt->close();
		}
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

		<?php if ($atidarytasSkelbimas !== null): ?>
			<div style="border:2px solid #888; padding:15px;
						margin:20px auto; max-width:650px;
						text-align:left">

				<h2><?= h($atidarytasSkelbimas['pavadinimas']) ?></h2>

				<?php if (!empty($atidarytasSkelbimas['nuotraukos_kelias'])): ?>
					<img
						src="<?= h($atidarytasSkelbimas['nuotraukos_kelias']) ?>"
						alt="Gyvūno nuotrauka"
						style="max-width:300px;max-height:250px"
					>
				<?php endif; ?>

				<p><b>Tipas:</b>
					<?= $atidarytasSkelbimas['tipas'] === 'dinges'
						? 'Dingęs' : 'Rastas' ?>
				</p>

				<p><b>Rūšis:</b>
					<?= h($atidarytasSkelbimas['rusis']) ?>
				</p>

				<p><b>Vieta:</b>
					<?= h($atidarytasSkelbimas['vieta']) ?>
				</p>

				<p><b>Aprašymas:</b></p>
				<p><?= nl2br(h($atidarytasSkelbimas['aprasymas'])) ?></p>

				<a href="operacija1.php">Grįžti į skelbimų sąrašą</a>
			</div>
		<?php endif; ?>

        <h2>Aktyvūs skelbimai</h2>

        <table border="1" cellpadding="8"
               style="margin:20px auto;border-collapse:collapse">

            <tr>
				<th>Nuotrauka</th>
                <th>Tipas</th>
                <th>Pavadinimas</th>
                <th>Rūšis</th>
                <th>Lytis</th>
                <th>Amžius</th>
                <th>Vieta</th>
                <th>Aprašymas</th>
                <th>Autorius</th>
                <th>Galioja iki</th>
				<th>Susisiekti</th>
				<th>Informacija</th>
            </tr>

            <?php foreach ($skelbimai as $s): ?>
                <tr>
					<td>
						<?php if (!empty($s['nuotraukos_kelias'])): ?>
							<img
								src="<?= h($s['nuotraukos_kelias']) ?>"
								alt="Gyvūno nuotrauka"
								style="width:130px;height:110px;object-fit:cover"
							>
						<?php else: ?>
							Nuotraukos nėra
						<?php endif; ?>
					</td>
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

					<td>
						<?php if ((int)($_SESSION['ulevel'] ?? 0) === 0): ?>

							Tik registruotiems naudotojams

						<?php elseif (
							($_SESSION['userid'] ?? '') === $s['autorius_id']
						): ?>

							<a href="zinutes.php">Mano žinutės</a>

						<?php else: ?>

							<a href="zinutes.php?skelbimas_id=<?= (int)$s['skelbimas_id'] ?>">
								Rašyti autoriui
							</a>

						<?php endif; ?>
					</td>

					<td>
						<a href="operacija1.php?id=<?= (int)$s['skelbimas_id'] ?>">
							Peržiūrėti
						</a>
					</td>
                </tr>
            <?php endforeach; ?>

        </table>

    <?php endif; ?>

</body>
</html>