# Denuncia Portal - producción con NGINX

## 1) Variables de entorno recomendadas

Configura al menos estas variables antes de desplegar:

- `DENUNCIA_ENV=production`
- `DENUNCIA_PUBLIC_BASE_URL=https://tu-dominio/denuncia`
- `DENUNCIA_DB_HOST=127.0.0.1`
- `DENUNCIA_DB_PORT=3306`
- `DENUNCIA_DB_NAME=denuncias_portal`
- `DENUNCIA_DB_USER=...`
- `DENUNCIA_DB_PASS=...`
- `DENUNCIA_TRUST_PROXY=true` si NGINX está detrás de otro proxy o balanceador
- `DENUNCIA_FORCE_HTTPS=true` si toda la instalación debe operar solo por HTTPS

Para SMTP:

- `DENUNCIA_MAIL_MODE=smtp`
- `DENUNCIA_SMTP_HOST=...`
- `DENUNCIA_SMTP_PORT=465` o `587`
- `DENUNCIA_SMTP_SECURE=ssl` o `tls`
- `DENUNCIA_SMTP_USER=...`
- `DENUNCIA_SMTP_PASS=...`

## 2) Directorios que no deben quedar expuestos

La configuración NGINX incluida ya bloquea acceso directo a:

- `/config_denuncia.php`
- `/deploy/`
- `/storage/`
- `/outbox/`
- `/uploads/` por URL directa
- `*.sql`, `*.zip`, `*.md`, `.git`
- `/phpmailer/`

## 3) Adjuntos y correos en modo file

- Los adjuntos se descargan solo a través de `download.php` o `admin/download.php`.
- El modo de correo `file` ahora usa por defecto `storage/outbox/`.
- En producción conviene usar `smtp` y dejar `file` solo para QA o contingencia.

## 4) Recomendaciones operativas

- Usa PHP-FPM y NGINX con HTTPS.
- Deja `display_errors=Off` y `log_errors=On`.
- Activa rotación de logs de NGINX y PHP.
- Saca los archivos SQL del root si no los necesitas. Si los mantienes, el bloque NGINX los niega.
- Restringe por firewall el acceso a MySQL para que solo el servidor de aplicación pueda conectarse.
- Haz backup de base de datos y de `uploads/`.

## 5) Checklist rápido

- Login admin con contraseña hasheada.
- Session cookie `HttpOnly` y `SameSite`.
- CSRF activo en formularios administrativos.
- Logout admin solo por `POST`.
- Rate limiting de login admin.
- Security headers básicos enviados por PHP y reforzados por NGINX.
