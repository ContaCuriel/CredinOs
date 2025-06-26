import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Obtén tu URL de ngrok. ¡Recuerda que esta cambia cada vez que reinicias ngrok!
const ngrokUrl = 'https://ab4e-2806-2f0-9360-f346-6113-4075-824-f1dd.ngrok-free.app';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // --- AÑADE ESTE BLOQUE COMPLETO ---
    server: {
        // Habilita HTTPS. Vite creará un certificado autofirmado.
        https: true, 
        
        // Define el host. '0.0.0.0' escucha en todas las interfaces de red.
        host: '0.0.0.0', 

        hmr: {
            // Le dice a Vite que el cliente de "Hot Module Replacement"
            // debe conectarse a través de tu host de ngrok.
            host: new URL(ngrokUrl).hostname
        }
    }
    // ------------------------------------
});