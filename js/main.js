/* ============================================
   MAIN JS – UTILIDADES GLOBALES Y REGISTRO
   ============================================ */

const API_BASE_GLOBAL = "/api"; // Ruta base para registro

// ==========================================
// 1. UTILIDADES UI (Menús, Modales, Dropdowns)
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

// Sistema de Modales Globales
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

// ==========================================
// 2. BADGE DEL CARRITO (Global)
// ==========================================
// Esta función se ejecuta en TODAS las páginas para mostrar el numerito rojo
function updateGlobalBadge() {
    const badge = document.getElementById('cart-count-badge');
    if (badge) {
        const cart = JSON.parse(localStorage.getItem('myCart')) || [];
        const totalItems = cart.reduce((acc, item) => acc + item.quantity, 0);
        badge.innerText = totalItems;
        badge.style.display = totalItems === 0 ? 'none' : 'flex';
    }
}

// ==========================================
// 3. LÓGICA DE REGISTRO (CONECTADA A BD)
// ==========================================

async function registerUserInDB(userData) {
    try {
        // Enviamos los datos a clientes.php usando POST
        const response = await fetch(`${API_BASE_GLOBAL}/clientes.php`, {
            method: 'POST', // Importante: POST para crear
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });

        const json = await response.json();
        return json;
    } catch (error) {
        console.error("Error en registro:", error);
        return { success: false, message: "Error de conexión con el servidor" };
    }
}

// ==========================================
// 4. INICIALIZADOR GENERAL (DOM READY)
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    
    // A. Actualizar siempre el numerito del carrito
    updateGlobalBadge();

    // B. Lógica de Registro PASO 1 (Guardar temporalmente)
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        // Cargar datos previos si el usuario vuelve atrás
        const savedData = localStorage.getItem('registroTemp');
        if(savedData) {
            const data = JSON.parse(savedData);
            // Rellenar inputs
            Object.keys(data).forEach(key => {
                 const el = document.getElementById(key);
                 if(el) el.value = data[key];
            });
        }
        
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Recoger datos del formulario
            const formData = {};
            const inputs = registerForm.querySelectorAll('input, select');
            inputs.forEach(input => formData[input.id] = input.value);
            
            // Validaciones básicas (puedes añadir más)
            if(formData.password !== formData.password_confirm) {
                alert("Las contraseñas no coinciden");
                return;
            }

            // Guardar en memoria y pasar al paso 2
            localStorage.setItem('registroTemp', JSON.stringify(formData));
            window.location.href = 'registro2.html'; // Asegúrate que esta página existe
        });
    }
    
    // C. Lógica de Registro PASO 2 (Confirmar y Enviar a BD)
    const confirmForm = document.getElementById('confirmForm');
    if (confirmForm) {
        // Recuperar datos del paso 1
        const savedDataString = localStorage.getItem('registroTemp');
        if (!savedDataString) {
            // Si no hay datos, volver al paso 1
            window.location.href = 'registro.html';
            return;
        }

        const savedData = JSON.parse(savedDataString);
        
        // Rellenar campos ocultos o visibles del paso 2 si existen
        Object.keys(savedData).forEach(key => {
            const el = document.getElementById(key);
            if(el) el.value = savedData[key];
        });

        confirmForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btnSubmit = confirmForm.querySelector('button[type="submit"]');
            const originalText = btnSubmit.innerText;
            btnSubmit.innerText = "Registrando...";
            btnSubmit.disabled = true;

            // Unir datos guardados con posibles datos nuevos del paso 2
            // (Si en el paso 2 pides más cosas, recógelas aquí)
            const finalData = { ...savedData };

            // ENVIAR A LA BASE DE DATOS
            const result = await registerUserInDB(finalData);

            if (result.success) {
                localStorage.removeItem('registroTemp'); // Limpiar temporal
                alert("¡Registro completado con éxito! Ahora puedes iniciar sesión.");
                window.location.href = 'login.html';
            } else {
                alert("Error al registrar: " + (result.message || "Inténtalo de nuevo."));
                btnSubmit.innerText = originalText;
                btnSubmit.disabled = false;
            }
        });
    }
});