<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión · Dibufala S.A.S.</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🐃</text></svg>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/publico/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <style>
    body.login-page {
      margin-left: 0 !important;
      display: flex;
      min-height: 100vh;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #111111 0%, #242424 100%);
      padding: 20px;
    }
    .auth-box {
      background: #FFFFFF;
      border-radius: 16px;
      width: 100%;
      max-width: 420px;
      padding: 32px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.4);
      border-top: 5px solid #F5B800;
    }
    .auth-logo { text-align: center; margin-bottom: 22px; }
    .auth-logo h1 { font-size: 25px; color: #111111; font-weight: 800; }
    .auth-logo p { font-size: 13px; color: #52525B; margin-top: 4px; }
    .auth-logo .sub-sede { font-size: 11px; color: #D91E2A; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; margin-top: 4px; }
    .tabs { display: flex; gap: 8px; margin-bottom: 18px; background: #F4F4F5; padding: 4px; border-radius: 10px; }
    .tabs button { flex: 1; padding: 8px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; background: transparent; color: #18181B; transition: 0.2s; }
    .tabs button.active { background: #111111; color: #F5B800; }
    .password-wrap { position: relative; }
    .password-wrap input { padding-right: 42px; }
    .toggle-pass {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #52525B;
      cursor: pointer;
      font-size: 18px;
      display: flex;
      align-items: center;
      padding: 0;
    }
    .toggle-pass:hover { color: #111111; }
  </style>
</head>
<body class="login-page">
<div class="auth-box" x-data="{
  modo: 'login',
  verPass: false,
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
    <h1>🐃 Dibufala S.A.S.</h1>
    <p>Lácteos Artesanales 100% Leche de Búfala</p>
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
      <input type="email" x-model="form.email" required placeholder="correo@ejemplo.com">
    </div>
    <div class="form-group">
      <label>Contraseña</label>
      <div class="password-wrap">
        <input :type="verPass ? 'text' : 'password'" x-model="form.password" required placeholder="••••••••">
        <button type="button" class="toggle-pass" @click="verPass = !verPass" :title="verPass ? 'Ocultar contraseña' : 'Ver contraseña'">
          <i :class="verPass ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
        </button>
      </div>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;" :disabled="cargando">
      <span x-text="cargando ? 'Procesando...' : (modo==='login' ? 'Ingresar al Sistema' : 'Crear Cuenta de Cliente')"></span>
    </button>
  </form>

  <p style="text-align:center; font-size:12px; color:#71717A; margin-top:16px;" x-text="modo==='registro' ? 'Las cuentas nuevas inician como Cliente (explorar y comprar).' : '🔒 Acceso Dibufala S.A.S. · El Rosal, Cundinamarca'"></p>
</div>
</body>
</html>