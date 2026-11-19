<?php
// logger.php - Sistema de logs para Bufalabella S.A.S.
class Logger {
    public static function log($nivel, $mensaje, $datos = []) {
        $fecha     = date('Y-m-d H:i:s');
        $ip        = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $datos_str = empty($datos) ? '' : ' | ' . json_encode($datos, JSON_UNESCAPED_UNICODE);
        $linea     = "[{$fecha}] [{$nivel}] [{$ip}] {$mensaje}{$datos_str}";
        error_log($linea);
    }

    public static function info($msg, $d = [])  { self::log('INFO',  $msg, $d); }
    public static function error($msg, $d = []) { self::log('ERROR', $msg, $d); }
    public static function warn($msg, $d = [])  { self::log('WARN',  $msg, $d); }
}
