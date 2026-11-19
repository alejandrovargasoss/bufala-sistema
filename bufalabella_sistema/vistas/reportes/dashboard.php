<div x-data="reportesApp()" x-init="init()">
  <div class="card">
    <div class="card-header"><h2>📈 Ventas por mes · <?= date('Y') ?></h2></div>
    <div class="card-body"><canvas id="graficaLinea" height="110"></canvas></div>
  </div>

  <div class="card">
    <div class="card-header"><h2>🏆 Top 5 productos más vendidos</h2></div>
    <div class="card-body">
      <div x-show="cargando" class="spinner-wrap"><div class="spinner"></div> Cargando...</div>
      <div x-show="!cargando" class="table-responsive">
        <table>
          <thead><tr><th>#</th><th>Producto</th><th>Unidades</th><th>Ingresos</th></tr></thead>
          <tbody>
            <template x-for="(p,i) in topProductos" :key="p.nombre">
              <tr>
                <td x-text="['🥇','🥈','🥉','4°','5°'][i] || (i+1)"></td>
                <td><strong x-text="p.nombre"></strong></td>
                <td x-text="p.total_vendido + ' uds.'"></td>
                <td><strong x-text="'$' + Number(p.total_ingresos).toLocaleString('es-CO')"></strong></td>
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
    <div class="card-header"><h2>⚠️ Productos con stock bajo (&lt; 5 unidades)</h2></div>
    <div class="card-body">
      <div x-show="!cargando" class="table-responsive">
        <table>
          <thead><tr><th>#</th><th>Producto</th><th>Marca</th><th>Stock</th><th>Precio</th></tr></thead>
          <tbody>
            <template x-for="p in stockBajo" :key="p.id">
              <tr>
                <td x-text="p.id"></td>
                <td><strong x-text="p.nombre"></strong></td>
                <td x-text="p.marca || '—'"></td>
                <td><span class="stock-bajo" x-text="p.stock + ' uds.'"></span></td>
                <td x-text="'$' + Number(p.precio).toLocaleString('es-CO')"></td>
              </tr>
            </template>
            <template x-if="!cargando && stockBajo.length===0">
              <tr><td colspan="5"><div class="empty-state"><p>Todo el inventario tiene stock saludable.</p></div></td></tr>
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
  try {
    const r = await fetch(`${apiBase}?modulo=reportes&accion=ventasPorMes`);
    const j = await r.json();
    if (j.ok) {
      new Chart(document.getElementById('graficaLinea'), {
        type: 'line',
        data: {
          labels: j.datos.map(d => d.mes),
          datasets: [{
            label: 'Ventas COP',
            data: j.datos.map(d => Number(d.total)),
            borderColor: '#2D6A4F',
            backgroundColor: 'rgba(45,106,79,0.14)',
            borderWidth: 3,
            pointRadius: 6,
            fill: true,
            tension: 0.35
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: { y: { ticks: { callback: v => '$' + (v / 1000).toFixed(0) + 'K' } } }
        }
      });
    }
  } catch (e) { console.error('Error gráfica reportes:', e); }
});
</script>
