#!/usr/bin/env php
<?php
/**
 * Download/Update MaxMind GeoLite2 Database
 * 
 * Downloads the latest GeoLite2-Country database from MaxMind.
 * Requires a free MaxMind account at https://www.maxmind.com/en/geolite2/geolite2-free-geolocation-databases
 * 
 * Usage:
 *   php scripts/download_geoip_db.php
 * 
 * Environment Variables:
 *   MAXMIND_ACCOUNT_ID  - Your MaxMind account ID
 *   MAXMIND_LICENSE_KEY - Your MaxMind license key
 */

$accountId = getenv('MAXMIND_ACCOUNT_ID');
$licenseKey = getenv('MAXMIND_LICENSE_KEY');

if (!$accountId || !$licenseKey) {
    fwrite(STDERR, "Error: MaxMind credentials not found.\n");
    fwrite(STDERR, "Please set environment variables:\n");
    fwrite(STDERR, "  export MAXMIND_ACCOUNT_ID=your_account_id\n");
    fwrite(STDERR, "  export MAXMIND_LICENSE_KEY=your_license_key\n");
    fwrite(STDERR, "\nGet free credentials at: https://www.maxmind.com/en/geolite2/geolite2-free-geolocation-databases\n");
    exit(1);
}

$dataDir = __DIR__ . '/../data';
$dbFile = $dataDir . '/GeoLite2-Country.mmdb';
$tarFile = $dataDir . '/GeoLite2-Country.tar.gz';

// Create data directory if it doesn't exist
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
    echo "Created data directory: $dataDir\n";
}

// Download URL
$url = sprintf(
    'https://download.maxmind.com/geoip/databases/GeoLite2-Country/download?suffix=tar.gz&license_key=%s',
    urlencode($licenseKey)
);

echo "Downloading GeoLite2-Country database...\n";

// Download tar.gz file
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 300);

$data = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    fwrite(STDERR, "Error: Failed to download database (HTTP $httpCode)\n");
    exit(1);
}

file_put_contents($tarFile, $data);
echo "Downloaded to: $tarFile\n";

// Extract tar.gz
echo "Extracting archive...\n";
$phar = new PharData($tarFile);
$phar->extractTo($dataDir);

// Find and move the extracted .mmdb file
$tmpFiles = glob($dataDir . '/GeoLite2-Country_*/GeoLite2-Country.mmdb');
if (empty($tmpFiles)) {
    fwrite(STDERR, "Error: Could not find extracted .mmdb file\n");
    exit(1);
}

$extractedFile = $tmpFiles[0];
copy($extractedFile, $dbFile);

// Cleanup
array_map('unlink', glob($dataDir . '/GeoLite2-Country_*/*.mmdb'));
array_map('rmdir', glob($dataDir . '/GeoLite2-Country_*/'));
rmdir(glob($dataDir . '/GeoLite2-Country_*')[0] ?? null);
unlink($tarFile);

echo "Database updated successfully: $dbFile\n";
echo "Database size: " . filesize($dbFile) . " bytes\n";