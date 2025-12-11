/* ============================================
   ADMIN DASHBOARD – VERSIÓN FINAL CORREGIDA
   Funciona con .htaccess y Router PHP
   ============================================ */

/* 
   1. CONFIGURACIÓN DE LA RUTA
   Al estar el HTML en la carpeta 'admin' y el htaccess en 'admin/api',
   la ruta relativa correcta es "api".
   El .htaccess convertirá "api/clientes" en una llamada interna a index.php.
*/
const API_BASE = "api"; 

/* 
   2. FUNCIÓN GENÉRICA PARA PEDIR DATOS
*/
async function getData(resource) {
    try {
        // Construimos la URL: api/clientes, api/cursos, etc.
        const url = `${API_BASE}/${resource}`;
        
        console.log(`Pidiendo datos a: ${url}`); 

        const res = await fetch(url);

        // Si el servidor devuelve error (404, 500)
        if (!res.ok) {
            console.error(`Error HTTP ${res.status}: Fallo al conectar con ${url}`);
            return [];
        }

        // Verificación de seguridad: ¿Nos han devuelto JSON?
        const contentType = res.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            // Si entra aquí, es que PHP ha fallado (Error de BD) y ha devuelto HTML
            const text = await res.text();
            console.error("CRÍTICO: El servidor no devolvió JSON. Revisa Database.php (nombre de la BD). Respuesta:", text);
            return [];
        }

        const json = await res.json();
        
        // Manejamos la estructura de respuesta típica
        if (json.success === true) {
            return json.data || [];
        } else if (Array.isArray(json)) {
            // Por si tu API devuelve directamente el array
            return json;
        } else {
            console.error(`La API devolvió un error para ${resource}:`, json.message);
            return [];
        }

    } catch (error) {
        console.error("Error de red o de sintaxis JS:", error);
        return [];
    }
}

/* 
   3. PETICIONES A LA API
   Usamos los nombres de recursos que tu index.php espera recibir.
*/

async function fetchUsuarios() {
    return await getData("clientes");
}

async function fetchTransacciones() {
    return await getData("transacciones");
}

async function fetchCursos() {
    return await getData("cursos");
}

async function fetchBonos() {
    return await getData("bonos");
}

async function fetchEventos() {
    return await getData("eventos");
}

/* 
   4. RENDERIZADO DE TABLAS Y LISTAS
*/

// === USUARIOS ===
async function renderUserTable() {
    const tbody = document.getElementById('user-table-body');
    if (!tbody) return;

    // Ponemos un loader o limpiamos antes
    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Cargando...</td></tr>';

    const usuarios = await fetchUsuarios();

    if (usuarios.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center p-4 text-red-500">No hay usuarios o error de conexión (Ver Consola)</td></tr>';
        return;
    }

    tbody.innerHTML = usuarios.map(user => `
        <tr class="border-b hover:bg-gray-50">
            <td class="p-3 font-bold">${user.Nombre} ${user.Apellidos || ''}</td>
            <td class="p-3">${user.Telefono}</td>
            <td class="p-3">${user.Email}</td>
            <td class="p-3">${user.DNI ?? '—'}</td>
            <td class="p-3 text-right">
                <button class="text-gray-500 hover:text-rose-600 mr-2" title="Editar">✏</button>
                <button class="text-gray-500 hover:text-rose-600" title="Eliminar">🗑</button>
            </td>
        </tr>
    `).join('');
}

// === TRANSACCIONES ===
async function renderTransactionTable() {
    const tbody = document.getElementById('transaction-table-body');
    if (!tbody) return;

    const transacciones = await fetchTransacciones();

    if (transacciones.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center p-4">No hay transacciones recientes.</td></tr>';
        return;
    }

    tbody.innerHTML = transacciones.map(tx => `
        <tr class="border-b hover:bg-gray-50">
            <td class="p-3 font-bold">${tx.cliente_DNI || tx.DNI}</td>
            <td class="p-3">${tx.FechaCompra || tx.fecha}</td>
            <td class="p-3">${tx.bono_tipo || '-'}</td>
            <td class="p-3 text-center">${tx.bono_numDias || '-'}</td>
            <td class="p-3 font-bold text-green-600">${tx.costo || tx.importe}€</td>
            <td class="p-3 text-right">
                <button class="text-gray-500 hover:text-blue-600">👁</button>
            </td>
        </tr>
    `).join('');
}

// === CURSOS ===
async function renderCursosTable() {
    const container = document.getElementById('cursos-list-container');
    if (!container) return;

    const cursos = await fetchCursos();

    if (cursos.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500">No hay cursos disponibles.</p>';
        return;
    }

    container.innerHTML = cursos.map(curso => `
        <div class="bg-white border rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <h4 class="font-black text-lg text-rose-600">${curso.TipoBaile}</h4>
                <span class="bg-gray-100 text-xs font-bold px-2 py-1 rounded">${curso.Nivel}</span>
            </div>
            <p class="text-gray-600 text-sm mt-2">${curso.Descripcion}</p>
            <div class="mt-3 text-xs text-gray-400">Aforo: ${curso.Aforo} pers.</div>
        </div>
    `).join('');
}

// === BONOS ===
async function renderBonosList() {
    const container = document.getElementById('bonos-list-container');
    if (!container) return;

    const bonos = await fetchBonos();

    container.innerHTML = bonos.map(bono => `
        <div class="bg-gray-900 text-white p-4 rounded-lg mb-4 flex justify-between items-center shadow-lg">
            <div>
                <span class="font-bold uppercase block text-rose-500 tracking-wider">${bono.tipo}</span>
                <span class="text-xs text-gray-400">${bono.numDias} clases / días</span>
            </div>
            <p class="font-black text-2xl">${bono.precio} €</p>
        </div>
    `).join('');
}

// === EVENTOS ===
async function renderEventosList() {
    const container = document.getElementById('eventos-list-container');
    if (!container) return;

    const eventos = await fetchEventos();

    container.innerHTML = eventos.map(evento => `
        <div class="bg-white border p-4 rounded-lg flex gap-4 mb-4 items-center shadow-sm">
            <div class="w-16 h-16 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                <img src="${evento.URLFoto}" alt="Evento" class="w-full h-full object-cover">
            </div>
            <div class="flex-1">
                <span class="font-bold uppercase text-gray-800 block">${evento.TítuloEvento}</span>
                <p class="text-sm text-gray-500 line-clamp-2">${evento.descripciónEvento}</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-rose-600 text-sm whitespace-nowrap">${evento.FechaEvento}</p>
                <a href="${evento.enlaceEvento}" target="_blank" class="text-xs text-blue-500 hover:underline">Info +</a>
            </div>
        </div>
    `).join('');
}

/* 
   5. INICIALIZACIÓN
*/
document.addEventListener('DOMContentLoaded', async () => {
    console.log("Iniciando Admin Dashboard con API en Localhost...");
    
    // Ejecutamos las cargas
    await renderUserTable();
    await renderTransactionTable();
    await renderCursosTable();
    await renderBonosList();
    await renderEventosList();
    
    // Inicializar Calendario (Función estética simple)
    initCalendar();
});

// Utilidad simple para pintar el calendario visual (sin datos)
function initCalendar() {
    const calendarDays = document.getElementById('calendarDays');
    if (!calendarDays) return;

    // Pintamos 30 días simulados
    calendarDays.innerHTML = Array.from({ length: 30 }, (_, i) =>
        `<div class="text-center p-2 rounded hover:bg-rose-50 cursor-pointer text-sm">
            <span class="font-bold block">${i + 1}</span>
        </div>`
    ).join('');
}