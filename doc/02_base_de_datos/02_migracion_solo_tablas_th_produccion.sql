-- ============================================================================
-- SCRIPT SEGURO PARA PRODUCCIÓN (civion.public / seblan)
-- CONTIENE "DOBLE COMILLA" EN CLAVES MAYÚSCULAS DE POSTGRESQL ("PK_cargo", etc.)
-- ============================================================================

-- 1. Catálogo Multi-Empresa
CREATE TABLE IF NOT EXISTS public.th_empresas (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(30) UNIQUE NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    ruc_identificacion VARCHAR(20),
    direccion VARCHAR(255),
    telefono VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO public.th_empresas (codigo, nombre, activo) 
VALUES ('SEBLAN', 'SEBLAN SERVICIOS CORPORATIVOS', TRUE)
ON CONFLICT (codigo) DO NOTHING;

-- 2. Dispositivos Biométricos Hikvision
CREATE TABLE IF NOT EXISTS public.th_dispositivos (
    id SERIAL PRIMARY KEY,
    empresa_id INT REFERENCES public.th_empresas(id) ON DELETE CASCADE,
    nombre VARCHAR(100) NOT NULL,
    ip_lan VARCHAR(45) NOT NULL,
    ip VARCHAR(45),
    ip_local VARCHAR(45),
    puerto INT DEFAULT 80,
    usuario VARCHAR(50) DEFAULT 'admin',
    password VARCHAR(100) NOT NULL,
    serial_number VARCHAR(100) UNIQUE,
    modelo VARCHAR(100),
    ubicacion VARCHAR(150),
    ultimo_ping TIMESTAMP,
    estado VARCHAR(20) DEFAULT 'ONLINE',
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Horarios y Turnos de Trabajo
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

-- 4. Colaboradores / Empleados (Con comillas para respetar case de Scriptcase)
CREATE TABLE IF NOT EXISTS public.th_empleados (
    identificacion VARCHAR(20) PRIMARY KEY,
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
    cargo_id INT REFERENCES public.seblan_cargos("PK_cargo") ON DELETE SET NULL,
    organigrama_id INT REFERENCES public.seblan_organigrama("PK_organigrama") ON DELETE SET NULL,
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
CREATE INDEX IF NOT EXISTS idx_th_empleados_empresa ON public.th_empleados(empresa_id);

-- 5. Planificación de Turnos
CREATE TABLE IF NOT EXISTS public.th_planificacion_horarios (
    id BIGSERIAL PRIMARY KEY,
    empleado_id VARCHAR(20) NOT NULL REFERENCES public.th_empleados(identificacion) ON DELETE CASCADE,
    horario_id INT NOT NULL REFERENCES public.th_horarios(id) ON DELETE RESTRICT,
    fecha DATE NOT NULL,
    es_dia_descanso BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_th_planificacion_emp_fecha UNIQUE (empleado_id, fecha)
);

-- 6. Timbradas en Crudo
CREATE TABLE IF NOT EXISTS public.th_timbradas (
    id BIGSERIAL PRIMARY KEY,
    empleado_id VARCHAR(20) REFERENCES public.th_empleados(identificacion) ON DELETE SET NULL,
    dispositivo_id INT REFERENCES public.th_dispositivos(id) ON DELETE SET NULL,
    fecha_hora TIMESTAMP NOT NULL,
    tipo_evento VARCHAR(50) DEFAULT 'VERIFY_SUCCESS',
    sub_tipo VARCHAR(50),
    serial_dispositivo VARCHAR(100),
    foto_captura_url VARCHAR(500),
    origen VARCHAR(20) DEFAULT 'WEBHOOK',
    procesado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_th_timbradas_emp_fecha ON public.th_timbradas(empleado_id, fecha_hora);

-- 7. Asistencias Diarias Consolidada
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
    estado_asistencia VARCHAR(30) DEFAULT 'PRESENTE',
    observacion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_th_asistencia_emp_fecha UNIQUE (empleado_id, fecha)
);

-- 8. Solicitudes de Permisos
CREATE TABLE IF NOT EXISTS public.th_solicitudes_permisos (
    id BIGSERIAL PRIMARY KEY,
    empleado_id VARCHAR(20) NOT NULL REFERENCES public.th_empleados(identificacion) ON DELETE CASCADE,
    tipo VARCHAR(50) NOT NULL,
    fecha_inicio TIMESTAMP NOT NULL,
    fecha_fin TIMESTAMP NOT NULL,
    motivo TEXT NOT NULL,
    adjunto_url VARCHAR(500),
    descuenta_vacaciones BOOLEAN DEFAULT FALSE,
    estado VARCHAR(20) DEFAULT 'PENDIENTE',
    aprobado_por VARCHAR(50) REFERENCES public.sec_users(login),
    fecha_aprobacion TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- COPIAR COLABORADORES EXISTENTES DE SEBLAN A th_empleados (SIN PÉRDIDA DE DATOS)
-- ============================================================================
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
    LPAD(TRIM(c."PK_colaborador"::text), 10, '0') AS identificacion,
    (SELECT id FROM public.th_empresas WHERE codigo = 'SEBLAN' LIMIT 1) AS empresa_id,
    u.login AS sec_user_login,
    c.col_nombres,
    c.col_fecha_nacimiento,
    c.col_inicio_labores,
    c."FK_cargo",
    c."FK_organigrama",
    c.col_foto,
    CASE WHEN c."FK_estado" = 1 THEN TRUE ELSE FALSE END
FROM public.seblan_colaboradores c
LEFT JOIN public.sec_users u ON TRIM(u.login) = TRIM(c."PK_colaborador"::text)
ON CONFLICT (identificacion) DO UPDATE SET
    nombres = EXCLUDED.nombres,
    cargo_id = EXCLUDED.cargo_id,
    organigrama_id = EXCLUDED.organigrama_id,
    foto_url = EXCLUDED.foto_url;
