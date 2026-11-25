// Garantizar catálogo base de Dibufala S.A.S. y clientes si la tabla está vacía
try {
    $resCat = $conexion->query("SELECT COUNT(*) AS total FROM categorias");
    if ($resCat && (int)$resCat->fetch_assoc()['total'] === 0) {
        $conexion->query("INSERT INTO categorias (id, nombre, descripcion) VALUES
            (1, 'Quesos Frescos y Hilados', 'Mozzarella, Burrata, Stracciatella, Bocconcini, Ovoline y Ciliegine 100% leche de búfala'),
            (2, 'Quesos Madurados y Untables', 'Ricotta artesanal y queso campesino de búfala'),
            (3, 'Yogures y Bebidas Lácteas', 'Yogur griego y bebidas fermentadas de leche de búfala'),
            (4, 'Mantequillas y Cremas', 'Mantequilla artesanal y crema de leche de búfala')");
    }

    $resProd = $conexion->query("SELECT COUNT(*) AS total FROM productos");
    if ($resProd && (int)$resProd->fetch_assoc()['total'] === 0) {
        $conexion->query("INSERT INTO productos (nombre, descripcion, categoria_id, precio, stock, marca, estado) VALUES
            ('Burrata de Búfala Vaso 250g', 'Mozzarella fresca rellena de cuajada y crema suave, 100% leche de búfala de libre pastoreo', 1, 24500, 18, 'Dibufala', 'activo'),
            ('Stracciatella de Búfala 500g', 'Hilos tiernos de mozzarella artesanal mezclados con crema fresca de búfala', 1, 34900, 14, 'Dibufala', 'activo'),
            ('Bocconcini de Búfala 250g', 'Perlas de mozzarella tamaño bocado, ideales para pizzas artesanales y pasta fresca', 1, 19800, 25, 'Dibufala', 'activo'),
            ('Ovoline de Búfala 300g', 'Mozzarella ovalada ideal para tajar con tomate, albahaca, sánduches y lasagnas', 1, 22500, 16, 'Dibufala', 'activo'),
            ('Ciliegine de Búfala 250g', 'Esferas tamaño cereza 100% vegetarianas con cuajo vegetal, ideales para ensaladas', 1, 20500, 20, 'Dibufala', 'activo'),
            ('Mozzarella Fior di Latte Búfala 500g', 'Bloque hilado tradicional de textura suave y fundido perfecto, bajo en colesterol', 1, 32000, 15, 'Dibufala', 'activo'),
            ('Queso Ricotta de Búfala 300g', 'Queso fino semidulce elaborado con suero de búfala, ligero y alto en calcio', 2, 16500, 10, 'Dibufala', 'activo'),
            ('Queso Campesino de Búfala 500g', 'Queso fresco prensado de sabor lácteo limpio elaborado en El Rosal, Cundinamarca', 2, 21000, 20, 'Dibufala', 'activo'),
            ('Yogur Griego de Búfala Natural 500g', 'Extra cremoso, sin azúcares añadidos y alto contenido proteico natural', 3, 18500, 30, 'Dibufala', 'activo'),
            ('Mantequilla de Búfala Artesanal 250g', 'Mantequilla pura batida lentamente, color marfil natural y rica en Omega 3', 4, 17200, 12, 'Dibufala', 'activo')");
    }

    $resCli = $conexion->query("SELECT COUNT(*) AS total FROM clientes");
    if ($resCli && (int)$resCli->fetch_assoc()['total'] === 0) {
        $conexion->query("INSERT INTO clientes (nombre, apellido, documento, telefono, email, direccion, estado) VALUES
            ('Trattoria Bella Italia', 'S.A.S.', '900456789-1', '3104567890', 'compras@bellaitalia.com', 'Calle 93 # 13-24, Bogotá', 'activo'),
            ('Mercado Orgánico', 'Sabana', '901234567-8', '3209876543', 'pedidos@mercadosabana.co', 'Km 15 Autopista Medellín, El Rosal', 'activo'),
            ('Hotel Boutique', 'La Pradera', '830112233-4', '3157894561', 'alimentos@hotellapradera.com', 'Vereda El Rodeo, El Rosal, Cundinamarca', 'activo'),
            ('Pizzería Artesanal', 'El Rosal', '900778899-2', '3185712967', 'pedidos@pizzeriaelrosal.com', 'Parque Principal, El Rosal, Cundinamarca', 'activo')");
    }
} catch (Throwable $e) {
    // Continuar normalmente si aún no se han creado las tablas
}