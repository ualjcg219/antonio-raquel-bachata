/* js/admin.js
   Adaptado para trabajar con api/index.php usando __route (evita dependencias de mod_rewrite).
   Coloca este fichero en: C:\xampp\htdocs\antonio-raquel-bachata\js\admin.js
*/

const API_INDEX = '/antonio-raquel-bachata/api/index.php'; // front controller
const API_TIMEOUT = 15000; // ms

// Helper: fetch con timeout
async function fetchWithTimeout(resource, options = {}) {
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), API_TIMEOUT);
    options.signal = controller.signal;
    try {
        const res = await fetch(resource, options);
        clearTimeout(id);
        return res;
    } catch (err) {
        clearTimeout(id);
        throw err;
    }
}

// Función auxiliar para obtener datos JSON desde el front controller usando __route
async function getData(route) {
    const url = `${API_INDEX}?__route=${encodeURIComponent(route)}`;
    try {
        const res = await fetchWithTimeout(url, { credentials: 'include' });
        const text = await res.text();
        // intentar parsear JSON; si no es JSON, retornar array vacío y loggear
        try {
            const json = text ? JSON.parse(text) : null;
            if (json && json.success === true) return json.data || [];
            console.warn('API responded but not success', route, json);
            return [];
        } catch (e) {
            console.warn('getData: respuesta no JSON para', route, 'raw:', text);
            return [];
        }
    } catch (err) {
        console.error('getData: error de red al solicitar', route, err);
        return [];
    }
}

// Fetchers que usan getData()
async function fetchUsuarios() { return await getData('/api/clientes'); }
async function fetchTransacciones() { return await getData('/api/transacciones'); } // si no existe, devolverá []
async function fetchCursos() { return await getData('/api/cursos'); }
async function fetchBonos() { return await getData('/api/bonos'); }
async function fetchEventos() { return await getData('/api/eventos'); }

/* -------------------------------------
   RENDERIZADO
   ------------------------------------- */

// === USUARIOS ===
async function renderUserTable() {
    const tbody = document.getElementById('user-table-body');
    if (!tbody) return;

    const usuarios = await fetchUsuarios();
    tbody.innerHTML = usuarios.map(user => `
        <tr>
            <td class="font-bold">${user.Nombre ?? user.nombre ?? user.NombreCliente ?? '—'}</td>
            <td>${user.Telefono ?? user.telefono ?? '—'}</td>
            <td>${user.Email ?? user.email ?? '—'}</td>
            <td>${user.DNI ?? user.dni ?? '—'}</td>
            <td class="text-right">
                <button class="text-gray-500 hover:text-rose-600 mr-2">✏</button>
                <button class="text-gray-500 hover:text-rose-600">🗑</button>
            </td>
        </tr>
    `).join('');
}

// === TRANSACCIONES ===
async function renderTransactionTable() {
    const tbody = document.getElementById('transaction-table-body');
    if (!tbody) return;

    const transacciones = await fetchTransacciones();

    tbody.innerHTML = transacciones.map(tx => `
        <tr>
            <td class="font-bold">${tx.cliente_DNI ?? tx.clienteDNI ?? tx.dni ?? '—'}</td>
            <td>${tx.fechaTransaccion ?? tx.fecha ?? '—'}</td>
            <td>${tx.bono_tipo ?? tx.tipo ?? '—'}</td>
            <td>${tx.bono_numDias ?? tx.numDias ?? '—'}</td>
            <td>${tx.importe ?? tx.coste ?? '—'}€</td>
            <td class="text-right">
                <button>✏</button>
                <button>🗑</button>
            </td>
        </tr>
    `).join('');
}

