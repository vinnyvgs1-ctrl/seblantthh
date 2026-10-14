# Arquitectura y Plan Maestro: Microproyecto Independiente de TTHH & Biometría Hikvision
## Integrado con Scriptcase (SEBLAN) y Preparado para Ecosistemas Multi-Tenant / Multi-Proyecto

---

## 1. Visión Estratégica y Arquitectura General

En lugar de reconstruir artesanalmente la lógica dentro del generador de Scriptcase (lo que limitaría el manejo de webhooks binarios en tiempo real, sockets e ISAPI Digest Auth), **se extraerá el motor completo de Talento Humano (TTHH) en un microproyecto autónomo**.

Este microproyecto:
1. **Es agnóstico y extensible:** Puede operar conectado a SEBLAN (`civion.public`), pero cuenta con diseño modular/multi-empresa para vincularse a futuros proyectos satélites.
2. **Se integra de forma transparente con Scriptcase:** El usuario de SEBLAN abre "Talento Humano" desde el menú de Scriptcase (vía Iframe o Single Sign-On / URL protegida) sin notar cambio de entorno.
3. **Maneja nativamente el hardware Hikvision:**
   - Receptor Webhook de alta velocidad (`multipart/form-data` con foto + JSON de eventos).
   - Cliente ISAPI con Digest Authentication para alta/baja remota de rostros y consulta de estado.
   - Normalización de identificaciones (parche de ceros iniciales en cédulas de 9 vs 10 dígitos).
4. **Persistencia sobre PostgreSQL:** Conexión nativa con transacciones seguras (`savepoints` / isolation level adecuado).

```
 ┌─────────────────────────────────────────────────────────────┐
 │                SEBLAN CRM (Scriptcase)                      │
 │  - Autenticación: sec_users, sec_usuario_detalles           │
 │  - Módulos comerciales y administrativos                    │
 │  - Opción de Menú: "Gestión de Talento Humano"              │
 └──────────────────────────────┬──────────────────────────────┘
                                │ (SSO Token JWT / Session Bridge)
                                ▼
 ┌─────────────────────────────────────────────────────────────┐
 │          MICRO-PROYECTO TTHH & BIOMETRÍA HIKVISION          │
 │  (Ubicación: /htdocs/seblan-tthh o tthh-core)               │
 ├─────────────────────────────────────────────────────────────┤
 │ • Webhook Receiver (Eventos en tiempo real)                 │
 │ • ISAPI Driver (Sincronización biométrica con terminales)   │
 │ • Monitor de Marcaciones en Vivo                            │
 │ • Planificador de Turnos y Calendario                       │
 │ • Consolidación de Asistencias, Faltas y Atrasos             │
 │ • Kardex de Vacaciones y Permisos Laborales                 │
 └──────────────────────────────┬──────────────────────────────┘
                                │
                                ▼
 ┌─────────────────────────────────────────────────────────────┐
 │                 BASE DE DATOS POSTGRESQL                    │
 │  Esquema `civion.public` (Tablas TTHH + Vinculación Seblan) │
 └─────────────────────────────────────────────────────────────┘
```

---

## 2. Estrategia de Datos: ¿Cómo Atar las Tablas a SEBLAN y a Futuros Proyectos?

Para permitir que este microservicio sirva a **SEBLAN hoy**, pero pueda **conectar otros proyectos mañana** de forma profesional, implementamos una arquitectura de **Empresa / Tenant**:

### Opción A (Recomendada): Prefijo Modular con Columna `empresa_id` / `tenant_id`
Las tablas de TTHH viven en la misma base PostgreSQL (`civion.public` o en su propio esquema `tthh.public`), pero cada registro lleva la clave de la empresa o sistema que lo consume:
- `empresa_codigo` = `'SEBLAN'` (o el ID que corresponda).
- Cuando agregues otra empresa o proyecto satélite en el futuro, no tendrás que duplicar la base de datos ni reprogramar nada; simplemente se filtra por la empresa activa.

