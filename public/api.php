<?php
/**
 * Example API endpoint
 * 
 * This endpoint is available only to visitors from allowed countries.
 */

header('Content-Type: application/json');

$response = [
    'status' => 'success',
    'message' => 'API request authorized',
    'timestamp' => date('c'),
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);