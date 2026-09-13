<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

date_default_timezone_set('Asia/Kolkata');

$redis_url = "https://careful-crane-121939.upstash.io";
$redis_token = "gQAAAAAAAdxTAAIgcDJjY2M1MWUyZWEzY2Y0YzhkYWI3ZDZmZWM4OTc3ZGMyYg";

$key = $_GET['key'] ?? $_POST['key'] ?? $_GET['user_key'] ?? $_POST['user_key'] ?? '';
$hwid = $_GET['hwid'] ?? $_POST['hwid'] ?? $_GET['serial'] ?? $_POST['serial'] ?? '';

if (empty($key) || empty($hwid)) {
    echo json_encode(["status" => false, "reason" => "Key and HWID required"], JSON_PRETTY_PRINT);
    exit;
}

// Hardcoded keys
$hardcoded_keys = ['hexmods', 'HEX-CIPHER-BFJFG767', 'PIYUSH-HACKS', 'XITEXE-KEY', 'DRAGON-MODZ'];

if (in_array($key, $hardcoded_keys)) {
    echo json_encode([
        "status" => true,
        "reason" => "Login successful",
        "data" => [
            "token" => md5(uniqid() . $hwid),
            "rng" => time(),
            "EXP" => "9999999999",
            "modname" => "PLASMA CHEATS",
            "mod_status" => "Online",
            "credit" => "@ARPANMODX",
            "ESP" => "1", "Item" => "1", "AIM" => "1",
            "SilentAim" => "1", "BulletTrack" => "1",
            "Floating" => "1", "Memory" => "1", "Setting" => "1"
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// Get key data from Redis
$ch = curl_init("$redis_url/get/keys:$key");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $redis_token"]);
$redis_response = curl_exec($ch);
curl_close($ch);

$redis_data = json_decode($redis_response, true);

if (!$redis_data || !isset($redis_data['result']) || $redis_data['result'] === null) {
    echo json_encode(["status" => false, "reason" => "Key not found"], JSON_PRETTY_PRINT);
    exit;
}

$key_data = json_decode($redis_data['result'], true);

// Check expiry
if ($key_data['expiry_timestamp'] < time() * 1000) {
    echo json_encode(["status" => false, "reason" => "Key expired"], JSON_PRETTY_PRINT);
    exit;
}

// --- DEVICE LIMIT LOGIC (FIXED) ---

// Initialize devices array if not exists
if (!isset($key_data['devices']) || !is_array($key_data['devices'])) {
    $key_data['devices'] = [];
}

// Check if this device is already registered
if (in_array($hwid, $key_data['devices'])) {
    // Same device — allow access
    $devices_used = count($key_data['devices']);
} else {
    // New device
    if (count($key_data['devices']) >= $key_data['max_devices']) {
        echo json_encode(["status" => false, "reason" => "Device limit reached"], JSON_PRETTY_PRINT);
        exit;
    }
    
    // Add new device
    $key_data['devices'][] = $hwid;
    $devices_used = count($key_data['devices']);
}

// Save updated key data
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

// Success response
echo json_encode([
    "status" => true,
    "reason" => "Login successful",
    "data" => [
        "token" => md5(uniqid() . $hwid),
        "rng" => time(),
        "EXP" => "9999999999",
        "modname" => "PLASMA CHEATS",
        "mod_status" => "Online",
        "credit" => "@ARPANMODX",
        "ESP" => "1", "Item" => "1", "AIM" => "1",
        "SilentAim" => "1", "BulletTrack" => "1",
        "Floating" => "1", "Memory" => "1", "Setting" => "1"
    ]
], JSON_PRETTY_PRINT);
?>
