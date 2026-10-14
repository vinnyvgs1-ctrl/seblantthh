-- 1. SEGURIDAD SCRIPTCASE Y ENTIDADES SEBLAN
CREATE TABLE IF NOT EXISTS public.sec_users (
    login VARCHAR(20) PRIMARY KEY,
    pswd VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    active VARCHAR(1) DEFAULT 'Y',
    activation_code VARCHAR(32),
    priv_admin VARCHAR(1) DEFAULT 'N',
    mfa VARCHAR(255),
    picture VARCHAR(500),
    role VARCHAR(128) DEFAULT 'user',
    phone VARCHAR(64),
    pswd_last_updated TIMESTAMP,
    mfa_last_updated TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.sec_usuario_detalles (
    PK_identificacion VARCHAR(20) PRIMARY KEY REFERENCES public.sec_users(login) ON DELETE CASCADE,
    usd_tipo_identificacion INT2 DEFAULT 1,
    usd_nombres VARCHAR(255),
    usd_fecha_nacimiento DATE,
    FK_estado_civil INT2,
    FK_sexo INT2,
    usd_telefono VARCHAR(50),
    FK_nacionalidad INT2,
    fecha_crea TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usr_upd VARCHAR(20),
    fecha_upd TIMESTAMP,
    ip VARCHAR(100),
    usd_pasaporte VARCHAR(20),
    usr_crea VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS public.sec_groups (
    group_id SERIAL PRIMARY KEY,
    description VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS public.sec_users_groups (
    login VARCHAR(255) NOT NULL,
    group_id INT4 NOT NULL,
    PRIMARY KEY (login, group_id)
);

CREATE TABLE IF NOT EXISTS public.sec_groups_apps (
    group_id INT4 NOT NULL,
    app_name VARCHAR(128) NOT NULL,
    priv_access VARCHAR(1) DEFAULT 'Y',
    priv_insert VARCHAR(1) DEFAULT 'Y',
    priv_delete VARCHAR(1) DEFAULT 'Y',
    priv_update VARCHAR(1) DEFAULT 'Y',
    priv_export VARCHAR(1) DEFAULT 'Y',
    priv_print VARCHAR(1) DEFAULT 'Y',
    PRIMARY KEY (group_id, app_name)
);

CREATE TABLE IF NOT EXISTS public.seblan_organigrama (
    PK_organigrama SERIAL PRIMARY KEY,
    organigrama VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS public.seblan_cargos (
    PK_cargo SERIAL PRIMARY KEY,
    cargo VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS public.seblan_comunicados (
    PK_comunicados SERIAL PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(500),
    adjunto VARCHAR(500),
    adjunto_nombre VARCHAR(100),
    area VARCHAR(80),
    activo BOOLEAN DEFAULT TRUE,
    fecha_inicio DATE,
    fecha_fin DATE,
    fecha_crea TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. TABLAS TTHH & BIOMETRÍA HIKVISION (th_*)
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
    estado VARCHAR(20) DEFAULT 'ONLINE',
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
CREATE INDEX IF NOT EXISTS idx_th_empleados_empresa ON public.th_empleados(empresa_id);

CREATE TABLE IF NOT EXISTS public.th_planificacion_horarios (
    id BIGSERIAL PRIMARY KEY,
    empleado_id VARCHAR(20) NOT NULL REFERENCES public.th_empleados(identificacion) ON DELETE CASCADE,
    horario_id INT NOT NULL REFERENCES public.th_horarios(id) ON DELETE RESTRICT,
    fecha DATE NOT NULL,
    es_dia_descanso BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_th_planificacion_emp_fecha UNIQUE (empleado_id, fecha)
);

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
