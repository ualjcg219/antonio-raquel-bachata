/* js/client.js - CONECTADO A BASE DE DATOS */

// ==========================================
// 0. CONFIGURACIÓN API
// ==========================================
const API_BASE = "admin/api"; 
// SIMULAMOS QUE EL USUARIO LOGUEADO ES JUAN PÉREZ (DNI de tu script SQL)
const CURRENT_USER_DNI = "12345678A"; 

// ==========================================
// 1. HELPERS PARA LA API (Igual que en admin.js)
// ==========================================

async function getData(resource) {
    try {
        const url = `${API_BASE}/${resource}`;
        const res = await fetch(url);
        
        if (!res.ok) return [];

        const json = await res.json();
        
        if (json.success === true) return json.data || [];
        if (Array.isArray(json)) return json;
        return [];
    } catch (error) {
        console.error(`Error al cargar ${resource}:`, error);
        return [];
    }
}

// Funciones específicas de carga
async function fetchMisDatos() {
    // Pedimos todos los clientes y buscamos el nuestro
    // (En una app real, la API tendría un endpoint /perfil que usa la sesión)
    const clientes = await getData("clientes");
    return clientes.find(c => c.DNI === CURRENT_USER_DNI);
}

async function fetchMisBonos() {
    const bonosComprados = await getData("bonos_comprados"); // Necesitas crear este endpoint o usar lógica similar
    // Si no tienes el endpoint específico, puedes filtrar aquí si traes todos (aunque no es seguro en producción)
    // Asumimos que la API devuelve una lista donde está el campo cliente_DNI
    return bonosComprados.filter(b => b.cliente_DNI === CURRENT_USER_DNI && b.SaldoClases > 0);
}

async function fetchMisReservas() {
    // Para mostrar las reservas bonitas, necesitamos cruzar datos:
    // Reservas + Clases + Cursos (para saber el nombre del baile)
    
    const [reservas, clases] = await Promise.all([
        getData("reservas"), // Asumimos que devuelve todas
        getData("clases")
    ]);

    // Filtramos reservas de este usuario (esto se debería hacer en PHP idealmente)
    // NOTA: Tu tabla 'reserva' tiene 'idBonoComprado', necesitamos saber de quién es ese bono.
    // Para simplificar, asumiremos que traemos las reservas unidas o filtramos por lógica de bono.
    // ESTRATEGIA JS: Traer mis bonos, obtener sus IDs, y filtrar reservas que usen esos IDs.
    
    const misBonos = await getData("bonos_comprados");
    const misBonosIds = misBonos
        .filter(b => b.cliente_DNI === CURRENT_USER_DNI)
        .map(b => b.idBonoComprado);

    const misReservasRaw = reservas.filter(r => misBonosIds.includes(r.idBonoComprado));

    // Formateamos los datos para que el HTML los entienda
    return misReservasRaw.map(reserva => {
        const claseInfo = clases.find(c => c.idClase == reserva.idClase);
        
        if (!claseInfo) return null; // Si la clase no existe

        // Convertir fechas MySQL (YYYY-MM-DD HH:MM:SS) a formato JS
        const fechaInicio = new Date(claseInfo.fechaInicio);
        
        // Determinar estado
        const hoy = new Date();
        const estado = fechaInicio < hoy ? 'pasada' : 'activa';

        return {
            id: reserva.idReserva,
            clase: `${claseInfo.baile} ${claseInfo.nivel}`, // Ej: Salsa Iniciación
            fecha: fechaInicio.toISOString().split('T')[0], // YYYY-MM-DD
            hora: fechaInicio.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            sala: "Sala Principal", // Dato hardcodeado o añadir a BD
            estado: estado
        };
    }).filter(r => r !== null); // Eliminar nulos
}

// ==========================================
// 2. UTILIDADES DE CLIENTE
// ==========================================

