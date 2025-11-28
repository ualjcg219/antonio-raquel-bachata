/* js/main.js */

// ==========================================
// 1. UTILIDADES GLOBALES (UI)
// ==========================================

// Menú Móvil
window.toggleMenu = function() {
    const menu = document.getElementById('mobile-menu');
    if (menu) menu.classList.toggle('hidden');
};

// Dropdown Usuario (Header)
window.toggleUserDropdown = function() {
    const dropdown = document.getElementById('user-dropdown');
    const chevron = document.getElementById('user-chevron');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
        if(chevron) {
            chevron.style.transform = dropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }
}

// Cerrar dropdown al hacer clic fuera
window.addEventListener('click', function(e) {
    const button = e.target.closest('#user-menu-btn');
    const dropdown = document.getElementById('user-dropdown');
    if (!button && dropdown && !dropdown.classList.contains('hidden')) {
        dropdown.classList.add('hidden');
        const chevron = document.getElementById('user-chevron');
        if(chevron) chevron.style.transform = 'rotate(0deg)';
    }
});

// Sistema de Modales (Popups) Globales
window.openModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        // Pequeño timeout para permitir la transición CSS
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

// ==========================================
// 2. LÓGICA DEL CARRITO DE COMPRAS
// ==========================================

function initializeCart(container) {
    // Escuchar clicks dentro del carrito (Delegación de eventos)
    container.addEventListener('click', function(e) {
        const target = e.target;
        const btnPlus = target.closest('.btn-plus');
        const btnMinus = target.closest('.btn-minus');
        const btnRemove = target.closest('.btn-remove');
        
        const cartItem = target.closest('.cart-item');
        if (!cartItem) return;

        const qtyDisplay = cartItem.querySelector('.qty-display');
        let currentQty = parseInt(qtyDisplay.innerText);

        if (btnPlus) {
            currentQty++;
            qtyDisplay.innerText = currentQty;
            updateItemTotal(cartItem, currentQty);
            updateCartTotals();
        } else if (btnMinus) {
            if (currentQty > 1) {
                currentQty--;
                qtyDisplay.innerText = currentQty;
                updateItemTotal(cartItem, currentQty);
                updateCartTotals();
            }
        } else if (btnRemove) {
            if(confirm('¿Estás seguro de eliminar este producto?')) {
                cartItem.remove();
                updateCartTotals();
                checkEmptyCart();
            }
        }
    });
}

function updateItemTotal(item, quantity) {
    const price = parseFloat(item.dataset.price);
    const subtotal = price * quantity;
    const subtotalDisplay = item.querySelector('.subtotal-display');
    if(subtotalDisplay) subtotalDisplay.innerText = subtotal + '€';
}

function updateCartTotals() {
    const items = document.querySelectorAll('.cart-item');
    let total = 0;
    let totalCount = 0;

    items.forEach(item => {
        const price = parseFloat(item.dataset.price);
        const qtyDisplay = item.querySelector('.qty-display');
        if(qtyDisplay) {
            const qty = parseInt(qtyDisplay.innerText);
            total += (price * qty);
            totalCount += qty;
        }
    });

    // Actualizar total precio
    const grandTotalEl = document.getElementById('grand-total');
    if(grandTotalEl) grandTotalEl.innerText = total + '€';
    
    // Actualizar badge rojo del header (existe en todas las páginas)
    const badge = document.getElementById('cart-count-badge');
    if(badge) badge.innerText = totalCount;
}

function checkEmptyCart() {
    const items = document.querySelectorAll('.cart-item');
    if (items.length === 0) {
        const cartContainer = document.getElementById('cart-container');
        const cartFooter = document.getElementById('cart-footer');
        const emptyMsg = document.getElementById('empty-msg');
        
        if(cartContainer) cartContainer.classList.add('hidden');
        if(cartFooter) cartFooter.classList.add('hidden');
        if(emptyMsg) emptyMsg.classList.remove('hidden');
    }
}

// ==========================================
// 3. INICIALIZADOR GENERAL (DOM READY)
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    
    // A. Inicializar Carrito (Si estamos en la página carrito)
    const cartContainer = document.getElementById('cart-container');
    if (cartContainer) {
        initializeCart(cartContainer);
    }
    // Siempre actualizar el badge del header, aunque no estemos en carrito.html
    // (Busca items solo si existen, si no, pone 0)
    updateCartTotals(); 

    // B. Lógica de Registro (Paso 1)
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        // Cargar datos previos
        const savedData = localStorage.getItem('registroTemp');
        if(savedData) {
            const data = JSON.parse(savedData);
            ['nombre','apellido','telefono','fecha_nacimiento','email','email_confirm','dni','password','password_confirm','cp','genero'].forEach(id => {
                 const el = document.getElementById(id);
                 if(el) el.value = data[id] || '';
            });
        }
        
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Aquí irían las validaciones...
            // Simulamos guardado:
            const formData = {};
            const inputs = registerForm.querySelectorAll('input, select');
            inputs.forEach(input => formData[input.id] = input.value);
            
            localStorage.setItem('registroTemp', JSON.stringify(formData));
            window.location.href = 'registro2.html';
        });
    }
    
    // C. Confirmación Registro (Paso 2)
    const confirmForm = document.getElementById('confirmForm');
    if (confirmForm) {
        const savedData = localStorage.getItem('registroTemp');
        if(savedData) {
            const data = JSON.parse(savedData);
            const fields = ['nombre', 'apellido', 'telefono', 'fecha_nacimiento', 'email', 'email_confirm', 'dni', 'password', 'password_confirm', 'cp', 'genero'];
            fields.forEach(id => {
                const el = document.getElementById(id);
                if(el) el.value = data[id] || '';
            });
        }
    }
});