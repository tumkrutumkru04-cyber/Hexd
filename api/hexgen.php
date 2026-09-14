<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

date_default_timezone_set('Asia/Kolkata');

$redis_url = "https://advanced-shepherd-122987.upstash.io";
$redis_token = "gQAAAAAAAeBrAAIgcDE5ZTdjYzk4YTc1MTU0ODMwYjk3NDBiNTgwNGRkNGIzZA";



$key = "HEX-CHEATS-" . strtoupper(bin2hex(random_bytes(2)));
$expiry = time() + (12 * 3600);

$key_data = [
    "key" => $key,
    "devices" => [],
    "devices_used" => 0,
    "max_devices" => 1,
    "created_at" => date('Y-m-d H:i:s'),
    "expires_at" => date('Y-m-d H:i:s', $expiry),
    "expiry_timestamp" => $expiry * 1000,
    "validity" => "12 Hours",
    "status" => "active"
];

$ch = curl_init("$redis_url/set/keys:$key");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $redis_token",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($key_data));
curl_exec($ch);
curl_close($ch);

echo json_encode([
    "status" => true,
    "key" => $key,
    "validity" => "12 Hours",
    "expires_at" => date('Y-m-d H:i:s', $expiry),
    "max_devices" => 10
], JSON_PRETTY_PRINT);
?>
