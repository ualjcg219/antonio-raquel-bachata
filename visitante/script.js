// Role Management System
class RoleManager {
    constructor() {
        this.currentRole = 'visitante';
        this.roleSelect = document.getElementById('role-select');
        this.sections = {
            visitante: document.getElementById('visitante-section'),
            cliente: document.getElementById('cliente-section'),
            administrador: document.getElementById('administrador-section')
        };
        
        this.init();
    }

    init() {
        // Set initial role from localStorage or default to 'visitante'
        const savedRole = localStorage.getItem('userRole');
        if (savedRole && this.sections[savedRole]) {
            this.currentRole = savedRole;
            this.roleSelect.value = savedRole;
        }
        
        // Show the appropriate section
        this.showRoleSection(this.currentRole);
        
        // Add event listener for role changes
        this.roleSelect.addEventListener('change', (e) => {
            this.changeRole(e.target.value);
        });
        
        // Log role change
        this.logRoleChange(this.currentRole);
    }

    changeRole(newRole) {
        if (this.sections[newRole]) {
            this.currentRole = newRole;
            this.showRoleSection(newRole);
            
            // Save to localStorage
            localStorage.setItem('userRole', newRole);
            
            // Log the change
            this.logRoleChange(newRole);
        }
    }

    showRoleSection(role) {
        // Hide all sections
        Object.keys(this.sections).forEach(key => {
            this.sections[key].classList.remove('active');
        });
        
        // Show the selected role section
        if (this.sections[role]) {
            this.sections[role].classList.add('active');
        }
    }

    logRoleChange(role) {
        const roleNames = {
            visitante: 'Visitante',
            cliente: 'Cliente',
            administrador: 'Administrador'
        };
        console.log(`Rol activo: ${roleNames[role]}`);
    }

    getCurrentRole() {
        return this.currentRole;
    }
}

// Initialize the role manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const roleManager = new RoleManager();
    
    // Make roleManager globally accessible for debugging
    window.roleManager = roleManager;
    
    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Admin button interactions (placeholder functionality)
    const adminButtons = document.querySelectorAll('.admin-btn');
    adminButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.textContent;
            console.log(`Acción de administrador: ${action}`);
            alert(`Funcionalidad "${action}" - Próximamente disponible`);
        });
    });
    
    // Welcome message based on role
    displayWelcomeMessage(roleManager.getCurrentRole());
});

function displayWelcomeMessage(role) {
    const messages = {
        visitante: '¡Bienvenido! Explora nuestras clases de bachata.',
        cliente: '¡Hola! Aquí tienes acceso a tus clases y materiales.',
        administrador: 'Panel de administración cargado correctamente.'
    };
    
    console.log(messages[role]);
}

// Utility function to format dates (used for potential future features)
function formatDate(date) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString('es-ES', options);
}

// Export for potential testing
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { RoleManager, formatDate };
}
