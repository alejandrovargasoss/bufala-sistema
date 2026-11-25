<?php
/**
 * index.php - Front Controller Dibufala S.A.S. (Único punto de entrada MVC)
 */

if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'http') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit();
}

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

// Verificar sesión activa
if (empty($_SESSION['usuario_id'])) {
    $esApi = !in_array($accion, ['listar', 'dashboard']) && $modulo !== 'dashboard';
    if ($esApi) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['ok' => false, 'mensaje' => 'Sesión expirada. Inicia sesión nuevamente.']);
        exit;
    }
    header('Location: ' . BASE_URL . '/index.php?modulo=auth&accion=loginForm');
    exit;
}

$rolUsuario = $_SESSION['usuario_rol'] ?? 'cliente';

// Restricciones para cuentas 'cliente': solo investigar/consultar productos y realizar compras
$accionesAdminBloqueadas = ['guardar', 'actualizar', 'eliminar', 'editar', 'crear', 'cancelar', 'anular'];
if ($rolUsuario !== 'admin') {
    if ($modulo === 'clientes' || $modulo === 'reportes') {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(['ok' => false, 'mensaje' => 'Acceso restringido: solo disponible para el administrador']);
        exit;
    }
    if ($modulo === 'productos' && in_array($accion, $accionesAdminBloqueadas, true)) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(['ok' => false, 'mensaje' => 'Como cliente solo puedes consultar e investigar productos']);
        exit;
    }
}

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