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
        <span>Planta El Rosal · Cundinamarca</span>
      </div>
    </div>

    <nav class="nav-pills">
      <a href="<?= BASE_URL ?>/index.php" class="<?= $moduloActivo==='dashboard'?'active':'' ?>">
        <i class="bi bi-grid-fill"></i> Dashboard
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="<?= $moduloActivo==='productos'?'active':'' ?>">
        <i class="bi bi-box-seam-fill"></i> Cava Láctea
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=clientes&accion=listar" class="<?= $moduloActivo==='clientes'?'active':'' ?>">
        <i class="bi bi-people-fill"></i> Clientes
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="<?= $moduloActivo==='ventas'?'active':'' ?>">
        <i class="bi bi-truck"></i> Ventas & Despachos
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=reportes&accion=dashboard" class="<?= $moduloActivo==='reportes'?'active':'' ?>">
        <i class="bi bi-graph-up-arrow"></i> Analítica
      </a>
    </nav>

    <div class="nav-actions">
      <span class="cold-chain-pill" title="Sensor IoT Cava Principal El Rosal">
        <i class="bi bi-snow2"></i> Cava: 3.2°C
      </span>
      <span class="user-chip">
        <i class="bi bi-person-fill"></i> <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin') ?>
      </span>
      <a href="<?= BASE_URL ?>/index.php?modulo=auth&accion=logout" class="btn btn-danger btn-sm" title="Cerrar sesión">
        <i class="bi bi-power"></i>
      </a>
    </div>
  </header>

  <div class="page-banner">
    <div>
      <h1>
        <?php
        $titulos = [
          'dashboard' => 'Centro de Operaciones · Dibufala S.A.S.',
          'productos' => 'Inventario de Cava y Derivados de Búfala',
          'clientes'  => 'Red de Clientes y Distribuidores',
          'ventas'    => 'Facturación, Rutas y Cadena de Frío',
          'reportes'  => 'Inteligencia Comercial y Rendimiento',
        ];
        echo $titulos[$moduloActivo] ?? 'Dibufala S.A.S.';
        ?>
      </h1>
      <p>Km 2 Vía El Rosal – Subachoque, Cundinamarca · NIT 900.845.219-7 · Motor IA Activo</p>
    </div>
  </div>

  <main class="content-area"></main>