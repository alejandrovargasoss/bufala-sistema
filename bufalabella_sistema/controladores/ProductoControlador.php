<?php
require_once __DIR__ . '/../modelos/ProductoModelo.php';

class ProductoControlador {
    private $modelo;

    public function __construct($bd) {
        $this->modelo = new ProductoModelo($bd);
    }

    public function listar() {
        header('Content-Type: text/html; charset=utf-8');
        require_once __DIR__ . '/../vistas/layout/header.php';
        require_once __DIR__ . '/../vistas/productos/lista.php';
        require_once __DIR__ . '/../vistas/layout/footer.php';
    }

    public function obtenerTodos() {
        $datos = $this->modelo->obtenerTodos();
        echo json_encode(['ok' => true, 'datos' => $datos, 'total' => count($datos)]);
    }

    public function guardar() {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty(trim($d['nombre'] ?? ''))) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Nombre requerido']);
            return;
        }
        if (floatval($d['precio'] ?? 0) <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'El precio debe ser mayor a 0']);
            return;
        }
        if (intval($d['stock'] ?? 0) < 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'El stock no puede ser negativo']);
            return;
        }
        $ok = $this->modelo->crear($d);
        http_response_code($ok ? 201 : 500);
        echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Producto registrado' : 'Error al guardar producto']);
    }

    public function actualizar() {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($d['id']) || empty(trim($d['nombre'] ?? ''))) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Datos incompletos']);
            return;
        }
        if (floatval($d['precio'] ?? 0) <= 0 || intval($d['stock'] ?? 0) < 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Precio debe ser > 0 y stock >= 0']);
            return;
        }
        $ok = $this->modelo->actualizar($d);
        echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Producto actualizado' : 'Error al actualizar']);
    }

    public function eliminar() {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'ID requerido']);
            return;
        }
        $ok = $this->modelo->eliminar($id);
        echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Producto eliminado' : 'Error al eliminar']);
    }

    public function categorias() {
        echo json_encode(['ok' => true, 'datos' => $this->modelo->obtenerCategorias()]);
    }
}
