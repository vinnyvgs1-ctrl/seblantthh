<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEBLAN • Talento Humano & Biometría Hikvision</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [v-cloak] { display: none; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">
    <div id="app" v-cloak class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">
        
        <!-- HEADER PRINCIPAL -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shadow-inner border border-blue-100">
                    <i class="fas fa-id-card-clip"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2.5">
                        SEBLAN • Talento Humano
                        <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                            Hikvision Online
                        </span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Gestión Integral: Colaboradores, Turnos, Horarios, Permisos, Vacaciones y Timbradas en Tiempo Real
                    </p>
                </div>
            </div>

            <!-- Botones de Acción Global -->
            <div class="flex items-center gap-2 flex-wrap">
                <button @click="openModalNuevoEmpleado" class="px-3.5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> Nuevo Colaborador
                </button>
                <button @click="openModalHorario" class="px-3.5 py-2.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs transition flex items-center gap-1.5 border border-indigo-200">
                    <i class="fas fa-clock"></i> Nuevo Horario
                </button>
                <button @click="openModalDispositivo()" class="px-3.5 py-2.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold text-xs transition flex items-center gap-1.5 border border-purple-200">
                    <i class="fas fa-fingerprint"></i> Biométricos
                </button>
                <a href="{{ url('/configuracion') }}" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Configuración Scriptcase">
                    <i class="fas fa-cog"></i>
                </a>
            </div>
        </div>

        <!-- TARJETAS DE MÉTRICAS -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Colaboradores</p>
                    <h3 class="text-2xl font-black text-slate-900 mt-1">@{{ metricas.total_empleados || 0 }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fas fa-users"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Presentes Hoy</p>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1">@{{ metricas.presentes_hoy || 0 }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Atrasos Hoy</p>
                    <h3 class="text-2xl font-black text-amber-500 mt-1">@{{ metricas.atrasos_hoy || 0 }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Biométricos</p>
                    <h3 class="text-2xl font-black text-purple-600 mt-1">@{{ metricas.dispositivos_activos || 0 }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                    <i class="fas fa-fingerprint"></i>
                </div>
            </div>
        </div>

        <!-- PESTAÑAS DE NAVEGACIÓN -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="flex items-center gap-1 border-b border-slate-200 p-2 overflow-x-auto bg-slate-50/50">
                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                    :class="[
                        'px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap',
                        activeTab === tab.id ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'
                    ]">
                    <i :class="tab.icon"></i>
                    @{{ tab.name }}
                </button>
            </div>

            <!-- PESTAÑA 1: TIMBRADAS EN VIVO -->
            <div v-if="activeTab === 'timbradas'" class="p-5 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Marcaciones Recibidas en Tiempo Real
                    </h2>
                    <div class="flex items-center gap-2">
                        <button @click="descargarTodasTimbradas" class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                            <i class="fas fa-cloud-arrow-down"></i> Descargar Historial del Biométrico
                        </button>
                        <button @click="cargarTimbradas" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold hover:bg-slate-50 text-slate-600 flex items-center gap-1.5">
                            <i class="fas fa-rotate" :class="{ 'fa-spin': cargandoTimbradas }"></i> Refrescar
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3.5">Foto</th>
                                <th class="p-3.5">Colaborador</th>
                                <th class="p-3.5">Cédula</th>
                                <th class="p-3.5">Fecha y Hora</th>
                                <th class="p-3.5">Dispositivo</th>
                                <th class="p-3.5">Método</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="t in timbradas" :key="t.id" class="hover:bg-slate-50/80 transition">
                                <td class="p-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 overflow-hidden flex items-center justify-center border border-slate-200">
                                        <img v-if="t.foto_captura_url" :src="t.foto_captura_url" class="w-full h-full object-cover">
                                        <i v-else class="fas fa-user text-slate-300 text-lg"></i>
                                    </div>
                                </td>
                                <td class="p-3 font-bold text-slate-800">
                                    @{{ t.empleado ? (t.empleado.nombres + ' ' + (t.empleado.apellidos || '')) : (t.empleado_id || 'Desconocido') }}
                                </td>
                                <td class="p-3 font-mono text-slate-600">@{{ t.empleado_id }}</td>
                                <td class="p-3 font-mono text-slate-700 font-bold">@{{ formatFecha(t.fecha_hora) }}</td>
                                <td class="p-3 text-slate-500">@{{ t.dispositivo ? t.dispositivo.nombre : (t.serial_dispositivo || 'Hikvision') }}</td>
                                <td class="p-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 uppercase">
                                        @{{ t.sub_tipo || 'Rostro / Biometría' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!timbradas.length">
                                <td colspan="6" class="p-8 text-center text-slate-400">
                                    No hay marcaciones registradas todavía hoy. Las timbradas desde los dispositivos Hikvision aparecerán aquí al instante.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PESTAÑA 2: COLABORADORES -->
            <div v-if="activeTab === 'empleados'" class="p-5 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="relative w-full max-w-sm">
                        <i class="fas fa-search absolute left-3.5 top-3 text-slate-400"></i>
                        <input v-model="busquedaColaborador" type="text" placeholder="Buscar por nombre, cédula o cargo..."
                            class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <button @click="openModalNuevoEmpleado" class="px-3.5 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition flex items-center gap-1.5">
                        <i class="fas fa-plus"></i> Registrar Colaborador
                    </button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3.5">Foto</th>
                                <th class="p-3.5">Cédula</th>
                                <th class="p-3.5">Colaborador</th>
                                <th class="p-3.5">Cargo / Depto</th>
                                <th class="p-3.5">Horario Asignado</th>
                                <th class="p-3.5">Biometría</th>
                                <th class="p-3.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="emp in empleadosFiltrados" :key="emp.identificacion" class="hover:bg-slate-50/80 transition">
                                <td class="p-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 overflow-hidden flex items-center justify-center border border-slate-200 text-slate-400">
                                        <img v-if="emp.foto_url" :src="emp.foto_url" class="w-full h-full object-cover">
                                        <i v-else class="fas fa-user"></i>
                                    </div>
                                </td>
                                <td class="p-3 font-mono font-bold text-slate-700">@{{ emp.identificacion }}</td>
                                <td class="p-3 font-bold text-slate-900">
                                    @{{ emp.nombres }} @{{ emp.apellidos }}
                                    <span v-if="emp.email" class="block font-normal text-slate-400 text-[10px]">@{{ emp.email }}</span>
                                </td>
                                <td class="p-3 text-slate-600">@{{ emp.cargo || 'General' }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                                        @{{ emp.horario ? emp.horario.nombre : 'Sin asignar' }}
                                    </span>
                                </td>
                                <td class="p-3 space-x-1">
                                    <span :class="[
                                        'px-2 py-0.5 rounded text-[10px] font-bold',
                                        emp.rostro_registrado ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'
                                    ]">
                                        <i class="fas fa-face-smile mr-1"></i> Rostro
                                    </span>
                                    <span :class="[
                                        'px-2 py-0.5 rounded text-[10px] font-bold',
                                        emp.huella_registrada ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-500'
                                    ]">
                                        <i class="fas fa-fingerprint mr-1"></i> Huella
                                    </span>
                                </td>
                                <td class="p-3 text-right">
                                    <button @click="sincronizarRostro(emp)" class="px-2 py-1 rounded bg-blue-50 text-blue-600 hover:bg-blue-100 text-[11px] font-bold transition">
                                        <i class="fas fa-upload mr-1"></i> Enviar a Terminal
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!empleadosFiltrados.length">
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    No se encontraron colaboradores registrados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PESTAÑA 3: HORARIOS Y TURNOS -->
            <div v-if="activeTab === 'horarios'" class="p-5 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Catálogo de Horarios & Jornadas</h2>
                        <p class="text-xs text-slate-400">Define horas de entrada, salida, almuerzos y tolerancias para el cálculo de atrasos</p>
                    </div>
                    <button @click="openModalHorario" class="px-3.5 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition flex items-center gap-1.5">
                        <i class="fas fa-plus"></i> Crear Nuevo Horario
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="h in horarios" :key="h.id" class="p-5 rounded-2xl border border-slate-200 bg-white shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                @{{ h.nombre }}
                            </h3>
                            <span v-if="h.es_nocturno" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">
                                Nocturno
                            </span>
                        </div>

                        <div class="space-y-1.5 text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Entrada:</span>
                                <span class="font-mono font-bold text-blue-600">@{{ h.hora_entrada }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Salida:</span>
                                <span class="font-mono font-bold text-slate-800">@{{ h.hora_salida }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Tolerancia Atraso:</span>
                                <span class="font-bold text-amber-600">@{{ h.tolerancia_minutos || 10 }} min</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Almuerzo:</span>
                                <span>@{{ h.minutos_almuerzo || 60 }} min</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="!horarios.length" class="col-span-full p-8 text-center text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        No hay horarios configurados todavía. Crea una jornada para asignar a los colaboradores.
                    </div>
                </div>
            </div>

            <!-- PESTAÑA 4: PERMISOS Y VACACIONES -->
            <div v-if="activeTab === 'permisos'" class="p-5 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Solicitudes de Permisos y Vacaciones</h2>
                        <p class="text-xs text-slate-400">Control y aprobación de ausencias, licencias médicas y periodos vacacionales</p>
                    </div>
                    <button @click="openModalSolicitud" class="px-3.5 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition flex items-center gap-1.5">
                        <i class="fas fa-plus"></i> Registrar Solicitud
                    </button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3.5">Colaborador</th>
                                <th class="p-3.5">Tipo</th>
                                <th class="p-3.5">Desde</th>
                                <th class="p-3.5">Hasta</th>
                                <th class="p-3.5">Motivo</th>
                                <th class="p-3.5">Estado</th>
                                <th class="p-3.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="s in solicitudes" :key="s.id" class="hover:bg-slate-50/80 transition">
                                <td class="p-3 font-bold text-slate-800">
                                    @{{ s.empleado ? (s.empleado.nombres + ' ' + (s.empleado.apellidos || '')) : s.empleado_id }}
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700">
                                        @{{ s.tipo || 'VACACIONES' }}
                                    </span>
                                </td>
                                <td class="p-3 font-mono text-slate-600">@{{ s.fecha_inicio }}</td>
                                <td class="p-3 font-mono text-slate-600">@{{ s.fecha_fin }}</td>
                                <td class="p-3 text-slate-500">@{{ s.motivo }}</td>
                                <td class="p-3">
                                    <span :class="[
                                        'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase',
                                        s.estado === 'APROBADO' ? 'bg-emerald-100 text-emerald-800' :
                                        s.estado === 'RECHAZADO' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'
                                    ]">
                                        @{{ s.estado || 'PENDIENTE' }}
                                    </span>
                                </td>
                                <td class="p-3 text-right space-x-1">
                                    <button v-if="s.estado === 'PENDIENTE'" @click="resolverSolicitud(s, 'APROBADO')" class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded font-bold hover:bg-emerald-600 hover:text-white transition">Aprobar</button>
                                    <button v-if="s.estado === 'PENDIENTE'" @click="resolverSolicitud(s, 'RECHAZADO')" class="px-2 py-1 bg-rose-50 text-rose-700 rounded font-bold hover:bg-rose-600 hover:text-white transition">Rechazar</button>
                                </td>
                            </tr>
                            <tr v-if="!solicitudes.length">
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    No hay solicitudes registradas o pendientes por resolver.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PESTAÑA 5: TERMINALES HIKVISION -->
            <div v-if="activeTab === 'dispositivos'" class="p-5 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            Terminales Biométricos Hikvision Registrados
                        </h2>
                        <p class="text-xs text-slate-400">Administra los terminales faciales, huelleros y parámetros de red WAN/LAN</p>
                    </div>
                    <button @click="openModalDispositivo()" class="px-3.5 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-plus"></i> Registrar Terminal
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="disp in dispositivos" :key="disp.id" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-md transition">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg">
                                        <i class="fas fa-video"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm">@{{ disp.nombre }}</h4>
                                        <p class="text-[11px] text-slate-400 font-mono">@{{ disp.modelo || 'DS-K1T323MBFWX-E1' }}</p>
                                    </div>
                                </div>
                                <span :class="[
                                    'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                    disp.estado === 'activo' || disp.estado === 'ONLINE' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'
                                ]">
                                    @{{ disp.estado }}
                                </span>
                            </div>

                            <div class="space-y-1.5 text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400"><i class="fas fa-globe text-blue-500 mr-1"></i> IP WAN / DDNS:</span>
                                    <span class="font-mono font-bold text-blue-600">@{{ disp.ip }}:@{{ disp.puerto || 80 }}</span>
                                </div>
                                <div v-if="disp.ip_local || disp.ip_lan" class="flex justify-between items-center">
                                    <span class="text-slate-400"><i class="fas fa-network-wired text-emerald-500 mr-1"></i> IP LAN (Local):</span>
                                    <span class="font-mono font-bold text-emerald-600">@{{ disp.ip_local || disp.ip_lan }}:@{{ disp.puerto_local || 80 }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400">Ubicación:</span>
                                    <span class="font-medium text-slate-700">@{{ disp.ubicacion || 'SEBLAN Principal' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400">Serial:</span>
                                    <span class="font-mono text-slate-500 text-[11px]">@{{ disp.serial_number || '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100">
                            <span class="text-xs text-slate-400">
                                <i class="fas fa-fingerprint mr-1"></i> @{{ disp.timbradas_count || 0 }} timbradas
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button @click="subirTodosAlTerminal(disp.id)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Subir Colaboradores al Biométrico">
                                    <i class="fas fa-users-cog"></i>
                                </button>
                                <button @click="syncTimbradasTerminal(disp.id)" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Descargar Marcaciones">
                                    <i class="fas fa-cloud-download-alt"></i>
                                </button>
                                <button @click="openModalDispositivo(disp)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Editar Dispositivo">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="deleteDispositivo(disp)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="!dispositivos.length" class="col-span-full p-8 text-center text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        No hay biométricos registrados todavía. Agrega el terminal Hikvision de SEBLAN para habilitar la sincronización remota.
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL REGISTRO COLABORADOR -->
        <div v-if="modalEmpleado.show" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-base text-slate-800">Nuevo Colaborador</h3>
                    <button @click="modalEmpleado.show = false" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="guardarEmpleado" class="space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Cédula (10 dígitos) *</label>
                            <input v-model="modalEmpleado.form.identificacion" required maxlength="10" placeholder="1712345678" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Código Empleado</label>
                            <input v-model="modalEmpleado.form.codigo_empleado" placeholder="EMP-001" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nombres *</label>
                            <input v-model="modalEmpleado.form.nombres" required placeholder="Juan Carlos" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Apellidos</label>
                            <input v-model="modalEmpleado.form.apellidos" placeholder="Pérez Gómez" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Correo Electrónico</label>
                            <input v-model="modalEmpleado.form.email" type="email" placeholder="correo@seblan.com" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Horario Asignado</label>
                            <select v-model="modalEmpleado.form.horario_predeterminado_id" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                                <option :value="null">Sin asignar</option>
                                <option v-for="h in horarios" :key="h.id" :value="h.id">@{{ h.nombre }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalEmpleado.show = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition">Guardar Colaborador</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL REGISTRO HORARIO -->
        <div v-if="modalHorario.show" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-base text-slate-800">Nuevo Horario / Jornada</h3>
                    <button @click="modalHorario.show = false" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="guardarHorario" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nombre del Horario *</label>
                        <input v-model="modalHorario.form.nombre" required placeholder="Ej: Jornada Matutina" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Hora Entrada *</label>
                            <input v-model="modalHorario.form.hora_entrada" type="time" required class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Hora Salida *</label>
                            <input v-model="modalHorario.form.hora_salida" type="time" required class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tolerancia (min)</label>
                            <input v-model="modalHorario.form.tolerancia_minutos" type="number" placeholder="10" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Almuerzo (min)</label>
                            <input v-model="modalHorario.form.minutos_almuerzo" type="number" placeholder="60" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono">
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-1">
                        <input v-model="modalHorario.form.es_nocturno" type="checkbox" id="nocturnoCheck" class="rounded text-blue-600">
                        <label for="nocturnoCheck" class="text-slate-700 font-bold">Turno Nocturno (cruza medianoche)</label>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalHorario.show = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition">Guardar Horario</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL REGISTRO / EDICIÓN DISPOSITIVO BIOMÉTRICO (ARQUITECTURA DUAL WAN / LAN) -->
        <div v-if="modalDispositivo.show" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">
                        <i class="fas fa-server text-purple-600"></i>
                        @{{ modalDispositivo.form.id ? 'Editar Terminal Hikvision' : 'Registrar Terminal Hikvision' }}
                    </h3>
                    <button @click="modalDispositivo.show = false" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="guardarDispositivo" class="space-y-3.5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nombre Descriptivo *</label>
                        <input v-model="modalDispositivo.form.nombre" required placeholder="Ej: Biométrico Acceso Principal Garita" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    </div>

                    <!-- PARÁMETROS WAN (PÚBLICA / REMOTA) -->
                    <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-blue-50/50 border border-blue-100">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">
                                <i class="fas fa-globe text-blue-500 mr-1"></i> IP WAN / DDNS / Pública *
                            </label>
                            <input v-model="modalDispositivo.form.ip" required placeholder="Ej: civioncloud.ddns.net o IP pública" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono bg-white">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Puerto WAN *</label>
                            <input v-model="modalDispositivo.form.puerto" type="number" required placeholder="80 o 443" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono bg-white">
                        </div>
                    </div>

                    <!-- PARÁMETROS LAN (LOCAL / PRUEBAS) -->
                    <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-emerald-50/50 border border-emerald-100">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">
                                <i class="fas fa-network-wired text-emerald-500 mr-1"></i> IP LAN (Local)
                            </label>
                            <input v-model="modalDispositivo.form.ip_local" placeholder="192.168.1.100" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono bg-white">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Puerto LAN</label>
                            <input v-model="modalDispositivo.form.puerto_local" type="number" placeholder="80" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Modelo del Terminal</label>
                            <input v-model="modalDispositivo.form.modelo" placeholder="DS-K1T323MBFWX-E1" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Número de Serie (Serial)</label>
                            <input v-model="modalDispositivo.form.serial_number" placeholder="DS-K1T323-..." class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ubicación Física</label>
                        <input v-model="modalDispositivo.form.ubicacion" placeholder="Ej: Planta Baja - Edificio Administrativo" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Usuario ISAPI</label>
                            <input v-model="modalDispositivo.form.usuario_api" placeholder="admin" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Contraseña ISAPI</label>
                            <input v-model="modalDispositivo.form.password_api" type="password" placeholder="••••••••" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Estado</label>
                        <select v-model="modalDispositivo.form.estado" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                            <option value="activo">Activo (Habilitado para timbradas)</option>
                            <option value="inactivo">Inactivo / En Mantenimiento</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalDispositivo.show = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 text-white font-bold hover:bg-purple-700 transition">
                            @{{ modalDispositivo.form.id ? 'Actualizar Terminal' : 'Guardar Terminal' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL REGISTRO SOLICITUD PERMISO/VACACION -->
        <div v-if="modalSolicitud.show" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-base text-slate-800">Registrar Solicitud / Licencia</h3>
                    <button @click="modalSolicitud.show = false" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="guardarSolicitud" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Colaborador *</label>
                        <select v-model="modalSolicitud.form.empleado_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200">
                            <option value="">Seleccione Colaborador</option>
                            <option v-for="emp in empleados" :key="emp.identificacion" :value="emp.identificacion">
                                @{{ emp.nombres }} @{{ emp.apellidos }} (@{{ emp.identificacion }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tipo de Solicitud *</label>
                        <select v-model="modalSolicitud.form.tipo" required class="w-full px-3 py-2 rounded-xl border border-slate-200">
                            <option value="VACACIONES">Vacaciones</option>
                            <option value="MEDICO">Permiso Médico</option>
                            <option value="CALAMIDAD">Calamidad Doméstica</option>
                            <option value="PERSONAL">Asunto Personal</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Fecha Inicio *</label>
                            <input v-model="modalSolicitud.form.fecha_inicio" type="date" required class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Fecha Fin *</label>
                            <input v-model="modalSolicitud.form.fecha_fin" type="date" required class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Motivo / Justificación *</label>
                        <textarea v-model="modalSolicitud.form.motivo" required rows="2" placeholder="Detalle el motivo de la ausencia..." class="w-full px-3 py-2 rounded-xl border border-slate-200"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalSolicitud.show = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition">Enviar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        const { createApp, ref, computed, onMounted } = Vue;

        createApp({
            setup() {
                const activeTab = ref('dispositivos');

                const tabs = [
                    { id: 'timbradas', name: 'Timbradas en Vivo', icon: 'fas fa-satellite-dish' },
                    { id: 'empleados', name: 'Ficha de Colaboradores', icon: 'fas fa-users' },
                    { id: 'horarios', name: 'Horarios & Turnos', icon: 'fas fa-clock' },
                    { id: 'permisos', name: 'Permisos & Vacaciones', icon: 'fas fa-umbrella-beach' },
                    { id: 'dispositivos', name: 'Terminales Biométricos', icon: 'fas fa-fingerprint' }
                ];

                const metricas = ref({});
                const timbradas = ref([]);
                const empleados = ref([]);
                const horarios = ref([]);
                const solicitudes = ref([]);
                const dispositivos = ref([]);

                const cargandoTimbradas = ref(false);
                const busquedaColaborador = ref('');

                const modalEmpleado = ref({ show: false, form: {} });
                const modalHorario = ref({ show: false, form: { tolerancia_minutos: 10, minutos_almuerzo: 60 } });
                const modalDispositivo = ref({
                    show: false,
                    form: {
                        id: null,
                        nombre: '',
                        ip: '',
                        puerto: 80,
                        ip_local: '192.168.1.100',
                        puerto_local: 80,
                        modelo: 'DS-K1T323MBFWX-E1',
                        serial_number: '',
                        ubicacion: 'SEBLAN Principal',
                        usuario_api: 'admin',
                        password_api: '',
                        estado: 'activo'
                    }
                });
                const modalSolicitud = ref({ show: false, form: { tipo: 'VACACIONES' } });

                const empleadosFiltrados = computed(() => {
                    if (!busquedaColaborador.value) return empleados.value;
                    const q = busquedaColaborador.value.toLowerCase();
                    return empleados.value.filter(e => 
                        (e.nombres + ' ' + (e.apellidos || '')).toLowerCase().includes(q) ||
                        (e.identificacion || '').includes(q)
                    );
                });

                const cargarDashboard = async () => {
                    try {
                        const res = await axios.get('{{ url("/api/th/dashboard") }}');
                        if (res.data?.metricas) metricas.value = res.data.metricas;
                    } catch (e) {
                        console.error('Error cargando métricas:', e);
                    }
                };

                const cargarTimbradas = async () => {
                    cargandoTimbradas.value = true;
                    try {
                        const res = await axios.get('{{ url("/api/th/timbradas/recientes") }}');
                        timbradas.value = res.data?.data || res.data || [];
                    } catch (e) {
                        console.error('Error cargando timbradas:', e);
                    } finally {
                        cargandoTimbradas.value = false;
                    }
                };

                const cargarEmpleados = async () => {
                    try {
                        const res = await axios.get('{{ url("/api/th/empleados") }}');
                        empleados.value = res.data?.data || res.data || [];
                    } catch (e) {
                        console.error('Error cargando colaboradores:', e);
                    }
                };

                const cargarHorarios = async () => {
                    try {
                        const res = await axios.get('{{ url("/api/th/horarios") }}');
                        horarios.value = res.data?.data || res.data || [];
                    } catch (e) {
                        console.error('Error cargando horarios:', e);
                    }
                };

                const cargarSolicitudes = async () => {
                    try {
                        const res = await axios.get('{{ url("/api/th/solicitudes/permisos") }}');
                        solicitudes.value = res.data?.data || res.data || [];
                    } catch (e) {
                        console.error('Error cargando solicitudes:', e);
                    }
                };

                const cargarDispositivos = async () => {
                    try {
                        const res = await axios.get('{{ url("/api/th/dispositivos") }}');
                        dispositivos.value = res.data || [];
                    } catch (e) {
                        console.error('Error cargando dispositivos:', e);
                    }
                };

                const openModalNuevoEmpleado = () => {
                    modalEmpleado.value = { show: true, form: { identificacion: '', nombres: '', apellidos: '', email: '', horario_predeterminado_id: null } };
                };

                const guardarEmpleado = async () => {
                    try {
                        await axios.post('{{ url("/api/th/empleados") }}', modalEmpleado.value.form);
                        Swal.fire('¡Éxito!', 'Colaborador guardado con éxito', 'success');
                        modalEmpleado.value.show = false;
                        cargarEmpleados();
                        cargarDashboard();
                    } catch (e) {
                        Swal.fire('Error', e.response?.data?.message || 'Error al guardar colaborador', 'error');
                    }
                };

                const openModalHorario = () => {
                    modalHorario.value = { show: true, form: { nombre: '', hora_entrada: '08:00', hora_salida: '17:00', tolerancia_minutos: 10, minutos_almuerzo: 60, es_nocturno: false } };
                };

                const guardarHorario = async () => {
                    try {
                        await axios.post('{{ url("/api/th/horarios") }}', modalHorario.value.form);
                        Swal.fire('¡Éxito!', 'Horario configurado', 'success');
                        modalHorario.value.show = false;
                        cargarHorarios();
                    } catch (e) {
                        Swal.fire('Error', e.response?.data?.message || 'Error al guardar horario', 'error');
                    }
                };

                const openModalDispositivo = (disp = null) => {
                    if (disp) {
                        modalDispositivo.value.form = {
                            id: disp.id,
                            nombre: disp.nombre,
                            ip: disp.ip,
                            puerto: disp.puerto || 80,
                            ip_local: disp.ip_local || disp.ip_lan || '',
                            puerto_local: disp.puerto_local || 80,
                            modelo: disp.modelo || 'DS-K1T323MBFWX-E1',
                            serial_number: disp.serial_number || '',
                            ubicacion: disp.ubicacion || 'SEBLAN Principal',
                            usuario_api: disp.usuario_api || 'admin',
                            password_api: '',
                            estado: disp.estado || 'activo'
                        };
                    } else {
                        modalDispositivo.value.form = {
                            id: null,
                            nombre: '',
                            ip: '',
                            puerto: 80,
                            ip_local: '192.168.1.100',
                            puerto_local: 80,
                            modelo: 'DS-K1T323MBFWX-E1',
                            serial_number: '',
                            ubicacion: 'SEBLAN Principal',
                            usuario_api: 'admin',
                            password_api: '',
                            estado: 'activo'
                        };
                    }
                    modalDispositivo.value.show = true;
                };

                const guardarDispositivo = async () => {
                    try {
                        const res = await axios.post('{{ url("/api/th/dispositivos") }}', modalDispositivo.value.form);
                        Swal.fire('¡Éxito!', res.data?.message || 'Terminal biométrico guardado exitosamente', 'success');
                        modalDispositivo.value.show = false;
                        cargarDispositivos();
                        cargarDashboard();
                    } catch (e) {
                        Swal.fire('Error', e.response?.data?.message || 'Error al guardar dispositivo', 'error');
                    }
                };

                const deleteDispositivo = async (disp) => {
                    const result = await Swal.fire({
                        title: '¿Eliminar Dispositivo?',
                        text: `Se desvinculará el terminal ${disp.nombre} (${disp.ip})`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#ef4444',
                    });

                    if (result.isConfirmed) {
                        try {
                            await axios.delete(`{{ url("/api/th/dispositivos") }}/${disp.id}`);
                            Swal.fire('Eliminado', 'Dispositivo eliminado', 'success');
                            cargarDispositivos();
                            cargarDashboard();
                        } catch (e) {
                            Swal.fire('Error', 'No se pudo eliminar el dispositivo', 'error');
                        }
                    }
                };

                const descargarTodasTimbradas = async () => {
                    try {
                        Swal.fire({
                            title: 'Conectando con los Biométricos...',
                            text: 'Descargando eventos de asistencia e ignorando duplicados automáticamente',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                        const res = await axios.post('{{ url("/api/th/dispositivos/sync-timbradas") }}');
                        Swal.fire({
                            icon: 'success',
                            title: '¡Descarga Finalizada!',
                            text: res.data?.message || 'Marcaciones sincronizadas con éxito.'
                        });
                        cargarTimbradas();
                        cargarDashboard();
                    } catch (e) {
                        Swal.fire('Aviso', e.response?.data?.message || 'No se pudo comunicar con los terminales. Verifique la IP y puerto.', 'warning');
                    }
                };

                const syncTimbradasTerminal = async (dispId) => {
                    try {
                        Swal.fire({
                            title: 'Conectando con el biométrico...',
                            text: 'Descargando eventos de asistencia desde el terminal Hikvision',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                        const res = await axios.post(`{{ url("/api/th/dispositivos/sync-timbradas") }}/${dispId}`);
                        Swal.fire('¡Sincronizado!', res.data?.message || 'Marcaciones descargadas correctamente', 'success');
                        cargarTimbradas();
                        cargarDashboard();
                    } catch (e) {
                        Swal.fire('Aviso de Conexión', e.response?.data?.message || 'No se pudo comunicar con la IP del terminal. Verifica que el puerto esté abierto.', 'warning');
                    }
                };

                const subirTodosAlTerminal = async (dispId) => {
                    try {
                        Swal.fire({
                            title: 'Enviando colaboradores...',
                            text: 'Sincronizando nómina y permisos faciales en el biométrico Hikvision',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                        const res = await axios.post(`{{ url("/api/th/dispositivos") }}/${dispId}/subir-todos`);
                        Swal.fire('¡Enviados!', res.data?.message || 'Colaboradores enrolados en el terminal', 'success');
                    } catch (e) {
                        Swal.fire('Aviso', e.response?.data?.message || 'No se pudo comunicar con el terminal Hikvision', 'warning');
                    }
                };

                const openModalSolicitud = () => {
                    modalSolicitud.value = { show: true, form: { empleado_id: '', tipo: 'VACACIONES', fecha_inicio: '', fecha_fin: '', motivo: '' } };
                };

                const guardarSolicitud = async () => {
                    try {
                        await axios.post('{{ url("/api/th/colaborador/solicitar-permiso") }}', modalSolicitud.value.form);
                        Swal.fire('¡Éxito!', 'Solicitud registrada', 'success');
                        modalSolicitud.value.show = false;
                        cargarSolicitudes();
                    } catch (e) {
                        Swal.fire('Error', e.response?.data?.message || 'Error al registrar solicitud', 'error');
                    }
                };

                const resolverSolicitud = async (sol, estado) => {
                    try {
                        await axios.post(`{{ url("/api/th/solicitudes/permisos") }}/${sol.id}/resolver`, { estado });
                        Swal.fire('Actualizado', `Solicitud ${estado.toLowerCase()} con éxito`, 'success');
                        cargarSolicitudes();
                    } catch (e) {
                        Swal.fire('Error', e.response?.data?.message || 'Error al resolver solicitud', 'error');
                    }
                };

                const sincronizarRostro = (emp) => {
                    Swal.fire('Enrolamiento Biométrico', `Sincronizando plantilla biométrica de ${emp.nombres}...`, 'info');
                };

                const formatFecha = (d) => {
                    if (!d) return '—';
                    return new Date(d).toLocaleString('es-EC', { dateStyle: 'short', timeStyle: 'medium' });
                };

                onMounted(() => {
                    cargarDashboard();
                    cargarDispositivos();
                    cargarEmpleados();
                    cargarHorarios();
                    cargarSolicitudes();
                    cargarTimbradas();
                });

                return {
                    activeTab,
                    tabs,
                    metricas,
                    timbradas,
                    empleados,
                    horarios,
                    solicitudes,
                    dispositivos,
                    cargandoTimbradas,
                    busquedaColaborador,
                    empleadosFiltrados,
                    modalEmpleado,
                    modalHorario,
                    modalDispositivo,
                    modalSolicitud,
                    openModalNuevoEmpleado,
                    guardarEmpleado,
                    openModalHorario,
                    guardarHorario,
                    openModalDispositivo,
                    guardarDispositivo,
                    deleteDispositivo,
                    descargarTodasTimbradas,
                    syncTimbradasTerminal,
                    subirTodosAlTerminal,
                    openModalSolicitud,
                    guardarSolicitud,
                    resolverSolicitud,
                    cargarTimbradas,
                    sincronizarRostro,
                    formatFecha
                };
            }
        }).mount('#app');
    </script>
</body>
</html>
