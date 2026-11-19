<?php
class ProductoModelo {
    private $bd;
    public function __construct($bd) { $this->bd = $bd; }

    public function obtenerTodos() {
        $sql = "SELECT p.*, c.nombre AS categoria
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.estado = 'activo' ORDER BY p.nombre ASC";
        $res = $this->bd->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerPorId($id) {
        $stmt = $this->bd->prepare("SELECT * FROM productos WHERE id=? AND estado='activo'");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crear($d) {
        $stmt = $this->bd->prepare(
            "INSERT INTO productos (nombre, descripcion, precio, stock, marca, categoria_id, estado)
             VALUES (?, ?, ?, ?, ?, ?, 'activo')"
        );
        $nombre = trim($d['nombre']);
        $desc   = trim($d['descripcion'] ?? '');
        $precio = floatval($d['precio']);
        $stock  = intval($d['stock'] ?? 0);
        $marca  = trim($d['marca'] ?? '');
        $catId  = !empty($d['categoria_id']) ? intval($d['categoria_id']) : null;

        $stmt->bind_param('ssdisi', $nombre, $desc, $precio, $stock, $marca, $catId);
        return $stmt->execute();
    }

    public function actualizar($d) {
        $stmt = $this->bd->prepare(
            "UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, marca=?, categoria_id=? WHERE id=?"
        );
        $nombre = trim($d['nombre']);
        $desc   = trim($d['descripcion'] ?? '');
        $precio = floatval($d['precio']);
        $stock  = intval($d['stock'] ?? 0);
        $marca  = trim($d['marca'] ?? '');
        $catId  = !empty($d['categoria_id']) ? intval($d['categoria_id']) : null;
        $id     = intval($d['id']);

        $stmt->bind_param('ssdisii', $nombre, $desc, $precio, $stock, $marca, $catId, $id);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $stmt = $this->bd->prepare("UPDATE productos SET estado='inactivo' WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function obtenerCategorias() {
        $res = $this->bd->query("SELECT * FROM categorias ORDER BY nombre ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}
