<?php
$esAdmin = (($_SESSION['usuario_rol'] ?? 'cliente') === 'admin');

if (!$esAdmin):
    // VISTA EXCLUSIVA PARA CLIENTE: Bienvenida y quesos recomendados
    $recomendados = $conexion->query(
        "SELECT p.id, p.nombre, p.descripcion, p.precio, p.stock, p.marca, c.nombre AS categoria
         FROM productos p
         LEFT JOIN categorias c ON p.categoria_id = c.id
         WHERE p.estado = 'activo' AND p.stock > 0
         ORDER BY p.id ASC LIMIT 6"
    )->fetch_all(MYSQLI_ASSOC);
    $nombreCliente = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Cliente');
?>
  <!-- BIENVENIDA CLIENTE -->
  <div class="card" style="background: linear-gradient(135deg, #111111 0%, #242424 100%); color: #FFFFFF; border: none; border-bottom: 4px solid #F5B800;">
    <div class="card-body" style="padding: 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 18px;">
      <div style="max-width: 620px;">
        <span class="badge badge-warning" style="margin-bottom: 10px;">🐃 100% Leche de Búfala · El Rosal, Cundinamarca</span>
        <h2 style="font-size: 24px; color: #FFFFFF; margin-bottom: 8px;">¡Bienvenido a Dibufala S.A.S., <?= $nombreCliente ?>!</h2>
        <p style="font-size: 14px; color: #D4D4D8; line-height: 1.55;">
          Descubre nuestra selección artesanal de quesos hilados, burratas, yogures griegos y mantequillas elaborados diariamente en nuestra planta de El Rosal con cadena de frío certificada.
        </p>
      </div>
      <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="btn btn-secondary">
          <i class="bi bi-search"></i> Investigar Catálogo
        </a>
        <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="btn btn-primary">
          <i class="bi bi-bag-check-fill"></i> Hacer un Pedido
        </a>
      </div>
    </div>
  </div>

  <!-- QUESOS Y LÁCTEOS RECOMENDADOS -->
  <div class="card">
    <div class="card-header">
      <h2>🧀 Quesos y Lácteos Recomendados para Ti</h2>
      <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="btn btn-dark btn-sm">Ver catálogo completo</a>
    </div>
    <div class="card-body">
      <?php if (empty($recomendados)): ?>
        <div class="empty-state"><p>Pronto tendremos nuevos productos disponibles.</p></div>
      <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 1fr)); gap: 16px;">
          <?php foreach ($recomendados as $prod): ?>
            <div style="border: 1px solid #E4E4E7; border-radius: 12px; padding: 18px; background: #FAFAFA; display: flex; flex-direction: column; justify-content: space-between;">
              <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                  <span class="badge badge-info"><?= htmlspecialchars($prod['categoria'] ?? 'Lácteos') ?></span>
                  <span style="font-size: 11.5px; color: #16A34A; font-weight: 700;">● Disponible (<?= (int)$prod['stock'] ?>)</span>
                </div>
                <h3 style="font-size: 16px; color: #111111; margin-bottom: 6px;"><?= htmlspecialchars($prod['nombre']) ?></h3>
                <p style="font-size: 13px; color: #52525B; line-height: 1.45; margin-bottom: 14px;">
                  <?= htmlspecialchars($prod['descripcion'] ?: 'Producto artesanal 100% leche de búfala.') ?>
                </p>
              </div>
              <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #E4E4E7;">
                <strong style="font-size: 17px; color: #D91E2A;">$<?= number_format($prod['precio'], 0, ',', '.') ?></strong>
                <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="btn btn-primary btn-sm">
                  <i class="bi bi-cart-plus"></i> Comprar
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

<?php
else:
    // PANEL DE ADMINISTRADOR (INTACTO)
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

<!-- RESUMEN RÁPIDO -->
<div class="stats-grid">
  <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="stat-card amarillo">
    <div class="stat-icon">🧀</div>
    <div class="stat-info"><p>Productos activos</p><h3><?= $totalProductos ?></h3></div>
  </a>
  <a href="<?= BASE_URL ?>/index.php?modulo=clientes&accion=listar" class="stat-card">
    <div class="stat-icon">👥</div>
    <div class="stat-info"><p>Clientes</p><h3><?= $totalClientes ?></h3></div>
  </a>
  <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="stat-card amarillo">
    <div class="stat-icon">🧾</div>
    <div class="stat-info"><p>Ventas del mes</p><h3><?= $totalVentas ?></h3></div>
  </a>
  <a href="<?= BASE_URL ?>/index.php?modulo=reportes&accion=dashboard" class="stat-card">
    <div class="stat-icon">💰</div>
    <div class="stat-info"><p>Ingresos del mes</p><h3>$<?= number_format($ingresosMes, 0, ',', '.') ?></h3></div>
  </a>
  <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="stat-card rojo">
    <div class="stat-icon">⚠️</div>
    <div class="stat-info"><p>Stock bajo (&lt;5)</p><h3><?= $stockBajo ?></h3></div>
  </a>
