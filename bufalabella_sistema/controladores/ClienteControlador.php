<?php
require_once __DIR__ . '/../modelos/ClienteModelo.php';

class ClienteControlador {
    private $modelo;

    public function __construct($bd) {
        $this->modelo = new ClienteModelo($bd);
    }

    public function listar() {
        header('Content-Type: text/html; charset=utf-8');
        require_once __DIR__ . '/../vistas/layout/header.php';
        require_once __DIR__ . '/../vistas/clientes/lista.php';
        require_once __DIR__ . '/../vistas/layout/footer.php';
    }

    public function obtenerTodos() {
        $datos = $this->modelo->obtenerTodos();
        echo json_encode(['ok' => true, 'datos' => $datos, 'total' => count($datos)]);
    }

    public function guardar() {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty(trim($d['nombre'] ?? '')) || empty(trim($d['apellido'] ?? '')) || empty(trim($d['documento'] ?? ''))) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Nombre, apellido y documento son requeridos']);
            return;
        }
        $ok = $this->modelo->crear($d);
        http_response_code($ok ? 201 : 500);
        echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Cliente registrado correctamente' : 'Error al guardar (verifica que el documento o email no estén duplicados)']);
    }

    public function actualizar() {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($d['id']) || empty(trim($d['nombre'] ?? '')) || empty(trim($d['documento'] ?? ''))) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Datos incompletos para actualizar']);
            return;
        }
        $ok = $this->modelo->actualizar($d);
        echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Cliente actualizado' : 'Error al actualizar']);
    }

    public function eliminar() {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'ID inválido']);
            return;
        }
        $ok = $this->modelo->eliminar($id);
        echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Cliente eliminado' : 'Error al eliminar']);
    }
}
