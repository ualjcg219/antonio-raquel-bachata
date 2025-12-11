/* ============================================
   CATÁLOGO – (js/catalog.js)
   Muestra productos desde la BD y gestiona el carrito
   ============================================ */

// 1. CONFIGURACIÓN API
const API_BASE = "/api"; // Asumimos que el HTML está en la raíz

// 2. HELPER PARA PEDIR DATOS (Igual que en los otros archivos)
async function getData(resource) {
    try {
        const res = await fetch(`${API_BASE}/${resource}`);
        if (!res.ok) return [];
        const json = await res.json();
        return json.success ? json.data : (Array.isArray(json) ? json : []);
    } catch (error) {
        console.error("Error cargando datos:", error);
        return [];
    }
}

// 3. INICIALIZACIÓN
document.addEventListener('DOMContentLoaded', async () => {
    updateBadge(); // Actualizar contador al entrar
    await renderBonos(); // Cargar productos de la BD
    await renderCursos(); // Cargar cursos (informativo)
});

// ==========================================
// 4. RENDERIZADO DE PRODUCTOS (Desde BD)
// ==========================================

async function renderBonos() {
    const container = document.getElementById('bonos-container'); 
    // ASEGÚRATE DE TENER <div id="bonos-container"></div> EN TU HTML
    
    if (!container) return;

    const bonos = await getData('bonos'); // Pide a admin/api/bonos.php

    if (bonos.length === 0) {
        container.innerHTML = '<p class="text-center w-full col-span-3">No hay bonos disponibles.</p>';
        return;
    }

    container.innerHTML = bonos.map(bono => {
        // Truco: Como el ID en la BD es el 'tipo' (string), lo pasamos entre comillas simples
        // Usamos encodeURIComponent para evitar errores si hay espacios o caracteres raros
        const safeId = bono.tipo; 
        
        return `
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col">
            <div class="h-48 overflow-hidden bg-gray-200">
                <img src="${bono.foto || 'img/bono-default.jpg'}" alt="${bono.tipo}" class="w-full h-full object-cover transform hover:scale-105 transition-transform duration-500">
            </div>
            <div class="p-6 flex flex-col flex-grow">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-xl font-bold uppercase text-gray-800">${bono.tipo}</h3>
                    <span class="bg-rose-100 text-rose-800 text-xs font-bold px-2 py-1 rounded">${bono.numDias} Clases</span>
                </div>
                <p class="text-gray-600 text-sm mb-4 flex-grow">${bono.descripcion}</p>
                <div class="flex justify-between items-center mt-4">
                    <span class="text-3xl font-black text-rose-600">${bono.precio}€</span>
                    
                    <!-- BOTÓN DE AÑADIR AL CARRITO -->
                    <!-- Pasamos los datos de la BD a la función JS -->
                    <button onclick="addToCart('${safeId}', '${bono.tipo}', ${bono.precio}, '${bono.descripcion}')" 
                            class="bg-black text-white px-4 py-2 rounded-full hover:bg-rose-600 transition-colors flex items-center gap-2">
                        <span>Añadir</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </button>
                </div>
            </div>
        </div>
    `}).join('');
}

async function renderCursos() {
    // Esto es opcional, por si quieres mostrar la lista de cursos disponibles
    const container = document.getElementById('courses-container');
    if (!container) return;

    const cursos = await getData('cursos');
    
    container.innerHTML = cursos.map(curso => `
        <div class="card bg-white shadow rounded p-4">
            <img src="${curso.Foto}" class="w-full h-40 object-cover rounded mb-4">
            <h3 class="font-bold">${curso.TipoBaile} - ${curso.Nivel}</h3>
            <p class="text-sm text-gray-500">${curso.Descripcion}</p>
        </div>
    `).join('');
}

// ==========================================
// 5. LÓGICA DEL CARRITO (Tu código adaptado)
// ==========================================

function addToCart(id, name, price, description) {
    console.log("Intentando añadir:", name, "ID:", id);

    let cart = JSON.parse(localStorage.getItem('myCart')) || [];

    // --- CAMBIO IMPORTANTE ---
    // Ya no usamos parseInt(id) porque tus bonos tienen IDs de texto (ej: "Mensual")
    // Comparamos directamente.
    const existingProduct = cart.find(item => item.id == id);

    if (existingProduct) {
        existingProduct.quantity += 1;
        console.log("Producto existente. +1 Cantidad.");
    } else {
        cart.push({
            id: id, // Guardamos el ID tal cual viene (texto o número)
            name: name,
            price: parseFloat(price),
            description: description,
            quantity: 1
        });
        console.log("Producto nuevo añadido.");
    }

    localStorage.setItem('myCart', JSON.stringify(cart));
    updateBadge();
    
    // Usamos showAlert si existe, si no, alert normal
    if (typeof showAlert === 'function') {
        showAlert('¡Producto Añadido!', `${name} añadido al carrito.`, 'alert');
    } else {
        alert(`${name} añadido al carrito.`);
    }
}

function updateBadge() {
    const badge = document.getElementById('cart-count-badge');
    if (badge) {
        const cart = JSON.parse(localStorage.getItem('myCart')) || [];
        const totalItems = cart.reduce((acc, item) => acc + item.quantity, 0);
        badge.innerText = totalItems;
        badge.style.display = totalItems === 0 ? 'none' : 'flex';
    }
}