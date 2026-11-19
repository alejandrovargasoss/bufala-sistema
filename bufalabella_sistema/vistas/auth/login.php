<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión - Bufalabella S.A.S. (Dibufala)</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/publico/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <style>
    body { justify-content: center; align-items: center; background: linear-gradient(135deg, #1B4332 0%, #1B2E25 100%); padding: 20px; }
    .auth-box { background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 420px; padding: 32px; box-shadow: 0 20px 40px rgba(0,0,0,0.35); border-top: 5px solid var(--dorado); }
    .auth-logo { text-align: center; margin-bottom: 24px; }
    .auth-logo h1 { font-size: 26px; color: var(--verde-dark); font-weight: 800; }
    .auth-logo p { font-size: 13px; color: #6B7280; margin-top: 4px; }
    .auth-logo .sub-sede { font-size: 11px; color: var(--dorado); font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; margin-top: 4px; }
    .tabs { display: flex; gap: 8px; margin-bottom: 20px; background: var(--gris-bg); padding: 4px; border-radius: 10px; }
    .tabs button { flex: 1; padding: 8px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; background: transparent; color: var(--texto); transition: 0.2s; }
    .tabs button.active { background: white; color: var(--verde-sena); box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
  </style>
</head>
<body>
<div class="auth-box" x-data="{
  modo: 'login',
  cargando: false,
  mensaje: '',
  tipoMsg: 'danger',
  form: { nombre: '', email: '', password: '' },
  async enviar() {
    this.cargando = true;
    this.mensaje = '';
    const accion = this.modo === 'login' ? 'login' : 'registrar';
    try {
      const res = await fetch('<?= BASE_URL ?>/index.php?modulo=auth&accion=' + accion, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(this.form)
      });
      const json = await res.json();
      if (json.ok) {
        this.tipoMsg = 'success';
        this.mensaje = json.mensaje;
        window.location.href = '<?= BASE_URL ?>/index.php';
      } else {
        this.tipoMsg = 'danger';
        this.mensaje = json.mensaje || 'Error de autenticación';
      }
    } catch (e) {
      this.tipoMsg = 'danger';
      this.mensaje = 'Error de conexión con el servidor';
    } finally {
      this.cargando = false;
    }
  }
}">
  <div class="auth-logo">
    <h1>🐃 Bufalabella S.A.S.</h1>
    <p>Lácteos Artesanales 100% Leche de Búfala · Dibufala</p>
    <div class="sub-sede">Planta El Rosal · Cundinamarca</div>
  </div>

  <div class="tabs">
    <button type="button" :class="modo==='login'?'active':''" @click="modo='login'; mensaje=''">Iniciar Sesión</button>
    <button type="button" :class="modo==='registro'?'active':''" @click="modo='registro'; form={nombre:'',email:'',password:''}; mensaje=''">Crear Cuenta</button>
  </div>

  <template x-if="mensaje">
    <div :class="'alert alert-' + tipoMsg" x-text="mensaje"></div>
  </template>

  <form @submit.prevent="enviar()">
    <div class="form-group" x-show="modo==='registro'">
      <label>Nombre completo</label>
      <input type="text" x-model="form.nombre" placeholder="Ej. María Fernanda Gómez">
    </div>
    <div class="form-group">
      <label>Correo electrónico</label>
      <input type="email" x-model="form.email" required placeholder="usuario@bufalabella.com">
    </div>
    <div class="form-group">
      <label>Contraseña</label>
      <input type="password" x-model="form.password" required placeholder="••••••••">
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;" :disabled="cargando">
      <span x-text="cargando ? 'Procesando...' : (modo==='login' ? 'Ingresar al Sistema' : 'Registrarme y Entrar')"></span>
    </button>
  </form>

  <p style="text-align:center; font-size:12px; color:#7A7A72; margin-top:18px;">
    🔒 Acceso exclusivo personal autorizado · Bufalabella S.A.S.
  </p>
</div>
</body>
</html>