### Opción B: Vinculación Híbrida con Tablas Nativas de Scriptcase
El microproyecto leerá a los usuarios del sistema desde `sec_users` / `sec_usuario_detalles` de Scriptcase, pero mantendrá su propio catálogo enriquecido de colaboradores biométricos (`th_empleados`), uniendo ambos mundos mediante `identificacion = login`.

---

## 3. Esquema DDL en PostgreSQL (`civion.public`)

Este set de tablas está completamente optimizado para PostgreSQL y diseñado para coexistir pacíficamente con las tablas actuales de Seblan:

```sql
-- ============================================================================
-- 1. CATÁLOGO DE EMPRESAS / PROYECTOS (Para soporte Multi-Proyecto)
-- ============================================================================
CREATE TABLE IF NOT EXISTS public.th_empresas (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(30) UNIQUE NOT NULL, -- Ej: 'SEBLAN', 'QUITOBICI', 'PROYECTO_C'
    nombre VARCHAR(150) NOT NULL,
    ruc_identificacion VARCHAR(20),
    direccion VARCHAR(255),
    telefono VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Registrar Seblan como empresa base
INSERT INTO public.th_empresas (codigo, nombre, activo) 
VALUES ('SEBLAN', 'SEBLAN SERVICIOS CORPORATIVOS', TRUE)
ON CONFLICT (codigo) DO NOTHING;

-- ============================================================================
-- 2. DISPOSITIVOS BIOMÉTRICOS HIKVISION
-- ============================================================================
CREATE TABLE IF NOT EXISTS public.th_dispositivos (
    id SERIAL PRIMARY KEY,
    empresa_id INT REFERENCES public.th_empresas(id) ON DELETE CASCADE,
    nombre VARCHAR(100) NOT NULL,
    ip_lan VARCHAR(45) NOT NULL,
    puerto INT DEFAULT 80,
    usuario VARCHAR(50) DEFAULT 'admin',
    password VARCHAR(100) NOT NULL,
    serial_number VARCHAR(100) UNIQUE,
    modelo VARCHAR(100),
    ubicacion VARCHAR(150),
    ultimo_ping TIMESTAMP,
    estado VARCHAR(20) DEFAULT 'ONLINE', -- ONLINE, OFFLINE, ERROR
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 3. HORARIOS Y JORNADAS LABORALES
-- ============================================================================
CREATE TABLE IF NOT EXISTS public.th_horarios (
    id SERIAL PRIMARY KEY,
    empresa_id INT REFERENCES public.th_empresas(id) ON DELETE CASCADE,
    nombre VARCHAR(100) NOT NULL,
    hora_entrada TIME NOT NULL,
    hora_salida TIME NOT NULL,
    tolerancia_minutos INT DEFAULT 10,
    minutos_almuerzo INT DEFAULT 60,
    es_nocturno BOOLEAN DEFAULT FALSE,
    color_hex VARCHAR(10) DEFAULT '#3B82F6',
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 4. COLABORADORES / EMPLEADOS (Conectado a sec_users / seblan_cargos)
-- ============================================================================
CREATE TABLE IF NOT EXISTS public.th_empleados (
    identificacion VARCHAR(20) PRIMARY KEY, -- Normalizado a 10 dígitos (cero inicial garantizado)
    empresa_id INT REFERENCES public.th_empresas(id) ON DELETE CASCADE,
    sec_user_login VARCHAR(20) REFERENCES public.sec_users(login) ON DELETE SET NULL,
    codigo_empleado VARCHAR(20),
    nombres VARCHAR(150) NOT NULL,
    apellidos VARCHAR(150),
    fecha_nacimiento DATE,
    fecha_ingreso DATE,
    fecha_salida DATE,
    email VARCHAR(150),
    telefono VARCHAR(50),
    direccion VARCHAR(255),
    cargo_id INT REFERENCES public.seblan_cargos(PK_cargo) ON DELETE SET NULL,
    organigrama_id INT REFERENCES public.seblan_organigrama(PK_organigrama) ON DELETE SET NULL,
    horario_predeterminado_id INT REFERENCES public.th_horarios(id) ON DELETE SET NULL,
    foto_url VARCHAR(500),
    biometria_rostro BOOLEAN DEFAULT FALSE,
    biometria_huella BOOLEAN DEFAULT FALSE,
    biometria_tarjeta VARCHAR(50),
    saldo_vacaciones_dias NUMERIC(6, 2) DEFAULT 0.00,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_th_empleados_empresa ON public.th_empleados(empresa_id);

-- ============================================================================
-- 5. PLANIFICACIÓN Y ASIGNACIÓN DE TURNOS
-- ============================================================================
CREATE TABLE IF NOT EXISTS public.th_planificacion_horarios (
    id BIGSERIAL PRIMARY KEY,
    empleado_id VARCHAR(20) NOT NULL REFERENCES public.th_empleados(identificacion) ON DELETE CASCADE,
    horario_id INT NOT NULL REFERENCES public.th_horarios(id) ON DELETE RESTRICT,
    fecha DATE NOT NULL,
    es_dia_descanso BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_th_planificacion_emp_fecha UNIQUE (empleado_id, fecha)
);

-- ============================================================================
-- 6. TIMBRADAS / MARCACIONES BIOMÉTRICAS (Eventos en tiempo real)
-- ============================================================================
CREATE TABLE IF NOT EXISTS public.th_timbradas (
    id BIGSERIAL PRIMARY KEY,
    empleado_id VARCHAR(20) REFERENCES public.th_empleados(identificacion) ON DELETE SET NULL,
    dispositivo_id INT REFERENCES public.th_dispositivos(id) ON DELETE SET NULL,
    fecha_hora TIMESTAMP NOT NULL,
    tipo_evento VARCHAR(50) DEFAULT 'VERIFY_SUCCESS',
    sub_tipo VARCHAR(50), -- FACE, FINGERPRINT, CARD, PASSWORD
    serial_dispositivo VARCHAR(100),
    foto_captura_url VARCHAR(500),
    origen VARCHAR(20) DEFAULT 'WEBHOOK', -- WEBHOOK o ISAPI_POLL
    procesado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_th_timbradas_emp_fecha ON public.th_timbradas(empleado_id, fecha_hora);

-- ============================================================================
-- 7. CONSOLIDACIÓN DE ASISTENCIA DIARIA
-- ============================================================================
CREATE TABLE IF NOT EXISTS public.th_asistencias_diarias (
    id BIGSERIAL PRIMARY KEY,
    empleado_id VARCHAR(20) NOT NULL REFERENCES public.th_empleados(identificacion) ON DELETE CASCADE,
    fecha DATE NOT NULL,
    horario_id INT REFERENCES public.th_horarios(id) ON DELETE SET NULL,
    primera_entrada TIMESTAMP,
    ultima_salida TIMESTAMP,
    minutos_atraso INT DEFAULT 0,
    minutos_salida_temprana INT DEFAULT 0,
    minutos_trabajados INT DEFAULT 0,
    horas_ordinarias NUMERIC(5, 2) DEFAULT 0.00,
    horas_suplementarias NUMERIC(5, 2) DEFAULT 0.00,
    horas_extraordinarias NUMERIC(5, 2) DEFAULT 0.00,
    estado_asistencia VARCHAR(30) DEFAULT 'PRESENTE', -- PRESENTE, ATRASO, FALTA, PERMISO, VACACIONES, DESCANSO
    observacion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_th_asistencia_emp_fecha UNIQUE (empleado_id, fecha)
);

-- ============================================================================
-- 8. SOLICITUDES DE PERMISOS Y VACACIONES
-- ============================================================================
CREATE TABLE IF NOT EXISTS public.th_solicitudes_permisos (
    id BIGSERIAL PRIMARY KEY,
    empleado_id VARCHAR(20) NOT NULL REFERENCES public.th_empleados(identificacion) ON DELETE CASCADE,
    tipo VARCHAR(50) NOT NULL, -- MEDICO, PERSONAL, CALAMIDAD, etc.
    fecha_inicio TIMESTAMP NOT NULL,
    fecha_fin TIMESTAMP NOT NULL,
    motivo TEXT NOT NULL,
    adjunto_url VARCHAR(500),
    descuenta_vacaciones BOOLEAN DEFAULT FALSE,
    estado VARCHAR(20) DEFAULT 'PENDIENTE', -- PENDIENTE, APROBADO, RECHAZADO
    aprobado_por VARCHAR(50) REFERENCES public.sec_users(login),
    fecha_aprobacion TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 4. Script de Migración Inicial desde `seblan_colaboradores`

Para no perder nada de lo que Seblan ya tiene registrado y dejarlo funcionando al instante:

```sql
-- Insertar datos desde seblan_colaboradores hacia th_empleados vinculando a SEBLAN
INSERT INTO public.th_empleados (
    identificacion,
    empresa_id,
    sec_user_login,
    nombres,
    fecha_nacimiento,
    fecha_ingreso,
    cargo_id,
    organigrama_id,
    foto_url,
    activo
)
SELECT 
    -- Garantizar formato de 10 dígitos si es cédula ecuatoriana
    LPAD(TRIM(c.PK_colaborador), 10, '0') AS identificacion,
    (SELECT id FROM public.th_empresas WHERE codigo = 'SEBLAN' LIMIT 1) AS empresa_id,
    u.login AS sec_user_login,
    c.col_nombres,
    c.col_fecha_nacimiento,
    c.col_inicio_labores,
    c.FK_cargo,
    c.FK_organigrama,
    c.col_foto,
    CASE WHEN c.FK_estado = 1 THEN TRUE ELSE FALSE END
