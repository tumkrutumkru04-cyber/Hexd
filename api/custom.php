<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

date_default_timezone_set('Asia/Kolkata');

$redis_url = getenv('UPSTASH_REDIS_REST_URL');
$redis_token = getenv('UPSTASH_REDIS_REST_TOKEN');

// Parameters
$custom_key = $_GET['key'] ?? $_POST['key'] ?? '';
$days = intval($_GET['days'] ?? $_POST['days'] ?? 0);
$hours = intval($_GET['hours'] ?? $_POST['hours'] ?? 0);
$devices = intval($_GET['devices'] ?? $_POST['devices'] ?? 0);

// Validation
if (empty($custom_key)) {
    echo json_encode([
        "status" => false,
        "reason" => "Key parameter required (e.g., ?key=MY-KEY&days=5&devices=3)"
    ], JSON_PRETTY_PRINT);
    exit;
}

if ($days <= 0 && $hours <= 0) {
    echo json_encode([
        "status" => false,
        "reason" => "Days or Hours required"
    ], JSON_PRETTY_PRINT);
    exit;
}

if ($devices <= 0) {
    echo json_encode([
        "status" => false,
        "reason" => "Devices required"
    ], JSON_PRETTY_PRINT);
    exit;
}

// Cap devices
if ($devices > 100000) $devices = 100000;

// Calculate expiry
$total_seconds = ($days * 24 * 3600) + ($hours * 3600);
$expiry = time() + $total_seconds;

// Validity text
if ($days > 0 && $hours > 0) {
    $validity_text = $days . " Days " . $hours . " Hours";
} elseif ($days > 0) {
    $validity_text = $days . " Days";
} else {
    $validity_text = $hours . " Hours";
}

// Check if key already exists
$ch = curl_init("$redis_url/get/keys:$custom_key");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $redis_token"]);
$existing = curl_exec($ch);
curl_close($ch);

$existing_data = json_decode($existing, true);
if ($existing_data && isset($existing_data['result']) && $existing_data['result'] !== null) {
    echo json_encode([
        "status" => false,
        "reason" => "Key already exists"
    ], JSON_PRETTY_PRINT);
    exit;
}

// Create key data
$key_data = [
    "key" => $custom_key,
    "device_id" => null,
    "devices_used" => 0,
    "max_devices" => $devices,
    "created_at" => date('Y-m-d H:i:s'),
    "expires_at" => date('Y-m-d H:i:s', $expiry),
    "expiry_timestamp" => $expiry * 1000,
    "validity" => $validity_text,
    "status" => "active"
];

// Save to Redis
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
