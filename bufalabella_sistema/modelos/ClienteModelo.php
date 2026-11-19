<?php
class ClienteModelo {
    private $bd;
    public function __construct($bd) { $this->bd = $bd; }

    public function obtenerTodos() {
        $res = $this->bd->query("SELECT * FROM clientes WHERE estado='activo' ORDER BY nombre ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerPorId($id) {
        $stmt = $this->bd->prepare("SELECT * FROM clientes WHERE id=? AND estado='activo'");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crear($d) {
        $stmt = $this->bd->prepare(
            "INSERT INTO clientes (nombre, apellido, documento, telefono, email, direccion, estado)
             VALUES (?, ?, ?, ?, ?, ?, 'activo')"
        );
        $nombre    = trim($d['nombre']);
        $apellido  = trim($d['apellido']);
        $documento = trim($d['documento']);
        $telefono  = trim($d['telefono'] ?? '');
        $email     = trim($d['email'] ?? '') ?: null;
        $direccion = trim($d['direccion'] ?? '');

        $stmt->bind_param('ssssss', $nombre, $apellido, $documento, $telefono, $email, $direccion);
        return $stmt->execute();
    }

    public function actualizar($d) {
        $stmt = $this->bd->prepare(
            "UPDATE clientes SET nombre=?, apellido=?, documento=?, telefono=?, email=?, direccion=?, estado=? WHERE id=?"
        );
        $nombre    = trim($d['nombre']);
        $apellido  = trim($d['apellido']);
        $documento = trim($d['documento']);
        $telefono  = trim($d['telefono'] ?? '');
        $email     = trim($d['email'] ?? '') ?: null;
        $direccion = trim($d['direccion'] ?? '');
        $estado    = $d['estado'] ?? 'activo';
        $id        = intval($d['id']);

        $stmt->bind_param('sssssssi', $nombre, $apellido, $documento, $telefono, $email, $direccion, $estado, $id);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $stmt = $this->bd->prepare("UPDATE clientes SET estado='inactivo' WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
