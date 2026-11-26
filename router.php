<?php

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$url = rtrim($url, '/') ?: '/';

$method = $_SERVER['REQUEST_METHOD'];

if ($url === '/' || $url === '/index.php') {
    header('Location: /home');
    exit;
}

$routes = [
    '/home' => 'home.php',
    '/animals' => 'operacija1.php',
    '/create-listing' => 'operacija2.php',
    '/moderation' => 'operacija3.php',
    '/my-listings' => 'mano_skelbimai.php',
    '/messages' => 'zinutes.php',

    '/admin' => 'admin.php',
    '/admin-confirm' => 'procadmin.php',
    '/admin-save' => 'procadmindb.php',

    '/register' => $method === 'POST'
        ? 'procregister.php' : 'register.php',

    '/profile' => $method === 'POST'
        ? 'procuseredit.php' : 'useredit.php',

    '/login' => $method === 'POST'
        ? 'proclogin.php' : 'home.php',

    '/logout' => 'logout.php',
    '/guest' => 'guest.php',
    '/forgot-password' => 'forgotpass.php',
    '/new-password' => 'newpass.php'
];

if (isset($routes[$url])) {
    require __DIR__ . '/' . $routes[$url];
} else {
    http_response_code(404);
    echo '<h1>404 - Puslapis nerastas</h1>';
    echo '<a href="/home">Grįžti į pradžią</a>';
}