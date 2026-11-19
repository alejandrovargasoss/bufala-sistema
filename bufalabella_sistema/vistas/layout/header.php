<?php $moduloActivo = $_GET['modulo'] ?? 'dashboard'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bufalabella S.A.S. | Dibufala - Gestión Comercial</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/publico/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script>
    window.APP_BASE_URL = <?= json_encode(BASE_URL) ?>;
  </script>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">
    <h2>🐃 Bufalabella</h2>
    <span class="ia-badge">✨ Lácteos Dibufala · IA</span>
    <span>Planta El Rosal, Cundinamarca</span>
  </div>
  <nav>
    <a href="<?= BASE_URL ?>/index.php" class="<?= $moduloActivo==='dashboard'?'active':'' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard + IA
    </a>
    <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="<?= $moduloActivo==='productos'?'active':'' ?>">
      <i class="bi bi-box-seam"></i> Productos Lácteos
    </a>
    <a href="<?= BASE_URL ?>/index.php?modulo=clientes&accion=listar" class="<?= $moduloActivo==='clientes'?'active':'' ?>">
      <i class="bi bi-people"></i> Clientes + IA
    </a>
    <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="<?= $moduloActivo==='ventas'?'active':'' ?>">
      <i class="bi bi-receipt"></i> Pedidos y Ventas
    </a>
    <a href="<?= BASE_URL ?>/index.php?modulo=reportes&accion=dashboard" class="<?= $moduloActivo==='reportes'?'active':'' ?>">
      <i class="bi bi-bar-chart-line"></i> Reportes
    </a>
  </nav>
  <div class="sidebar-footer">
    <strong style="color:#F3D37A;">🤖 IA: Gemini 2.5 Flash</strong><br>
    <strong style="color:rgba(255,255,255,0.65)">Bufalabella S.A.S. · El Rosal</strong><br>
    <a href="<?= BASE_URL ?>/index.php?modulo=auth&accion=logout" style="color:#FCA5A5;text-decoration:none;display:inline-block;margin-top:8px;">
      <i class="bi bi-box-arrow-right"></i> Cerrar sesión
    </a>
  </div>
</aside>

<div class="main-wrapper">
  <header class="topbar">
    <h1>
      <?php
      $titulos = [
        'dashboard' => '📊 Panel Principal · Bufalabella S.A.S.',
        'productos' => '🧀 Catálogo e Inventario Lácteo',
        'clientes'  => '👥 Clientes y Distribuidores + IA',
        'ventas'    => '🛒 Gestión de Pedidos y Ventas',
        'reportes'  => '📈 Reportes Comerciales Dibufala',
      ];
      echo $titulos[$moduloActivo] ?? 'Bufalabella S.A.S.';
      ?>
    </h1>
    <div class="topbar-right">
      <span class="badge-ia">✨ Dibufala IA</span>
      <span class="topbar-user"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
      <a href="<?= BASE_URL ?>/index.php?modulo=auth&accion=logout" class="btn btn-secondary btn-sm" title="Cerrar sesión">
        <i class="bi bi-box-arrow-right"></i>
      </a>
    </div>
  </header>
  <main class="content">
