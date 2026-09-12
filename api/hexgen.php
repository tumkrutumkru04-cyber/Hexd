<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

date_default_timezone_set('Asia/Kolkata');

$redis_url = getenv('UPSTASH_REDIS_REST_URL');
$redis_token = getenv('UPSTASH_REDIS_REST_TOKEN');

// Generate key: HEX-CHEATS-XXXX
$key = "HEX-CHEATS-" . strtoupper(bin2hex(random_bytes(2)));

// 12 HOURS validity
$expiry = time() + (12 * 3600);

$key_data = [
    "key" => $key,
    "device_id" => null,
    "devices_used" => 0,
    "max_devices" => 1,
    "created_at" => date('Y-m-d H:i:s'),
    "expires_at" => date('Y-m-d H:i:s', $expiry),
    "expiry_timestamp" => $expiry * 1000,
    "validity" => "12 Hours",
    "status" => "active"
];

// Save to Redis
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
    "max_devices" => 1
], JSON_PRETTY_PRINT);
?>
