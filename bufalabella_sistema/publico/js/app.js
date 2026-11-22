/**
 * app.js - Funciones Alpine.js Dibufala S.A.S. (El Rosal, Cundinamarca)
 * CRUD + Ventas Multi-producto + Generación Automática de Factura + IA Gemini
 */

const API = (window.APP_BASE_URL || '') + '/index.php';

/** Helper para mostrar alertas */
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
        const res  = await fetch(`${API}?modulo=clientes&accion=obtenerTodos`);
        const json = await res.json();
        this.clientes = json.ok ? json.datos : [];
      } catch (e) { crearAlerta(this, 'Error de conexión.', 'danger'); }
      finally { this.cargando = false; }
    },

    abrirFormulario() {
      this.modoEdicion  = false;
      this.form         = { id:null, nombre:'', apellido:'', documento:'', telefono:'', email:'', direccion:'', estado:'activo' };
      this.mostrarModal = true;
    },

    editar(c) { this.modoEdicion = true; this.form = { ...c }; this.mostrarModal = true; },
    cerrarModal() { this.mostrarModal = false; },

    async guardar() {
      if (!this.form.nombre || !this.form.apellido || !this.form.documento) {
        crearAlerta(this, 'Nombre, apellido y documento son obligatorios.', 'warning');
        return;
      }
      const accion = this.modoEdicion ? 'actualizar' : 'guardar';
      try {
        const res  = await fetch(`${API}?modulo=clientes&accion=${accion}`, {
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
      if (!confirm('¿Eliminar este cliente?')) return;
      try {
        const res  = await fetch(`${API}?modulo=clientes&accion=eliminar&id=${id}`, { method: 'DELETE' });
        const json = await res.json();
        crearAlerta(this, json.mensaje, json.ok ? 'success' : 'danger');
        if (json.ok) await this.cargar();
      } catch (e) { crearAlerta(this, 'Error al eliminar.', 'danger'); }
    },

    async verSugerenciasIA(cliente) {
      this.clienteSeleccionado = cliente;
      this.sugerencias         = [];
      this.cargandoIA          = true;
      this.mostrarIA           = true;
      try {
        const res  = await fetch(`${API}?modulo=ia&accion=sugerirProductos&id=${cliente.id}`);
        const json = await res.json();
        this.sugerencias = json.datos?.sugerencias ?? [];
      } catch (e) {
        this.sugerencias = ['Burrata de Búfala Artesanal 250g', 'Yogur Griego de Búfala Natural 500g', 'Mantequilla de Búfala Artesanal 250g'];
      } finally { this.cargandoIA = false; }
    },
  };
}

// VENTAS + FACTURACIÓN AUTOMÁTICA DIBUFALA S.A.S.
function ventasApp() {
  return {
    ventas:            [],
    clientes:          [],
    productos:         [],
    cargando:          true,
    mostrarModal:      false,
    mostrarPasarela:   false,
    mostrarDetalle:    false,
    mostrarFactura:    false,
    facturaActual:     null,
    refTransaccion:    '',
    ventaSeleccionada: null,
    detalleItems:      [],
    mensaje:           '',
    tipoMsg:           'success',
    itemTemp:          { id_producto: '', cantidad: 1 },
    form:              { id_cliente: '', estado: 'completada', metodo_pago: 'PSE', banco_pse: 'Bancolombia', tipo_persona: 'Natural', franquicia: 'Visa', ref_tarjeta: '', observacion: '', items: [] },

    extraerMetodoPago(obs) {
      if (!obs) return '💵 Efectivo';
      if (obs.includes('[PSE')) return '🏦 PSE';
      if (obs.includes('[Tarjeta')) return '💳 Tarjeta';
      if (obs.includes('[Nequi')) return '📱 Nequi/Daviplata';
      return '💵 Efectivo';
    },

    get productosDisponibles() {
      return this.productos.filter(p => Number(p.stock) > 0);
    },

    get totalVenta() {
      return this.form.items.reduce((acc, it) => acc + (Number(it.precio) * Number(it.cantidad)), 0);
    },

    async init() {
      await Promise.all([this.cargar(), this.cargarAuxiliares()]);
    },

    async cargar() {
      this.cargando = true;
      try {
        const res  = await fetch(`${API}?modulo=ventas&accion=obtenerTodos`);
        const json = await res.json();
        this.ventas = json.ok ? json.datos : [];
      } catch (e) { crearAlerta(this, 'Error de conexión.', 'danger'); }
      finally { this.cargando = false; }
    },

    async cargarAuxiliares() {
      try {
        const [rc, rp] = await Promise.all([
          fetch(`${API}?modulo=clientes&accion=obtenerTodos`).then(r => r.json()),
          fetch(`${API}?modulo=productos&accion=obtenerTodos`).then(r => r.json()),
        ]);
        this.clientes  = rc.ok ? rc.datos : [];
        this.productos = rp.ok ? rp.datos : [];
      } catch (e) {}
    },

    async abrirFormulario() {
      await this.cargarAuxiliares();
      this.form         = { id_cliente: '', estado: 'completada', metodo_pago: 'PSE', banco_pse: 'Bancolombia', tipo_persona: 'Natural', franquicia: 'Visa', ref_tarjeta: '', observacion: '', items: [] };
      this.itemTemp     = { id_producto: '', cantidad: 1 };
      this.mostrarModal = true;
    },

    cerrarModal() { this.mostrarModal = false; },

    agregarItem() {
      const idProd = Number(this.itemTemp.id_producto);
      const cant   = Number(this.itemTemp.cantidad);
      if (!idProd || cant <= 0) {
        crearAlerta(this, 'Selecciona un producto y una cantidad válida.', 'warning');
        return;
      }
      const prod = this.productos.find(p => Number(p.id) === idProd);
      if (!prod) return;

      const existente = this.form.items.find(i => i.id_producto === idProd);
      const cantTotal = (existente ? existente.cantidad : 0) + cant;

      if (cantTotal > Number(prod.stock)) {
        crearAlerta(this, `Stock insuficiente. Solo hay ${prod.stock} unidades disponibles.`, 'warning');
        return;
      }

      if (existente) {
        existente.cantidad = cantTotal;
      } else {
        this.form.items.push({
          id_producto: idProd,
          nombre:      prod.nombre,
          precio:      Number(prod.precio),
          cantidad:    cant
        });
      }
      this.itemTemp = { id_producto: '', cantidad: 1 };
    },

    quitarItem(idx) {
      this.form.items.splice(idx, 1);
    },

    async guardar() {
      if (!this.form.id_cliente) {
        crearAlerta(this, 'Selecciona un cliente.', 'warning'); return;
      }
      if (this.form.items.length === 0) {
        crearAlerta(this, 'Agrega al menos un producto a la venta.', 'warning'); return;
      }
      if (this.form.metodo_pago === 'PSE' || this.form.metodo_pago === 'Tarjeta Crédito / Débito') {
        this.refTransaccion = Math.floor(100000 + Math.random() * 900000).toString();
        this.mostrarPasarela = true;
        return;
      }
      await this.enviarVentaServidor('');
    },

    async confirmarPagoPasarela() {
      this.mostrarPasarela = false;
      const detallePago = this.form.metodo_pago === 'PSE'
        ? `[PSE · ${this.form.banco_pse} (${this.form.tipo_persona}) · Ref: DBUF-${this.refTransaccion}]`
        : `[Tarjeta ${this.form.franquicia} ${this.form.ref_tarjeta} · Ref: DBUF-${this.refTransaccion}]`;
      await this.enviarVentaServidor(detallePago);
    },

    async enviarVentaServidor(prefijoPago) {
      let tag = prefijoPago;
      if (!tag) {
        tag = this.form.metodo_pago === 'Nequi / Daviplata' ? '[Nequi / Daviplata]' : '[Efectivo]';
      }
      const obsFinal = `${tag} ${this.form.observacion || ''}`.trim();
      const payload = { ...this.form, observacion: obsFinal };

      const clienteObj = this.clientes.find(c => Number(c.id) === Number(this.form.id_cliente));
      const itemsCopia = JSON.parse(JSON.stringify(this.form.items));
      const totalCopia = this.totalVenta;
      const estadoCopia = this.form.estado;

      try {
        const res  = await fetch(`${API}?modulo=ventas&accion=guardar`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
        const json = await res.json();
        crearAlerta(this, json.mensaje, json.ok ? 'success' : 'danger');
        if (json.ok) {
          this.cerrarModal();
          await this.cargar();
          const nuevaVenta = this.ventas.length > 0 ? this.ventas[0] : null;
          const idFactura = json.id || (nuevaVenta ? nuevaVenta.id : Date.now().toString().slice(-4));

          this.facturaActual = {
            id: idFactura,
            fecha: nuevaVenta ? nuevaVenta.fecha : new Date().toLocaleString('es-CO'),
            estado: estadoCopia,
            total: totalCopia,
            observacion: obsFinal,
            cliente_nombre: clienteObj ? `${clienteObj.nombre} ${clienteObj.apellido}` : 'Cliente General',
            cliente_documento: clienteObj?.documento || '',
            cliente_direccion: clienteObj?.direccion || '',
            cliente_telefono: clienteObj?.telefono || '',
            cliente_email: clienteObj?.email || '',
            items: itemsCopia
          };
          this.mostrarFactura = true;
        }
      } catch (e) { crearAlerta(this, 'Error al registrar venta.', 'danger'); }
    },

    async abrirFactura(venta) {
      if (!venta) return;
      const clienteObj = this.clientes.find(c => Number(c.id) === Number(venta.id_cliente));
      let itemsFactura = [];
      try {
        const res = await fetch(`${API}?modulo=ventas&accion=detalle&id=${venta.id}`);
        const json = await res.json();
        itemsFactura = json.ok ? json.datos : [];
      } catch (e) {}

      this.facturaActual = {
        id: venta.id,
        fecha: venta.fecha,
        estado: venta.estado,
        total: venta.total,
        observacion: venta.observacion,
        cliente_nombre: venta.cliente || (clienteObj ? `${clienteObj.nombre} ${clienteObj.apellido}` : 'Cliente #' + venta.id_cliente),
        cliente_documento: clienteObj?.documento || '',
        cliente_direccion: clienteObj?.direccion || '',
        cliente_telefono: clienteObj?.telefono || '',
        cliente_email: clienteObj?.email || '',
        items: itemsFactura
      };
      this.mostrarFactura = true;
    },

    imprimirFactura() {
      window.print();
    },

    async verDetalle(venta) {
      this.ventaSeleccionada = venta;
      this.detalleItems      = [];
      this.mostrarDetalle    = true;
      try {
        const res  = await fetch(`${API}?modulo=ventas&accion=detalle&id=${venta.id}`);
        const json = await res.json();
        this.detalleItems = json.ok ? json.datos : [];
      } catch (e) {}
    },

    async completar(id) {
      try {
        const res  = await fetch(`${API}?modulo=ventas&accion=completar&id=${id}`, { method: 'POST' });
        const json = await res.json();
        crearAlerta(this, json.mensaje, json.ok ? 'success' : 'danger');
        if (json.ok) await this.cargar();
      } catch (e) {}
    },

    async cancelar(id) {
      if (!confirm('¿Cancelar esta venta y devolver los productos al inventario?')) return;
      try {
        const res  = await fetch(`${API}?modulo=ventas&accion=cancelar&id=${id}`, { method: 'DELETE' });
        const json = await res.json();
        crearAlerta(this, json.mensaje, json.ok ? 'success' : 'danger');
        if (json.ok) await this.cargar();
      } catch (e) { crearAlerta(this, 'Error al cancelar.', 'danger'); }
    },
  };
}

// REPORTES
function reportesApp() {
  return {
    topProductos: [],
    stockBajo:    [],
    cargando:     true,
    async init() {
      this.cargando = true;
      try {
        const [r1, r2] = await Promise.all([
          fetch(`${API}?modulo=reportes&accion=topProductos`).then(r => r.json()),
          fetch(`${API}?modulo=reportes&accion=stockBajo`).then(r => r.json()),
        ]);
        this.topProductos = r1.ok ? r1.datos : [];
        this.stockBajo    = r2.ok ? r2.datos : [];
      } catch (e) { console.error('Error reportes:', e); }
      finally { this.cargando = false; }
    },
  };
}

// IA - RESUMEN INTELIGENTE
function resumenIAApp() {
  return {
    datos:    null,
    cargando: true,
    async cargar() {
      this.cargando = true;
      try {
        const res  = await fetch(`${API}?modulo=ia&accion=resumenNegocio`);
        const json = await res.json();
        this.datos = json.ok ? json.datos : null;
      } catch (e) { console.error('IA no disponible:', e); }
      finally { this.cargando = false; }
    },
  };
}

// IA - PREDICCIÓN DE AGOTAMIENTO
function prediccionApp() {
  return {
    predicciones: [],
    cargando:     true,
    async cargar() {
      this.cargando = true;
      try {
        const res  = await fetch(`${API}?modulo=ia&accion=predecirAgotamiento`);
        const json = await res.json();
        this.predicciones = json.ok ? json.datos : [];
      } catch (e) { console.error('Error predicción:', e); }
      finally { this.cargando = false; }
    },
  };
}