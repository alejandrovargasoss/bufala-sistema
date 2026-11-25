/**
 * app.js - Dibufala S.A.S. (El Rosal, Cundinamarca) v2.1
 * CRUD + Roles (Admin / Cliente) + Compra Directa + Factura Automática + IA
 */

const API = (window.APP_BASE_URL || '') + '/index.php';

function crearAlerta(ctx, mensaje, tipo = 'success') {
  ctx.mensaje = mensaje;
  ctx.tipoMsg = tipo;
  setTimeout(() => { ctx.mensaje = ''; }, 4500);
}

// PRODUCTOS
function productosApp() {
  return {
    productos:    [],
    categorias:   [],
    busqueda:     '',
    cargando:     true,
    generandoIA:  false,
    mostrarModal: false,
    modoEdicion:  false,
    mensaje:      '',
    tipoMsg:      'success',
    form: { id:null, nombre:'', descripcion:'', precio:'', stock:'', marca:'', categoria_id:'' },

    get productosFiltrados() {
      const q = this.busqueda.toLowerCase().trim();
      if (!q) return this.productos;
      return this.productos.filter(p =>
        (p.nombre || '').toLowerCase().includes(q) ||
        (p.marca || '').toLowerCase().includes(q) ||
        (p.categoria || '').toLowerCase().includes(q)
      );
    },

    async init() { await this.cargar(); await this.cargarCategorias(); },

    async cargar() {
      this.cargando = true;
      try {
        const res  = await fetch(`${API}?modulo=productos&accion=obtenerTodos`);
        const json = await res.json();
        this.productos = json.ok ? json.datos : [];
      } catch (e) { crearAlerta(this, 'Error de conexión.', 'danger'); }
      finally { this.cargando = false; }
    },

    async cargarCategorias() {
      try {
        const res  = await fetch(`${API}?modulo=productos&accion=categorias`);
        const json = await res.json();
        this.categorias = json.ok ? json.datos : [];
      } catch (e) {}
    },

    abrirFormulario() {
      this.modoEdicion  = false;
      this.form         = { id:null, nombre:'', descripcion:'', precio:'', stock:'', marca:'', categoria_id:'' };
      this.mostrarModal = true;
    },

    editar(p) {
      this.modoEdicion  = true;
      this.form         = { ...p };
      this.mostrarModal = true;
    },

    cerrarModal() { this.mostrarModal = false; },

    async generarDescripcionIA() {
      if (!this.form.nombre) {
        crearAlerta(this, 'Escribe primero el nombre del producto.', 'warning');
        return;
      }
      this.generandoIA = true;
      try {
        const res = await fetch(`${API}?modulo=ia&accion=generarDescripcion`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ nombre: this.form.nombre, marca: this.form.marca })
        });
        const json = await res.json();
        if (json.ok) this.form.descripcion = json.descripcion;
      } catch (e) {
        crearAlerta(this, 'No se pudo generar la descripción con IA.', 'danger');
      } finally {
        this.generandoIA = false;
      }
    },

    async guardar() {
      if (!this.form.nombre) {
        crearAlerta(this, 'El nombre es obligatorio.', 'warning'); return;
      }
      if (Number(this.form.precio) <= 0) {
        crearAlerta(this, 'El precio debe ser mayor a 0.', 'warning'); return;
      }
      if (Number(this.form.stock) < 0) {
        crearAlerta(this, 'El stock no puede ser negativo.', 'warning'); return;
      }
      const accion = this.modoEdicion ? 'actualizar' : 'guardar';
      try {
        const res  = await fetch(`${API}?modulo=productos&accion=${accion}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form),
        });
        const json = await res.json();
        crearAlerta(this, json.mensaje, json.ok ? 'success' : 'danger');
        if (json.ok) { this.cerrarModal(); await this.cargar(); }
      } catch (e) { crearAlerta(this, 'Error al guardar.', 'danger'); }
    },

    async eliminar(id) {
      if (!confirm('¿Eliminar este producto?')) return;
      try {
        const res  = await fetch(`${API}?modulo=productos&accion=eliminar&id=${id}`, { method: 'DELETE' });
        const json = await res.json();
        crearAlerta(this, json.mensaje, json.ok ? 'success' : 'danger');
        if (json.ok) await this.cargar();
      } catch (e) { crearAlerta(this, 'Error al eliminar.', 'danger'); }
    },
  };
}

// CLIENTES con IA
function clientesApp() {
  return {
    clientes:            [],
    busqueda:            '',
    cargando:            true,
    mostrarModal:        false,
    mostrarIA:           false,
    cargandoIA:          false,
    sugerencias:         [],
    clienteSeleccionado: null,
    modoEdicion:         false,
    mensaje:             '',
    tipoMsg:             'success',
    form: { id:null, nombre:'', apellido:'', documento:'', telefono:'', email:'', direccion:'', estado:'activo' },

    get clientesFiltrados() {
      const q = this.busqueda.toLowerCase().trim();
      if (!q) return this.clientes;
      return this.clientes.filter(c =>
        `${c.nombre} ${c.apellido}`.toLowerCase().includes(q) ||
        (c.documento || '').toLowerCase().includes(q)
      );
    },

    async init() { await this.cargar(); },

    async cargar() {
      this.cargando = true;
      try {