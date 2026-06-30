<?php
require_once __DIR__ . '/backend/config.php';
$ch = curl_init(SUPABASE_STORAGE_URL . '/auth/v1/user');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: " . SUPABASE_KEY // Service role ou anon key fonctionne ici
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$err1 = curl_error($ch);
$code1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response2 = curl_exec($ch);
$err2 = curl_error($ch);
$code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Test 1 (only peer=false):\nCode: $code1\nError: $err1\n";
echo "Test 2 (peer=false, host=false):\nCode: $code2\nError: $err2\n";
?>