FROM public.seblan_colaboradores c
LEFT JOIN public.sec_users u ON TRIM(u.login) = TRIM(c.PK_colaborador)
ON CONFLICT (identificacion) DO NOTHING;
```

---

## 5. Integración con la Seguridad de Scriptcase

Para que los usuarios que ya inician sesión en el CRM de SEBLAN puedan acceder al nuevo microproyecto sin volver a escribir su contraseña:

### Método 1: Token Bridge / SSO vía Scriptcase
1. En Scriptcase creas un item de menú tipo **Enlace URL** o **Blank**:
   ```php
   // En evento OnExecute de la aplicación Scriptcase de llamada:
   $login_actual = [usr_login]; // Variable global de sesión de Scriptcase
   $secret_key = "SEBLAN_TTHH_SECRET_KEY_2026";
   $time = time();
   $hash = hash_hmac('sha256', $login_actual . '|' . $time, $secret_key);
   
   $url = "http://localhost/seblan-tthh/auth/sso?user=" . urlencode($login_actual) . "&t=" . $time . "&token=" . $hash;
   sc_redir($url, "_blank"); // O incrustado dentro de un iframe
   ```
2. En el microproyecto TTHH:
   Un middleware valida que `$hash` sea legítimo y que `$time` no tenga más de 60 segundos de antigüedad. Verifica el rol en `sec_users` y le abre la sesión de forma inmediata.

### Método 2: Autenticación Directa contra `sec_users`
El microproyecto configura su modelo `User` para apuntar a la tabla `sec_users`, usando la misma columna de contraseña (`pswd`) y validando el estado `active = 'Y'`.

---

## 6. Plan de Ejecución Inmediato

| Paso | Acción | Descripción |
| :---: | :--- | :--- |
| **1** | **Crear Carpeta del Microproyecto** | Inicializar `/Applications/XAMPP/xamppfiles/htdocs/seblan-tthh` con la base limpia del motor TTHH. |
| **2** | **Migraciones SQL en PostgreSQL** | Ejecutar el DDL de `th_*` en la base de datos `civion.public` y migrar `seblan_colaboradores`. |
| **3** | **Copiar Controladores y Servicios Clave** | Traer `HikvisionWebhookController`, `HikvisionIsapiService`, y vistas de Timbradas, Asistencias, Turnos y Colaboradores. |
| **4** | **Configurar Auth Bridge con Scriptcase** | Configurar autenticación conectada a `sec_users` y parámetro multi-empresa `SEBLAN`. |
| **5** | **Prueba de Enlace e Ingesta Biométrica** | Validar timbrada de prueba en el webhook y verificación en el monitor en vivo. |
