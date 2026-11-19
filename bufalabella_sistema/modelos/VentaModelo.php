<?php
class VentaModelo {
    private $bd;
    public function __construct($bd) { $this->bd = $bd; }

    public function obtenerTodos() {
        $res = $this->bd->query(
            "SELECT v.*, CONCAT(c.nombre, ' ', c.apellido) AS cliente
             FROM ventas v
             LEFT JOIN clientes c ON v.id_cliente = c.id
             ORDER BY v.fecha DESC"
        );
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerDetalle($idVenta) {
        $stmt = $this->bd->prepare(
            "SELECT dv.*, p.nombre AS producto, p.marca
             FROM detalle_ventas dv
             JOIN productos p ON dv.id_producto = p.id
             WHERE dv.id_venta = ?"
        );
        $stmt->bind_param('i', $idVenta);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function crear($d) {
        $this->bd->begin_transaction();
        try {
            $idCliente = intval($d['id_cliente']);
            $estado    = in_array($d['estado'] ?? '', ['pendiente', 'completada']) ? $d['estado'] : 'completada';
            $obs       = trim($d['observacion'] ?? '');
            $items     = $d['items'];

            $totalCalculado = 0;
            $itemsValidados = [];

            // Validar stock y precios actuales de cada producto
            $stmtProd = $this->bd->prepare("SELECT id, nombre, precio, stock FROM productos WHERE id = ? AND estado = 'activo' FOR UPDATE");
            foreach ($items as $it) {
                $idProd = intval($it['id_producto'] ?? 0);
                $cant   = intval($it['cantidad'] ?? 0);
                if ($idProd <= 0 || $cant <= 0) continue;

                $stmtProd->bind_param('i', $idProd);
                $stmtProd->execute();
                $prod = $stmtProd->get_result()->fetch_assoc();

                if (!$prod) {
                    throw new Exception("Producto ID #$idProd no disponible.");
                }
                if ($prod['stock'] < $cant) {
                    throw new Exception("Stock insuficiente para '{$prod['nombre']}' (disponible: {$prod['stock']}).");
                }

                $precioUnit = floatval($prod['precio']);
                $subtotal   = $precioUnit * $cant;
                $totalCalculado += $subtotal;

                $itemsValidados[] = [
                    'id_producto' => $idProd,
                    'cantidad'    => $cant,
                    'precio_unit' => $precioUnit,
                ];
            }

            if (empty($itemsValidados)) {
                throw new Exception("No hay productos válidos en la venta.");
            }

            // Insertar cabecera de venta
            $stmtVenta = $this->bd->prepare(
                "INSERT INTO ventas (id_cliente, total, estado, observacion) VALUES (?, ?, ?, ?)"
            );
            $stmtVenta->bind_param('idss', $idCliente, $totalCalculado, $estado, $obs);
            if (!$stmtVenta->execute()) {
                throw new Exception("Error al crear el encabezado de la venta.");
            }
            $idVenta = $this->bd->insert_id;

            // Insertar detalles y descontar stock
            $stmtDet   = $this->bd->prepare("INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unit) VALUES (?, ?, ?, ?)");
            $stmtStock = $this->bd->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");

            foreach ($itemsValidados as $iv) {
                $stmtDet->bind_param('iiid', $idVenta, $iv['id_producto'], $iv['cantidad'], $iv['precio_unit']);
                if (!$stmtDet->execute()) {
                    throw new Exception("Error al guardar detalle de producto.");
                }
                $stmtStock->bind_param('ii', $iv['cantidad'], $iv['id_producto']);
                if (!$stmtStock->execute()) {
                    throw new Exception("Error al descontar inventario.");
                }
            }

            $this->bd->commit();
            return ['ok' => true, 'id_venta' => $idVenta];
        } catch (Exception $e) {
            $this->bd->rollback();
            return ['ok' => false, 'mensaje' => $e->getMessage()];
        }
    }

    public function cambiarEstado($id, $nuevoEstado) {
        $stmt = $this->bd->prepare("UPDATE ventas SET estado = ? WHERE id = ? AND estado != 'cancelada'");
        $stmt->bind_param('si', $nuevoEstado, $id);
        return $stmt->execute();
    }

    public function cancelar($id) {
        $this->bd->begin_transaction();
        try {
            // Verificar que no esté ya cancelada
            $stmtCheck = $this->bd->prepare("SELECT estado FROM ventas WHERE id = ? FOR UPDATE");
            $stmtCheck->bind_param('i', $id);
            $stmtCheck->execute();
            $venta = $stmtCheck->get_result()->fetch_assoc();
            if (!$venta || $venta['estado'] === 'cancelada') {
                $this->bd->rollback();
                return false;
            }

            // Devolver stock de los productos vendidos
            $detalles = $this->obtenerDetalle($id);
            $stmtStock = $this->bd->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
            foreach ($detalles as $det) {
                $stmtStock->bind_param('ii', $det['cantidad'], $det['id_producto']);
                $stmtStock->execute();
            }

            $stmtCancel = $this->bd->prepare("UPDATE ventas SET estado = 'cancelada' WHERE id = ?");
            $stmtCancel->bind_param('i', $id);
            $stmtCancel->execute();

            $this->bd->commit();
            return true;
        } catch (Exception $e) {
            $this->bd->rollback();
            return false;
        }
    }
}
