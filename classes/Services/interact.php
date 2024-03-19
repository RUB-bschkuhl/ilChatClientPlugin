<?php

/**
 * ChatClient plugin
 * @author Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 */

require __DIR__ . '/../Helper/Chatclient.php';
global $_POST;

//TODO Security

$client = new chatclient();
$resp = $client->curl_interact($_POST);

if ($resp == false) {
    echo ("false");
    die();
}
header('Content-type: application/json');
echo json_encode($resp);
