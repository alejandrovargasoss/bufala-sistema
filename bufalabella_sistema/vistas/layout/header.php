<?php $moduloActivo = $_GET['modulo'] ?? 'dashboard'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DIBUFALA S.A.S. · Planta El Rosal, Cundinamarca</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/publico/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script>
    window.APP_BASE_URL = <?= json_encode(BASE_URL) ?>;
  </script>
</head>
<body>
<div class="app-shell">
  <header class="top-navbar">
    <div class="brand-box">
      <div class="brand-icon">🐃</div>
      <div class="brand-text">
        <h2>DIBUFALA S.A.S.</h2>
        <span>El Rosal · Cundinamarca</span>
      </div>
    </div>

    <nav class="nav-pills">
      <a href="<?= BASE_URL ?>/index.php" class="<?= $moduloActivo==='dashboard'?'active':'' ?>">
        <i class="bi bi-speedometer2"></i> Inicio
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="<?= $moduloActivo==='ventas'?'active':'' ?>">
        <i class="bi bi-receipt-cutoff"></i> Ventas y Facturas
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="<?= $moduloActivo==='productos'?'active':'' ?>">
        <i class="bi bi-box-seam-fill"></i> Inventario Lácteo
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=clientes&accion=listar" class="<?= $moduloActivo==='clientes'?'active':'' ?>">
        <i class="bi bi-people-fill"></i> Clientes
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=reportes&accion=dashboard" class="<?= $moduloActivo==='reportes'?'active':'' ?>">
        <i class="bi bi-bar-chart-fill"></i> Gráficas y Reportes
      </a>
    </nav>

    <div class="nav-actions">
      <span class="cold-chain-pill" title="Temperatura Cava Principal El Rosal">
        <i class="bi bi-thermometer-snow"></i> Cava: 3.1°C
      </span>
      <span class="user-chip">
        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin') ?>
      </span>
      <a href="<?= BASE_URL ?>/index.php?modulo=auth&accion=logout" class="btn btn-rojo btn-sm" title="Salir del sistema">
        <i class="bi bi-box-arrow-right"></i> Salir
      </a>
    </div>
  </header>

  <!-- BARRA DE ACCESOS RÁPIDOS INTUITIVOS -->
  <div class="quick-bar">
    <div>
      <h1>
        <?php
        $titulos = [
          'dashboard' => 'Panel Principal · Dibufala S.A.S.',
          'productos' => 'Inventario de Productos de Búfala',
          'clientes'  => 'Directorio de Clientes y Aliados',
          'ventas'    => 'Facturación y Rutas Refrigeradas',
          'reportes'  => 'Gráficas de Rendimiento Comercial',
        ];
        echo $titulos[$moduloActivo] ?? 'Dibufala S.A.S.';
        ?>
      </h1>
      <p>Planta Principal El Rosal, Cundinamarca · NIT 900.845.219-7</p>
    </div>

    <div class="quick-buttons">
      <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="btn btn-primary">
        <i class="bi bi-cart-plus-fill"></i> Registrar Venta
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="btn btn-dark">
        <i class="bi bi-plus-circle-fill"></i> Gestionar Stock
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=clientes&accion=listar" class="btn btn-secondary">
        <i class="bi bi-person-plus-fill"></i> Clientes
      </a>
    </div>
  </div>

  <main class="content-area"></main>