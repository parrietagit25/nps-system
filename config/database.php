<?php
// Cargar variables de entorno desde .env
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar el archivo .env si existe
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Database configuration desde variables de entorno
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost:3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'nps_system');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// SendGrid configuration desde variables de entorno
define('SENDGRID_API_KEY', $_ENV['SENDGRID_API_KEY'] ?? '');
define('SENDGRID_FROM_EMAIL', $_ENV['SENDGRID_FROM_EMAIL'] ?? 'noreply@example.com');
define('SENDGRID_FROM_NAME', $_ENV['SENDGRID_FROM_NAME'] ?? 'NPS System');

// Application configuration desde variables de entorno
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/nps');
define('APP_NAME', $_ENV['APP_NAME'] ?? 'NPS System');

// Configuración de seguridad desde variables de entorno
define('APP_SECRET', $_ENV['APP_SECRET'] ?? 'default-secret-change-in-production');
define('SESSION_SECRET', $_ENV['SESSION_SECRET'] ?? 'default-session-secret-change-in-production');

// Configuración de entorno
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('DEBUG', $_ENV['DEBUG'] ?? 'false');

// Database connection class
class Database {
    private $connection;
    
    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                )
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}