function formatearFechaBonita(fechaString) {
    if(!fechaString) return "";
    const opciones = { weekday: 'long', day: 'numeric', month: 'long' };
    const fecha = new Date(fechaString);
    return fecha.toLocaleDateString('es-ES', opciones);
}

// ==========================================
// 3. LÓGICA DEL DASHBOARD (INICIO)
// ==========================================
async function initDashboard() {
    // 1. Cargar Usuario
    const usuario = await fetchMisDatos();
    const nombreEls = document.querySelectorAll('.user-name-display');
    if (usuario) {
        nombreEls.forEach(el => el.innerText = usuario.Nombre);
    }

    // 2. Cargar Bonos Activos
    const misBonos = await fetchMisBonos();
    const bonoEl = document.getElementById('user-bonus-status');
    
    if(bonoEl) {
        if(misBonos.length > 0) {
            // Mostramos el primero y sus clases restantes
            const bono = misBonos[0];
            bonoEl.innerText = `${bono.bono_tipo} (${bono.SaldoClases} clases restantes)`;
        } else {
            bonoEl.innerText = "No tienes bonos activos";
        }
    }

    // 3. Calcular próxima clase
    const misReservas = await fetchMisReservas();
    
    // Filtramos las futuras
    const hoy = new Date();
    // Ponemos hora 0 para comparar fechas puras si es necesario, pero aquí usaremos timestamp
    
    const proximas = misReservas
        .filter(r => r.estado === 'activa')
        .sort((a, b) => new Date(a.fecha + 'T' + a.hora) - new Date(b.fecha + 'T' + b.hora));

    const nextClassCard = document.getElementById('next-class-card');
    const noClassMsg = document.getElementById('no-next-class');

    if (proximas.length > 0) {
        const proxima = proximas[0];
        const titleEl = document.getElementById('next-class-title');
        const dateEl = document.getElementById('next-class-date');
        const timeEl = document.getElementById('next-class-time');
        const roomEl = document.getElementById('next-class-room');

        if(titleEl) titleEl.innerText = proxima.clase;
        if(dateEl) dateEl.innerText = formatearFechaBonita(proxima.fecha);
        if(timeEl) timeEl.innerText = proxima.hora + 'h';
        if(roomEl) roomEl.innerText = proxima.sala;
        
        if(nextClassCard) nextClassCard.classList.remove('hidden');
        if(noClassMsg) noClassMsg.classList.add('hidden');
    } else {
        if(nextClassCard) nextClassCard.classList.add('hidden');
        if(noClassMsg) noClassMsg.classList.remove('hidden');
    }
}

// ==========================================
// 4. LÓGICA DEL CALENDARIO
// ==========================================
let currentCalendarDate = new Date();
const today = new Date();

async function initCalendar() {
    // Necesitamos las reservas para pintar los puntitos en el calendario
    const misReservas = await fetchMisReservas();
    
    // Renderizamos el calendario pasando las reservas reales
    renderCalendar(currentCalendarDate, misReservas);
    
    // Rellenar desplegable Bonos
    const bonoSelect = document.getElementById('select-bono');
    if (bonoSelect) {
        const misBonos = await fetchMisBonos();
        
        // Limpiar opciones previas
        while (bonoSelect.options.length > 1) {
            bonoSelect.remove(1);
        }
        
        misBonos.forEach(bono => {
            const option = document.createElement('option');
            option.value = bono.idBonoComprado; // Usamos el ID real de la BD
            option.text = `${bono.bono_tipo} - ${bono.SaldoClases} clases`;
            bonoSelect.add(option);
        });
    }

    // Botones Mes Anterior / Siguiente
    const prevBtn = document.getElementById('prev-month');
    const nextBtn = document.getElementById('next-month');

    if(prevBtn) {
        prevBtn.addEventListener('click', () => {
            const prevMonthDate = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth() - 1, 1);
            if (prevMonthDate.getMonth() >= today.getMonth() || prevMonthDate.getFullYear() > today.getFullYear()) {
                currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
                renderCalendar(currentCalendarDate, misReservas);
            }
        });
    }

    if(nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
            renderCalendar(currentCalendarDate, misReservas);
        });
    }
}

