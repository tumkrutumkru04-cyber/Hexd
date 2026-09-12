<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

date_default_timezone_set('Asia/Kolkata');

$redis_url = "https://alive-minnow-172519.upstash.io";
$redis_token = "gQAAAAAAAqHnAAIgcDI0Zjk3ZTRiMWY2MTg0Mjc2YTVmYTMzNzZlY2M2OGE3OQ";

$key = $_GET['key'] ?? $_POST['key'] ?? $_GET['user_key'] ?? $_POST['user_key'] ?? '';
$hwid = $_GET['hwid'] ?? $_POST['hwid'] ?? $_GET['serial'] ?? $_POST['serial'] ?? '';

if (empty($key) || empty($hwid)) {
    echo json_encode(["status" => false, "reason" => "Key and HWID required"], JSON_PRETTY_PRINT);
    exit;
}

$hardcoded_keys = [
    'hexmods',
    'HEX-CIPHER-N6F8JG',
    'PIYUSH-HACKS',
    'JOELITHON-MODS',
    'XITEXE-75A3-F7718'
];

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

if ($key_data['expiry_timestamp'] < time() * 1000) {
    echo json_encode(["status" => false, "reason" => "Key expired"], JSON_PRETTY_PRINT);
    exit;
}

if ($key_data['device_id'] === null) {
    $key_data['device_id'] = $hwid;
    $key_data['devices_used'] = 1;
} elseif ($key_data['device_id'] !== $hwid) {
    echo json_encode(["status" => false, "reason" => "Device limit reached"], JSON_PRETTY_PRINT);
    exit;
}

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
