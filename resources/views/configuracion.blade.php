<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Integración - SEBLAN TTHH</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #0B1120;
            --card-bg: #131C31;
            --card-border: #1E293B;
            --accent-primary: #3B82F6;
            --accent-glow: rgba(59, 130, 246, 0.25);
            --accent-success: #10B981;
            --text-title: #F8FAFC;
            --text-body: #94A3B8;
            --text-muted: #64748B;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-body); min-height: 100vh; padding: 2.5rem 1.5rem; }
        .container { max-width: 1100px; margin: 0 auto; }
        
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; border-bottom: 1px solid var(--card-border); padding-bottom: 1.5rem; }
        .header-title h1 { color: var(--text-title); font-size: 1.75rem; font-weight: 800; display: flex; align-items: center; gap: 0.75rem; }
        .badge-status { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-success { background: rgba(16, 185, 129, 0.12); color: var(--accent-success); border: 1px solid rgba(16, 185, 129, 0.3); }

        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: var(--card-bg); border: 1px solid var(--card-border); padding: 1.25rem; border-radius: 1rem; }
        .stat-label { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.35rem; }
        .stat-value { font-size: 1.5rem; font-weight: 800; color: var(--text-title); }

        .card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.75rem; margin-bottom: 1.75rem; }
        .card-header { margin-bottom: 1.25rem; }
        .card-header h2 { font-size: 1.2rem; font-weight: 700; color: var(--text-title); display: flex; align-items: center; gap: 0.5rem; }
        .card-header p { font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem; }

        .field-group { margin-bottom: 1.25rem; }
        label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-title); margin-bottom: 0.5rem; }
        .input-row { display: flex; gap: 0.75rem; }
        input[type="text"] { flex: 1; background: #0F172A; border: 1px solid var(--card-border); color: #E2E8F0; padding: 0.75rem 1rem; border-radius: 0.6rem; font-family: 'JetBrains Mono', monospace; font-size: 0.9rem; }
        input[type="text"]:focus { outline: none; border-color: var(--accent-primary); box-shadow: 0 0 0 3px var(--accent-glow); }
        
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1.25rem; border-radius: 0.6rem; font-weight: 700; font-size: 0.875rem; cursor: pointer; border: none; transition: all 0.2s; }
        .btn-primary { background: var(--accent-primary); color: #fff; }
        .btn-primary:hover { background: #2563EB; box-shadow: 0 4px 12px var(--accent-glow); }
        .btn-copy { background: #1E293B; color: #CBD5E1; }
        .btn-copy:hover { background: #334155; color: #FFF; }

        .code-block { position: relative; background: #0A0F1D; border: 1px solid var(--card-border); border-radius: 0.75rem; padding: 1.25rem; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; color: #38BDF8; line-height: 1.6; overflow-x: auto; white-space: pre; }
        .copy-code-btn { position: absolute; top: 0.75rem; right: 0.75rem; padding: 0.4rem 0.75rem; font-size: 0.75rem; }

        .alert { padding: 1rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem; margin-bottom: 1.5rem; font-weight: 500; }
        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34D399; }
        .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #F87171; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-title">
                <h1>⚙️ SEBLAN • Panel de Integración & SSO</h1>
            </div>
            <div class="badge-status badge-success">
                ● Base de Datos: {{ $dbStatus }}
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="grid-stats">
            <div class="stat-card">
                <div class="stat-label">Base de Datos</div>
                <div class="stat-value">{{ $dbName }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Usuarios Scriptcase</div>
                <div class="stat-value">{{ $totalUsuarios }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Empresas Registradas</div>
                <div class="stat-value">{{ $totalEmpresas }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Biométricos Hikvision</div>
                <div class="stat-value">{{ $totalDispositivos }}</div>
            </div>
        </div>

        <!-- SECCIÓN 1: CLAVE SECRETA SSO -->
        <div class="card">
            <div class="card-header">
                <h2>🔑 Clave Secreta SSO (Single Sign-On)</h2>
                <p>Esta clave compartida firma digitalmente los tokens HMAC para que Scriptcase pueda abrir este microservicio de forma segura y sin pedir contraseña.</p>
            </div>
            <form action="{{ url('/configuracion/sso-secret') }}" method="POST">
                @csrf
                <div class="field-group">
                    <label>SCRIPTCASE_SSO_SECRET:</label>
                    <div class="input-row">
                        <input type="text" name="sso_secret" id="sso_secret" value="{{ $ssoSecret }}" required>
                        <button type="submit" class="btn btn-primary">Guardar Clave</button>
                        <button type="button" class="btn btn-copy" onclick="copyValue('sso_secret')">Copiar</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- SECCIÓN 2: CÓDIGO SCRIPTCASE LISTO PARA PEGAR -->
        <div class="card">
            <div class="card-header">
                <h2>📋 Código PHP para Scriptcase (OnExecute del Menú)</h2>
                <p>Copia y pega este bloque en el evento <code>OnExecute</code> de tu aplicación o elemento de menú en Scriptcase:</p>
            </div>
            <div class="code-block" id="scriptcase_code">
// 1. Tomar usuario activo de la sesión de Scriptcase
$usr_actual = [usr_login];
$timestamp = time();

// 2. Clave secreta compartida con el microservicio
$clave_secreta = "{{ $ssoSecret }}";
$token = hash_hmac("sha256", $usr_actual . "|" . $timestamp, $clave_secreta);

// 3. URL de destino protegida con SSO
$url_tthh = "{{ $ssoUrl }}?user=" . urlencode($usr_actual) . "&t=" . $timestamp . "&token=" . $token;

// 4. Redireccionar (pestaña nueva o dentro de un contenedor Iframe de Scriptcase)
sc_redir($url_tthh, "_blank");
                <button type="button" class="btn btn-copy copy-code-btn" onclick="copySnippet('scriptcase_code')">Copiar Código</button>
            </div>
        </div>

        <!-- SECCIÓN 3: WEBHOOK BIOMÉTRICO HIKVISION -->
        <div class="card">
            <div class="card-header">
                <h2>📡 Endpoint Webhook para Terminales Hikvision</h2>
                <p>Configura este URL en la sección <strong>Configuration &gt; Network &gt; Advanced &gt; HTTP Listening / Alarm Server</strong> del biométrico:</p>
            </div>
            <div class="field-group">
                <label>URL de Recepción en Tiempo Real:</label>
                <div class="input-row">
                    <input type="text" id="webhook_url" value="{{ $webhookUrl }}" readonly>
                    <button type="button" class="btn btn-copy" onclick="copyValue('webhook_url')">Copiar URL</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyValue(id) {
            const input = document.getElementById(id);
            input.select();
            navigator.clipboard.writeText(input.value);
            alert('¡Copiado al portapapeles!');
        }

        function copySnippet(id) {
            const el = document.getElementById(id);
            const text = el.innerText.replace('Copiar Código', '').trim();
            navigator.clipboard.writeText(text);
            alert('¡Código Scriptcase copiado al portapapeles!');
        }
    </script>
</body>
</html>
