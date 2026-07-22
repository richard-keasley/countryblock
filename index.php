<?php
/**
 * Country Block Entry Point
 * 
 * All requests are routed through this file via .htaccess rewrite.
 * Geo-blocking is checked before routing to application code.
 */

require __DIR__ . '/vendor/autoload.php';

use CountryBlock\CountryBlocker;

// Load configuration
$allowedCountries = require __DIR__ . '/config/allowed_countries.php';
$geoIPDbPath = __DIR__ . '/data/GeoLite2-Country.mmdb';

try {
    // Initialize geo-blocker
    $blocker = new CountryBlocker($geoIPDbPath, $allowedCountries);

    // Check if visitor is allowed
    if (!$blocker->isAllowed()) {
        $visitorCountry = $blocker->getVisitorCountry();
        $blocker->block("Access denied. Your country ($visitorCountry) is not permitted to access this website.");
    }

    // Visitor is allowed - continue with your application
    // Route requests to appropriate handler
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestUri = ltrim($requestUri, '/');

    // Simple routing example
    if (empty($requestUri) || $requestUri === 'index.php') {
        include __DIR__ . '/public/home.php';
    } elseif (strpos($requestUri, 'api/') === 0) {
        include __DIR__ . '/public/api.php';
    } else {
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
    }

} catch (\Exception $e) {
    // Log error and show generic error page
    error_log("CountryBlock Error: " . $e->getMessage());
    http_response_code(500);
    echo "<h1>Server Error</h1><p>An unexpected error occurred.</p>";
}