window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');
    require('tilt.js/src/tilt.jquery');

    require('bootstrap');
} catch (e) {}

// require('./main.js');
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.baseURL = '/';
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

/**
 * Automatic JWT Bearer Token injector on every request
 */
window.axios.interceptors.request.use(
    config => {
        const tokenJWT = localStorage.getItem('token');
        if (tokenJWT) {
            config.headers['Authorization'] = tokenJWT.startsWith('Bearer ') ? tokenJWT : `Bearer ${tokenJWT}`;
        }
        return config;
    },
    error => Promise.reject(error)
);

/**
 * Global HTTP Response Interceptor
 * Captura códigos de error corporativos (401, 403, 422, 500) y muestra notificaciones amigables.
 */
window.axios.interceptors.response.use(
    response => response,
    error => {
        const izi = window.iziToast;

        if (!error.response) {
            // Error de red o timeout
            if (izi) {
                izi.error({
                    title: 'Error de Conexión',
                    message: 'No se pudo contactar con el servidor. Verifique su conexión de red.',
                    position: 'topRight',
                    timeout: 5000,
                });
            }
            return Promise.reject(error);
        }

        const status = error.response.status;
        const data = error.response.data;

        switch (status) {
            case 401:
                if (izi) {
                    izi.warning({
                        title: 'Sesión Expirada',
                        message: data.message || 'Su sesión ha expirado. Por favor, ingrese sus credenciales nuevamente.',
                        position: 'topRight',
                        timeout: 4000,
                    });
                }
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                localStorage.removeItem('permissions');
                if (window.location.pathname !== '/pages/login' && window.location.pathname !== '/login') {
                    setTimeout(() => {
                        window.location.href = '/pages/login';
                    }, 1000);
                }
                break;

            case 403:
                if (izi) {
                    izi.warning({
                        title: 'Acceso Restringido',
                        message: data.message || 'No cuenta con los privilegios suficientes para ejecutar esta acción.',
                        position: 'topRight',
                        timeout: 5000,
                    });
                }
                break;

            case 422:
                // Errores de validación de datos
                const validationMsg = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Datos inválidos.');
                if (izi) {
                    izi.info({
                        title: 'Validación',
                        message: typeof validationMsg === 'string' ? validationMsg : 'Verifique los campos requeridos.',
                        position: 'topRight',
                        timeout: 5000,
                    });
                }
                break;

            case 500:
                if (izi) {
                    izi.error({
                        title: 'Error Interno',
                        message: data.message || 'Ocurrió un error en el servidor. El evento ha sido registrado para auditoría.',
                        position: 'topRight',
                        timeout: 6000,
                    });
                }
                break;
        }

        return Promise.reject(error);
    }
);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo'

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted: true
// });
