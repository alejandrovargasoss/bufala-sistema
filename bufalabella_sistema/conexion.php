<?php
/**
 * conexion.php - Conexión MySQL + Lector .env + Cabeceras API REST
 * Bufalabella S.A.S. (Dibufala) - El Rosal, Cundinamarca
 */

// Cargar variables desde archivo .env si existe (para entorno local XAMPP/Laragon)
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lineas = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if ($linea === '' || str_starts_with($linea, '#')) continue;
        if (strpos($linea, '=') !== false) {
            list($claveEnv, $valorEnv) = explode('=', $linea, 2);
            $claveEnv = trim($claveEnv);
            $valorEnv = trim($valorEnv, " 	

");
            if (!getenv($claveEnv)) {
                putenv("$claveEnv=$valorEnv");
                $_ENV[$claveEnv] = $valorEnv;
            }
        }
    }
}

// Iniciar sesión de forma segura si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detectar ruta base dinámicamente (funciona en localhost/tecnoshop y en dominio raíz /)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim($scriptDir, '/'));

// Cabeceras CORS y seguridad básica
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Leer credenciales desde variables de entorno
$host    = getenv('DB_HOST')    ?: 'localhost';
$usuario = getenv('DB_USER')    ?: 'root';
$clave   = getenv('DB_PASS')    ?: '';
$base    = getenv('DB_NAME')    ?: 'bufalabella_db';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

mysqli_report(MYSQLI_REPORT_OFF);
$conexion = @new mysqli($host, $usuario, $clave, $base);

if ($conexion->connect_error) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Error de conexión a la base de datos: ' . $conexion->connect_error,
    ]);
    exit;
}

$conexion->set_charset($charset);
