<?php
session_start();

if (!isset($_SESSION['prev']) ||
    $_SESSION['prev'] !== 'login') {
    header('Location: index.php');
    exit;
}

require_once 'include/nustatymai.php';
require_once 'include/functions.php';

$_SESSION['prev'] = 'proclogin';

// Išvalome ankstesnius prisijungimo duomenis
$_SESSION['user'] = '';
$_SESSION['ulevel'] = 0;
$_SESSION['userid'] = 0;
$_SESSION['umail'] = '';

$_SESSION['name_error'] = '';
$_SESSION['pass_error'] = '';
$_SESSION['message'] = 'Pabandykite dar kartą';

$user = $_POST['user'] ?? '';
$pass = $_POST['pass'] ?? '';

$user = is_string($user) ? strtolower(trim($user)) : '';
$pass = is_string($pass) ? $pass : '';

$_SESSION['name_login'] = $user;
$_SESSION['pass_login'] = '';

if (isset($_POST['problem'])) {
    $_SESSION['message'] =
        'Slaptažodžio atkūrimas šiame prototipe neįdiegtas.';

    header('Location: index.php');
    exit;
}

if (checkname($user)) {

    list($dbuname, $dbpass, $dblevel, $dbuid, $dbemail) =
        checkdb($user);

    if ($dbuname !== null && checkpass($pass, $dbpass)) {

        if ((int)$dblevel === (int)UZBLOKUOTAS) {
            $_SESSION['message'] = 'Jūsų paskyra užblokuota.';
        } else {

            $galiojantiRole = in_array(
                (int)$dblevel,
                [4, 5, 9],
                true
            );

            if (!$galiojantiRole) {
                $_SESSION['message'] =
                    'Negaliojanti vartotojo rolė.';
            } else {

                // Atnaujiname paskutinio prisijungimo laiką
                $db = new mysqli(
                    DB_SERVER, DB_USER, DB_PASS, DB_NAME
                );

                $stmt = $db->prepare(
                    "UPDATE users
                     SET timestamp = NOW()
                     WHERE userid = ?"
                );

                $stmt->bind_param('s', $dbuid);
                $stmt->execute();

                $stmt->close();
                $db->close();

                // Teises suteikiame tik patikrinę slaptažodį
                session_regenerate_id(true);

                $_SESSION['user'] = $dbuname;
                $_SESSION['ulevel'] = $dblevel;
                $_SESSION['userid'] = $dbuid;
                $_SESSION['umail'] = $dbemail;
                $_SESSION['message'] = '';
            }
        }
    }
}

header('Location: index.php');
exit;