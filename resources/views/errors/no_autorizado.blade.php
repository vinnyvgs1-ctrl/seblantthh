<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Restringido • SEBLAN Talento Humano</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>* { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-200 p-8 text-center space-y-6">
        <div class="w-20 h-20 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-3xl mx-auto border border-amber-100 shadow-inner">
            <i class="fas fa-shield-halved"></i>
        </div>

        <div class="space-y-2">
            <h1 class="text-xl font-black text-slate-900">Acceso Seguro Requerido</h1>
            <p class="text-xs text-slate-500 leading-relaxed">
                {{ $mensaje ?? 'No tiene una sesión activa autorizada. Este módulo es exclusivo y requiere ingresar a través del menú principal del CRM de SEBLAN (Scriptcase).' }}
            </p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-left text-xs space-y-2 text-slate-600">
            <div class="font-bold flex items-center gap-1.5 text-slate-700">
                <i class="fas fa-circle-info text-blue-500"></i> ¿Cómo ingresar correctamente?
            </div>
            <ol class="list-decimal list-inside space-y-1 text-slate-500">
                <li>Inicie sesión en el sistema principal SEBLAN CRM.</li>
                <li>Haga clic en la opción <strong>Talento Humano</strong> del menú.</li>
                <li>El sistema validará su firma de seguridad digital automáticamente.</li>
            </ol>
        </div>

        <div class="pt-2">
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-sm">
                <i class="fas fa-arrow-left"></i> Regresar al CRM
            </a>
        </div>
    </div>
</body>
</html>
