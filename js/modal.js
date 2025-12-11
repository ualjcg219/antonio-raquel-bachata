/* ============================================
   MODAL JS – Sistema de Alertas y Confirmaciones
   ============================================ */

// 1. INYECTAR EL HTML DEL MODAL AL CARGAR LA PÁGINA
document.addEventListener('DOMContentLoaded', () => {
    // Usamos z-[9999] para asegurar que siempre esté por encima de todo (incluso del menú móvil)
    const modalHTML = `
        <div id="custom-modal-overlay" class="fixed inset-0 bg-black bg-opacity-60 z-[9999] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300 opacity-0">
            <div id="custom-modal-box" class="bg-white w-full max-w-md rounded-lg shadow-2xl overflow-hidden scale-95 transition-transform duration-300 border-t-4 border-rose-600">
                <div class="flex justify-between items-center p-4 border-b border-gray-100 bg-gray-50">
                    <h3 id="modal-title" class="text-lg font-bold text-gray-900 uppercase tracking-wider">Título</h3>
                    <!-- CAMBIO: Llamamos a closeAlert() en lugar de closeModal() -->
                    <button onclick="closeAlert()" class="text-gray-400 hover:text-rose-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <div class="p-6">
                    <p id="modal-message" class="text-gray-600 text-sm leading-relaxed">Mensaje aquí...</p>
                </div>
                <div id="modal-actions" class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <!-- CAMBIO: Llamamos a closeAlert() -->
                    <button id="btn-modal-cancel" onclick="closeAlert()" class="hidden px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded text-sm font-bold uppercase hover:bg-gray-100 transition-colors">
                        Cancelar
                    </button>
                    <button id="btn-modal-confirm" class="px-6 py-2 bg-rose-600 text-white rounded text-sm font-bold uppercase hover:bg-rose-700 shadow-sm transition-colors">
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Cerrar si se hace clic fuera del modal
    document.getElementById('custom-modal-overlay').addEventListener('click', (e) => {
        if (e.target.id === 'custom-modal-overlay') closeAlert();
    });
});

// Variable global para guardar la acción a realizar al confirmar
let currentConfirmAction = null;

// 2. FUNCIÓN PARA MOSTRAR EL MODAL (GLOBAL)
window.showAlert = function(title, message, type = 'alert', onConfirm = null) {
    const overlay = document.getElementById('custom-modal-overlay');
    const box = document.getElementById('custom-modal-box');
    const titleEl = document.getElementById('modal-title');
    const messageEl = document.getElementById('modal-message');
    const cancelBtn = document.getElementById('btn-modal-cancel');
    const confirmBtn = document.getElementById('btn-modal-confirm');

    // Rellenar textos
    titleEl.innerText = title;
    messageEl.innerText = message;

    // Configurar botones según el tipo
    if (type === 'confirm') {
        cancelBtn.classList.remove('hidden');
        confirmBtn.innerText = 'SÍ, CONFIRMAR';
        confirmBtn.classList.remove('bg-rose-600', 'hover:bg-rose-700');
        confirmBtn.classList.add('bg-red-700', 'hover:bg-red-800'); // Botón rojo para acciones peligrosas
        currentConfirmAction = onConfirm; 
    } else {
        cancelBtn.classList.add('hidden');
        confirmBtn.innerText = 'ACEPTAR';
        confirmBtn.classList.remove('bg-red-700', 'hover:bg-red-800');
        confirmBtn.classList.add('bg-rose-600', 'hover:bg-rose-700'); 
        currentConfirmAction = closeAlert; // Si es alerta simple, aceptar solo cierra
    }

    // Asignar la acción al botón de confirmar
    confirmBtn.onclick = function() {
        if (currentConfirmAction && typeof currentConfirmAction === 'function') {
            currentConfirmAction();
        }
        closeAlert();
    };

    // Mostrar con animación
    overlay.classList.remove('hidden');
    // Pequeño retardo para que la transición CSS funcione
    setTimeout(() => {
        overlay.classList.remove('opacity-0');
        box.classList.remove('scale-95');
    }, 10);
};

// 3. FUNCIÓN PARA CERRAR (Renombrada para evitar conflictos con main.js)
window.closeAlert = function() {
    const overlay = document.getElementById('custom-modal-overlay');
    const box = document.getElementById('custom-modal-box');
    
    if (!overlay || !box) return;

    // Ocultar con animación
    overlay.classList.add('opacity-0');
    box.classList.add('scale-95');

    setTimeout(() => {
        overlay.classList.add('hidden');
        currentConfirmAction = null; // Limpiar acción pendiente
    }, 300); // Esperar a que termine la transición
};