/* config.js */
tailwind.config = {
    theme: {
      extend: {
        // 1. SOBRESCRIBIMOS LA TIPOGRAFÍA
        fontFamily: {
          // Al usar 'font-sans' en el HTML, cargará Montserrat automáticamente
          sans: ['Montserrat', 'sans-serif'],
        },
        // 2. SOBRESCRIBIMOS LOS COLORES (Tu paleta exacta)
        colors: {
          rose: {
            50: '#fff1f2',
            100: '#ffe4e6',
            400: '#fb7185', // Hover links claros
            500: '#f43f5e', // Texto resaltado
            600: '#e11d48', // Botones y acentos principales
            700: '#be123c', // Hover botones
          },
          gray: {
            50: '#f9fafb',
            100: '#f3f4f6',
            200: '#e5e7eb',
            800: '#1f2937', // Bordes oscuros
            900: '#111827', // Texto principal y fondo negro
          }
        },
        // 3. SOBRESCRIBIMOS ANIMACIONES (Las definimos aquí para usarlas como clases)
        animation: {
          'fade-in': 'fadeIn 0.5s ease-in',
          'sweep': 'sweep 0.5s ease-in-out',
        },
        keyframes: {
          fadeIn: {
            '0%': { opacity: '0' },
            '100%': { opacity: '1' },
          },
          sweep: {
            '0%': { opacity: '0', marginTop: '-10px' },
            '100%': { opacity: '1', marginTop: '0px' },
          }
        }
      }
    }
  }