// === CURSOS ===
async function renderCursosTable() {
    const container = document.getElementById('cursos-list-container');
    if (!container) return;

    const cursos = await fetchCursos();

    container.innerHTML = cursos.map(curso => `
        <div class="border rounded p-4 shadow-sm mb-4">
            <h4 class="font-black text-lg">${curso.TipoBaile ?? curso.nombre ?? 'Curso'}</h4>
            <p class="text-sm text-gray-600">${curso.Nivel ?? curso.nivel ?? ''}</p>
            <p class="text-sm">${curso.Descripcion ?? curso.descripcion ?? ''}</p>
        </div>
    `).join('');
}

// === BONOS ===
async function renderBonosList() {
    const container = document.getElementById('bonos-list-container');
    if (!container) return;

    const bonos = await fetchBonos();

    if (!bonos || bonos.length === 0) {
        // fallback visual si no hay datos
        container.innerHTML = `<div class="p-6 text-center text-gray-500">No hay bonos disponibles</div>`;
        return;
    }

    container.innerHTML = bonos.map(bono => {
        const fotoUrl = bono.foto ? (bono.foto.startsWith('http') ? bono.foto : '/antonio-raquel-bachata/' + bono.foto) : 'https://picsum.photos/400/100?grayscale';
        return `
        <div class="bg-black text-white p-4 rounded-lg flex justify-between items-center mb-4 relative overflow-hidden group">
            <img src="${fotoUrl}" class="absolute inset-0 w-full h-full object-cover opacity-30 group-hover:opacity-50 transition-opacity">
            <div class="relative z-10 flex items-center gap-4">
                <img src="${fotoUrl}" class="rounded w-12 h-12 object-cover border-2 border-white">
                <div>
                    <span class="font-bold uppercase text-lg">${bono.tipo ?? bono.Tipo ?? 'Bono'}</span>
                    <div class="text-xs text-gray-200">${bono.descripcion ? (bono.descripcion.substring(0,80) + (bono.descripcion.length>80?'...':'')) : ''}</div>
                </div>
            </div>
            <div class="relative z-10 text-right">
                <p class="text-[10px] font-bold text-rose-500 uppercase">Precio</p>
                <p class="font-black text-xl">${bono.precio ?? bono.Precio ?? '—'}</p>
                <p class="text-xs text-gray-300">${bono.numDias ?? bono.NumDias ?? ''} días</p>
            </div>
        </div>
        `;
    }).join('');
}

// === EVENTOS ===
async function renderEventosList() {
    const container = document.getElementById('eventos-list-container');
    if (!container) return;

    const eventos = await fetchEventos();

    if (!eventos || eventos.length === 0) {
        container.innerHTML = `<div class="p-6 text-center text-gray-500">No hay eventos</div>`;
        return;
    }

    container.innerHTML = eventos.map(evento => `
        <div class="bg-black text-white p-4 rounded-lg flex justify-between mb-4">
            <div class="flex items-center gap-4">
                <img src="${evento.URLFoto ?? evento.urlFoto ?? 'https://picsum.photos/100/80'}" class="rounded w-16 h-12 object-cover">
                <span class="font-bold uppercase">${evento.TítuloEvento ?? evento.titulo ?? 'Evento'}</span>
            </div>
            <p>${evento.FechaEvento ?? evento.fecha ?? ''}</p>
        </div>
    `).join('');
}

/* -------------------------------------
   INICIALIZACIÓN AL CARGAR LA PÁGINA
   ------------------------------------- */

document.addEventListener('DOMContentLoaded', async () => {
    await renderUserTable();
    await renderTransactionTable();
    await renderCursosTable();
    await renderBonosList();
    await renderEventosList();
});

/* -------------------------------------
   UTILIDADES
   ------------------------------------- */

function initCalendar() {
    const calendarDays = document.getElementById('calendarDays');
    if (!calendarDays) return;
    const daysInMonth = 31;
    calendarDays.innerHTML = Array.from({ length: daysInMonth }, (_, i) =>
        `<button class="px-3 py-2 rounded hover:bg-gray-100">${i + 1}</button>`
    ).join('');
}
initCalendar();