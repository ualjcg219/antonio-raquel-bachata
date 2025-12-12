// js/catalog.js
// Version adaptada: carga bonos desde la API (/api/index.php?__route=/api/bonos),
// renderiza la cuadrícula y mantiene la lógica de carrito que ya tenías (localStorage 'myCart').

/* ========== Config ========== */
const API_INDEX = '/antonio-raquel-bachata/api/index.php';
const API_TIMEOUT = 15000; // ms

/* ========== Utilidades de red ========== */
async function fetchWithTimeout(url, opts = {}) {
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), API_TIMEOUT);
    opts.signal = controller.signal;
    try {
        const res = await fetch(url, opts);
        clearTimeout(id);
        return res;
    } catch (err) {
        clearTimeout(id);
        throw err;
    }
}

async function fetchBonosFromApi() {
    const url = `${API_INDEX}?__route=${encodeURIComponent('/api/bonos')}`;
    try {
        const res = await fetchWithTimeout(url, { credentials: 'include' });
        const text = await res.text();
        try {
            const json = text ? JSON.parse(text) : null;
            if (json && json.success === true) return json.data || [];
            console.warn('fetchBonosFromApi: API responded but not success', json);
            return [];
        } catch (e) {
            console.warn('fetchBonosFromApi: response not JSON', text);
            return [];
        }
    } catch (err) {
        console.error('fetchBonosFromApi: network error', err);
        return [];
    }
}

/* ========== Utilidades UI / Formato ========== */
function formatPrice(p) {
    if (p === null || p === undefined || p === '') return '—';
    const n = parseFloat(String(p).replace(',', '.'));
    if (isNaN(n)) return p;
    // Mostrar sin decimales si entero, o con 2 decimales
    return Number.isInteger(n) ? `${n}` : n.toFixed(2);
}

/* Pequeña notificación visual (si no existe función global showAlert) */
function showAlert(title, message, type = 'info', timeout = 2500) {
    // Evitar duplicados si ya existe
    if (document.getElementById('site-toast')) {
        const t = document.getElementById('site-toast');
        t.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'site-toast';
    toast.className = 'fixed bottom-6 right-6 z-50 max-w-xs p-4 rounded shadow-lg';
    toast.style.background = type === 'alert' ? '#ec4899' : (type === 'error' ? '#ef4444' : '#111827');
    toast.style.color = 'white';
    toast.innerHTML = `<strong class="block font-bold mb-1">${title}</strong><div class="text-sm">${message}</div>`;

    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity 300ms ease';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 350);
    }, timeout);
}

/* ========== Carrito (mantener tu API localStorage 'myCart') ========== */
function updateBadge() {
    const badge = document.getElementById('cart-count-badge');
    if (badge) {
        const cart = JSON.parse(localStorage.getItem('myCart')) || [];
        const totalItems = cart.reduce((acc, item) => acc + (item.quantity || 0), 0);
        badge.innerText = totalItems;
        badge.style.display = totalItems === 0 ? 'none' : 'flex';
    }
}

function addToCart(id, name, price, description) {
    // Mantener tu comportamiento existente pero robusto a tipos
    console.log("Click recibido. Intentando añadir:", name);

    let cart = JSON.parse(localStorage.getItem('myCart')) || [];

    // Asegurar id numérico (si no es numérico, lo convertimos con hash simple)
    let numericId = parseInt(id);
    if (isNaN(numericId)) {
        // fallback: generar hash pequeño y positivo
        numericId = Math.abs(Array.from(String(id)).reduce((s, ch) => (s * 31 + ch.charCodeAt(0)) | 0, 0));
    }

    const existingProduct = cart.find(item => item.id === numericId);

    if (existingProduct) {
        existingProduct.quantity = (existingProduct.quantity || 0) + 1;
        console.log("Producto existente. Cantidad actualizada.");
    } else {
        cart.push({
            id: numericId,
            name: name,
            price: parseFloat(price) || 0,
            description: description,
            quantity: 1
        });
        console.log("Producto nuevo añadido.");
    }

    localStorage.setItem('myCart', JSON.stringify(cart));
    updateBadge();

    showAlert(
        '¡Producto Añadido!',
        `${name} se ha añadido correctamente a tu carrito de compra.`,
        'alert'
    );
}

/* ========== Renderizado de bonos dinamicamente ========== */
function buildBonoCard(bono, dynId) {
    const fotoUrl = bono.foto
        ? (String(bono.foto).startsWith('http') ? bono.foto : '/antonio-raquel-bachata/' + bono.foto)
        : 'https://picsum.photos/400/100?grayscale';

    const priceText = formatPrice(bono.precio) + (bono.precio ? '€' : '');

    const desc = bono.descripcion ? String(bono.descripcion) : '';

    // dynId será un número (ej: 1001, 1002...) para evitar choque con IDs estáticos
    return `
    <div class="bg-gray-100 rounded-lg p-8 flex flex-col items-center text-center hover:bg-white hover:shadow-xl transition-all duration-300 border border-transparent hover:border-gray-200">
        <h3 class="text-gray-600 font-bold uppercase tracking-wide mb-2">${(bono.tipo ?? 'Bono')}</h3>
        <div class="text-5xl font-black text-gray-900 mb-4">${priceText}</div>
        <p class="text-xs text-gray-500 mb-6 uppercase">${desc}</p>
        <button data-bono-id="${dynId}" data-bono-name="${escapeHtml(bono.tipo ?? 'Bono')}" data-bono-price="${bono.precio ?? 0}" data-bono-desc="${escapeHtml(desc)}"
            class="btn-add-to-cart w-full bg-gray-900 text-white py-3 px-4 rounded text-xs font-bold uppercase hover:bg-rose-600 transition-colors flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            Añadir al carrito
        </button>
    </div>
    `;
}

/* escapado simple para atributos */
function escapeHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

async function renderBonosGrid() {
    updateBadge();
    const grid = document.getElementById('bonos-grid');
    if (!grid) return;

    grid.innerHTML = '<div class="col-span-full text-center p-8 text-gray-500">Cargando bonos…</div>';

    const bonos = await fetchBonosFromApi();

    if (!bonos || bonos.length === 0) {
        grid.innerHTML = '<div class="col-span-full text-center p-8 text-gray-500">No hay bonos disponibles</div>';
        return;
    }

    // Generar tarjetas con IDs dinámicos a partir de un offset (para no chocar con IDs estáticos)
    const ID_OFFSET = 1000;
    const cardsHtml = bonos.map((b, idx) => buildBonoCard(b, ID_OFFSET + idx + 1)).join('');
    grid.innerHTML = cardsHtml;

    // Añadir listeners a botones
    grid.querySelectorAll('.btn-add-to-cart').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const el = e.currentTarget;
            const id = el.getAttribute('data-bono-id');
            const name = el.getAttribute('data-bono-name') || 'Bono';
            const price = el.getAttribute('data-bono-price') || 0;
            const desc = el.getAttribute('data-bono-desc') || '';
            addToCart(id, name, price, desc);
        });
    });
}

/* ========== Inicialización ========== */
document.addEventListener('DOMContentLoaded', () => {
    // Si ya existía tu updateBadge en otro script, esta llamada es segura
    updateBadge();
    renderBonosGrid();
});