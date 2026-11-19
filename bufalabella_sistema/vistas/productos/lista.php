<div class="card" x-data="productosApp()">
  <template x-if="mensaje">
    <div :class="`alert alert-${tipoMsg}`" x-text="mensaje"></div>
  </template>
  <div class="card-header">
    <h2>🧀 Inventario de Productos Lácteos</h2>
    <div style="display:flex; gap:10px; align-items:center;">
      <input type="text" x-model="busqueda" placeholder="Buscar producto o marca..." style="padding:7px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;">
      <button class="btn btn-primary" @click="abrirFormulario()">
        <i class="bi bi-plus-lg"></i> Nuevo Producto
      </button>
    </div>
  </div>
  <div class="card-body">
    <div x-show="cargando" class="spinner-wrap"><div class="spinner"></div> Cargando inventario...</div>
    <div x-show="!cargando" class="table-responsive">
      <table>
        <thead><tr><th>#</th><th>Nombre</th><th>Marca</th><th>Precio</th><th>Stock</th><th>Categoría</th><th>Acciones</th></tr></thead>
        <tbody>
          <template x-for="p in productosFiltrados" :key="p.id">
            <tr>
              <td x-text="p.id"></td>
              <td>
                <strong x-text="p.nombre"></strong>
                <div style="font-size:12px; color:#94A3B8;" x-text="p.descripcion || ''"></div>
              </td>
              <td x-text="p.marca || '—'"></td>
              <td x-text="'$' + Number(p.precio).toLocaleString('es-CO')"></td>
              <td><span :class="Number(p.stock) < 5 ? 'stock-bajo' : ''" x-text="p.stock + ' uds.'"></span></td>
              <td><span class="badge badge-info" x-text="p.categoria || 'General'"></span></td>
              <td style="display:flex;gap:6px;">
                <button class="btn btn-secondary btn-sm" @click="editar(p)" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-danger btn-sm" @click="eliminar(p.id)" title="Eliminar"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          </template>
          <template x-if="!cargando && productosFiltrados.length===0">
            <tr><td colspan="7"><div class="empty-state"><p>No hay productos registrados.</p></div></td></tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal formulario -->
  <div class="modal-overlay" x-show="mostrarModal" x-cloak>
    <div class="modal">
      <div class="modal-header">
        <h3 x-text="modoEdicion ? '✏️ Editar Producto' : '➕ Nuevo Producto'"></h3>
        <button class="btn-close" @click="cerrarModal()">✕</button>
      </div>
      <div class="modal-body">
        <div class="form-grid-2">
          <div class="form-group"><label>Nombre *</label><input type="text" x-model="form.nombre" placeholder="Ej. Burrata de Búfala 250g"></div>
          <div class="form-group"><label>Marca</label><input type="text" x-model="form.marca" placeholder="Ej. Dibufala"></div>
          <div class="form-group"><label>Precio (COP) *</label><input type="number" x-model="form.precio" placeholder="0" min="1"></div>
          <div class="form-group"><label>Stock disponible *</label><input type="number" x-model="form.stock" placeholder="0" min="0"></div>
        </div>
        <div class="form-group">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
            <label style="margin:0;">Descripción</label>
            <button type="button" class="btn btn-secondary btn-sm" @click="generarDescripcionIA()" :disabled="generandoIA">
              <span x-text="generandoIA ? '✨ Redactando...' : '✨ Redactar con IA'"></span>
            </button>
          </div>
          <textarea x-model="form.descripcion" rows="2" placeholder="Ej. Queso artesanal 100% leche de búfala, textura cremosa..."></textarea>
        </div>
        <div class="form-group"><label>Categoría</label>
          <select x-model="form.categoria_id">
            <option value="">Seleccionar categoría</option>
            <template x-for="c in categorias" :key="c.id">
              <option :value="c.id" x-text="c.nombre"></option>
            </template>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" @click="cerrarModal()">Cancelar</button>
        <button class="btn btn-primary" @click="guardar()" x-text="modoEdicion?'Actualizar':'Guardar'"></button>
      </div>
    </div>
  </div>
</div>
