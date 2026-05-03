<?php
// backend/supabase_storage.php

require_once __DIR__ . '/config.php';

class SupabaseStorage {

    /**
     * Upload a file to Supabase Storage
     *
     * @param string $bucketName The name of the Supabase bucket
     * @param string $destinationPath The path inside the bucket (e.g., 'maths/2023/file.pdf')
     * @param string $localFilePath The local path to the file to upload
     * @param string $mimeType The MIME type of the file
     * @return bool|array True if successful, or an array with error details
     */
    public static function uploadFile($bucketName, $destinationPath, $localFilePath, $mimeType = 'application/octet-stream') {
        if (!defined('SUPABASE_STORAGE_URL') || !defined('SUPABASE_KEY')) {
            return ['error' => 'Supabase configuration is missing.'];
        }

        $url = rtrim(SUPABASE_STORAGE_URL, '/') . '/storage/v1/object/' . $bucketName . '/' . ltrim($destinationPath, '/');
        $fileContent = file_get_contents($localFilePath);

        if ($fileContent === false) {
            return ['error' => 'Could not read local file.'];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . SUPABASE_KEY,
            'apikey: ' . SUPABASE_KEY,
            'Content-Type: ' . $mimeType
        ]);

        // Optionnel : Désactiver la vérification SSL si le bundle CA est manquant sur Windows
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        // curl_close est déprécié en PHP 8.5+ et inutile depuis PHP 8.0
        if (PHP_VERSION_ID < 80500) {
            curl_close($ch);
        }

        // Supabase retourne 200 OK pour un upload réussi
        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        return [
            'error' => $curlError ?: 'HTTP ' . $httpCode,
            'response' => $response,
            'http_code' => $httpCode
        ];
    }

    /**
     * Get the public URL for a file in Supabase Storage
     *
     * @param string $bucketName The name of the Supabase bucket
     * @param string $path The path to the file inside the bucket
     * @return string The public URL
     */
    public static function getPublicUrl($bucketName, $path) {
        if (!defined('SUPABASE_STORAGE_URL')) {
            return '';
        }
        
        // Ensure path is URL encoded for special characters, but keep slashes
        $parts = explode('/', ltrim($path, '/'));
        $encodedParts = array_map('rawurlencode', $parts);
        $encodedPath = implode('/', $encodedParts);

        return rtrim(SUPABASE_STORAGE_URL, '/') . '/storage/v1/object/public/' . $bucketName . '/' . $encodedPath;
    }
}
