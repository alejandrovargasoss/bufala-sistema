<?php $moduloActivo = $_GET['modulo'] ?? 'dashboard'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dibufala S.A.S. | Quesería & Gestión Comercial · El Rosal</title>
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
    <h2>🐃 Dibufala S.A.S.</h2>
    <span class="ia-badge">✦ Lácteos de Búfala · IA</span>
    <span>Planta Principal · El Rosal, Cundinamarca</span>
  </div>
  <nav>
    <a href="<?= BASE_URL ?>/index.php" class="<?= $moduloActivo==='dashboard'?'active':'' ?>">
      <i class="bi bi-grid-1x2-fill"></i> Dashboard + IA
    </a>
    <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="<?= $moduloActivo==='productos'?'active':'' ?>">
      <i class="bi bi-box-seam-fill"></i> Productos Lácteos
    </a>
    <a href="<?= BASE_URL ?>/index.php?modulo=clientes&accion=listar" class="<?= $moduloActivo==='clientes'?'active':'' ?>">
      <i class="bi bi-people-fill"></i> Clientes + IA
    </a>
    <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="<?= $moduloActivo==='ventas'?'active':'' ?>">
      <i class="bi bi-receipt-cutoff"></i> Ventas y Facturación
    </a>
    <a href="<?= BASE_URL ?>/index.php?modulo=reportes&accion=dashboard" class="<?= $moduloActivo==='reportes'?'active':'' ?>">
      <i class="bi bi-bar-chart-line-fill"></i> Reportes
    </a>
  </nav>
  <div class="sidebar-footer">
    <strong style="color:#F6D88C;">✦ Motor IA: Gemini 2.5 Flash</strong><br>
    <strong style="color:rgba(255,255,255,0.72)">Dibufala S.A.S. · NIT 900.845.219-7</strong><br>
    <span>El Rosal, Cundinamarca · Colombia</span><br>
    <a href="<?= BASE_URL ?>/index.php?modulo=auth&accion=logout" style="color:#FCA5A5;text-decoration:none;display:inline-flex;align-items:center;gap:5px;margin-top:8px;font-weight:600;">
      <i class="bi bi-box-arrow-right"></i> Cerrar sesión
    </a>
  </div>
</aside>

<div class="main-wrapper">
  <header class="topbar">
    <h1>
      <?php
      $titulos = [
        'dashboard' => 'Panel Ejecutivo · Dibufala S.A.S.',
        'productos' => 'Cava & Catálogo de Productos Lácteos',
        'clientes'  => 'Directorio de Clientes y Aliados',
        'ventas'    => 'Pedidos, Pagos y Facturación Electrónica',
        'reportes'  => 'Analítica Comercial Dibufala S.A.S.',
      ];
      echo $titulos[$moduloActivo] ?? 'Dibufala S.A.S.';
      ?>
    </h1>
    <div class="topbar-right">
      <span class="badge-ia">✦ Dibufala IA Activa</span>
      <span class="topbar-user"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
      <a href="<?= BASE_URL ?>/index.php?modulo=auth&accion=logout" class="btn btn-secondary btn-sm" title="Cerrar sesión">
        <i class="bi bi-box-arrow-right"></i>
      </a>
    </div>
  </header>
  <main class="content"></main>