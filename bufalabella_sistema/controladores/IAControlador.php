<?php
/**
 * IAControlador.php - Inteligencia Artificial con Gemini API
 * Bufalabella S.A.S. (Dibufala) - El Rosal, Cundinamarca
 */

class IAControlador {
    private $bd;

    public function __construct($bd) {
        $this->bd = $bd;
    }

    private function llamarGemini($prompt) {
        $apiKey = getenv('GEMINI_API_KEY') ?: '';
        if ($apiKey === '' || str_starts_with($apiKey, 'REEMPLAZA')) {
            return null;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . urlencode($apiKey);
        $payload = json_encode([
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ]);

        // Usar cURL si está disponible, o file_get_contents como respaldo
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 12,
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code !== 200 || !$resp) return null;
        } else {
            $ctx = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/json\r\n",
                    'content' => $payload,
                    'timeout' => 12,
                ]
            ]);
            $resp = @file_get_contents($url, false, $ctx);
            if (!$resp) return null;
        }

        $data  = json_decode($resp, true);
        $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        return $texto ? trim($texto) : null;
    }

    /** Sugerencias de productos lácteos para un cliente según su historial */
    public function sugerirProductos() {
        $idCliente = intval($_GET['id'] ?? 0);
        if (!$idCliente) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'ID de cliente requerido']);
            return;
        }

        $stmt = $this->bd->prepare(
            "SELECT p.nombre FROM detalle_ventas dv
             JOIN ventas v    ON dv.id_venta    = v.id
             JOIN productos p ON dv.id_producto = p.id
             WHERE v.id_cliente = ? AND v.estado != 'cancelada'
             ORDER BY v.fecha DESC LIMIT 10"
        );
        $stmt->bind_param('i', $idCliente);
        $stmt->execute();
        $historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($historial)) {
            echo json_encode([
                'ok'      => true,
                'datos'   => ['sugerencias' => ['Burrata de Búfala Artesanal 250g', 'Bocconcini de Búfala x 250g', 'Yogur Griego de Búfala Natural 500g']],
                'mensaje' => 'Sugerencias iniciales Dibufala (cliente aún sin compras)'
            ]);
            return;
        }

        $productos = implode(', ', array_unique(array_map(fn($p) => $p['nombre'], $historial)));
        $prompt    = "Eres asesor comercial de Bufalabella S.A.S. (marca Dibufala), empresa productora de lácteos artesanales 100% leche de búfala en El Rosal, Cundinamarca. Un cliente compró: $productos. " .
                     "Sugiere exactamente 3 productos lácteos de búfala complementarios (como burrata, bocconcini, mozzarella fior di latte, ricotta, yogur griego o mantequilla de búfala) que le podrían interesar. " .
                     'Responde ÚNICAMENTE con un objeto JSON válido con este formato: {"sugerencias":["producto 1","producto 2","producto 3"]}';

        $texto = $this->llamarGemini($prompt);
        if ($texto) {
            $limpio = preg_replace('/```json|```/', '', $texto);
            $sugs   = json_decode(trim($limpio), true);
            if (!empty($sugs['sugerencias'])) {
                echo json_encode([
                    'ok'      => true,
                    'datos'   => $sugs,
                    'mensaje' => 'Sugerencias generadas por IA Gemini'
                ]);
                return;
            }
        }

        echo json_encode([
            'ok'      => true,
            'datos'   => ['sugerencias' => ['Burrata de Búfala Artesanal 250g', 'Queso Ricotta de Búfala 300g', 'Mantequilla de Búfala Artesanal 250g']],
            'mensaje' => 'Sugerencias inteligentes de respaldo'
        ]);
    }

    /** Generar descripción comercial automática para un producto lácteo */
    public function generarDescripcion() {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        $nombre = trim($d['nombre'] ?? '');
        $marca  = trim($d['marca'] ?? 'Dibufala');

        if ($nombre === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Escribe el nombre del producto primero']);
            return;
        }

        $prompt = "Redacta una descripción comercial corta (máximo 2 líneas, en español, apetitosa y profesional) para vender el producto lácteo de búfala: '$nombre' de la marca '$marca' (Bufalabella S.A.S., El Rosal, Cundinamarca). Devuelve solo el texto de la descripción sin comillas ni encabezados.";
        $texto  = $this->llamarGemini($prompt);

        if ($texto) {
            echo json_encode(['ok' => true, 'descripcion' => $texto]);
        } else {
            $marcaTxt = $marca ? " de nuestra línea artesanal $marca" : "";
            echo json_encode([
                'ok' => true,
                'descripcion' => "$nombre$marcaTxt, elaborado en El Rosal con leche 100% de búfala fresca, de textura cremosa y alto valor nutricional."
            ]);
        }
    }

    /** Predicción de agotamiento de stock basada en velocidad de ventas */
    public function predecirAgotamiento() {
        $sql = "SELECT p.id, p.nombre, p.stock, p.marca,
                       COALESCE(SUM(dv.cantidad), 0) AS ventas_30d,
                       ROUND(p.stock / NULLIF(COALESCE(SUM(dv.cantidad), 0) / 30, 0), 1) AS dias_restantes
                FROM productos p
                LEFT JOIN detalle_ventas dv ON p.id = dv.id_producto
                LEFT JOIN ventas v ON dv.id_venta = v.id
                    AND v.estado != 'cancelada'
                    AND v.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                WHERE p.estado = 'activo'
                GROUP BY p.id, p.nombre, p.stock, p.marca
                HAVING ventas_30d > 0
                ORDER BY dias_restantes ASC LIMIT 5";
        $res   = $this->bd->query($sql);
        $datos = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        $pred = array_map(function($p) {
            $dias = floatval($p['dias_restantes']);
            return array_merge($p, [
                'dias_restantes' => $dias,
                'alerta' => $dias <= 7  ? 'CRÍTICO'    : ($dias <= 15 ? 'PRECAUCIÓN' : 'NORMAL'),
                'color'  => $dias <= 7  ? 'danger'     : ($dias <= 15 ? 'warning'    : 'success'),
            ]);
        }, $datos);

        echo json_encode(['ok' => true, 'datos' => $pred, 'mensaje' => 'Predicción calculada']);
    }

    /** Resumen inteligente del negocio para el dashboard */
    public function resumenNegocio() {
        $mesActual = $this->bd->query(
            "SELECT COALESCE(SUM(total), 0) AS t FROM ventas
             WHERE YEAR(fecha)=YEAR(NOW()) AND MONTH(fecha)=MONTH(NOW()) AND estado='completada'"
        )->fetch_assoc()['t'] ?? 0;

        $mesAnterior = $this->bd->query(
            "SELECT COALESCE(SUM(total), 0) AS t FROM ventas
             WHERE YEAR(fecha)=YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))
               AND MONTH(fecha)=MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))
               AND estado='completada'"
        )->fetch_assoc()['t'] ?? 0;

        $crec = $mesAnterior > 0
            ? round((($mesActual - $mesAnterior) / $mesAnterior) * 100, 1)
            : ($mesActual > 0 ? 100 : 0);

        $consejoDefecto = $crec < 0
            ? 'Las ventas bajaron respecto al mes anterior. Considera impulsar combos de burrata, yogur griego y quesos frescos en restaurantes.'
            : 'Excelente rotación de lácteos Dibufala. Asegura la producción fresca en planta para los productos de mayor demanda.';

        // Intentar enriquecer consejo con Gemini si hay API Key configurada
        $prompt = "Actúa como analista comercial para Bufalabella S.A.S. (lácteos Dibufala en El Rosal, Cundinamarca). Ventas mes actual: $$mesActual COP. Ventas mes anterior: $$mesAnterior COP. Variación: $crec%. Dame un consejo práctico de 1 sola oración (máximo 25 palabras) para optimizar ventas o producción láctea.";
        $consejoIA = $this->llamarGemini($prompt) ?: $consejoDefecto;

        echo json_encode([
            'ok'    => true,
            'datos' => [
                'ventas_mes'      => floatval($mesActual),
                'mes_anterior'    => floatval($mesAnterior),
                'crecimiento_pct' => $crec,
                'tendencia'       => $crec >= 0 ? 'subiendo' : 'bajando',
                'emoji'           => $crec >= 0 ? '📈' : '📉',
                'consejo_ia'      => $consejoIA,
            ],
            'mensaje' => 'Resumen generado'
        ]);
    }
}
