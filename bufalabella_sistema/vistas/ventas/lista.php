<div class="card" x-data="ventasApp()">
  <template x-if="mensaje">
    <div :class="`alert alert-${tipoMsg}`" x-text="mensaje"></div>
  </template>
  <div class="card-header">
    <h2>🧾 Registro de Ventas y Facturación · Dibufala S.A.S.</h2>
    <button class="btn btn-primary" @click="abrirFormulario()"><i class="bi bi-plus-lg"></i> Nueva Venta y Factura</button>
  </div>
  <div class="card-body">
    <div x-show="cargando" class="spinner-wrap"><div class="spinner"></div> Cargando ventas...</div>
    <div x-show="!cargando" class="table-responsive">
      <table>
        <thead><tr><th>Factura</th><th>Cliente</th><th>Fecha</th><th>Método de Pago</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <template x-for="v in ventas" :key="v.id">
            <tr>
              <td><strong style="color:#1F513B;" x-text="'FAC-' + String(v.id).padStart(4, '0')"></strong></td>
              <td x-text="v.cliente || 'Cliente #' + v.id_cliente"></td>
              <td x-text="v.fecha"></td>
              <td>
                <span class="badge badge-info" x-text="extraerMetodoPago(v.observacion)"></span>
              </td>
              <td><strong x-text="'$'+Number(v.total).toLocaleString('es-CO')"></strong></td>
              <td>
                <span :class="v.estado==='completada'?'badge badge-success':v.estado==='pendiente'?'badge badge-warning':'badge badge-danger'"
                      x-text="v.estado"></span>
              </td>
              <td style="display:flex; gap:6px; flex-wrap:wrap;">
                <button class="btn btn-gold btn-sm" @click="abrirFactura(v)" title="Ver e imprimir factura"><i class="bi bi-printer-fill"></i> Factura</button>
                <button class="btn btn-secondary btn-sm" @click="verDetalle(v)" title="Ver detalle"><i class="bi bi-eye"></i></button>
                <button x-show="v.estado==='pendiente'" class="btn btn-primary btn-sm" @click="completar(v.id)" title="Confirmar pago"><i class="bi bi-check-lg"></i></button>
                <button x-show="v.estado!=='cancelada'" class="btn btn-danger btn-sm" @click="cancelar(v.id)">Cancelar</button>
              </td>
            </tr>
          </template>
          <template x-if="!cargando && ventas.length===0">
            <tr><td colspan="7"><div class="empty-state"><p>No hay ventas registradas todavía.</p></div></td></tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal nueva venta con selección de productos y método de pago -->
  <div class="modal-overlay" x-show="mostrarModal" x-cloak>
    <div class="modal" style="max-width: 720px;">
      <div class="modal-header">
        <h3>🛒 Registrar Nueva Venta y Generar Factura</h3>
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
            <label>Estado del pago</label>
            <select x-model="form.estado">
              <option value="completada">Completada (Pagada)</option>
              <option value="pendiente">Pendiente de confirmación</option>
            </select>
          </div>
        </div>

        <!-- Selector de Método de Pago -->
        <div class="form-group">
          <label>💳 Método de Pago *</label>
          <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:8px;">
            <button type="button" @click="form.metodo_pago='PSE'"
              :style="form.metodo_pago==='PSE' ? 'border:2px solid #1F513B; background:#E6F2EC; color:#122B20; font-weight:700;' : 'border:1px solid #E5DFD3; background:#FFF; color:#3F423E;'"
              style="padding:10px; border-radius:10px; cursor:pointer; font-size:13px; text-align:center;">
              🏦 PSE (Bancos)
            </button>
            <button type="button" @click="form.metodo_pago='Tarjeta Crédito / Débito'"
              :style="form.metodo_pago==='Tarjeta Crédito / Débito' ? 'border:2px solid #1F513B; background:#E6F2EC; color:#122B20; font-weight:700;' : 'border:1px solid #E5DFD3; background:#FFF; color:#3F423E;'"
              style="padding:10px; border-radius:10px; cursor:pointer; font-size:13px; text-align:center;">
              💳 Tarjeta Créd/Déb
            </button>
            <button type="button" @click="form.metodo_pago='Nequi / Daviplata'"
              :style="form.metodo_pago==='Nequi / Daviplata' ? 'border:2px solid #1F513B; background:#E6F2EC; color:#122B20; font-weight:700;' : 'border:1px solid #E5DFD3; background:#FFF; color:#3F423E;'"
              style="padding:10px; border-radius:10px; cursor:pointer; font-size:13px; text-align:center;">
              📱 Nequi / Daviplata
            </button>
            <button type="button" @click="form.metodo_pago='Efectivo'"
              :style="form.metodo_pago==='Efectivo' ? 'border:2px solid #1F513B; background:#E6F2EC; color:#122B20; font-weight:700;' : 'border:1px solid #E5DFD3; background:#FFF; color:#3F423E;'"
              style="padding:10px; border-radius:10px; cursor:pointer; font-size:13px; text-align:center;">
              💵 Efectivo
            </button>
          </div>
        </div>

        <!-- Campos dinámicos según PSE o Tarjeta -->
        <div x-show="form.metodo_pago==='PSE'" style="background:#F0FDF4; border:1px solid #BBF7D0; padding:12px; border-radius:10px; margin-bottom:14px;">
          <div class="form-grid-2">
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Banco PSE *</label>
              <select x-model="form.banco_pse" style="padding:8px; font-size:13px;">
                <option value="Bancolombia">Bancolombia</option>
                <option value="Davivienda">Davivienda</option>
                <option value="Banco de Bogotá">Banco de Bogotá</option>
                <option value="BBVA Colombia">BBVA Colombia</option>
                <option value="Nequi">Nequi</option>
                <option value="Daviplata">Daviplata</option>
                <option value="Banco Agrario">Banco Agrario</option>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Tipo de persona</label>
              <select x-model="form.tipo_persona" style="padding:8px; font-size:13px;">
                <option value="Natural">Persona Natural</option>
                <option value="Jurídica">Persona Jurídica (Empresa)</option>
              </select>
            </div>
          </div>
        </div>

        <div x-show="form.metodo_pago==='Tarjeta Crédito / Débito'" style="background:#FFFBEB; border:1px solid #FDE68A; padding:12px; border-radius:10px; margin-bottom:14px;">
          <div class="form-grid-2">
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Franquicia</label>
              <select x-model="form.franquicia" style="padding:8px; font-size:13px;">
                <option value="Visa">Visa</option>
                <option value="Mastercard">Mastercard</option>
                <option value="American Express">American Express</option>
                <option value="Diners Club">Diners Club</option>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Últimos 4 dígitos / Aprobación</label>
              <input type="text" x-model="form.ref_tarjeta" maxlength="10" placeholder="Ej. *4589 / Auth 9021" style="padding:8px; font-size:13px;">
            </div>
          </div>
        </div>

        <!-- Agregar productos al carrito -->
        <div style="background:#F7F5F0; padding:14px; border-radius:12px; border:1px solid #E5DFD3; margin-bottom:16px;">
          <label style="display:block; font-size:13px; font-weight:700; margin-bottom:8px; color:#16221C;">Agregar productos lácteos al pedido</label>
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
            <div style="text-align:right; margin-top:10px; font-size:16px; font-weight:800; color:#16221C;">
              Total a facturar (<span x-text="form.metodo_pago"></span>): <span style="color:#1F513B;" x-text="'$' + totalVenta.toLocaleString('es-CO')"></span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Observación adicional</label>
          <textarea x-model="form.observacion" rows="2" placeholder="Notas de entrega, dirección o referencia de pago..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" @click="cerrarModal()">Cancelar</button>
        <button class="btn btn-primary" @click="guardar()">
          <span x-text="form.metodo_pago==='PSE' || form.metodo_pago==='Tarjeta Crédito / Débito' ? '💳 Procesar Pago y Facturar' : '🧾 Confirmar y Generar Factura'"></span>
        </button>
      </div>
    </div>
  </div>

  <!-- Modal Pasarela de Pago PSE / Tarjeta -->
  <div class="modal-overlay" x-show="mostrarPasarela" x-cloak>
    <div class="modal" style="max-width: 440px; text-align:center;">
      <div class="modal-header">
        <h3>🔒 Pasarela de Pago Segura · Dibufala S.A.S.</h3>
        <button class="btn-close" @click="mostrarPasarela=false">✕</button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <div style="font-size:40px; margin-bottom:10px;" x-text="form.metodo_pago==='PSE' ? '🏦' : '💳'"></div>
        <h4 style="color:#122B20; margin-bottom:6px;" x-text="'Pago vía ' + form.metodo_pago"></h4>
        <p style="font-size:13px; color:#6B7280; margin-bottom:14px;" x-text="form.metodo_pago==='PSE' ? ('Banco seleccionado: ' + form.banco_pse + ' (' + form.tipo_persona + ')') : ('Franquicia: ' + form.franquicia)"></p>
        <div style="background:#F7F5F0; border:1px solid #E5DFD3; border-radius:10px; padding:14px; margin-bottom:16px;">
          <span style="font-size:12px; color:#75756E;">Monto a debitar</span>
          <div style="font-size:24px; font-weight:800; color:#1F513B;" x-text="'$' + totalVenta.toLocaleString('es-CO') + ' COP'"></div>
          <span style="font-size:11px; color:#8A6214;" x-text="'Ref. Transacción: DBUF-' + refTransaccion"></span>
        </div>
        <p style="font-size:12px; color:#75756E; margin-bottom:16px;">
          Al aprobar, se emitirá automáticamente la factura electrónica de Dibufala S.A.S. y se descontará el inventario.
        </p>
        <div style="display:flex; gap:10px; justify-content:center;">
          <button class="btn btn-secondary" @click="mostrarPasarela=false">Cancelar</button>
          <button class="btn btn-primary" @click="confirmarPagoPasarela()">✅ Aprobar y Emitir Factura</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL DE FACTURA OFICIAL IMPRIMIBLE (SE ABRE AUTOMÁTICAMENTE AL COMPRAR) -->
  <div class="modal-overlay" x-show="mostrarFactura" x-cloak>
    <div class="modal" style="max-width: 760px;">
      <div class="modal-header no-print">
        <h3>🧾 Factura Electrónica Generada · Dibufala S.A.S.</h3>
        <button class="btn-close" @click="mostrarFactura=false">✕</button>
      </div>
      <div class="modal-body">
        <div id="area-impresion-factura" class="factura-sheet" x-if="facturaActual">
          <div class="factura-top">
            <div class="factura-brand">
              <h2>🐃 DIBUFALA S.A.S.</h2>
              <p>
                <strong>Productos Lácteos 100% Leche de Búfala · Calidad Artesanal</strong><br>
                NIT: 900.845.219-7 · Régimen Común · Resolución DIAN No. 18764009281<br>
                Planta Principal: Km 2 Vía El Rosal – Subachoque, El Rosal, Cundinamarca<br>
                Tel: (601) 824-5590 · facturacion@dibufala.com.co
              </p>
            </div>
            <div class="factura-folio">
              <span>Factura Electrónica de Venta</span>
              <strong x-text="'FAC-' + String(facturaActual.id).padStart(4, '0')"></strong>
              <p style="font-size:11px; color:#5C615D; margin-top:4px;" x-text="'Fecha: ' + facturaActual.fecha"></p>
              <span class="badge badge-success" style="margin-top:6px;" x-text="facturaActual.estado.toUpperCase()"></span>
            </div>
          </div>

          <div class="factura-info-grid">
            <div>
              <strong style="color:#122B20; display:block; margin-bottom:4px;">DATOS DEL CLIENTE</strong>
              <div><strong>Nombre / Razón Social:</strong> <span x-text="facturaActual.cliente_nombre"></span></div>
              <div><strong>Documento / NIT:</strong> <span x-text="facturaActual.cliente_documento || 'No registrado'"></span></div>
              <div><strong>Dirección:</strong> <span x-text="facturaActual.cliente_direccion || 'Entrega en planta El Rosal'"></span></div>
              <div><strong>Teléfono / Email:</strong> <span x-text="(facturaActual.cliente_telefono || 'N/A') + ' · ' + (facturaActual.cliente_email || '')"></span></div>
            </div>
            <div>
              <strong style="color:#122B20; display:block; margin-bottom:4px;">DETALLES DE LA OPERACIÓN</strong>
              <div><strong>Sede Expedición:</strong> El Rosal, Cundinamarca</div>
              <div><strong>Método de Pago:</strong> <span x-text="extraerMetodoPago(facturaActual.observacion)"></span></div>
              <div><strong>Notas / Referencia:</strong> <span x-text="facturaActual.observacion || 'Venta directa'"></span></div>
            </div>
          </div>

          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Descripción del Producto Lácteo</th>
                <th style="text-align:center;">Cant.</th>
                <th style="text-align:right;">Valor Unit.</th>
                <th style="text-align:right;">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <template x-for="(item, idx) in facturaActual.items" :key="idx">
                <tr>
                  <td x-text="idx + 1"></td>
                  <td><strong x-text="item.producto || item.nombre"></strong></td>
                  <td style="text-align:center;" x-text="item.cantidad"></td>
                  <td style="text-align:right;" x-text="'$' + Number(item.precio_unit || item.precio).toLocaleString('es-CO')"></td>
                  <td style="text-align:right;"><strong x-text="'$' + Number(item.subtotal || (item.cantidad * (item.precio_unit || item.precio))).toLocaleString('es-CO')"></strong></td>
                </tr>
              </template>
            </tbody>
          </table>

          <div class="factura-totales">
            <div>
              <span>Subtotal Base (Sin IVA):</span>
              <span x-text="'$' + Math.round(Number(facturaActual.total) / 1.19).toLocaleString('es-CO')"></span>
            </div>
            <div>
              <span>IVA Incluido (19%):</span>
              <span x-text="'$' + (Number(facturaActual.total) - Math.round(Number(facturaActual.total) / 1.19)).toLocaleString('es-CO')"></span>
            </div>
            <div class="gran-total">
              <span>TOTAL PAGADO:</span>
              <span x-text="'$' + Number(facturaActual.total).toLocaleString('es-CO') + ' COP'"></span>
            </div>
          </div>

          <div class="factura-footer-note">
            <div>
              <strong>¡Gracias por preferir Dibufala S.A.S. · El Rosal, Cundinamarca!</strong><br>
              Cadena de frío: Mantener refrigerado entre 2°C y 4°C para conservar su frescura.
            </div>
            <div style="text-align:right; font-family:monospace; background:#F7F5F0; padding:6px 10px; border-radius:6px; border:1px solid #E5DFD3;">
              CUFE-DBUF-<span x-text="String(facturaActual.id).padStart(4,'0')"></span>-2026
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer no-print">
        <button class="btn btn-secondary" @click="mostrarFactura=false">Cerrar</button>
        <button class="btn btn-gold" @click="imprimirFactura()"><i class="bi bi-printer-fill"></i> Imprimir / Guardar PDF</button>
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
        <p style="margin-bottom:6px; font-size:13px;"><strong>Cliente:</strong> <span x-text="ventaSeleccionada?.cliente"></span></p>
        <p style="margin-bottom:12px; font-size:13px;"><strong>Pago y Notas:</strong> <span x-text="ventaSeleccionada?.observacion || 'Sin observaciones'"></span></p>
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
      <div class="modal-footer">
        <button class="btn btn-gold" @click="mostrarDetalle=false; abrirFactura(ventaSeleccionada)"><i class="bi bi-printer-fill"></i> Ver Factura</button>
        <button class="btn btn-secondary" @click="mostrarDetalle=false">Cerrar</button>
      </div>
    </div>
  </div>
</div>