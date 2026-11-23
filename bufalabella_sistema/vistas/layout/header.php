<?php $moduloActivo = $_GET['modulo'] ?? 'dashboard'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DIBUFALA S.A.S. · El Rosal, Cundinamarca</title>
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
        <i class="bi bi-receipt-cutoff"></i> Ventas
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="<?= $moduloActivo==='productos'?'active':'' ?>">
        <i class="bi bi-box-seam-fill"></i> Productos
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=clientes&accion=listar" class="<?= $moduloActivo==='clientes'?'active':'' ?>">
        <i class="bi bi-people-fill"></i> Clientes
      </a>
      <a href="<?= BASE_URL ?>/index.php?modulo=reportes&accion=dashboard" class="<?= $moduloActivo==='reportes'?'active':'' ?>">
        <i class="bi bi-bar-chart-fill"></i> Reportes
      </a>
    </nav>

    <div class="nav-actions">
      <span class="user-chip">
        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin') ?>
      </span>
      <a href="<?= BASE_URL ?>/index.php?modulo=auth&accion=logout" class="btn btn-danger btn-sm" title="Cerrar sesión">
        <i class="bi bi-box-arrow-right"></i> Salir
      </a>
    </div>
  </header>

  <main class="content-area"></main>