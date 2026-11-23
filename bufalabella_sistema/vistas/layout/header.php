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
<body x-data="{ menuAbierto: localStorage.getItem('dibufala_menu') === 'true' }">

<!-- MENÚ LATERAL COLAPSABLE ESTILO MODERNO -->
<aside class="side-rail" :class="{ 'expanded': menuAbierto }">
  <button type="button" class="rail-toggle-btn"
          @click="menuAbierto = !menuAbierto; localStorage.setItem('dibufala_menu', menuAbierto)"
          :title="menuAbierto ? 'Contraer menú' : 'Desplegar menú'">
    <i class="bi bi-chevron-right"></i>
  </button>

  <div>
    <!-- Marca -->
    <div class="rail-brand">
      <div class="rail-logo-icon">🐃</div>
      <div class="rail-brand-text" x-show="menuAbierto" x-transition.opacity>
        <h2>DIBUFALA</h2>
        <span>El Rosal · Cund.</span>
      </div>
    </div>

    <!-- Navegación -->
    <nav class="rail-nav">
      <a href="<?= BASE_URL ?>/index.php" class="rail-link <?= $moduloActivo==='dashboard'?'active':'' ?>" title="Inicio">
        <i class="bi bi-house-door-fill"></i>
        <span x-show="menuAbierto" x-transition.opacity>Inicio</span>
      </a>

      <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="rail-link <?= $moduloActivo==='ventas'?'active':'' ?>" title="Ventas y Facturas">
        <i class="bi bi-currency-dollar"></i>
        <span x-show="menuAbierto" x-transition.opacity>Ventas</span>
      </a>

      <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="rail-link <?= $moduloActivo==='productos'?'active':'' ?>" title="Productos">
        <i class="bi bi-box-seam-fill"></i>
        <span x-show="menuAbierto" x-transition.opacity>Productos</span>
      </a>

      <a href="<?= BASE_URL ?>/index.php?modulo=clientes&accion=listar" class="rail-link <?= $moduloActivo==='clientes'?'active':'' ?>" title="Clientes">
        <i class="bi bi-people-fill"></i>
        <span x-show="menuAbierto" x-transition.opacity>Clientes</span>
      </a>

      <a href="<?= BASE_URL ?>/index.php?modulo=reportes&accion=dashboard" class="rail-link <?= $moduloActivo==='reportes'?'active':'' ?>" title="Reportes">
        <i class="bi bi-bar-chart-fill"></i>
        <span x-show="menuAbierto" x-transition.opacity>Reportes</span>
      </a>
    </nav>
  </div>

  <!-- Botón Cerrar Sesión abajo -->
  <div class="rail-footer">
    <a href="<?= BASE_URL ?>/index.php?modulo=auth&accion=logout" class="rail-logout" title="Cerrar sesión">
      <i class="bi bi-power"></i>
      <span x-show="menuAbierto" x-transition.opacity>Cerrar sesión</span>
    </a>
  </div>
</aside>

<!-- CONTENIDO PRINCIPAL -->
<div class="app-shell" :class="{ 'rail-open': menuAbierto }">
  <header class="mini-topbar">
    <div>
      <h1>
        <?php
        $titulos = [
          'dashboard' => 'Inicio · Dibufala S.A.S.',
          'ventas'    => 'Ventas y Facturación',
          'productos' => 'Inventario de Productos',
          'clientes'  => 'Directorio de Clientes',
          'reportes'  => 'Reportes y Estadísticas',
        ];
        echo $titulos[$moduloActivo] ?? 'Dibufala S.A.S.';
        ?>
      </h1>
      <p>Planta El Rosal, Cundinamarca · NIT 900.845.219-7</p>
    </div>

    <span class="user-chip">
      <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin') ?>
    </span>
  </header>

  <main class="content-area"></main>