document.addEventListener('DOMContentLoaded', () => {
    renderCart();
});

// Función principal para dibujar el carrito
function renderCart() {
    const cartContainer = document.getElementById('cart-container');
    const emptyMsg = document.getElementById('empty-msg');
    const cartFooter = document.getElementById('cart-footer');
    const grandTotalEl = document.getElementById('grand-total');

    // 1. Obtener datos
    let cart = JSON.parse(localStorage.getItem('myCart')) || [];

    // 2. Control de estado vacío
    if (cart.length === 0) {
        if (cartContainer) cartContainer.innerHTML = '';
        if (cartContainer) cartContainer.classList.add('hidden');
        if (cartFooter) cartFooter.classList.add('hidden');
        if (emptyMsg) emptyMsg.classList.remove('hidden');
        updateBadge();
        return;
    }

    // 3. Preparar interfaz para items
    if (emptyMsg) emptyMsg.classList.add('hidden');
    if (cartContainer) cartContainer.classList.remove('hidden');
    if (cartFooter) cartFooter.classList.remove('hidden');
    if (cartContainer) cartContainer.innerHTML = '';

    let totalAmount = 0;

    // 4. Bucle para generar items
    cart.forEach(item => {
        // --- AQUÍ ESTÁ LA SOLUCIÓN AL NaN ---
        // Forzamos que el precio sea un número (float). 
        // Si viene "10€", quitamos el € y lo convertimos. Si viene "10", lo convertimos.
        let safePrice = parseFloat(item.price.toString().replace('€', ''));
        
        // Si por lo que sea falla la conversión, usamos 0 para no romper la web
        if (isNaN(safePrice)) safePrice = 0;

        const subtotal = safePrice * item.quantity;
        totalAmount += subtotal;

        // Generamos el HTML
        const itemHTML = `
            <div class="cart-item border-4 border-rose-600 p-6 lg:p-8 flex flex-col lg:flex-row items-center justify-between gap-6 bg-white shadow-sm mb-6">
                <div class="w-full lg:w-1/3 text-center lg:text-left">
                    <h4 class="text-xl font-normal text-black uppercase mb-2">${item.name}</h4>
                    <p class="text-sm text-black font-bold">${item.description || ''}</p>
                </div>
                <div class="w-full lg:w-2/3 flex flex-col md:flex-row items-center justify-between gap-6 text-center">
                    <div class="flex flex-col">
                        <span class="text-[10px] text-gray-900 uppercase mb-1">COSTE UNITARIO</span>
                        <span class="text-2xl font-light text-gray-900">${safePrice}€</span>
                    </div>
                    <div class="flex flex-col no-select">
                         <span class="text-[10px] text-gray-900 uppercase mb-2">CANTIDAD</span>
                         <div class="flex justify-center items-center gap-4">
                            <button type="button" class="text-black hover:text-rose-600 transition-colors" onclick="updateQuantity(${item.id}, -1)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="pointer-events-none"><path d="M19 20L9 12l10-8v16zM5 4h2v16H5V4z"/></svg>
                            </button>
                            <span class="font-light text-2xl w-8 text-center">${item.quantity}</span>
                            <button type="button" class="text-black hover:text-rose-600 transition-colors" onclick="updateQuantity(${item.id}, 1)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="pointer-events-none"><path d="M5 4l10 8-10 8V4zm14 0h2v16h-2V4z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] text-gray-900 uppercase mb-1">TOTAL</span>
                        <span class="text-2xl font-light text-gray-900">${subtotal}€</span> </div>
                    <div class="flex items-center gap-4">
                        <button type="button" class="text-black hover:text-red-600 transition-colors" title="Eliminar" onclick="removeItem(${item.id})">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        cartContainer.insertAdjacentHTML('beforeend', itemHTML);
    });

    // 5. Actualizar Total General
    if (grandTotalEl) grandTotalEl.innerText = `${totalAmount}€`;
    updateBadge();
}

// --- FUNCIONES DE ACCIÓN (Globales para que el HTML las vea) ---

window.updateQuantity = function(id, change) {
    let cart = JSON.parse(localStorage.getItem('myCart')) || [];
    const itemIndex = cart.findIndex(item => item.id === id);
    
    if (itemIndex > -1) {
        const newQuantity = cart[itemIndex].quantity + change;

        // Si la cantidad va a ser 0, preguntamos si quiere eliminar
        if (newQuantity <= 0) {
            // Usamos nuestro modal de confirmación
            showAlert(
                '¿Eliminar artículo?',
                'La cantidad ha llegado a cero. ¿Quieres eliminar este producto del carrito?',
                'confirm', // Tipo confirmación (botones Sí/No)
                () => {
                    // ESTE CÓDIGO SOLO SE EJECUTA SI PULSAN "SÍ, ELIMINAR"
                    cart.splice(itemIndex, 1);
                    localStorage.setItem('myCart', JSON.stringify(cart));
                    renderCart(); 
                    // Opcional: feedback de que se borró
                    setTimeout(() => showAlert('Eliminado', 'El producto ha sido eliminado.', 'alert'), 300);
                }
            );
            // IMPORTANTE: No hacemos nada más aquí, esperamos al clic del usuario.
            return; 
        }

        // Si la cantidad es mayor que 0, simplemente actualizamos
        cart[itemIndex].quantity = newQuantity;
        localStorage.setItem('myCart', JSON.stringify(cart));
        renderCart();
    }
};

window.removeItem = function(id) {
    // Usamos nuestro modal de confirmación en lugar de confirm()
    showAlert(
        '¿Estás seguro?',
        'Vas a eliminar este artículo de tu carrito de compra de forma permanente.',
        'confirm', // Tipo confirmación
        () => {
            // ESTE CÓDIGO SOLO SE EJECUTA SI PULSAN "SÍ, ELIMINAR"
            let cart = JSON.parse(localStorage.getItem('myCart')) || [];
            cart = cart.filter(item => item.id !== id);
            localStorage.setItem('myCart', JSON.stringify(cart));
            renderCart();
            // Opcional: feedback de que se borró
            setTimeout(() => showAlert('Eliminado', 'El producto ha sido eliminado.', 'alert'), 300);
        }
    );
};

function updateBadge() {
    const badge = document.getElementById('cart-count-badge');
    if (badge) {
        const cart = JSON.parse(localStorage.getItem('myCart')) || [];
        const totalItems = cart.reduce((acc, item) => acc + item.quantity, 0);
        badge.innerText = totalItems;
        badge.style.display = totalItems === 0 ? 'none' : 'flex';
    }
}