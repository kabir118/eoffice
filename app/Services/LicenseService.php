<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class LicenseService
{
    /**
     * Master Authorization Verification Endpoint
     */
    private static string $authApiUrl = 'https://ustb.edu.pk/ac.php';

    /**
     * Master Secret Salt Fallback
     */
    private static string $masterSecret = 'KABIR_MASTER_REMOTE_SALT_2026_99A721_EOFFICE';

    /**
     * Get normalized current domain / hostname
     */
    public static function getCurrentHost(): string
    {
        $host = Request::getHost();

        if (empty($host)) {
            $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        }

        if (str_contains($host, ':')) {
            $host = explode(':', $host)[0];
        }

        return strtolower(trim($host));
    }

    /**
     * Generate a cryptographic License Key for a specific domain / host
     */
    public static function generateKeyForDomain(string $domain): string
    {
        $cd = strtolower(trim($domain));
        if (str_contains($cd, ':')) {
            $cd = explode(':', $cd)[0];
        }

        $rh = hash_hmac('sha256', 'EOFFICE_REMOTE_LIC:' . $cd, self::$masterSecret);
        
        $c1 = strtoupper(substr($rh, 0, 5));
        $c2 = strtoupper(substr($rh, 5, 5));
        $c3 = strtoupper(substr($rh, 10, 5));
        $c4 = strtoupper(substr($rh, 15, 5));
        $c5 = strtoupper(substr($rh, 20, 5));

        return "LIC-{$c1}-{$c2}-{$c3}-{$c4}-{$c5}";
    }

    /**
     * Validate license status with Central Master Server
     */
    public static function validate(): bool
    {
        $currentHost = self::getCurrentHost();
        $configuredKey = env('SYSTEM_LICENSE_KEY');

        if (empty($configuredKey)) {
            return false;
        }

        return self::performCheck($currentHost, $configuredKey);
    }

    /**
     * Perform actual HTTPS / Local Cryptographic Verification
     */
    private static function performCheck(string $currentHost, string $configuredKey): bool
    {
        try {
            // Query Master Licensing Central Server at ustb.edu.pk/ac.php
            $response = Http::timeout(4)
                ->withoutVerifying()
                ->get(self::$authApiUrl, [
                    'action' => 'verify',
                    'domain' => $currentHost,
                    'key'    => trim($configuredKey)
                ]);

            if ($response->successful()) {
                $data = $response->json();
                // CENTRAL SERVER VERDICT IS FINAL (APPROVED = true, BLOCKED/REJECTED = false)
                return isset($data['status']) && $data['status'] === 'APPROVED';
            }
        } catch (\Throwable $e) {
            // Only fallback if master server network is completely offline/unreachable
            $expectedKey = self::generateKeyForDomain($currentHost);
            return hash_equals($expectedKey, trim($configuredKey));
        }

        return false;
    }
}
