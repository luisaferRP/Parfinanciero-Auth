import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.17.1/firebase-app.js';
import { getAuth, GoogleAuthProvider, signInWithPopup } from 'https://www.gstatic.com/firebasejs/9.17.1/firebase-auth.js';
import axios from 'https://cdn.skypack.dev/axios';


// Tu configuración de Firebase
const firebaseConfig = {
    apiKey: 'AIzaSyD4wsjXjw8UsofiX1lt_wJoZ2jH5DCYalc',
    authDomain: 'prueba-a3263.firebaseapp.com',
    projectId: 'prueba-a3263',
    storageBucket: 'prueba-a3263.firebasestorage.app',
    messagingSenderId: '785781536496',
    appId: '1:785781536496:web:235df38014db8d626b3435'
};

// Inicializar Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

// Configurar el proveedor de Google
const provider = new GoogleAuthProvider();

// Función para iniciar sesión con Google
export function loginWithGoogle() {
    signInWithPopup(auth, provider)
        .then(function (result) {
            const user = result.user;
            console.log('Usuario autenticado:', user);
            console.log("datos", user.displayName)
            axios.post('/register', {
                name: user.displayName,
                last_name: 'Predeterminado',
                email: user.email,
                password: user.uid, // Puedes usar el UID como una contraseña temporal
                password_confirmation: user.uid, // Confirmación para cumplir con la validación
                terms: true 
            })
            .then(response => {
                console.log('Usuario registrado con Google:', response.data);
                window.location.href = '/dashboard'; // Redirige al dashboard después del registro
            })
            // Puedes enviar la información del usuario al servidor si es necesario
        })
        .catch(function (error) {
            console.error('Error al autenticar con Google', error);
        });
}

// Asociar el evento del clic a la función loginWithGoogle
document.addEventListener('DOMContentLoaded', () => {
    const googleButton = document.getElementById('google-login-button');
    if (googleButton) {
        googleButton.addEventListener('click', loginWithGoogle);
    }
});