<?php
// Dashboard principal Dibufala S.A.S. - Chart.js Corporativo + IA + Últimas ventas
$totalProductos = $conexion->query("SELECT COUNT(*) AS t FROM productos WHERE estado='activo'")->fetch_assoc()['t'] ?? 0;
$totalClientes  = $conexion->query("SELECT COUNT(*) AS t FROM clientes  WHERE estado='activo'")->fetch_assoc()['t'] ?? 0;
$totalVentas    = $conexion->query("SELECT COUNT(*) AS t FROM ventas WHERE YEAR(fecha)=YEAR(NOW()) AND MONTH(fecha)=MONTH(NOW()) AND estado!='cancelada'")->fetch_assoc()['t'] ?? 0;
$ingresosMes    = $conexion->query("SELECT COALESCE(SUM(total),0) AS t FROM ventas WHERE YEAR(fecha)=YEAR(NOW()) AND MONTH(fecha)=MONTH(NOW()) AND estado='completada'")->fetch_assoc()['t'] ?? 0;
$stockBajo      = $conexion->query("SELECT COUNT(*) AS t FROM productos WHERE stock < 5 AND estado='activo'")->fetch_assoc()['t'] ?? 0;
$ultimasVentas  = $conexion->query(
    "SELECT v.id, v.fecha, v.total, v.estado, CONCAT(c.nombre, ' ', c.apellido) AS cliente
     FROM ventas v LEFT JOIN clientes c ON v.id_cliente = c.id
     ORDER BY v.fecha DESC LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);
?>

<!-- TARJETAS RESUMEN CLICABLES -->
<div class="stats-grid">
  <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="stat-card amarillo">
    <div class="stat-icon">🧀</div>
    <div class="stat-info"><p>Productos en Catálogo</p><h3><?= $totalProductos ?></h3></div>
  </a>
  <a href="<?= BASE_URL ?>/index.php?modulo=clientes&accion=listar" class="stat-card">
    <div class="stat-icon">👥</div>
    <div class="stat-info"><p>Clientes Registrados</p><h3><?= $totalClientes ?></h3></div>
  </a>
  <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="stat-card amarillo">
    <div class="stat-icon">🧾</div>
    <div class="stat-info"><p>Ventas del Mes</p><h3><?= $totalVentas ?></h3></div>
  </a>
  <a href="<?= BASE_URL ?>/index.php?modulo=reportes&accion=dashboard" class="stat-card">
    <div class="stat-icon">💰</div>
    <div class="stat-info"><p>Ingresos del Mes</p><h3>$<?= number_format($ingresosMes, 0, ',', '.') ?></h3></div>
  </a>
  <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="stat-card rojo">
    <div class="stat-icon">⚠️</div>
    <div class="stat-info"><p>Alerta Stock Bajo (&lt;5)</p><h3><?= $stockBajo ?></h3></div>
  </a>
</div>

<!-- IA - RESUMEN INTELIGENTE -->
<div class="card" x-data="resumenIAApp()" x-init="cargar()">
  <div class="card-header">
    <h2>✨ Asistente Comercial IA · Dibufala S.A.S.</h2>
    <button class="btn btn-primary btn-sm" @click="cargar()"><i class="bi bi-arrow-clockwise"></i> Actualizar Análisis</button>
  </div>
  <div class="card-body">
    <div x-show="cargando" class="spinner-wrap"><div class="spinner"></div> Analizando ventas e inventario de El Rosal...</div>
    <template x-if="!cargando && datos">
      <div class="ia-grid">
        <div class="ia-card">
          <p>Ventas completadas este mes</p>
          <h3 x-text="'$' + Number(datos.ventas_mes).toLocaleString('es-CO')"></h3>
        </div>
        <div class="ia-card">
          <p>Variación vs mes anterior</p>
          <h3 :style="datos.crecimiento_pct>=0?'color:#111111':'color:#D91E2A'"
              x-text="datos.emoji + ' ' + datos.crecimiento_pct + '%'"></h3>
        </div>
        <div class="ia-card consejo">
          <p><strong>🤖 Recomendación Estratégica IA:</strong></p>
          <p style="font-size:14px; color:#111111; font-weight:600; margin-top:4px;" x-text="datos.consejo_ia"></p>
        </div>
      </div>
    </template>
  </div>
</div>

<!-- GRÁFICAS CHART.JS REDISEÑADAS (NEGRO, AMARILLO Y ROJO) -->
<div class="charts-grid">
  <div class="card">
    <div class="card-header">
      <h2>📊 Evolución de Ventas Mensuales · <?= date('Y') ?></h2>
      <span class="badge badge-warning">COP</span>
    </div>
    <div class="card-body"><canvas id="graficaVentasMes" height="210"></canvas></div>
  </div>
  <div class="card">
    <div class="card-header">
      <h2>🍩 Participación por Línea Láctea</h2>
      <span class="badge badge-danger">% Ventas</span>
    </div>
    <div class="card-body"><canvas id="graficaCategoria" height="210"></canvas></div>
  </div>
</div>

