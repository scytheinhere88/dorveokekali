<?php
/**
 * Biteship Configuration Helper
 * Loads settings from database
 */

class BiteshipConfig {
    private static $config = null;
    
    public static function load() {
        if (self::$config !== null) {
            return self::$config;
        }

        global $pdo;
        $settings = [];

        try {
            $stmt = $pdo->query("SELECT * FROM payment_gateway_settings WHERE gateway_name = 'biteship'");
            $biteship = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($biteship) {
                $settings['biteship_api_key'] = $biteship['api_key'] ?? '';
                $settings['biteship_environment'] = $biteship['is_production'] ? 'production' : 'sandbox';
            }
        } catch (Exception $e) {
            // Gateway settings not available
        }

        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'store_%' OR setting_key LIKE 'biteship_%'");
            $systemSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $settings = array_merge($settings, $systemSettings);
        } catch (Exception $e) {
            // System settings not available
        }

        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'store_%'");
            $siteSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $settings = array_merge($settings, $siteSettings);
        } catch (Exception $e) {
            // Site settings not available
        }

        self::$config = [
            'api_key' => $settings['biteship_api_key'] ?? '',
            'environment' => $settings['biteship_environment'] ?? 'sandbox',
            'webhook_secret' => $settings['biteship_webhook_secret'] ?? '',
            'base_url' => ($settings['biteship_environment'] ?? 'sandbox') === 'production'
                ? 'https://api.biteship.com/v1'
                : 'https://api-sandbox.biteship.com/v1',
            'store' => [
                'name' => $settings['store_name'] ?? 'Dorve.id Official Store',
                'phone' => $settings['store_phone'] ?? '+62-813-7737-8859',
                'address' => $settings['store_address'] ?? '',
                'city' => $settings['store_city'] ?? '',
                'province' => $settings['store_province'] ?? '',
                'postal_code' => $settings['store_postal_code'] ?? '',
                'country' => $settings['store_country'] ?? 'ID'
            ]
        ];

        return self::$config;
    }
    
    public static function get($key, $default = null) {
        $config = self::load();
        return $config[$key] ?? $default;
    }
    
    public static function getStore($key, $default = null) {
        $config = self::load();
        return $config['store'][$key] ?? $default;
    }
}
