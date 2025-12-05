/* ============================================
   ADMIN DASHBOARD – JAVASCRIPT COMPLETO
   Conexión a API en XAMPP
   ============================================ */

/* ============================================
   ADMIN DASHBOARD – JAVASCRIPT CORREGIDO
   ============================================ */

// Asegúrate de que esta ruta base es correcta

const API_BASE = "http://localhost/antonio-raquel-bachata/api";

// Función auxiliar para manejar la respuesta
async function getData(url) {
    try {
        const res = await fetch(url);
        const json = await res.json();
        
        // Si la respuesta es success, devolvemos SOLO el array 'data'
        // Si 'data' viene vacío o null, devolvemos un array vacío [] para que no falle el .map
        if (json.success === true) {
            return json.data || [];
        } else {
            console.error("Error API:", json.message);
            return [];
        }
    } catch (error) {
        console.error("Error de red:", error);
        return [];
    }
}

// === TUS FUNCIONES ACTUALIZADAS ===

async function fetchUsuarios() {
    // Usamos la ruta nueva '/clientes' y extraemos .data
    return await getData(`${API_BASE}/clientes`);
}

async function fetchTransacciones() {
    return await getData(`${API_BASE}/transacciones`);
}

async function fetchCursos() {
    return await getData(`${API_BASE}/cursos`);
}

async function fetchBonos() {
    return await getData(`${API_BASE}/bonos`);
}

async function fetchEventos() {
    return await getData(`${API_BASE}/eventos`);
}
/* -------------------------------------
   2. RENDERIZAR TABLAS Y LISTADOS
   ------------------------------------- */

// === USUARIOS ===
async function renderUserTable() {
    const tbody = document.getElementById('user-table-body');
    if (!tbody) return;

    const usuarios = await fetchUsuarios();

    tbody.innerHTML = usuarios.map(user => `
        <tr>
            <td class="font-bold">${user.Nombre}</td>
            <td>${user.Telefono}</td>
            <td>${user.Email}</td>
            <td>${user.DNI ?? '—'}</td>
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
            <td class="font-bold">${tx.cliente_DNI}</td>
            <td>${tx.fechaTransaccion}</td>
            <td>${tx.bono_tipo}</td>
            <td>${tx.bono_numDias}</td>
            <td>${tx.importe}€</td>
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
        <div class="border rounded p-4 shadow-sm">
            <h4 class="font-black text-lg">${curso.TipoBaile}</h4>
            <p>${curso.Nivel}</p>
            <p>${curso.Descripcion}</p>
        </div>
    `).join('');
}

// === BONOS ===
async function renderBonosList() {
    const container = document.getElementById('bonos-list-container');
    if (!container) return;

    const bonos = await fetchBonos();

    container.innerHTML = bonos.map(bono => `
        <div class="bg-black text-white p-4 rounded-lg mb-4">
            <span class="font-bold uppercase">${bono.tipo}</span>
            <p class="font-black text-xl">${bono.precio} €</p>
        </div>
    `).join('');
}

// === EVENTOS ===
async function renderEventosList() {
    const container = document.getElementById('eventos-list-container');
    if (!container) return;

    const eventos = await fetchEventos();

    container.innerHTML = eventos.map(evento => `
        <div class="bg-black text-white p-4 rounded-lg flex justify-between mb-4">
            <div class="flex items-center gap-4">
                <img src="${evento.URLFoto}" class="rounded w-16 h-12">
                <span class="font-bold uppercase">${evento.TítuloEvento}</span>
            </div>
            <p>${evento.FechaEvento}</p>
        </div>
    `).join('');
}

/* -------------------------------------
   3. INICIALIZACIÓN AL CARGAR LA PÁGINA
   ------------------------------------- */

document.addEventListener('DOMContentLoaded', async () => {
    await renderUserTable();
    await renderTransactionTable();
    await renderCursosTable();
    await renderBonosList();
    await renderEventosList();
});

/* -------------------------------------
   4. UTILIDADES GENERALES DEL PANEL
   ------------------------------------- */

// Calendario simple
function initCalendar() {
    const calendarDays = document.getElementById('calendarDays');
    if (!calendarDays) return;

    const daysInMonth = 31;
    calendarDays.innerHTML = Array.from({ length: daysInMonth }, (_, i) =>
        `<button class="px-3 py-2 rounded hover:bg-gray-100">${i + 1}</button>`
    ).join('');
}
initCalendar();
