# Checklist de Despliegue: Servidor de Desarrollo (Scriptcase + PostgreSQL)

Sigue estos 4 pasos ordenados para dejar funcionando la integración en Desarrollo sin riesgos:

---

## PASO 1: Base de Datos en el Servidor de Desarrollo
1. Conéctate a la base de datos PostgreSQL de desarrollo (con pgAdmin, DBeaver o consola).
2. Ejecuta el script DDL ubicado en:
   `doc/02_base_de_datos/01_init_seblan_db.sql`
   *(Crea las tablas `th_*` y verifica que existan `sec_users`, `seblan_cargos`, etc.)*

---

## PASO 2: Subir el Microproyecto al Servidor de Desarrollo
1. Copia la carpeta `seblan_biometricos` a la raíz web de tu servidor de desarrollo (por ejemplo en `/var/www/html/seblan_biometricos` o la ruta de tu Apache).
2. Configura los permisos en el servidor:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```
3. Edita el archivo `.env` en el servidor con los datos de desarrollo:
   ```ini
   APP_URL=http://tu-servidor-desarrollo/seblan_biometricos/public
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=tu_bd_desarrollo
   DB_USERNAME=tu_usuario_postgres
   DB_PASSWORD=tu_password_postgres
   SCRIPTCASE_SSO_SECRET=SEBLAN_TTHH_SECRET_KEY_2026
   ```

---

## PASO 3: Probar el Panel de Configuración en el Navegador
Abre en tu navegador:
`http://tu-servidor-desarrollo/seblan_biometricos/public/configuracion`
- Comprueba que el indicador diga: **`Base de Datos: CONECTADO`**.

---

## PASO 4: Crear la Aplicación Puente en Scriptcase (Desarrollo)
1. En Scriptcase (entorno de desarrollo), crea una nueva aplicación de tipo **`Blank`** llamada `app_tthh_admin`.
2. En su evento `OnExecute`, pega:
   ```php
   $login = [usr_login];
   $time = time();
   $token = hash_hmac("sha256", $login . "|" . $time, "SEBLAN_TTHH_SECRET_KEY_2026");
   
   $url = "http://tu-servidor-desarrollo/seblan_biometricos/public/auth/sso?user=" . urlencode($login) . "&t=" . $time . "&token=" . $token;
   sc_redir($url, "_blank");
   ```
3. Asocia `app_tthh_admin` al menú de SEBLAN.
4. Haz clic en la opción del menú y valida que ingrese automáticamente con tu usuario activo.
