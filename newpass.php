<?php
// TODO: Ateityje įdiegti saugų slaptažodžio atkūrimą
// naudojant vienkartini url

session_start();

$_SESSION['message'] =
    'Slaptažodžio atkūrimas šiame prototipe dar neįdiegtas.';

header('Location: index.php');
exit;