function renderCalendar(date, reservas = []) {
    const month = date.getMonth();
    const year = date.getFullYear();
    const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    
    const titleEl = document.getElementById('calendar-title');
    if(titleEl) titleEl.innerText = `${monthNames[month]} ${year}`;

    // Deshabilitar botón "Anterior"
    const btnPrev = document.getElementById('prev-month');
    if(btnPrev) {
        if (month === today.getMonth() && year === today.getFullYear()) {
            btnPrev.classList.add('opacity-50', 'cursor-not-allowed');
            btnPrev.disabled = true;
        } else {
            btnPrev.classList.remove('opacity-50', 'cursor-not-allowed');
            btnPrev.disabled = false;
        }
    }

    const calendarGrid = document.getElementById('calendar-grid-days');
    if(!calendarGrid) return;
    
    calendarGrid.innerHTML = '';

    const firstDay = new Date(year, month, 1).getDay();
    const startDay = firstDay === 0 ? 6 : firstDay - 1; 
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    // Celdas vacías
    for (let i = 0; i < startDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'p-3 border-b border-r border-gray-200 bg-gray-50';
        calendarGrid.appendChild(emptyCell);
    }

    // Días del mes
    for (let day = 1; day <= daysInMonth; day++) {
        const cell = document.createElement('div');
        cell.className = 'p-3 text-sm font-medium border-b border-r border-gray-200 relative h-16 sm:h-auto flex items-center justify-center';
        
        const isToday = day === today.getDate() && month === today.getMonth() && year === today.getFullYear();
        const cellDate = new Date(year, month, day);
        const isPast = cellDate < new Date(today.getFullYear(), today.getMonth(), today.getDate());

        if (isToday) {
            cell.classList.add('bg-rose-600', 'text-white', 'font-black');
            cell.innerHTML = `${day} <span class="absolute top-1 right-1 w-2 h-2 bg-yellow-400 rounded-full"></span>`;
        } else if (isPast) {
            cell.classList.add('text-gray-300', 'bg-gray-50');
            cell.innerText = day;
        } else {
            cell.classList.add('hover:bg-rose-50', 'cursor-pointer', 'transition-colors');
            cell.innerText = day;
            cell.onclick = function() {
                const selected = document.querySelector('.calendar-selected-day');
                if(selected) selected.classList.remove('calendar-selected-day', 'bg-rose-200');
                cell.classList.add('calendar-selected-day', 'bg-rose-200');
                
                // Aquí podrías llamar a una función para mostrar las clases disponibles ESE día
                // showClassesForDate(year, month, day);
            };
        }

        // Marcar días con reserva activa usando los datos reales
        const formattedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        
        const hasReservation = reservas.some(r => r.fecha === formattedDate && r.estado === 'activa');

        if (hasReservation && !isToday) {
            const indicator = document.createElement('div');
            indicator.className = 'absolute bottom-1 right-1 w-2 h-2 bg-rose-500 rounded-full';
            cell.appendChild(indicator);
        }

        calendarGrid.appendChild(cell);
    }
}

// ==========================================
// 5. INICIALIZADOR DE CLIENTE
// ==========================================
document.addEventListener('DOMContentLoaded', async function() {
    
    // Detectar página Dashboard
    if (document.getElementById('dashboard-container')) {
        await initDashboard();
    }

    // Detectar página Calendario
    if (document.getElementById('calendar-container')) {
        await initCalendar();
    }
    
    // Perfil
    const profileNameInput = document.querySelector('input[name="nombre"]'); // Asegúrate que el name coincida
    if(profileNameInput) {
        const usuario = await fetchMisDatos();
        if(usuario) {
            profileNameInput.value = usuario.Nombre;
            // Rellenar resto del form...
        }
    }
});