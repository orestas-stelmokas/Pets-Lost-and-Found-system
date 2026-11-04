<?php
// TODO: Įgyvendinti slaptažodžio atkūrimą el. paštu.

session_start();

$_SESSION['message'] =
    'Slaptažodžio atkūrimas šiame prototipe dar neįdiegtas.';

header('Location: index.php');
exit;