# Documentación del Proyecto: SEBLAN Talento Humano & Biometría

Estructura de documentación organizada por carpetas temáticas y numeración secuencial:

```
doc/
├── README.md                                  # Índice general de documentación
├── 01_arquitectura_y_plan/
│   └── 01_plan_migracion_tthh_seblan.md       # Análisis comparativo, arquitectura desacoplada y roadmap
├── 02_base_de_datos/
│   └── 01_init_seblan_db.sql                  # Script DDL completo de PostgreSQL (16 tablas)
└── 03_integracion_scriptcase/
    ├── 01_guia_integracion_sso.md             # Guía técnica de Single Sign-On y webhook Hikvision
    └── 02_diseno_menu_scriptcase.md           # Estructura del menú: TTHH (RRHH) vs Portal del Colaborador
```

---

## 📌 Guía Rápida de Acceso
- **Arquitectura y Estrategia:** Ver [`01_arquitectura_y_plan/01_plan_migracion_tthh_seblan.md`](./01_arquitectura_y_plan/01_plan_migracion_tthh_seblan.md)
- **Base de Datos PostgreSQL:** Ver [`02_base_de_datos/01_init_seblan_db.sql`](./02_base_de_datos/01_init_seblan_db.sql)
- **Configurar Scriptcase:** Ver [`03_integracion_scriptcase/01_guia_integracion_sso.md`](./03_integracion_scriptcase/01_guia_integracion_sso.md)
- **Menú y Roles:** Ver [`03_integracion_scriptcase/02_diseno_menu_scriptcase.md`](./03_integracion_scriptcase/02_diseno_menu_scriptcase.md)
