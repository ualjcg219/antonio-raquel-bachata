// js/admin-bonos-anadir.js
// Envía el formulario directamente a index.php y agrega el parámetro '__route' para indicar la ruta real.
// Esto evita dependencias de mod_rewrite/.htaccess mientras pruebas en XAMPP.

const API_INDEX = '/antonio-raquel-bachata/api/index.php'; // punto directo al front controller
const API_ROUTE = '/api/bonos';

console.log('[DEBUG] admin-bonos-anadir.js cargado', { API_INDEX, API_ROUTE });

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('bonoForm');
    const msgBox = document.getElementById('formMessage');
    const submitBtn = document.getElementById('submitBonoBtn');

    if (!form) {
        console.error('Formulario #bonoForm no encontrado');
        return;
    }

    function showMessage(text, type = 'success') {
        if (!msgBox) {
            console[type === 'success' ? 'log' : 'error']('[MSG]', text);
            return;
        }
        msgBox.classList.remove('hidden');
        msgBox.className = type === 'success' ? 'p-3 mb-4 bg-green-50 text-green-800 rounded' : 'p-3 mb-4 bg-red-50 text-red-800 rounded';
        msgBox.innerText = text;
    }

    async function handleSubmit(e) {
        if (e) e.preventDefault();
        if (submitBtn) submitBtn.disabled = true;

        const tipo = form.querySelector('#tipo')?.value || '';
        const numDias = form.querySelector('#numDias')?.value || '';
        const descripcion = form.querySelector('#descripcion')?.value || '';
        const precio = form.querySelector('#precio')?.value || '';

        if (!tipo || !numDias || !descripcion || !precio) {
            showMessage('Rellena todos los campos obligatorios.', 'error');
            if (submitBtn) submitBtn.disabled = false;
            return;
        }

        const fd = new FormData(form);
        // Añadimos la ruta real para que index.php la use
        fd.append('__route', API_ROUTE);

        try {
            const res = await fetch(API_INDEX, {
                method: 'POST',
                body: fd,
                credentials: 'include'
            });

            const raw = await res.text().catch(() => null);
            console.log('Respuesta raw:', raw);
            let data = null;
            try { data = raw ? JSON.parse(raw) : null; } catch (err) { console.warn('No JSON:', err); }

            if (!res.ok) {
                showMessage('Error servidor: HTTP ' + res.status, 'error');
                console.error('Server error', {status: res.status, raw, data});
            } else {
                if (data && data.success) {
                    showMessage(data.message || 'Bono creado correctamente', 'success');
                    form.reset();
                } else {
                    showMessage(data?.message || 'Respuesta sin success', data?.success ? 'success' : 'error');
                    console.warn('Backend response (no success):', data);
                }
            }
        } catch (err) {
            console.error('Fetch error:', err);
            showMessage('Error de red: ' + err.message, 'error');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    form.addEventListener('submit', handleSubmit);
    if (submitBtn) submitBtn.addEventListener('click', function (ev) {
        ev.preventDefault();
        handleSubmit(ev);
    });
});