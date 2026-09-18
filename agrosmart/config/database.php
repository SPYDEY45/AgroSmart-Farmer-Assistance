<?php
/**
 * AgroSmart - Smart Agriculture Assistant & Farmer Market Portal
 * BCA Field Project Database Configuration File
 *
 * Uses PHP PDO for secure, prepared SQL execution.
 */

// Start session securely if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials (Configure for your local XAMPP / WAMP environment)
define('DB_HOST', 'localhost');
define('DB_NAME', 'agrosmart');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');

// Weather API Configuration
// Can use OpenWeatherMap or WeatherAPI.com (Leave empty to use educational demonstration advisory mode)
define('WEATHER_API_KEY', ''); 
define('WEATHER_DEFAULT_CITY', 'Pune');

// Site URL Root (Adjust if running in a custom directory)
define('BASE_URL', '/agrosmart');

// Language default
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

/**
 * Get Database Connection
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // User-friendly error message without leaking sensitive credentials
            error_log("AgroSmart Database Connection Error: " . $e->getMessage());
            die("<div style='font-family:sans-serif; max-width:600px; margin:50px auto; padding:20px; border:1px solid #f5c6cb; background:#f8d7da; color:#721c24; border-radius:8px;'>
                <h3 style='margin-top:0;'>⚠️ Database Connection Notice</h3>
                <p>Unable to connect to the <strong>agrosmart</strong> database.</p>
                <p><strong>BCA Project Quick-Fix Guide:</strong></p>
                <ol style='line-height:1.6;'>
                    <li>Open XAMPP Control Panel and ensure <strong>Apache</strong> and <strong>MySQL</strong> are running.</li>
                    <li>Go to <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>
                    <li>Create database named <code>agrosmart</code>.</li>
                    <li>Import the file <code>database/agrosmart.sql</code>.</li>
                    <li>Check <code>config/database.php</code> credentials if you have set a MySQL root password.</li>
                </ol>
            </div>");
        }
    }
    return $pdo;
}
