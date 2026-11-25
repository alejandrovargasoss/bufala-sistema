// VENTAS + LOGÍSTICA CADENA DE FRÍO + FACTURA AUTOMÁTICA DIBUFALA S.A.S.
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
    form:              { id_cliente: '', estado: 'completada', ruta_entrega: 'Planta El Rosal', metodo_pago: 'PSE', banco_pse: 'Bancolombia', tipo_persona: 'Natural', franquicia: 'Visa', ref_tarjeta: '', observacion: '', items: [] },

    extraerMetodoPago(obs) {
      if (!obs) return '💵 Efectivo';
      if (obs.includes('[PSE')) return '🏦 PSE';
      if (obs.includes('[Tarjeta')) return '💳 Tarjeta';
      if (obs.includes('[Nequi')) return '📱 Nequi/Daviplata';
      return '💵 Efectivo';
    },

    extraerRuta(obs) {
      if (!obs) return '📍 Planta El Rosal';
      const match = obs.match(/\{Ruta:\s*([^}]+)\}/);
      return match ? '🚚 ' + match[1] : '📍 Planta El Rosal';
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
        const rp = await fetch(`${API}?modulo=productos&accion=obtenerTodos`).then(r => r.json());
        this.productos = rp.ok ? rp.datos : [];

        if (window.APP_ES_ADMIN) {
          const rc = await fetch(`${API}?modulo=clientes&accion=obtenerTodos`).then(r => r.json());
          this.clientes = rc.ok ? rc.datos : [];
        }
      } catch (e) {}
    },

    async abrirFormulario() {
      await this.cargarAuxiliares();
      this.form         = { id_cliente: '', estado: 'completada', ruta_entrega: 'Planta El Rosal', metodo_pago: 'PSE', banco_pse: 'Bancolombia', tipo_persona: 'Natural', franquicia: 'Visa', ref_tarjeta: '', observacion: '', items: [] };
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
      if (window.APP_ES_ADMIN && !this.form.id_cliente) {
        crearAlerta(this, 'Selecciona un cliente.', 'warning'); return;
      }
      if (this.form.items.length === 0) {
        crearAlerta(this, 'Agrega al menos un producto.', 'warning'); return;
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
      const rutaTag = `{Ruta: ${this.form.ruta_entrega}}`;
      const obsFinal = `${tag} ${rutaTag} ${this.form.observacion || ''}`.trim();
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
          const idFactura = json.id_venta || json.id || (nuevaVenta ? nuevaVenta.id : Date.now().toString().slice(-4));

          this.facturaActual = {
            id: idFactura,
            fecha: nuevaVenta ? nuevaVenta.fecha : new Date().toLocaleString('es-CO'),
            estado: estadoCopia,
            total: totalCopia,
            observacion: obsFinal,
            cliente_nombre: clienteObj ? `${clienteObj.nombre} ${clienteObj.apellido}` : (window.APP_USER_NAME || 'Cliente Web'),
            cliente_documento: clienteObj?.documento || '',
            cliente_direccion: clienteObj?.direccion || '',
            cliente_telefono: clienteObj?.telefono || '',
            cliente_email: clienteObj?.email || '',
            items: itemsCopia
          };
          this.mostrarFactura = true;
        }
      } catch (e) { crearAlerta(this, 'Error al registrar compra.', 'danger'); }
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
        cliente_nombre: window.APP_ES_ADMIN
          ? (venta.cliente || (clienteObj ? `${clienteObj.nombre} ${clienteObj.apellido}` : 'Cliente #' + venta.id_cliente))
          : (window.APP_USER_NAME || 'Cliente Web'),
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