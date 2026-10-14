# Guía de Integración: SEBLAN CRM (Scriptcase) con Módulo TTHH Biométrico

## 1. Arquitectura de Desacoplamiento

El módulo biométrico opera como un microservicio independiente en:
- **Ruta local:** `/Applications/XAMPP/xamppfiles/htdocs/seblan_biometricos`
- **URL Base:** `http://localhost/seblan_biometricos/public`
- **Base de Datos:** PostgreSQL `seblan_db` (puerto 5432)

---

## 2. Acceso Single Sign-On (SSO) desde Scriptcase

Para que un usuario autenticado en el CRM de Scriptcase ingrese a Talento Humano sin volver a ingresar credenciales:

### En Scriptcase (Menú o Aplicación Blank / Control):
En el evento `OnExecute` de la opción de menú:

```php
// 1. Obtener el usuario logueado en Scriptcase
$usr_login = [usr_login]; 

// 2. Parámetros de seguridad
$timestamp = time();
$clave_secreta = "SEBLAN_TTHH_SECRET_KEY_2026"; // Debe coincidir con SCRIPTCASE_SSO_SECRET del .env
$token = hash_hmac('sha256', $usr_login . '|' . $timestamp, $clave_secreta);

// 3. URL del microproyecto
$url = "http://localhost/seblan_biometricos/public/auth/sso?user=" . urlencode($usr_login) . "&t=" . $timestamp . "&token=" . $token;

// 4. Redireccionar al usuario (abre en pestaña o en el target configurado)
sc_redir($url, "_blank");
```

---

## 3. Configuración del Terminal Facial Hikvision (DS-K1T323MBFWX-E1)

Para que el terminal envíe las marcaciones en tiempo real:

1. Ingresar a la interfaz web del terminal Hikvision: `http://[IP_DEL_BIOMETRICO]`
2. Ir a **Configuration > Network > Advanced Settings > HTTP Listening / Alarm Server** (o Centro de Recepción de Alarmas).
3. Configurar:
   - **Server IP:** IP de tu servidor donde corre el microservicio (ej. `192.168.1.50` o dominio público).
   - **Port:** `80` (o el puerto de tu Apache/Nginx).
   - **URL Path:** `/seblan_biometricos/public/api/th/hikvision/event`
   - **Protocol:** `HTTP` o `HTTPS`
   - **Data Type:** `JSON / Binary` (Envía foto + JSON de acceso).
4. Guardar cambios. Cada vez que alguien timbre, el evento llegará a `th_timbradas` y consolidará asistencia automáticamente.

---

## 4. Estructura de Tablas en PostgreSQL (`seblan_db`)

Consulte el script DDL completo en: [`init_seblan_db.sql`](./init_seblan_db.sql)

- **`sec_users`**: Usuarios y credenciales administrados por Scriptcase.
- **`th_empresas`**: Soporte multi-proyecto/tenant (ej. `SEBLAN`).
- **`th_empleados`**: Catálogo enriquecido de colaboradores biométricos.
- **`th_dispositivos`**: Inventario de terminales Hikvision (IP, credenciales Digest, puertos).
- **`th_horarios`**: Definición de jornadas y tolerancias.
- **`th_planificacion_horarios`**: Asignación de turnos por fecha.
- **`th_timbradas`**: Repositorio de eventos en tiempo real.
- **`th_asistencias_diarias`**: Consolidado diario de horas, atrasos y faltas.
