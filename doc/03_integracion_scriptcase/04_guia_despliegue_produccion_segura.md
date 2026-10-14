# Guía de Despliegue en Producción Segura (Cero Riesgos)

Al desplegar directo en el servidor de Producción de SEBLAN, aplicamos la regla de oro:
**"Solo agregar, nunca modificar ni borrar tablas existentes del CRM"**.

---

## PASO 1: Ejecutar Script SQL Seguro en PostgreSQL de Producción
1. Conéctate a la base de datos de producción (`civion` / esquema `public`).
2. Ejecuta el script:
   `doc/02_base_de_datos/02_migracion_solo_tablas_th_produccion.sql`
   - **Garantía:** No altera `sec_users`, `sec_groups`, ni `seblan_colaboradores`.
   - Crea únicamente las tablas con prefijo `th_*`.
   - Copia automáticamente los colaboradores existentes a `th_empleados` normalizando cédulas con 10 dígitos.

---

## PASO 2: Subir el Directorio `seblan_biometricos` a Producción
1. Sube la carpeta completa al servidor de producción (vía SCP / SFTP o Git).
2. Otorga permisos en Linux:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```
3. Configura el archivo `.env` en producción con las credenciales de la base de datos PostgreSQL de producción:
   ```ini
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://crm.seblan.com/seblan_biometricos/public
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=civion
   DB_USERNAME=usuario_prod
   DB_PASSWORD=password_prod
   SCRIPTCASE_SSO_SECRET=SEBLAN_TTHH_SECRET_KEY_2026
   ```

---

## PASO 3: Probar el Panel de Configuración
Abre en tu navegador:
`https://crm.seblan.com/seblan_biometricos/public/configuracion`
- Debe mostrar: **`Base de Datos: CONECTADO`** y la cantidad de colaboradores migrados.

---

## PASO 4: Enlazar en el Menú de Scriptcase (Producción)
En cuanto restablezcas la conexión con Scriptcase:
1. Creas `app_tthh_admin` (Blank) con el código de redirección SSO.
2. La asocias al menú `TTHH` y ¡listo!
