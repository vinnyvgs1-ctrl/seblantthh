# Propuesta de Reestructuración del Menú SEBLAN: TTHH vs Portal del Colaborador

En base a la estructura actual del CRM SEBLAN:
```
[Vehículos]  [Viáticos]  [TTHH (Actual)]  [Procesos]
                         ├── Colaboradores
                         ├── Vacaciones / Permisos
                         └── Comunicados
```

---

## 1. Nueva Distribución Recomendada

Separamos la responsabilidad en **2 secciones bien definidas**:
1. **⚙️ TALENTO HUMANO (Gestión / RRHH / Jefaturas)**
2. **👤 MI PORTAL (Autogestión del Colaborador)**

```
┌───────────────────────────────┬───────────────────────────────┐
│       ⚙️ TALENTO HUMANO       │       👤 MI ESPACIO           │
│   (Gestión y Administración)  │  (Autogestión del Colaborador)│
├───────────────────────────────┼───────────────────────────────┤
│ • Colaboradores & Biometría   │ • Mi Perfil & Datos           │
│ • Monitor en Vivo (Timbradas) │ • Mis Marcaciones & Asistencia│
│ • Turnos & Planificación      │ • Solicitar Permiso / Licencia│
│ • Aprobación de Permisos      │ • Solicitar Vacaciones        │
│ • Reporte de Asistencia       │ • Comunicados Internos        │
│ • Dispositivos Hikvision      │                               │
└───────────────────────────────┴───────────────────────────────┘
```

---

## 2. Aplicaciones Puente en Scriptcase

Creamos dos aplicaciones `Blank` en Scriptcase para cada perfil de acceso:

### A. Para la Gestión de RRHH: `app_tthh_admin`
```php
// En OnExecute de app_tthh_admin
$login = [usr_login];
$time = time();
$token = hash_hmac("sha256", $login . "|" . $time, "SEBLAN_TTHH_SECRET_KEY_2026");
$url = "http://localhost/seblan_biometricos/public/auth/sso?user=" . urlencode($login) . "&t=" . $time . "&token=" . $token . "&view=admin";
sc_redir($url, "_blank");
```

### B. Para el Colaborador: `app_tthh_colaborador`
```php
// En OnExecute de app_tthh_colaborador
$login = [usr_login];
$time = time();
$token = hash_hmac("sha256", $login . "|" . $time, "SEBLAN_TTHH_SECRET_KEY_2026");
$url = "http://localhost/seblan_biometricos/public/auth/sso?user=" . urlencode($login) . "&t=" . $time . "&token=" . $token . "&view=portal";
sc_redir($url, "_blank");
```

---

## 3. Control de Acceso por Roles (Scriptcase Security)
- **`app_tthh_admin`**: Asignada en `sec_groups_apps` solo a administradores y jefes de RRHH.
- **`app_tthh_colaborador`**: Asignada a todos los colaboradores con usuario en el sistema.
