<?php

/**
 * ChatClient plugin
 * @author Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 */


 //TODO 
require __DIR__ . '/../class.ChatclientHelper.php';
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
