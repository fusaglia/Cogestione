<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Log</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .dedicata {
            background-color: #e8f5e9;
            font-weight: bold;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background-color: #45a049;
        }
        .stats {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        .info-box {
            background-color: #e3f2fd;
            padding: 10px;
            border-left: 4px solid #2196F3;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div id="app"></div>

    <script>
        const API_URL = 'api.php';

        // Gestione cookies
        function setCookie(name, value, days = 365) {
            const d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = `${name}=${value};expires=${d.toUTCString()};path=/`;
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for(let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function deleteCookie(name) {
            document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/`;
        }

        // Verifica sessione
        async function checkSession() {
            const sessionToken = getCookie('session_token');
            if (!sessionToken) return null;

            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'check_session', session_token: sessionToken})
            });
            const data = await response.json();
            return data.success ? data.user : null;
        }

        // Registrazione
        async function register(username, password) {
            const canRegister = getCookie('can_register');
            if (canRegister !== 'true') {
                alert('Non puoi registrarti da questa postazione');
                return false;
            }

            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'register', username, password})
            });
            const data = await response.json();
            
            if (data.success) {
                alert('Registrazione completata!');
                return true;
            } else {
                alert(data.message);
                return false;
            }
        }

        // Login
        async function login(username, password) {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'login', username, password})
            });
            const data = await response.json();
            
            if (data.success) {
                setCookie('session_token', data.session_token);
                render();
                return true;
            } else {
                alert(data.message);
                return false;
            }
        }

        // Logout
        async function logout() {
            const sessionToken = getCookie('session_token');
            await fetch(API_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'logout', session_token: sessionToken})
            });
            deleteCookie('session_token');
            render();
        }

        // Carica accessi utente
        async function loadAccessi() {
            const sessionToken = getCookie('session_token');
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'get_accessi', session_token: sessionToken})
            });
            const data = await response.json();
            return {
                accessi: data.accessi || [],
                stats: data.accessi_postazione_dedicata || 0
            };
        }

        // Download log
        async function downloadLog() {
            const sessionToken = getCookie('session_token');
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'download_log', session_token: sessionToken})
            });
            const data = await response.json();
            
            if (data.success) {
                // Crea blob e scarica
                const blob = new Blob([data.csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = data.filename;
                link.click();
            } else {
                alert('Errore nel download del log');
            }
        }

        // Render
        async function render() {
            const app = document.getElementById('app');
            const user = await checkSession();
            
            if (user) {
                const {accessi, stats} = await loadAccessi();
                const stationId = getCookie('station_id');
                const isDedicata = stationId === 'POSTAZIONE_DEDICATA';
                
                app.innerHTML = `
                    <h1>Benvenuto, ${user.username}</h1>
                    <button onclick="logout()">Logout</button>
                    <button onclick="downloadLog()">📥 Scarica Log CSV</button>
                    
                    <div class="info-box">
                        <strong>Postazione corrente:</strong> ${stationId} 
                        ${isDedicata ? '✅ <strong>(POSTAZIONE DEDICATA)</strong>' : ''}
                    </div>
                    
                    <div class="stats">
                        <h3>📊 Statistiche</h3>
                        <p><strong>Totale accessi dalla postazione dedicata:</strong> ${stats}</p>
                        <p><strong>Totale accessi registrati:</strong> ${accessi.length}</p>
                    </div>
                    
                    <hr>
                    <h2>I tuoi accessi</h2>
                    <table>
                        <tr>
                            <th>Data Login</th>
                            <th>Data Logout</th>
                            <th>Postazione</th>
                            <th>Dedicata</th>
                            <th>Logout Auto</th>
                        </tr>
                        ${accessi.map(a => `
                            <tr class="${a.is_postazione_dedicata ? 'dedicata' : ''}">
                                <td>${a.data_login}</td>
                                <td>${a.data_logout || '⏱️ In corso'}</td>
                                <td>${a.station_id}</td>
                                <td>${a.is_postazione_dedicata ? '✅ Sì' : 'No'}</td>
                                <td>${a.logout_automatico ? '⚠️ Sì' : 'No'}</td>
                            </tr>
                        `).join('')}
                    </table>
                `;
            } else {
                const canRegister = getCookie('can_register') === 'true';
                const stationId = getCookie('station_id');
                const isDedicata = stationId === 'POSTAZIONE_DEDICATA';
                
                app.innerHTML = `
                    <h1>Monitor Log - Sistema di Accesso</h1>
                    
                    <div class="info-box">
                        <strong>Postazione:</strong> ${stationId}
                        ${isDedicata ? '<br>✅ <strong>POSTAZIONE DEDICATA</strong>' : ''}
                        <br><strong>Registrazione:</strong> ${canRegister ? '✅ Disponibile' : '❌ Non disponibile'}
                    </div>
                    
                    ${canRegister ? `
                        <h2>Registrazione</h2>
                        <form onsubmit="handleRegister(event)" autocomplete="off">
                            <label>Username:</label><br>
                            <input type="text" id="reg_username" required autocomplete="off"><br><br>
                            <label>Password:</label><br>
                            <input type="password" id="reg_password" required autocomplete="new-password"><br><br>
                            <button type="submit">Registrati</button>
                        </form>
                        <hr>
                    ` : '<p><em>⚠️ Registrazione non disponibile da questa postazione</em></p><hr>'}
                    
                    <h2>Login</h2>
                    <form onsubmit="handleLogin(event)" autocomplete="off">
                        <label>Username:</label><br>
                        <input type="text" id="login_username" required autocomplete="off"><br><br>
                        <label>Password:</label><br>
                        <input type="password" id="login_password" required autocomplete="current-password"><br><br>
                        <button type="submit">Accedi</button>
                    </form>
                `;
            }
        }

        // Event handlers
        async function handleRegister(e) {
            e.preventDefault();
            const username = document.getElementById('reg_username').value;
            const password = document.getElementById('reg_password').value;
            if (await register(username, password)) {
                render();
            }
        }

        async function handleLogin(e) {
            e.preventDefault();
            const username = document.getElementById('login_username').value;
            const password = document.getElementById('login_password').value;
            await login(username, password);
        }

        // Inizializzazione
        // IMPORTANTE: Per testare la postazione dedicata, cambia 'STATION_' in 'POSTAZIONE_DEDICATA'
        if (!getCookie('station_id')) {
            // Per postazione dedicata usa: setCookie('station_id', 'POSTAZIONE_DEDICATA');
            // Per altre postazioni usa: setCookie('station_id', 'STATION_' + Math.random().toString(36).substr(2, 9));
            setCookie('station_id', 'STATION_' + Math.random().toString(36).substr(2, 9));
        }
        
        // Imposta se questa postazione può registrare
        // true = può registrare (solo postazione dedicata)
        // false = non può registrare (altre postazioni)
        if (!getCookie('can_register')) {
            const stationId = getCookie('station_id');
            const canReg = stationId === 'POSTAZIONE_DEDICATA' ? 'true' : 'false';
            setCookie('can_register', canReg);
        }

        render();
    </script>
</body>
</html>