<?php

namespace CountryBlock;

/**
 * CountryBlocker - Geo-blocking middleware for LAMP stack
 * 
 * Blocks access to website based on visitor's country using MaxMind GeoLite2 database.
 * Checks allowed countries and returns 403 Forbidden for blocked regions.
 */
class CountryBlocker
{
    private string $geoIPDbPath;
    private array $allowedCountries;
    private string $visitorIP;

    /**
     * Constructor
     * 
     * @param string $geoIPDbPath Path to MaxMind GeoLite2 .mmdb database file
     * @param array $allowedCountries Array of ISO 3166-1 alpha-2 country codes (e.g., ['US', 'GB', 'CA'])
     */
    public function __construct(string $geoIPDbPath, array $allowedCountries)
    {
        $this->geoIPDbPath = $geoIPDbPath;
        $this->allowedCountries = $allowedCountries;
        $this->visitorIP = $this->getVisitorIP();

        // Verify GeoIP database exists
        if (!file_exists($geoIPDbPath)) {
            throw new \Exception("GeoIP database not found at: {$geoIPDbPath}");
        }

        // Verify MaxMind Reader library is available
        if (!class_exists('MaxMind\Db\Reader')) {
            throw new \Exception("MaxMind GeoIP2 library not installed. Run: composer require maxmind/geoip2");
        }
    }

    /**
     * Check if visitor is allowed based on country
     * 
     * @return bool True if allowed, false if blocked
     * @throws \Exception If GeoIP lookup fails
     */
    public function isAllowed(): bool
    {
        $country = $this->getVisitorCountry();
        return in_array($country, $this->allowedCountries);
    }

    /**
     * Get visitor's country code
     * 
     * @return string ISO 3166-1 alpha-2 country code
     * @throws \Exception If lookup fails
     */
    public function getVisitorCountry(): string
    {
        try {
            $reader = new \MaxMind\Db\Reader($this->geoIPDbPath);
            $record = $reader->get($this->visitorIP);
            $reader->close();

            if ($record && isset($record['country']['iso_code'])) {
                return $record['country']['iso_code'];
            }

            // Default to blocking if country cannot be determined
            return 'UNKNOWN';
        } catch (\Exception $e) {
            throw new \Exception("GeoIP lookup failed for {$this->visitorIP}: " . $e->getMessage());
        }
    }

    /**
     * Get visitor's IP address, accounting for proxies
     * 
     * Checks in order:
     * - CF-Connecting-IP (Cloudflare)
     * - X-Forwarded-For (proxy chains)
     * - REMOTE_ADDR (direct connection)
     * 
     * @return string Visitor's IP address
     */
    private function getVisitorIP(): string
    {
        // Cloudflare
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $this->sanitizeIP($_SERVER['HTTP_CF_CONNECTING_IP']);
        }

        // Proxy chains - take the first (originating) IP
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
            return $this->sanitizeIP($ips[0]);
        }

        // Direct connection
        return $this->sanitizeIP($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /**
     * Sanitize and validate IP address
     * 
     * @param string $ip IP address to validate
     * @return string Validated IP or fallback to 0.0.0.0
     */
    private function sanitizeIP(string $ip): string
    {
        $ip = filter_var($ip, FILTER_VALIDATE_IP);
        return $ip ?: '0.0.0.0';
    }

    /**
     * Block access and send 403 Forbidden response
     * 
     * @param string|null $message Optional custom error message
     */
    public function block(?string $message = null): void
    {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');

        $defaultMessage = "Access Denied";
        $customMessage = $message ?? "Your country is not allowed to access this website.";

        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$defaultMessage</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        h1 {
            color: #333;
            margin: 0 0 20px 0;
        }
        p {
            color: #666;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>$defaultMessage</h1>
        <p>$customMessage</p>
    </div>
</body>
</html>
HTML;
        exit;
    }

    /**
     * Get allowed countries list
     * 
     * @return array
     */
    public function getAllowedCountries(): array
    {
        return $this->allowedCountries;
    }

    /**
     * Get visitor's IP address
     * 
     * @return string
     */
    public function getVisitorIP(): string
    {
        return $this->visitorIP;
    }
}
