<?php

include_once("./config.php");

$curl = curl_init($config["api"]["products"]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

$response = $err ? array() : $response;
echo json_encode($response);

?>