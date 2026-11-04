<?php
session_start();

if (empty($_SESSION['user']) ||
    !in_array((int)($_SESSION['ulevel'] ?? -1), [0,4,5,9], true)) {
    header("Location: index.php");
    exit;
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

    <h2>Skelbimų sąrašas</h2>
    <p>Šiuo metu skelbimų nėra.</p>
    <p>Čia bus rodomi dingę ir rasti gyvūnai,
       jų nuotraukos, aprašymai bei radimo arba dingimo vietos.</p>
</body>
</html>