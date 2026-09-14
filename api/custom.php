<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

date_default_timezone_set('Asia/Kolkata');

$redis_url = "https://advanced-shepherd-122987.upstash.io";
$redis_token = "gQAAAAAAAdxTAAIgcDJjY2M1MWUyZWEzY2Y0YzhkYWI3ZDZmZWM4OTc3ZGMyYg";

$custom_key = $_GET['key'] ?? $_POST['key'] ?? '';
$days = intval($_GET['days'] ?? $_POST['days'] ?? 0);
$hours = intval($_GET['hours'] ?? $_POST['hours'] ?? 0);
$devices = intval($_GET['devices'] ?? $_POST['devices'] ?? 0);

if (empty($custom_key) || ($days <= 0 && $hours <= 0) || $devices <= 0) {
    echo json_encode(["status" => false, "reason" => "key, days/hours, devices required"], JSON_PRETTY_PRINT);
    exit;
}

if ($devices > 100000) $devices = 100000;

$total_seconds = ($days * 24 * 3600) + ($hours * 3600);
$expiry = time() + $total_seconds;

$validity_text = ($days > 0 ? "$days Days " : "") . ($hours > 0 ? "$hours Hours" : "");
$validity_text = trim($validity_text);

$key_data = [
    "key" => $custom_key,
    "devices" => [],
    "devices_used" => 0,
    "max_devices" => $devices,
    "created_at" => date('Y-m-d H:i:s'),
    "expires_at" => date('Y-m-d H:i:s', $expiry),
    "expiry_timestamp" => $expiry * 1000,
    "validity" => $validity_text,
    "status" => "active"
];

$ch = curl_init("$redis_url/set/keys:$custom_key");
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
    "key" => $custom_key,
    "validity" => $validity_text,
    "expires_at" => date('Y-m-d H:i:s', $expiry),
    "max_devices" => $devices
], JSON_PRETTY_PRINT);
?>
