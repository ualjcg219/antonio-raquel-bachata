/* script.js */

// --- 1. FUNCIÓN GLOBAL: Menú Móvil ---
// La exponemos a window para que el 'onclick' del HTML la encuentre
window.toggleMenu = function() {
    const menu = document.getElementById('mobile-menu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    
    // --- 2. LÓGICA: Carrito de Compras (Solo en carrito.html) ---
    const cartContainer = document.getElementById('cart-container');
    if (cartContainer) {
        initializeCart(cartContainer);
    }

    // --- 3. LÓGICA: Registro Paso 1 (Solo en registro.html) ---
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        initializeRegisterForm(registerForm);
    }

    // --- 4. LÓGICA: Registro Paso 2 (Solo en registro2.html) ---
    const confirmForm = document.getElementById('confirmForm');
    if (confirmForm) {
        initializeConfirmForm();
    }
});

// === DEFINICIÓN DE FUNCIONES (Modularizadas) ===

function initializeCart(container) {
    updateCartTotals(); // Calcular al cargar

    container.addEventListener('click', function(e) {
        const target = e.target;
        // Detectar botones usando closest para atrapar clicks en el icono SVG
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
        const qtyElement = item.querySelector('.qty-display');
        if (qtyElement) {
            const qty = parseInt(qtyElement.innerText);
            total += (price * qty);
            totalCount += qty;
        }
    });

    const grandTotalEl = document.getElementById('grand-total');
    if(grandTotalEl) grandTotalEl.innerText = total + '€';
    
    // Actualizar badge del header si existe
    const badge = document.getElementById('cart-count-badge');
    if(badge) badge.innerText = totalCount;
}

function checkEmptyCart() {
    const items = document.querySelectorAll('.cart-item');
    if (items.length === 0) {
        document.getElementById('cart-container').classList.add('hidden');
        document.getElementById('cart-footer').classList.add('hidden');
        document.getElementById('empty-msg').classList.remove('hidden');
    }
}

function initializeRegisterForm(form) {
    // Cargar datos previos si existen
    const savedData = localStorage.getItem('registroTemp');
    if(savedData) {
        const data = JSON.parse(savedData);
        ['nombre','apellido','telefono','fecha_nacimiento','email','email_confirm','dni','password','password_confirm','cp','genero'].forEach(id => {
             const input = document.getElementById(id);
             if(input) input.value = data[id] || '';
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const fields = ['nombre', 'apellido', 'telefono', 'fecha_nacimiento', 'email', 'email_confirm', 'dni', 'password', 'password_confirm', 'cp', 'genero'];
        let hasErrors = false;

        // Limpieza de errores
        fields.forEach(id => {
            const input = document.getElementById(id);
            const errorMsg = document.getElementById('error-' + id);
            if(input) input.classList.remove('input-error');
            if(errorMsg) errorMsg.classList.add('hidden');
        });

        // Validación básica
        fields.forEach(id => {
            const input = document.getElementById(id);
            const errorMsg = document.getElementById('error-' + id);
            
            if (input && !input.value.trim()) {
                input.classList.add('input-error');
                if(errorMsg) {
                    errorMsg.innerText = "Este campo es obligatorio";
                    errorMsg.classList.remove('hidden');
                }
                hasErrors = true;
            }
        });

        // Validaciones específicas (Email y Password)
        const email = document.getElementById('email');
        const emailConfirm = document.getElementById('email_confirm');
        if(email && emailConfirm && email.value !== emailConfirm.value) {
            emailConfirm.classList.add('input-error');
            document.getElementById('error-email_confirm').classList.remove('hidden');
            hasErrors = true;
        }

        const pass = document.getElementById('password');
        const passConfirm = document.getElementById('password_confirm');
        if(pass && passConfirm && pass.value !== passConfirm.value) {
            passConfirm.classList.add('input-error');
            document.getElementById('error-password_confirm').classList.remove('hidden');
            hasErrors = true;
        }

        if (hasErrors) return;

        // Guardado
        const formData = {};
        fields.forEach(id => {
            const el = document.getElementById(id);
            if(el) formData[id] = el.value;
        });

        localStorage.setItem('registroTemp', JSON.stringify(formData));
        window.location.href = 'registro2.html';
    });
}

function initializeConfirmForm() {
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