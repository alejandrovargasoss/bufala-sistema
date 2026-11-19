<?php
/**
 * index.php - Front Controller Bufalabella S.A.S. (Único punto de entrada MVC)
 * Bufalabella S.A.S. - Dibufala
 */

// Redirigir a HTTPS en producción si llega por HTTP detrás de proxy
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'http') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit();
}

// Cabeceras de seguridad
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/controladores/logger.php';
require_once __DIR__ . '/controladores/AuthControlador.php';
require_once __DIR__ . '/controladores/ProductoControlador.php';
require_once __DIR__ . '/controladores/ClienteControlador.php';
require_once __DIR__ . '/controladores/VentaControlador.php';
require_once __DIR__ . '/controladores/ReporteControlador.php';
require_once __DIR__ . '/controladores/IAControlador.php';

$modulo = $_GET['modulo'] ?? 'dashboard';
$accion = $_GET['accion'] ?? 'listar';

// Rutas públicas de autenticación
if ($modulo === 'auth') {
    $authCtrl = new AuthControlador($conexion);
    if (method_exists($authCtrl, $accion)) {
        $authCtrl->$accion();
    } else {
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'Acción de autenticación no válida']);
    }
    exit;
}

// Verificar sesión activa para proteger el sistema en línea
if (empty($_SESSION['usuario_id'])) {
    // Si es una petición API (fetch/JSON), devolver 401
    $esApi = !in_array($accion, ['listar', 'dashboard']) && $modulo !== 'dashboard';
    if ($esApi) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['ok' => false, 'mensaje' => 'Sesión expirada. Inicia sesión nuevamente.']);
        exit;
    }
    // Si es navegación HTML, redirigir al login
    header('Location: ' . BASE_URL . '/index.php?modulo=auth&accion=loginForm');
    exit;
}

// Por defecto las respuestas de acciones son JSON salvo las vistas HTML
header('Content-Type: application/json; charset=utf-8');

switch ($modulo) {
    case 'productos':
        $ctrl = new ProductoControlador($conexion);
        break;
    case 'clientes':
        $ctrl = new ClienteControlador($conexion);
        break;
    case 'ventas':
        $ctrl = new VentaControlador($conexion);
        break;
    case 'reportes':
        $ctrl = new ReporteControlador($conexion);
        break;
    case 'ia':
        $ctrl = new IAControlador($conexion);
        break;
    case 'dashboard':
        header('Content-Type: text/html; charset=utf-8');
        require_once __DIR__ . '/vistas/layout/header.php';
        require_once __DIR__ . '/vistas/dashboard.php';
        require_once __DIR__ . '/vistas/layout/footer.php';
        exit;
    default:
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'Módulo no encontrado']);
        exit;
}

if (method_exists($ctrl, $accion)) {
    $ctrl->$accion();
} else {
    http_response_code(404);
    echo json_encode([
        'ok'      => false,
        'mensaje' => "Acción '$accion' no encontrada en módulo '$modulo'",
    ]);
}
