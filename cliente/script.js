/* script.js */

// ==========================================
// 1. BASE DE DATOS SIMULADA (MOCK DATA)
// ==========================================
const mockData = {
    usuario: {
        nombre: "Juan",
        // Ahora el usuario tiene una lista de bonos disponibles
        bonos: [
            { id: "B-100", nombre: "Bono Mensual Bachata (2 clases restantes)" },
            { id: "B-101", nombre: "Bono 10 Clases Salsa (8 clases restantes)" }
        ]
    },
    reservas: [
        {
            id: "RES-1024",
            clase: "Bachata Intermedio",
            fecha: "2025-03-22",
            hora: "20:30",
            sala: "Sala 2",
            estado: "activa"
        },
        {
            id: "RES-1025",
            clase: "Salsa Cubana Inicio",
            fecha: "2025-03-25",
            hora: "19:00",
            sala: "Sala 1",
            estado: "activa"
        },
        {
            id: "RES-0900",
            clase: "Estilo Chicas",
            fecha: "2025-02-10",
            hora: "18:00",
            sala: "Sala 1",
            estado: "pasada"
        }
    ]
};

// ==========================================
// 2. CONFIGURACIÓN GLOBAL & UTILS
// ==========================================

// Menú Móvil
window.toggleMenu = function() {
    const menu = document.getElementById('mobile-menu');
    if (menu) menu.classList.toggle('hidden');
};

// Dropdown Usuario
window.toggleUserDropdown = function() {
    const dropdown = document.getElementById('user-dropdown');
    const chevron = document.getElementById('user-chevron');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
        if(chevron) chevron.style.transform = dropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
    }
}

// Cerrar dropdown al click fuera
window.addEventListener('click', function(e) {
    const button = e.target.closest('#user-menu-btn');
    const dropdown = document.getElementById('user-dropdown');
    if (!button && dropdown && !dropdown.classList.contains('hidden')) {
        dropdown.classList.add('hidden');
        const chevron = document.getElementById('user-chevron');
        if(chevron) chevron.style.transform = 'rotate(0deg)';
    }
});

// Modales
window.openModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.add('open'), 10);
    }
}

window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('open');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
}

function formatearFechaBonita(fechaString) {
    const opciones = { weekday: 'long', day: 'numeric', month: 'long' };
    const fecha = new Date(fechaString);
    return fecha.toLocaleDateString('es-ES', opciones);
}

// ==========================================
// 3. LÓGICA DE INICIO (DASHBOARD)
// ==========================================
function initDashboard() {
    const nombreEls = document.querySelectorAll('.user-name-display');
    nombreEls.forEach(el => el.innerText = mockData.usuario.nombre);

    // Mostrar el primer bono como estado general (o podrías listar todos)
    const bonoEl = document.getElementById('user-bonus-status');
    if(bonoEl && mockData.usuario.bonos.length > 0) {
        bonoEl.innerText = mockData.usuario.bonos[0].nombre; 
    }

    const hoy = new Date();
    const proximas = mockData.reservas
        .filter(r => new Date(r.fecha) >= hoy && r.estado === 'activa')
        .sort((a, b) => new Date(a.fecha) - new Date(b.fecha));

    const nextClassCard = document.getElementById('next-class-card');
    const noClassMsg = document.getElementById('no-next-class');

    if (proximas.length > 0) {
        const proxima = proximas[0];
        document.getElementById('next-class-title').innerText = proxima.clase;
        document.getElementById('next-class-date').innerText = formatearFechaBonita(proxima.fecha);
        document.getElementById('next-class-time').innerText = proxima.hora + 'h';
        document.getElementById('next-class-room').innerText = proxima.sala;
        
        if(nextClassCard) nextClassCard.classList.remove('hidden');
        if(noClassMsg) noClassMsg.classList.add('hidden');
    } else {
        if(nextClassCard) nextClassCard.classList.add('hidden');
        if(noClassMsg) noClassMsg.classList.remove('hidden');
    }
}

// ==========================================
// 4. LÓGICA DE CALENDARIO Y FORMULARIO RESERVA
// ==========================================
let currentCalendarDate = new Date();
const today = new Date();

function initCalendar() {
    renderCalendar(currentCalendarDate);
    
    // Rellenar desplegable de Bonos si existe
    const bonoSelect = document.getElementById('select-bono');
    if (bonoSelect) {
        // Limpiar opciones por defecto (excepto la primera)
        while (bonoSelect.options.length > 1) {
            bonoSelect.remove(1);
        }
        // Añadir bonos del usuario
        mockData.usuario.bonos.forEach(bono => {
            const option = document.createElement('option');
            option.value = bono.id;
            option.text = bono.nombre;
            bonoSelect.add(option);
        });
    }

    // Botones del calendario
    const prevBtn = document.getElementById('prev-month');
    const nextBtn = document.getElementById('next-month');

    if(prevBtn) {
        prevBtn.addEventListener('click', () => {
            const prevMonthDate = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth() - 1, 1);
            if (prevMonthDate.getMonth() >= today.getMonth() || prevMonthDate.getFullYear() > today.getFullYear()) {
                currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
                renderCalendar(currentCalendarDate);
            }
        });
    }

    if(nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
            renderCalendar(currentCalendarDate);
        });
    }
}

function renderCalendar(date) {
    const month = date.getMonth();
    const year = date.getFullYear();
    const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    
    const titleEl = document.getElementById('calendar-title');
    if(titleEl) titleEl.innerText = `${monthNames[month]} ${year}`;

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

    for (let i = 0; i < startDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'p-3 border-b border-r border-gray-200 bg-gray-50';
        calendarGrid.appendChild(emptyCell);
    }

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
                // Al hacer clic, marcar visualmente y (simulado) habilitar horarios
                const selected = document.querySelector('.calendar-selected-day');
                if(selected) selected.classList.remove('calendar-selected-day', 'bg-rose-200');
                cell.classList.add('calendar-selected-day', 'bg-rose-200');
            };
        }

        const formattedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const hasReservation = mockData.reservas.some(r => r.fecha === formattedDate && r.estado === 'activa');

        if (hasReservation && !isToday) {
            const indicator = document.createElement('div');
            indicator.className = 'absolute bottom-1 right-1 w-2 h-2 bg-rose-500 rounded-full';
            cell.appendChild(indicator);
        }

        calendarGrid.appendChild(cell);
    }
}

// ==========================================
// 5. INICIALIZADOR
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('dashboard-container')) initDashboard();
    if (document.getElementById('calendar-container')) initCalendar();
    
    // Registro 1
    const registerForm = document.getElementById('registerForm');
    if (registerForm) { /* ... lógica registro ... */ } // Mantenla si la usas
});