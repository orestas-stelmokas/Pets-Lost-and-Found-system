<?php

function atnaujintiSkelbimuGaliojima($db) {
    $siandien = (new DateTimeImmutable(
        'now',
        new DateTimeZone('Europe/Vilnius')
    ))->format('Y-m-d');

    $stmt = $db->prepare(
        "UPDATE skelbimas
         SET busena = 'pasibaiges'
         WHERE busena = 'aktyvus'
           AND galioja_iki IS NOT NULL
           AND galioja_iki < ?"
    );

    $stmt->bind_param('s', $siandien);
    $stmt->execute();
    $stmt->close();
}