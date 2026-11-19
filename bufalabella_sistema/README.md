# 🐃 Bufalabella S.A.S. (Dibufala) — Sistema de Gestión Comercial con IA

Sistema web desarrollado en **PHP (MVC) + MySQL + Alpine.js + Chart.js + Google Gemini AI**, adaptado para la gestión de producción, inventario y ventas de lácteos de búfala en **El Rosal, Cundinamarca**.

## 🚀 Instalación rápida en local (XAMPP / Laragon)

1. Copia la carpeta `bufalabella` dentro de `htdocs/` (en XAMPP) o `www/` (en Laragon).
2. Abre **phpMyAdmin** (`http://localhost/phpmyadmin`), crea o selecciona tu servidor e importa el archivo `bufalabella_db.sql`.
3. Abre el archivo `.env` y coloca tu clave gratuita de Google AI Studio en `GEMINI_API_KEY=...`.
4. Entra en tu navegador a: `http://localhost/bufalabella/`
   - **Correo demo:** `admin@bufalabella.com`
   - **Contraseña:** `password`

## 🌐 Despliegue en línea (Railway / Render / Hosting)

1. Sube esta carpeta a un repositorio en **GitHub** (el archivo `.gitignore` ya protege tu `.env`).
2. En tu proveedor (ej. **Railway**):
   - Agrega un servicio **MySQL** e importa `bufalabella_db.sql`.
   - Agrega tu repositorio PHP y configura en la pestaña **Variables**:
     - `DB_HOST`
     - `DB_USER`
     - `DB_PASS`
     - `DB_NAME=bufalabella_db`
     - `GEMINI_API_KEY`
3. ¡Listo! Las rutas son dinámicas y funcionan tanto en subcarpeta local como en dominio raíz en internet.
