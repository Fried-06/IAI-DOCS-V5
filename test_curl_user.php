<?php
require_once __DIR__ . '/backend/config.php';
$ch = curl_init(SUPABASE_STORAGE_URL . '/auth/v1/user');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: " . SUPABASE_KEY // Service role ou anon key fonctionne ici
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
echo "HTTP: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo "Response: " . $response . "\n";
?>
