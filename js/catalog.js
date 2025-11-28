// js/catalog.js

// Al cargar la página, actualizamos el numerito del menú
document.addEventListener('DOMContentLoaded', () => {
    updateBadge();
});

function addToCart(id, name, price, description) {
    // 1. Mensaje de control para ver si funciona el clic
    console.log("Click recibido. Intentando añadir:", name);

    // 2. Obtener el carrito actual de la memoria
    let cart = JSON.parse(localStorage.getItem('myCart')) || [];

    // 3. Buscar si el producto ya existe (comparamos IDs)
    // Usamos parseInt para asegurar que comparamos números con números
    const existingProduct = cart.find(item => item.id === parseInt(id));

    if (existingProduct) {
        existingProduct.quantity += 1;
        console.log("Producto existente. Cantidad actualizada.");
    } else {
        cart.push({
            id: parseInt(id), // Guardamos ID como número
            name: name,
            price: parseFloat(price), // Guardamos Precio como número (IMPORTANTE)
            description: description,
            quantity: 1
        });
        console.log("Producto nuevo añadido.");
    }

    // 4. Guardar en la memoria del navegador
    localStorage.setItem('myCart', JSON.stringify(cart));

    // Actualizar interfaz
    updateBadge();
    
    // Feedback visual
    showAlert(
        '¡Producto Añadido!', 
        `${name} se ha añadido correctamente a tu carrito de compra.`,
        'alert' // Tipo alerta simple
    );}

function updateBadge() {
    const badge = document.getElementById('cart-count-badge');
    if (badge) {
        const cart = JSON.parse(localStorage.getItem('myCart')) || [];
        const totalItems = cart.reduce((acc, item) => acc + item.quantity, 0);
        badge.innerText = totalItems;
        // Si hay 0 items, ocultamos el badge, si hay más, lo mostramos
        badge.style.display = totalItems === 0 ? 'none' : 'flex';
    }
}