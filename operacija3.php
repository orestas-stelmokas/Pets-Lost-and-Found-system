<?php
session_start();

if (empty($_SESSION['user']) ||
    !in_array((int)($_SESSION['ulevel'] ?? -1), [5,9], true)) {
    header("Location: index.php");
    exit;
}
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

    <?php include("include/meniu.php"); ?>

    <p><a href="index.php">Grįžti į pradžią</a></p>

    <h2>Skelbimų administravimas</h2>
    <p>Šioje aplinkoje kontrolierius galės:</p>
    <ul style="display:inline-block;text-align:left">
        <li>Peržiūrėti visus skelbimus.</li>
        <li>Matyti skelbimų peržiūrų skaičių.</li>
        <li>Pašalinti netinkamus skelbimus.</li>
        <li>Pranešti apie galimus dingusių ir rastų gyvūnų sutapimus.</li>
    </ul>

    <p>Skelbimų valdymo lentelę pridėsime prijungę duomenų bazę.</p>
</body>
</html>