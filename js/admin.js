/* js/admin.js */

// Datos simulados para tablas
const adminData = {
    usuarios: [
        { id: 1, nombre: "Antonio Castillo", telefono: "600123456", email: "antonio@mail.com" },
        { id: 2, nombre: "Raquel Lopez", telefono: "666777888", email: "raquel@mail.com" },
        { id: 3, nombre: "Juan Pérez", telefono: "611223344", email: "juan@mail.com" }
    ],
    cursos: [
        { nombre: "Bachata Inicio", horario: "L-X 21:00", nivel: "Principiante" },
        { nombre: "Salsa Cubana", horario: "M-J 20:00", nivel: "Medio" }
    ],
    transacciones: [
        { nombre: "Antonio Castillo", fecha: "21/06/2025", tipo: "Clase Individual", dias: 1, coste: 8 },
        { nombre: "Raquel Lopez", fecha: "22/06/2025", tipo: "Bono Mensual", dias: 30, coste: 35 }
    ]
};

// Función para rellenar la tabla de usuarios (admin-usuarios-lista.html)
function renderUserTable() {
    const tbody = document.getElementById('user-table-body');
    if (!tbody) return;

    tbody.innerHTML = adminData.usuarios.map(user => `
        <tr>
            <td class="font-bold">${user.nombre}</td>
            <td>${user.telefono}</td>
            <td>${user.email}</td>
            <td class="text-right">
                <button class="text-gray-500 hover:text-rose-600 mr-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></button>
                <button class="text-gray-500 hover:text-rose-600"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
            </td>
        </tr>
    `).join('');
}

// Función para rellenar la tabla de transacciones (admin-transacciones.html)
function renderTransactionTable() {
    const tbody = document.getElementById('transaction-table-body');
    if (!tbody) return;

    tbody.innerHTML = adminData.transacciones.map(tx => `
        <tr>
            <td class="font-bold">${tx.nombre}</td>
            <td>${tx.fecha}</td>
            <td>${tx.tipo}</td>
            <td>${tx.dias}</td>
            <td>${tx.coste}€</td>
            <td class="text-right">
                <button class="text-gray-500 hover:text-rose-600 mr-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></button>
                <button class="text-gray-500 hover:text-rose-600"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
            </td>
        </tr>
    `).join('');
}

// Inicializador
document.addEventListener('DOMContentLoaded', function() {
    renderUserTable();
    renderTransactionTable();
});

/* js/admin.js (Actualización: añade esto al contenido existente) */

// ... (Mantén los datos de adminData anteriores: usuarios y transacciones) ...

// AÑADIR ESTOS DATOS AL OBJETO adminData:
adminData.bonos = [
    { nombre: "5 Clases a la Semana", precio: "70€/Mes", tipo: "Semanal", dias: 5 },
    { nombre: "3 Clases a la Semana", precio: "50€/Mes", tipo: "Semanal", dias: 3 },
    { nombre: "2 Clases a la Semana", precio: "35€/Mes", tipo: "Semanal", dias: 2 }
];

adminData.eventos = [
    { titulo: "Noche de Salsa", fecha: "04/10/2025", imagen: "https://picsum.photos/100/100?random=1" },
    { titulo: "Bachata Lovers Night", fecha: "10/10/2025", imagen: "https://picsum.photos/100/100?random=2" },
    { titulo: "Bachata Festival", fecha: "25/10/2025", imagen: "https://picsum.photos/100/100?random=3" }
];

adminData.listaCursos = [
    { nombre: "Bachata Medio-Avanzado", horario: "Martes y Jueves 21:00-22:00", nivel: "Avanzado" },
    { nombre: "Bachata Inicio-Medio", horario: "Martes y Jueves 20:00-21:00", nivel: "Medio" },
    { nombre: "Bachata Principiante", horario: "Lunes y Miércoles 21:00-22:00", nivel: "Inicio" }
];

// --- FUNCIONES DE RENDERIZADO NUEVAS ---

function renderCursosTable() {
    const container = document.getElementById('cursos-list-container');
    if (!container) return;

    container.innerHTML = adminData.listaCursos.map(curso => `
        <div class="border border-gray-200 rounded p-4 mb-4 flex flex-col md:flex-row justify-between items-center bg-white shadow-sm">
            <div class="mb-4 md:mb-0">
                <h4 class="font-black text-lg text-gray-800 uppercase">${curso.nombre}</h4>
                <p class="text-xs font-bold text-rose-600 uppercase mb-1">Horario</p>
                <p class="text-sm text-gray-600">${curso.horario}</p>
            </div>
            <div class="flex gap-2">
                <a href="admin-cursos-editar.html" class="p-2 border border-gray-300 rounded hover:bg-gray-100"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a>
                <button class="p-2 border border-red-200 text-red-500 rounded hover:bg-red-50"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
            </div>
        </div>
    `).join('');
}

function renderBonosList() {
    const container = document.getElementById('bonos-list-container');
    if (!container) return;

    container.innerHTML = adminData.bonos.map(bono => `
        <div class="bg-black text-white p-4 rounded-lg flex justify-between items-center mb-4 relative overflow-hidden group">
            <img src="https://picsum.photos/400/100?grayscale" class="absolute inset-0 w-full h-full object-cover opacity-30 group-hover:opacity-50 transition-opacity">
            <div class="relative z-10 flex items-center gap-4">
                <img src="https://picsum.photos/50/50" class="rounded w-12 h-12 object-cover border-2 border-white">
                <span class="font-bold uppercase text-lg">${bono.nombre}</span>
            </div>
            <div class="relative z-10 text-right">
                <p class="text-[10px] font-bold text-rose-500 uppercase">Precio</p>
                <p class="font-black text-xl">${bono.precio}</p>
            </div>
        </div>
    `).join('');
}

function renderEventosList() {
    const container = document.getElementById('eventos-list-container');
    if (!container) return;

    container.innerHTML = adminData.eventos.map(evento => `
        <div class="bg-black text-white p-4 rounded-lg flex justify-between items-center mb-4 relative overflow-hidden">
            <div class="flex items-center gap-4 relative z-10">
                <img src="${evento.imagen}" class="rounded w-16 h-12 object-cover border border-gray-600">
                <span class="font-bold uppercase text-lg">${evento.titulo}</span>
            </div>
            <div class="text-right relative z-10">
                <p class="text-[10px] font-bold text-rose-500 uppercase">Fecha</p>
                <p class="font-bold text-sm">${evento.fecha}</p>
            </div>
        </div>
    `).join('');
}

// ACTUALIZAR EL LISTENER AL FINAL DEL ARCHIVO:
document.addEventListener('DOMContentLoaded', function() {
    renderUserTable();
    renderTransactionTable();
    renderCursosTable();
    renderBonosList();
    renderEventosList();
});