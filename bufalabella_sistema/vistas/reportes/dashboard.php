<div x-data="reportesApp()" x-init="init()">
  <div class="charts-grid">
    <div class="card">
      <div class="card-header">
        <h2>📈 Tendencia de Ingresos · <?= date('Y') ?></h2>
        <span class="badge badge-warning">Histórico Anual</span>
      </div>
      <div class="card-body"><canvas id="graficaLinea" height="190"></canvas></div>
    </div>

    <div class="card">
      <div class="card-header">
        <h2>🧀 Top Productos (Unidades Vendidas)</h2>
        <span class="badge badge-danger">Ranking</span>
      </div>
      <div class="card-body"><canvas id="graficaTopBarras" height="190"></canvas></div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h2>🏆 Detalle Top 5 Productos Más Vendidos · Dibufala S.A.S.</h2>
      <a href="<?= BASE_URL ?>/index.php?modulo=ventas&accion=listar" class="btn btn-primary btn-sm">+ Registrar Nueva Venta</a>
    </div>
    <div class="card-body">
      <div x-show="cargando" class="spinner-wrap"><div class="spinner"></div> Cargando ranking de productos...</div>
      <div x-show="!cargando" class="table-responsive">
        <table>
          <thead><tr><th>Posición</th><th>Producto</th><th>Unidades Vendidas</th><th>Ingresos Generados</th></tr></thead>
          <tbody>
            <template x-for="(p,i) in topProductos" :key="p.nombre">
              <tr>
                <td><strong x-text="['🥇 1°','🥈 2°','🥉 3°','4°','5°'][i] || (i+1)"></strong></td>
                <td><strong x-text="p.nombre"></strong></td>
                <td x-text="p.total_vendido + ' uds.'"></td>
                <td><strong style="color:#D91E2A;" x-text="'$' + Number(p.total_ingresos).toLocaleString('es-CO')"></strong></td>
              </tr>
            </template>
            <template x-if="!cargando && topProductos.length===0">
              <tr><td colspan="4"><div class="empty-state"><p>Aún no hay ventas registradas para calcular el ranking.</p></div></td></tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h2>⚠️ Alerta de Reposición en Cava (&lt; 5 unidades)</h2>
      <a href="<?= BASE_URL ?>/index.php?modulo=productos&accion=listar" class="btn btn-dark btn-sm">Reabastecer Inventario →</a>
    </div>
    <div class="card-body">
      <div x-show="!cargando" class="table-responsive">
        <table>
          <thead><tr><th>Código</th><th>Producto</th><th>Línea / Marca</th><th>Stock Actual</th><th>Precio Unitario</th></tr></thead>
          <tbody>
            <template x-for="p in stockBajo" :key="p.id">
              <tr>
                <td x-text="'#' + p.id"></td>
                <td><strong x-text="p.nombre"></strong></td>
                <td x-text="p.marca || 'Dibufala'"></td>
                <td><span class="badge badge-danger" x-text="p.stock + ' uds. restantes'"></span></td>
                <td x-text="'$' + Number(p.precio).toLocaleString('es-CO')"></td>
              </tr>
            </template>
            <template x-if="!cargando && stockBajo.length===0">
              <tr><td colspan="5"><div class="empty-state"><p>✅ Todo el inventario en El Rosal tiene stock óptimo.</p></div></td></tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  const apiBase = window.APP_BASE_URL + '/index.php';

  // Gráfica de Línea Corporativa (Negro + Amarillo + Puntos Rojos)
  try {
    const r = await fetch(`${apiBase}?modulo=reportes&accion=ventasPorMes`);
    const j = await r.json();
    if (j.ok) {
      const ctxLinea = document.getElementById('graficaLinea').getContext('2d');
      const areaAmarilla = ctxLinea.createLinearGradient(0, 0, 0, 260);
      areaAmarilla.addColorStop(0, 'rgba(245, 184, 0, 0.45)');
      areaAmarilla.addColorStop(1, 'rgba(245, 184, 0, 0.02)');

      new Chart(ctxLinea, {
        type: 'line',
        data: {
          labels: j.datos.map(d => d.mes),
          datasets: [{
            label: 'Ventas COP',
            data: j.datos.map(d => Number(d.total)),
            borderColor: '#111111',
            backgroundColor: areaAmarilla,
            borderWidth: 3.5,
            pointBackgroundColor: '#D91E2A',
            pointBorderColor: '#FFFFFF',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            fill: true,
            tension: 0.35
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
            x: { grid: { display: false }, ticks: { color: '#111111', font: { weight: '600' } } },
            y: { grid: { color: '#E4E4E7' }, ticks: { color: '#52525B', callback: v => '$' + (v / 1000).toFixed(0) + 'K' } }
          }
        }
      });
    }
  } catch (e) { console.error('Error gráfica reportes:', e); }

  // Segunda Gráfica: Barras Horizontales Top Productos
  try {
    const rTop = await fetch(`${apiBase}?modulo=reportes&accion=topProductos`);
    const jTop = await rTop.json();
    if (jTop.ok && jTop.datos.length > 0) {
      new Chart(document.getElementById('graficaTopBarras'), {
        type: 'bar',
        data: {
          labels: jTop.datos.map(p => p.nombre),
          datasets: [{
            label: 'Unidades',
            data: jTop.datos.map(p => Number(p.total_vendido)),
            backgroundColor: ['#D91E2A', '#F5B800', '#111111', '#D99B00', '#52525B'],
            borderRadius: 8
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#111111',
              titleColor: '#F5B800',
              bodyColor: '#FFFFFF',
              padding: 10,
              cornerRadius: 8
            }
          },
          scales: {
            x: { grid: { color: '#E4E4E7' }, ticks: { color: '#52525B' } },
            y: { grid: { display: false }, ticks: { color: '#111111', font: { weight: '600' } } }
          }
        }
      });
    }
  } catch (e) {}
});
</script>