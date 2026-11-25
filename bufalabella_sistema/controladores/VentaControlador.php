<?php
require_once __DIR__ . '/../modelos/VentaModelo.php';

class VentaControlador {
    private $modelo;
    private $bd;

    public function __construct($bd) {
        $this->bd     = $bd;
        $this->modelo = new VentaModelo($bd);
    }

    private function esAdmin(): bool {
        return (($_SESSION['usuario_rol'] ?? 'cliente') === 'admin');
    }

    public function listar() {
        Logger::info('Vista de ventas abierta', ['usuario_id' => $_SESSION['usuario_id'] ?? 0]);
        header('Content-Type: text/html; charset=utf-8');
        require_once __DIR__ . '/../vistas/layout/header.php';
        require_once __DIR__ . '/../vistas/ventas/lista.php';
        require_once __DIR__ . '/../vistas/layout/footer.php';
    }

    public function obtenerTodos() {
        $datos = $this->modelo->obtenerTodos();
        if (!$this->esAdmin()) {
            $uidTag = '[UID:' . intval($_SESSION['usuario_id'] ?? 0) . ']';
            $datos  = array_values(array_filter($datos, function ($v) use ($uidTag) {
                return strpos($v['observacion'] ?? '', $uidTag) !== false;
            }));
        }
        echo json_encode(['ok' => true, 'datos' => $datos, 'total' => count($datos)]);
    }

    public function detalle() {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'ID de venta requerido']);
            return;
        }
        $datos = $this->modelo->obtenerDetalle($id);
        echo json_encode(['ok' => true, 'datos' => $datos]);
    }

    public function guardar() {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];

        if (!$this->esAdmin()) {
            $uid     = intval($_SESSION['usuario_id'] ?? 0);
            $uidTag  = "[UID:$uid]";
            $obsBase = trim($d['observacion'] ?? '');
            if (strpos($obsBase, $uidTag) === false) {
                $d['observacion'] = trim("$uidTag $obsBase");
            }
            if (empty($d['id_cliente'])) {
                $rowCliente = $this->bd->query("SELECT id FROM clientes WHERE estado='activo' ORDER BY id ASC LIMIT 1")->fetch_assoc();
                $d['id_cliente'] = $rowCliente['id'] ?? 1;
            }
        }

        if (empty($d['id_cliente'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Debe seleccionar un cliente']);
            return;
        }
        if (empty($d['items']) || !is_array($d['items'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Debe agregar al menos un producto a la venta']);
            return;
        }

        $res = $this->modelo->crear($d);
        if ($res['ok']) {
            Logger::info('Venta registrada', ['id_venta' => $res['id_venta'], 'usuario_id' => $_SESSION['usuario_id'] ?? 0]);
            http_response_code(201);
            echo json_encode(['ok' => true, 'id_venta' => $res['id_venta'], 'mensaje' => 'Compra registrada exitosamente']);
        } else {
            Logger::error('Error al registrar venta', ['error' => $res['mensaje']]);
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => $res['mensaje']]);
        }
    }

    public function completar() {
        if (!$this->esAdmin()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'mensaje' => 'Acción permitida solo para el administrador']);
            return;
        }
        $id = intval($_GET['id'] ?? 0);
        $ok = $this->modelo->cambiarEstado($id, 'completada');
        echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Venta marcada como completada' : 'Error al actualizar estado']);
    }

    public function cancelar() {
        if (!$this->esAdmin()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'mensaje' => 'Un cliente no puede anular ventas']);
            return;
        }
        $id = intval($_GET['id'] ?? 0);
        $ok = $this->modelo->cancelar($id);
        Logger::info('Venta cancelada', ['id_venta' => $id]);
        echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Venta cancelada y stock devuelto' : 'Error al cancelar venta']);
    }
}