</div>

<!-- ANÁLISIS IA -->
<div class="card" x-data="resumenIAApp()" x-init="cargar()">
  <div class="card-header">
    <h2>✨ Análisis del negocio · IA</h2>
    <button class="btn btn-secondary btn-sm" @click="cargar()"><i class="bi bi-arrow-clockwise"></i> Actualizar</button>
  </div>
  <div class="card-body">
    <div x-show="cargando" class="spinner-wrap"><div class="spinner"></div> Analizando métricas...</div>
    <template x-if="!cargando && datos">
      <div class="ia-grid">
        <div class="ia-card">
          <p>Ventas completadas este mes</p>
          <h3 x-text="'$' + Number(datos.ventas_mes).toLocaleString('es-CO')"></h3>
        </div>
        <div class="ia-card">
          <p>Vs mes anterior</p>
          <h3 :style="datos.crecimiento_pct>=0?'color:#111111':'color:#D91E2A'"
              x-text="datos.emoji + ' ' + datos.crecimiento_pct + '%'"></h3>
        </div>
        <div class="ia-card consejo">
          <p><strong>🤖 Consejo IA:</strong></p>
          <p style="font-size:13.5px; color:#111111; font-weight:600; margin-top:4px;" x-text="datos.consejo_ia"></p>
        </div>
      </div>
    </template>
  </div>
</div>

<!-- GRÁFICAS -->
<div class="charts-grid">
  <div class="card">
    <div class="card-header"><h2>📊 Ventas por mes · <?= date('Y') ?></h2></div>
    <div class="card-body"><canvas id="graficaVentasMes" height="200"></canvas></div>
  </div>
  <div class="card">
    <div class="card-header"><h2>🍩 Ventas por categoría</h2></div>
    <div class="card-body"><canvas id="graficaCategoria" height="200"></canvas></div>
  </div>
</div>

<!-- PREDICCIÓN DE AGOTAMIENTO -->
<div class="card" x-data="prediccionApp()" x-init="cargar()">
  <div class="card-header">
    <h2>🔮 Predicción de agotamiento · IA</h2>
  </div>
  <div class="card-body">
    <div x-show="cargando" class="spinner-wrap"><div class="spinner"></div> Calculando rotación...</div>
    <template x-if="!cargando && predicciones.length === 0">
      <div class="empty-state"><p>✅ No hay productos en riesgo de agotamiento.</p></div>
    </template>
    <div class="table-responsive" x-show="!cargando && predicciones.length > 0">
      <table>
        <thead><tr><th>Producto</th><th>Stock</th><th>Vendidos (30d)</th><th>Días restantes</th><th>Alerta</th></tr></thead>
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
    <h2>🧾 Últimas 5 ventas</h2>
    <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="btn btn-dark btn-sm">Ver todas</a>
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

  try {
    const r1 = await fetch(`${apiBase}?modulo=reportes&accion=ventasPorMes`);
    const j1 = await r1.json();
    if (j1.ok && j1.datos.length > 0) {
      const ctx1 = document.getElementById('graficaVentasMes').getContext('2d');
      const gradiente = ctx1.createLinearGradient(0, 0, 0, 280);
      gradiente.addColorStop(0, '#F5B800');
      gradiente.addColorStop(1, '#D91E2A');

      new Chart(ctx1, {
        type: 'bar',
        data: {
          labels: j1.datos.map(d => d.mes),
          datasets: [{
            label: 'Ventas (COP)',
            data: j1.datos.map(d => Number(d.total)),
            backgroundColor: gradiente,
            hoverBackgroundColor: '#111111',
            borderRadius: 8,
            barPercentage: 0.6
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
              padding: 10,
              cornerRadius: 8,
              callbacks: { label: ctx => ' $' + Number(ctx.raw).toLocaleString('es-CO') }
            }
          },
          scales: {
            x: { grid: { display: false }, ticks: { color: '#111111', font: { weight: 'bold' } } },
            y: { border: { display: false }, grid: { color: '#E4E4E7' }, ticks: { callback: v => '$' + (v / 1000).toFixed(0) + 'K' } }
          }
        }
      });
    }
  } catch (e) {}

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
            backgroundColor: ['#F5B800', '#111111', '#D91E2A', '#71717A'],
            borderWidth: 2,
            borderColor: '#FFFFFF'
          }]
        },
        options: {
          responsive: true,
          cutout: '65%',
          plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, color: '#111111' } }
          }
        }
      });
    }
  } catch (e) {}
});
</script>
<?php endif; ?>