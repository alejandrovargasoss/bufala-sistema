<div class="card" x-data="ventasApp()">
  <template x-if="mensaje">
    <div :class="`alert alert-${tipoMsg}`" x-text="mensaje"></div>
  </template>
  <div class="card-header">
    <h2>🛒 Registro de Ventas</h2>
    <button class="btn btn-primary" @click="abrirFormulario()"><i class="bi bi-plus-lg"></i> Nueva Venta</button>
  </div>
  <div class="card-body">
    <div x-show="cargando" class="spinner-wrap"><div class="spinner"></div> Cargando ventas...</div>
    <div x-show="!cargando" class="table-responsive">
      <table>
        <thead><tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <template x-for="v in ventas" :key="v.id">
            <tr>
              <td><strong x-text="'#'+v.id"></strong></td>
              <td x-text="v.cliente || 'Cliente #' + v.id_cliente"></td>
              <td x-text="v.fecha"></td>
              <td><strong x-text="'$'+Number(v.total).toLocaleString('es-CO')"></strong></td>
              <td>
                <span :class="v.estado==='completada'?'badge badge-success':v.estado==='pendiente'?'badge badge-warning':'badge badge-danger'"
                      x-text="v.estado"></span>
              </td>
              <td style="display:flex; gap:6px;">
                <button class="btn btn-secondary btn-sm" @click="verDetalle(v)" title="Ver productos"><i class="bi bi-eye"></i></button>
                <button x-show="v.estado==='pendiente'" class="btn btn-primary btn-sm" @click="completar(v.id)" title="Marcar completada"><i class="bi bi-check-lg"></i></button>
                <button x-show="v.estado!=='cancelada'" class="btn btn-danger btn-sm" @click="cancelar(v.id)">Cancelar</button>
              </td>
            </tr>
          </template>
          <template x-if="!cargando && ventas.length===0">
            <tr><td colspan="6"><div class="empty-state"><p>No hay ventas registradas todavía.</p></div></td></tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal nueva venta con selección de productos -->
  <div class="modal-overlay" x-show="mostrarModal" x-cloak>
    <div class="modal" style="max-width: 680px;">
      <div class="modal-header">
        <h3>🛒 Registrar Nueva Venta</h3>
        <button class="btn-close" @click="cerrarModal()">✕</button>
      </div>
      <div class="modal-body">
        <div class="form-grid-2">
          <div class="form-group">
            <label>Cliente *</label>
            <select x-model="form.id_cliente">
              <option value="">-- Selecciona un cliente --</option>
              <template x-for="c in clientes" :key="c.id">
                <option :value="c.id" x-text="c.nombre + ' ' + c.apellido + ' (' + c.documento + ')'"></option>
              </template>
            </select>
          </div>
          <div class="form-group">
            <label>Estado de la venta</label>
            <select x-model="form.estado">
              <option value="completada">Completada (Pagada)</option>
              <option value="pendiente">Pendiente</option>
            </select>
          </div>
        </div>

        <!-- Agregar productos al carrito -->
        <div style="background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0; margin-bottom:16px;">
          <label style="display:block; font-size:13px; font-weight:700; margin-bottom:8px; color:#1E293B;">Agregar productos a la venta</label>
          <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <select x-model="itemTemp.id_producto" style="flex:2; min-width:200px; padding:8px; border-radius:8px; border:1px solid #CBD5E1;">
              <option value="">-- Elegir producto disponible --</option>
              <template x-for="p in productosDisponibles" :key="p.id">
                <option :value="p.id" x-text="p.nombre + ' ($' + Number(p.precio).toLocaleString('es-CO') + ' · Stock: ' + p.stock + ')'"></option>
              </template>
            </select>
            <input type="number" x-model="itemTemp.cantidad" min="1" placeholder="Cant." style="width:80px; padding:8px; border-radius:8px; border:1px solid #CBD5E1;">
            <button type="button" class="btn btn-primary btn-sm" @click="agregarItem()">+ Agregar</button>
          </div>

          <!-- Lista de productos agregados -->
          <div x-show="form.items.length > 0" style="margin-top:12px;">
            <table style="font-size:13px;">
              <thead><tr><th>Producto</th><th>Cant.</th><th>Precio Unit.</th><th>Subtotal</th><th></th></tr></thead>
              <tbody>
                <template x-for="(it, idx) in form.items" :key="idx">
                  <tr>
                    <td x-text="it.nombre"></td>
                    <td x-text="it.cantidad"></td>
                    <td x-text="'$' + Number(it.precio).toLocaleString('es-CO')"></td>
                    <td><strong x-text="'$' + (it.cantidad * it.precio).toLocaleString('es-CO')"></strong></td>
                    <td><button type="button" class="btn btn-danger btn-sm" @click="quitarItem(idx)">✕</button></td>
                  </tr>
                </template>
              </tbody>
            </table>
            <div style="text-align:right; margin-top:10px; font-size:16px; font-weight:800; color:#1E293B;">
              Total a pagar: <span style="color:#2D6A4F;" x-text="'$' + totalVenta.toLocaleString('es-CO')"></span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Observación</label>
          <textarea x-model="form.observacion" rows="2" placeholder="Notas opcionales de la venta..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" @click="cerrarModal()">Cancelar</button>
        <button class="btn btn-primary" @click="guardar()">Confirmar y Registrar Venta</button>
      </div>
    </div>
  </div>

  <!-- Modal Detalle Venta -->
  <div class="modal-overlay" x-show="mostrarDetalle" x-cloak>
    <div class="modal">
      <div class="modal-header">
        <h3>🧾 Detalle de Venta #<span x-text="ventaSeleccionada?.id"></span></h3>
        <button class="btn-close" @click="mostrarDetalle=false">✕</button>
      </div>
      <div class="modal-body">
        <p style="margin-bottom:10px; font-size:13px;"><strong>Cliente:</strong> <span x-text="ventaSeleccionada?.cliente"></span></p>
        <table>
          <thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead>
          <tbody>
            <template x-for="d in detalleItems" :key="d.id">
              <tr>
                <td x-text="d.producto"></td>
                <td x-text="d.cantidad"></td>
                <td x-text="'$' + Number(d.precio_unit).toLocaleString('es-CO')"></td>
                <td><strong x-text="'$' + Number(d.subtotal).toLocaleString('es-CO')"></strong></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" @click="mostrarDetalle=false">Cerrar</button></div>
    </div>
  </div>
</div>
