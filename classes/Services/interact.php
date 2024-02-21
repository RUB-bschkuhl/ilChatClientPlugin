<?php

require __DIR__ . '/../Helper/chatclient.php';
global $_POST;

// $context = context_system::instance();
// require_capability('block/rub_chatbot:interact', $context);

$client = new chatclient();
$resp = $client->curl_interact($client->get_interact_url(), $_POST);

if ($resp == false) {
    echo ("false");
    die();
}
header('Content-type: application/json');
echo json_encode($resp);
