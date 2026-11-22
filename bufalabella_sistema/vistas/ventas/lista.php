<div x-data="ventasApp()">
  <!-- MONITOR DE CADENA DE FRÍO EL ROSAL -->
  <div class="cold-monitor-grid">
    <div class="cold-truck-card">
      <div>
        <h4>🚛 Furgón #01 · Ruta Sabana Occidente</h4>
        <p>El Rosal → Madrid → Funza → Mosquera</p>
      </div>
      <span class="temp-badge">2.9°C</span>
    </div>
    <div class="cold-truck-card">
      <div>
        <h4>🚚 Furgón #02 · Ruta Bogotá Norte</h4>
        <p>El Rosal → Calle 80 → Bogotá</p>
      </div>
      <span class="temp-badge">3.3°C</span>
    </div>
    <div class="cold-truck-card">
      <div>
        <h4>❄️ Cava Principal · Planta El Rosal</h4>
        <p>Control Térmico Activo (2°C – 4°C)</p>
      </div>
      <span class="temp-badge">3.1°C</span>
    </div>
  </div>

  <div class="card">
    <template x-if="mensaje">
      <div :class="`alert alert-${tipoMsg}`" x-text="mensaje"></div>
    </template>
    <div class="card-header">
      <h2>🧾 Historial de Ventas y Facturas · Dibufala S.A.S.</h2>
      <button class="btn btn-primary" @click="abrirFormulario()"><i class="bi bi-plus-circle-fill"></i> Crear Nueva Venta</button>
    </div>
    <div class="card-body">
      <div x-show="cargando" class="spinner-wrap"><div class="spinner"></div> Cargando facturas...</div>
      <div x-show="!cargando" class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>N° Factura</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Pago y Ruta</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Opciones</th>
            </tr>
          </thead>
          <tbody>
            <template x-for="v in ventas" :key="v.id">
              <tr>
                <td><strong style="color:#D91E2A;" x-text="'FAC-' + String(v.id).padStart(4, '0')"></strong></td>
                <td x-text="v.cliente || 'Cliente #' + v.id_cliente"></td>
                <td x-text="v.fecha"></td>
                <td>
                  <span class="badge badge-info" x-text="extraerMetodoPago(v.observacion)"></span>
                  <span class="badge badge-warning" style="margin-left:4px;" x-text="extraerRuta(v.observacion)"></span>
                </td>
                <td><strong x-text="'$'+Number(v.total).toLocaleString('es-CO')"></strong></td>
                <td>
                  <span :class="v.estado==='completada'?'badge badge-success':v.estado==='pendiente'?'badge badge-warning':'badge badge-danger'"
                        x-text="v.estado.toUpperCase()"></span>
                </td>
                <td style="display:flex; gap:6px; flex-wrap:wrap;">
                  <button class="btn btn-dark btn-sm" @click="abrirFactura(v)"><i class="bi bi-printer-fill"></i> Imprimir Factura</button>
                  <button class="btn btn-secondary btn-sm" @click="verDetalle(v)"><i class="bi bi-eye-fill"></i> Ver</button>
                  <button x-show="v.estado==='pendiente'" class="btn btn-primary btn-sm" @click="completar(v.id)"><i class="bi bi-check-lg"></i> Pagar</button>
                  <button x-show="v.estado!=='cancelada'" class="btn btn-danger btn-sm" @click="cancelar(v.id)">Anular</button>
                </td>
              </tr>
            </template>
            <template x-if="!cargando && ventas.length===0">
              <tr><td colspan="7"><div class="empty-state"><p>No hay ventas registradas todavía. Haz clic en <strong>"Crear Nueva Venta"</strong>.</p></div></td></tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- MODAL NUEVA VENTA INTUITIVO -->
  <div class="modal-overlay" x-show="mostrarModal" x-cloak>
    <div class="modal" style="max-width: 740px;">
      <div class="modal-header">
        <h3>🛒 Nueva Venta y Factura · Dibufala S.A.S.</h3>
        <button class="btn-close" @click="cerrarModal()">✕</button>
      </div>
      <div class="modal-body">
        <div class="form-grid-2">
          <div class="form-group">
            <label>1. Selecciona el Cliente *</label>
            <select x-model="form.id_cliente">
              <option value="">-- Elige un cliente de la lista --</option>
              <template x-for="c in clientes" :key="c.id">
                <option :value="c.id" x-text="c.nombre + ' ' + c.apellido + ' (' + c.documento + ')'"></option>
              </template>
            </select>
          </div>
          <div class="form-group">
            <label>2. Ruta de Entrega Refrigerada *</label>
            <select x-model="form.ruta_entrega">
              <option value="Planta El Rosal">📍 Recogida en Planta El Rosal</option>
              <option value="Ruta Sabana Occidente">🚛 Ruta Sabana (Madrid / Funza / Mosquera)</option>
              <option value="Ruta Bogotá Norte">🚚 Ruta Bogotá Calle 80 / Norte</option>
              <option value="Ruta Subachoque-Facatativá">🚐 Ruta Subachoque / Facatativá</option>
            </select>
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label>3. Forma de Pago *</label>
            <select x-model="form.metodo_pago">
              <option value="PSE">🏦 PSE (Transferencia Bancaria)</option>
              <option value="Tarjeta Crédito / Débito">💳 Tarjeta Crédito / Débito</option>
              <option value="Nequi / Daviplata">📱 Nequi / Daviplata</option>
              <option value="Efectivo">💵 Efectivo</option>
            </select>
          </div>
          <div class="form-group">
            <label>4. Estado del Pago</label>
            <select x-model="form.estado">
              <option value="completada">✅ Pagada (Completada)</option>
              <option value="pendiente">⏳ Pendiente de pago</option>
            </select>
          </div>
        </div>

        <!-- Opciones PSE / Tarjeta -->
        <div x-show="form.metodo_pago==='PSE'" style="background:#FFFBEB; border:1px solid #F5B800; padding:12px; border-radius:12px; margin-bottom:14px;">
          <div class="form-grid-2">
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Banco PSE</label>
              <select x-model="form.banco_pse" style="padding:8px;">
                <option value="Bancolombia">Bancolombia</option>
                <option value="Davivienda">Davivienda</option>
                <option value="Banco de Bogotá">Banco de Bogotá</option>
                <option value="BBVA Colombia">BBVA Colombia</option>
                <option value="Banco Agrario">Banco Agrario</option>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Tipo de Persona</label>
              <select x-model="form.tipo_persona" style="padding:8px;">
                <option value="Natural">Persona Natural</option>
                <option value="Jurídica">Persona Jurídica</option>
              </select>
            </div>
          </div>
        </div>

        <div x-show="form.metodo_pago==='Tarjeta Crédito / Débito'" style="background:#FEF2F2; border:1px solid #D91E2A; padding:12px; border-radius:12px; margin-bottom:14px;">
          <div class="form-grid-2">
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Franquicia</label>
              <select x-model="form.franquicia" style="padding:8px;">
                <option value="Visa">Visa</option>
                <option value="Mastercard">Mastercard</option>
                <option value="American Express">American Express</option>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Últimos 4 dígitos</label>
              <input type="text" x-model="form.ref_tarjeta" maxlength="10" placeholder="Ej. *4589" style="padding:8px;">
            </div>
          </div>
        </div>

        <!-- Paso 5: Agregar Productos -->
        <div style="background:#F6F6F4; padding:14px; border-radius:14px; border:1.5px solid #E2E2DF; margin-bottom:16px;">
          <label style="display:block; font-size:13px; font-weight:800; margin-bottom:8px; color:#111111;">5. Agrega los Productos Lácteos al Pedido</label>
          <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <select x-model="itemTemp.id_producto" style="flex:2; min-width:200px; padding:10px; border-radius:10px; border:1.5px solid #D4D4D8;">
              <option value="">-- Selecciona un producto --</option>
              <template x-for="p in productosDisponibles" :key="p.id">
                <option :value="p.id" x-text="p.nombre + ' ($' + Number(p.precio).toLocaleString('es-CO') + ' · Disponibles: ' + p.stock + ')'"></option>
              </template>
            </select>
            <input type="number" x-model="itemTemp.cantidad" min="1" placeholder="Cant." style="width:85px; padding:10px; border-radius:10px; border:1.5px solid #D4D4D8;">
            <button type="button" class="btn btn-dark" @click="agregarItem()">+ Agregar</button>
          </div>

          <div x-show="form.items.length > 0" style="margin-top:12px;">
            <table style="font-size:13px;">
              <thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th><th>Quitar</th></tr></thead>
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
            <div style="text-align:right; margin-top:12px; font-size:17px; font-weight:800;">
              Total a Pagar: <span style="color:#D91E2A;" x-text="'$' + totalVenta.toLocaleString('es-CO') + ' COP'"></span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Notas adicionales (Opcional)</label>
          <textarea x-model="form.observacion" rows="2" placeholder="Indicaciones de entrega o referencia..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" @click="cerrarModal()">Cancelar</button>
        <button class="btn btn-primary" @click="guardar()"><i class="bi bi-check-circle-fill"></i> Guardar Venta y Ver Factura</button>
      </div>
    </div>
  </div>

  <!-- MODAL PASARELA PSE / TARJETA -->
  <div class="modal-overlay" x-show="mostrarPasarela" x-cloak>
    <div class="modal" style="max-width: 430px; text-align:center;">
      <div class="modal-header">
        <h3>🔒 Confirmación de Pago · Dibufala S.A.S.</h3>
        <button class="btn-close" @click="mostrarPasarela=false">✕</button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <div style="font-size:40px; margin-bottom:8px;" x-text="form.metodo_pago==='PSE' ? '🏦' : '💳'"></div>
        <h4 style="margin-bottom:6px;" x-text="'Pago vía ' + form.metodo_pago"></h4>
        <div style="background:#FFFBEB; border:1.5px solid #F5B800; border-radius:12px; padding:14px; margin:14px 0;">
          <span style="font-size:12px; color:#52525B;">Valor de la compra</span>
          <div style="font-size:25px; font-weight:800; color:#D91E2A;" x-text="'$' + totalVenta.toLocaleString('es-CO') + ' COP'"></div>
          <span style="font-size:11px; color:#111111; font-weight:700;" x-text="'Referencia: DBUF-' + refTransaccion"></span>
        </div>
        <div style="display:flex; gap:10px; justify-content:center;">
          <button class="btn btn-secondary" @click="mostrarPasarela=false">Volver</button>
          <button class="btn btn-primary" @click="confirmarPagoPasarela()">✅ Aprobar Pago</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL FACTURA OFICIAL DIBUFALA S.A.S. -->
  <div class="modal-overlay" x-show="mostrarFactura" x-cloak>
    <div class="modal" style="max-width: 760px;">
      <div class="modal-header no-print">
        <h3>🧾 Factura Oficial · Dibufala S.A.S.</h3>
        <button class="btn-close" @click="mostrarFactura=false">✕</button>
      </div>
      <div class="modal-body">
        <div id="area-impresion-factura" class="factura-sheet" x-if="facturaActual">
          <div class="factura-top">
            <div class="factura-brand">
              <h2>🐃 DIBUFALA S.A.S.</h2>
              <p>
                <strong>Productos Lácteos 100% Leche de Búfala</strong><br>
                NIT: 900.845.219-7 · Régimen Común · Res. DIAN 18764009281<br>
                Planta Principal: Km 2 Vía El Rosal – Subachoque, El Rosal, Cundinamarca<br>
                Tel: (601) 824-5590 · ventas@dibufala.com.co
              </p>
            </div>
            <div class="factura-folio">
              <span>Factura de Venta</span>
              <strong x-text="'FAC-' + String(facturaActual.id).padStart(4, '0')"></strong>
              <p style="font-size:11px; color:#E4E4E7; margin-top:4px;" x-text="facturaActual.fecha"></p>
              <span class="badge badge-warning" style="margin-top:6px;" x-text="facturaActual.estado.toUpperCase()"></span>
            </div>
          </div>

          <div class="factura-info-grid">
            <div>
              <strong style="display:block; margin-bottom:4px;">DATOS DEL CLIENTE</strong>
              <div><strong>Nombre:</strong> <span x-text="facturaActual.cliente_nombre"></span></div>
              <div><strong>CC / NIT:</strong> <span x-text="facturaActual.cliente_documento || 'No registrado'"></span></div>
              <div><strong>Dirección:</strong> <span x-text="facturaActual.cliente_direccion || 'Planta El Rosal'"></span></div>
              <div><strong>Teléfono:</strong> <span x-text="facturaActual.cliente_telefono || 'N/A'"></span></div>
            </div>
            <div>
              <strong style="display:block; margin-bottom:4px;">LOGÍSTICA Y PAGO</strong>
              <div><strong>Ruta de Entrega:</strong> <span x-text="extraerRuta(facturaActual.observacion)"></span></div>
              <div><strong>Cadena de Frío:</strong> ❄️ 3.1°C Certificada</div>
              <div><strong>Forma de Pago:</strong> <span x-text="extraerMetodoPago(facturaActual.observacion)"></span></div>
              <div><strong>Observación:</strong> <span x-text="facturaActual.observacion || 'Venta directa'"></span></div>
            </div>
          </div>

          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Producto Lácteo</th>
                <th style="text-align:center;">Cant.</th>
                <th style="text-align:right;">Unitario</th>
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
              <span>Subtotal Base:</span>
              <span x-text="'$' + Math.round(Number(facturaActual.total) / 1.19).toLocaleString('es-CO')"></span>
            </div>
            <div>
              <span>IVA (19%):</span>
              <span x-text="'$' + (Number(facturaActual.total) - Math.round(Number(facturaActual.total) / 1.19)).toLocaleString('es-CO')"></span>
            </div>
            <div class="gran-total">
              <span>TOTAL FACTURA:</span>
              <span x-text="'$' + Number(facturaActual.total).toLocaleString('es-CO') + ' COP'"></span>
            </div>
          </div>

          <div class="factura-footer-note">
            <div>
              <strong>Dibufala S.A.S. · El Rosal, Cundinamarca</strong><br>
              Conservar refrigerado entre 2°C y 4°C para mantener su frescura.
            </div>
            <div style="font-family:monospace; background:#111111; color:#F5B800; padding:6px 10px; border-radius:8px; font-weight:700;">
              CUFE-DBUF-<span x-text="String(facturaActual.id).padStart(4,'0')"></span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer no-print">
        <button class="btn btn-secondary" @click="mostrarFactura=false">Cerrar</button>
        <button class="btn btn-primary" @click="imprimirFactura()"><i class="bi bi-printer-fill"></i> Imprimir o Guardar PDF</button>
      </div>
    </div>
  </div>

  <!-- MODAL DETALLE -->
  <div class="modal-overlay" x-show="mostrarDetalle" x-cloak>
    <div class="modal">
      <div class="modal-header">
        <h3>Detalle de Factura #<span x-text="ventaSeleccionada?.id"></span></h3>
        <button class="btn-close" @click="mostrarDetalle=false">✕</button>
      </div>
      <div class="modal-body">
        <p style="margin-bottom:6px; font-size:13px;"><strong>Cliente:</strong> <span x-text="ventaSeleccionada?.cliente"></span></p>
        <p style="margin-bottom:12px; font-size:13px;"><strong>Detalles:</strong> <span x-text="ventaSeleccionada?.observacion"></span></p>
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
        <button class="btn btn-primary" @click="mostrarDetalle=false; abrirFactura(ventaSeleccionada)"><i class="bi bi-printer-fill"></i> Abrir Factura</button>
        <button class="btn btn-secondary" @click="mostrarDetalle=false">Cerrar</button>
      </div>
    </div>
  </div>
</div>