<!-- IA - PREDICCIÓN AGOTAMIENTO -->
<div class="card" x-data="prediccionApp()" x-init="cargar()">
  <div class="card-header">
    <h2>🔮 Predicción de Reabastecimiento en Cava · IA</h2>
    <span class="badge badge-info">Proyección 30 días</span>
  </div>
  <div class="card-body">
    <div x-show="cargando" class="spinner-wrap"><div class="spinner"></div> Calculando rotación de productos...</div>
    <template x-if="!cargando && predicciones.length === 0">
      <div class="empty-state"><p>✅ Todos los derivados de búfala tienen inventario suficiente.</p></div>
    </template>
    <div class="table-responsive" x-show="!cargando && predicciones.length > 0">
      <table>
        <thead><tr><th>Producto</th><th>Stock Actual</th><th>Vendidos (30d)</th><th>Duración Estimada</th><th>Estado IA</th></tr></thead>
        <tbody>
          <template x-for="p in predicciones" :key="p.id">
            <tr>
              <td><strong x-text="p.nombre"></strong></td>
              <td x-text="p.stock + ' uds.'"></td>
              <td x-text="p.ventas_30d + ' uds.'"></td>
              <td><strong x-text="p.dias_restantes + ' días'"></strong></td>
              <td><span :class="'badge badge-' + p.color" x-text="p.alerta"></span></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ÚLTIMAS VENTAS -->
<div class="card">
  <div class="card-header">
    <h2>🧾 Últimas 5 Ventas Registradas</h2>
    <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="btn btn-dark btn-sm">Ir a Facturación Completa →</a>
  </div>
  <div class="card-body">
    <?php if (empty($ultimasVentas)): ?>
      <div class="empty-state"><p>No hay ventas registradas aún.</p></div>
    <?php else: ?>
    <div class="table-responsive">
      <table>
        <thead><tr><th>Factura</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead>
        <tbody>
          <?php foreach ($ultimasVentas as $v): ?>
          <tr>
            <td><strong style="color:#D91E2A;">FAC-<?= str_pad($v['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
            <td><?= htmlspecialchars($v['cliente'] ?? 'Cliente general') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
            <td><strong>$<?= number_format($v['total'], 0, ',', '.') ?></strong></td>
            <td><span class="badge <?= $v['estado']==='completada'?'badge-success':($v['estado']==='pendiente'?'badge-warning':'badge-danger') ?>"><?= ucfirst($v['estado']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  const apiBase = window.APP_BASE_URL + '/index.php';

  // Gráfica 1: Barras redondeadas con degradado Amarillo Dorado y borde Negro
  try {
    const r1 = await fetch(`${apiBase}?modulo=reportes&accion=ventasPorMes`);
    const j1 = await r1.json();
    if (j1.ok && j1.datos.length > 0) {
      const ctx1 = document.getElementById('graficaVentasMes').getContext('2d');
      const gradienteAmarillo = ctx1.createLinearGradient(0, 0, 0, 300);
      gradienteAmarillo.addColorStop(0, '#F5B800');
      gradienteAmarillo.addColorStop(1, '#D91E2A');

      new Chart(ctx1, {
        type: 'bar',
        data: {
          labels: j1.datos.map(d => d.mes),
          datasets: [{
            label: 'Ingresos (COP)',
            data: j1.datos.map(d => Number(d.total)),
            backgroundColor: gradienteAmarillo,
            hoverBackgroundColor: '#111111',
            borderRadius: 10,
            barPercentage: 0.62
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#111111',
              titleColor: '#F5B800',
              bodyColor: '#FFFFFF',
              padding: 12,
              cornerRadius: 10,
              callbacks: { label: ctx => ' $' + Number(ctx.raw).toLocaleString('es-CO') + ' COP' }
            }
          },
          scales: {
            x: { grid: { display: false }, ticks: { font: { weight: 'bold' }, color: '#111111' } },
            y: {
              border: { display: false },
              grid: { color: '#E4E4E7' },
              ticks: { color: '#52525B', callback: v => '$' + (v / 1000).toFixed(0) + 'K' }
            }
          }
        }
      });
    }
  } catch (e) { console.error('Error gráfica ventas por mes:', e); }

  // Gráfica 2: Dona Moderna con recorte amplio (cutout 68%) en Negro, Amarillo, Rojo y Gris
  try {
    const r2 = await fetch(`${apiBase}?modulo=reportes&accion=ventasPorCategoria`);
    const j2 = await r2.json();
    if (j2.ok && j2.datos.length > 0) {
      new Chart(document.getElementById('graficaCategoria'), {
        type: 'doughnut',
        data: {
          labels: j2.datos.map(d => d.categoria),
          datasets: [{
            data: j2.datos.map(d => Number(d.total)),
            backgroundColor: ['#F5B800', '#111111', '#D91E2A', '#71717A', '#FBBF24'],
            borderWidth: 3,
            borderColor: '#FFFFFF',
            hoverOffset: 8
          }]
        },
        options: {
          responsive: true,
          cutout: '68%',
          plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16, font: { weight: '600' }, color: '#111111' } },
            tooltip: {
              backgroundColor: '#111111',
              titleColor: '#F5B800',
              bodyColor: '#FFFFFF',
              padding: 12,
              cornerRadius: 10,
              callbacks: { label: ctx => ' ' + ctx.label + ': $' + Number(ctx.raw).toLocaleString('es-CO') }
            }
          }
        }
      });
    }
  } catch (e) { console.error('Error gráfica por categoría:', e); }
});
</script>