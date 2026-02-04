# Denuncias Portal (Whistleblowing / Ethics & Compliance)

Portal web para **recibir y gestionar denuncias** (canal de ética / cumplimiento / seguridad) de forma **anónima o identificada**, con **seguimiento por Clave + Contraseña**.

> ⚠️ **Estado:** Demo

---

## ✨ Funcionalidades

- **Multi-empresa** (branding por empresa: logo, nombre, recursos)
- **Categorías dinámicas** por empresa (cargadas vía API)
- **Ingreso de denuncia** (título, ubicación, fecha del evento, descripción)
- **Denuncia anónima** opcional
- **Seguimiento** con `Clave de Reporte` + contraseña
- **Vista de Caso** con historial y mensajes
- UI limpia, responsive + datepicker para fecha del evento

---

## 🧱 Stack

- **PHP** (recomendado: 8.x)
- **MySQL / MariaDB**
- **HTML/CSS** + **Vanilla JS**
- **Flatpickr** (date picker, locale ES)

---

## 📁 Estructura del proyecto (referencial)

- `index.php` — Inicio + selector de empresa
- `reportar.php` — Formulario de denuncia
- `seguimiento.php` — Login para ver caso (Clave + contraseña)
- `caso.php` — Detalle del caso y mensajes
- `faq.php` — Preguntas frecuentes
- `_header.php`, `_footer.php` — Layout base
- `_bootstrap.php` — Config, helpers, conexión DB
- `config_denuncia.php` — Credenciales DB (NO commitear)
- `api/categories.php` — Endpoint JSON para categorías
- `assets/css/` — estilos
- `assets/js/portal.js` — JS (categorías, datepicker, etc.)
- `images/` — logos por empresa

> Nota: Si tu estructura real difiere, ajusta los nombres de archivos en esta sección.

---

## ✅ Requisitos

- XAMPP / WAMP / LAMP con:
  - PHP 8.x (ideal)
  - MySQL/MariaDB
- Extensión PHP `mysqli` habilitada
- Un servidor local (Apache/Nginx)

---

## 🚀 Instalación rápida (XAMPP en Windows)

### 1) Clonar el repo en `htdocs`
```bash
cd C:\xampp\htdocs
git clone https://github.com/LTTNguyen/denuncia.git denuncia
