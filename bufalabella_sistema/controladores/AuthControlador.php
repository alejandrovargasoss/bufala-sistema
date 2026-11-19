<?php
class AuthControlador {
    private $bd;

    public function __construct($bd) {
        $this->bd = $bd;
    }

    public function loginForm() {
        if (!empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
        header('Content-Type: text/html; charset=utf-8');
        require_once __DIR__ . '/../vistas/auth/login.php';
    }

    public function login() {
        header('Content-Type: application/json; charset=utf-8');
        $d = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $email = trim($d['email'] ?? '');
        $password = $d['password'] ?? '';

        if ($email === '' || $password === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Correo y contraseña requeridos']);
            return;
        }

        $stmt = $this->bd->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? AND estado = 'activo' LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['usuario_id']     = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            $_SESSION['usuario_rol']    = $user['rol'];
            Logger::info('Inicio de sesión exitoso', ['email' => $email]);
            echo json_encode(['ok' => true, 'mensaje' => 'Bienvenido ' . $user['nombre']]);
        } else {
            Logger::warn('Intento fallido de inicio de sesión', ['email' => $email]);
            http_response_code(401);
            echo json_encode(['ok' => false, 'mensaje' => 'Credenciales incorrectas']);
        }
    }

    public function registrar() {
        header('Content-Type: application/json; charset=utf-8');
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        $nombre   = trim($d['nombre'] ?? '');
        $email    = trim($d['email'] ?? '');
        $password = $d['password'] ?? '';

        if ($nombre === '' || $email === '' || strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Nombre, correo válido y contraseña (mín. 6 caracteres) son requeridos']);
            return;
        }

        $check = $this->bd->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'mensaje' => 'Este correo ya está registrado']);
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $rol  = 'vendedor';
        $stmt = $this->bd->prepare("INSERT INTO usuarios (nombre, email, password, rol, estado) VALUES (?, ?, ?, ?, 'activo')");
        $stmt->bind_param('ssss', $nombre, $email, $hash, $rol);

        if ($stmt->execute()) {
            $_SESSION['usuario_id']     = $this->bd->insert_id;
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_rol']    = $rol;
            Logger::info('Nuevo usuario registrado', ['email' => $email]);
            http_response_code(201);
            echo json_encode(['ok' => true, 'mensaje' => 'Cuenta creada exitosamente']);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'mensaje' => 'No se pudo crear la cuenta']);
        }
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?modulo=auth&accion=loginForm');
        exit;
    }
}
