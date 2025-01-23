import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.17.1/firebase-app.js';
// import { initializeApp } from 'firebase/app';
import { getAuth, GoogleAuthProvider, signInWithPopup, OAuthProvider  } from 'https://www.gstatic.com/firebasejs/9.17.1/firebase-auth.js';
import axios from 'https://cdn.skypack.dev/axios';


// Tu configuración de Firebase
// const firebaseConfig = {
//     apiKey: 'AIzaSyD4wsjXjw8UsofiX1lt_wJoZ2jH5DCYalc',
//     authDomain: 'prueba-a3263.firebaseapp.com',
//     projectId: 'prueba-a3263',
//     storageBucket: 'prueba-a3263.firebasestorage.app',
//     messagingSenderId: '785781536496',
//     appId: '1:785781536496:web:235df38014db8d626b3435'
// };

// // Inicializar Firebase
// const app = initializeApp(firebaseConfig);
// const auth = getAuth(app);

// // Configurar el proveedor de Google
// const provider = new GoogleAuthProvider();

// // Función para iniciar sesión con Google
// export function loginWithGoogle() {
//     signInWithPopup(auth, provider)
//         .then(function (result) {
//             const user = result.user;
//             console.log('Usuario autenticado:', user);
//             console.log("datos", user.displayName)
//             axios.post('/register', {
//                 name: user.displayName,
//                 last_name: 'Predeterminado',
//                 email: user.email,
//                 password: user.uid, // Puedes usar el UID como una contraseña temporal
//                 password_confirmation: user.uid, // Confirmación para cumplir con la validación
//                 terms: true 
//             })
//             .then(response => {
//                 console.log('Usuario registrado con Google:', response.data);
//                 window.location.href = '/dashboard'; // Redirige al dashboard después del registro
//             })
//             // Puedes enviar la información del usuario al servidor si es necesario
//         })
//         .catch(function (error) {
//             console.error('Error al autenticar con Google', error);
//         });
// }

// // Asociar el evento del clic a la función loginWithGoogle
// document.addEventListener('DOMContentLoaded', () => {
//     const googleButton = document.getElementById('google-login-button');
//     if (googleButton) {
//         googleButton.addEventListener('click', loginWithGoogle);
//     }
// });

//acaaaaaaaaaaaaaa
// const firebaseConfig = {
//     apiKey: "AIzaSyDldaPf5gc10ZHH1z9ymjCuMq2lKHcbqgg",
//     authDomain: "parfinancieroauth-76f9d.firebaseapp.com",
//     projectId: "parfinancieroauth-76f9d",
//     storageBucket: "parfinancieroauth-76f9d.firebasestorage.app",
//     messagingSenderId: "629167238351",
//     appId: "1:629167238351:web:7f41c8cbb3b972fc151bba",
//     measurementId: "G-ZTR3Q63V17"
//   };

//MICROSOFT ------------------

// Configuración del botón de inicio de sesión con Microsoft
document.addEventListener('DOMContentLoaded', () => {
    const microsoftButton = document.getElementById('microsoft-login-button');
    if (microsoftButton) {
        console.log('Botón de login con Microsoft está listo.');
        microsoftButton.addEventListener('click', loginWithMicrosoft);
    }
});


const firebaseConfig = {
    apiKey: "AIzaSyDldaPf5gc10ZHH1z9ymjCuMq2lKHcbqgg",
    authDomain: "parfinancieroauth-76f9d.firebaseapp.com",
    projectId: "parfinancieroauth-76f9d",
    storageBucket: "parfinancieroauth-76f9d.firebasestorage.app",
    messagingSenderId: "629167238351",
    appId: "1:629167238351:web:7f41c8cbb3b972fc151bba",
    measurementId: "G-ZTR3Q63V17"
  };
  
// Inicializa Firebase solo una vez
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

const provider = new OAuthProvider('microsoft.com');

export async function getBearerToken(email) {
    try {
        const response = await axios.post("/api/v1/get-bearer-token", { email });
        console.log(response.data);
        return response.data; // Make sure `jwt` is the correct field name in the response
    } catch (error) {
        console.error("Error al verificar el usuario:", error);
        throw error; // Throws the error so it can be handled later
    }
}

// Function to log in with Google
export function loginWithMicrosoft() {
    signInWithPopup(auth, provider)
        .then(async function (result) {
            const user = result.user;
            console.log("Usuario autenticado:", user);

            const fullName = user.displayName;
            const [firstName, ...lastNameParts] = fullName.split(" ");
            const lastName = lastNameParts.join(" ");

            try {
                const checkResponse = await axios.post("/api/v1/check-user", {
                    email: user.email,
                });

                if (checkResponse.data.exists) {
                    console.log("Usuario ya registrado, iniciando sesión...");
                    const loginResponse = await axios.post("/login", {
                        email: user.email,
                        password: user.uid,
                    });

                    console.log("Sesión iniciada:", loginResponse.data);

                    // Wait to get the JWT
                    const Jwt = await getBearerToken(user.email);

                    // Redirect to dashboard with JWT in URL
                    window.location.href = `/dashboard?jwt=${Jwt.jwt}`;
                } else {
                    console.log("Usuario no registrado, registrando...");
                    const registerResponse = await axios.post("/register", {
                        name: firstName,
                        last_name: lastName || "Predeterminado",
                        email: user.email,
                        password: user.uid,
                        password_confirmation: user.uid,
                        terms: true,
                    });

                    console.log("Usuario registrado:", registerResponse.data);

                    // Wait to get the JWT
                    const Jwt = await getBearerToken(user.email);
                    console.log("JWT obtenido:", Jwt);

                    // Redirect to dashboard with JWT in URL
                    window.location.href = `/dashboard?jwt=${Jwt.jwt}`;
                }
            } catch (error) {
                console.error("Error en el proceso de autenticación:", error);
            }
        })
        .catch(function (error) {
            console.error("Error al autenticar con Google", error);
        });
}



// export function loginWithMicrosoft() {
//     signInWithPopup(auth, provider)
//         .then((result) => {
//             // Obtener el token de Microsoft
//             const user = result.user;
//             user.getIdToken().then((idToken) => {
//                 console.log('Token de ID:', idToken);

//                 //objeto de usuario
//                 const userData = {
//                     idToken: idToken,
//                     name: user.displayName,
//                     last_name: 'Predeterminado',
//                     email: user.email,
//                     password: user.uid, 
//                     current_team_id : user.providerData[0].providerId,
//                     profile_photo_path : user.providerData[0].photoURL,
//                 };
//                 //eviamos el objeto a el backend
//                 fetch('/api/microsoft-login',{
//                     method: 'POST',
//                     headers: {
//                         'Content-Type': 'application/json',
//                         'Accept': 'application/json,'
//                     },
//                     body: JSON.stringify({idToken: user.idToken}),
//                 })
//                 .then((response) => {
//                     if (!response.ok) {
//                         throw new Error('Error en la solicitud');
//                     }
//                     return response.json();
//                 })
//                 .then((data)=>{
//                     console.log('Respuesta:',data);
//                 })
//                 .catch((error) => {
//                     console.error('Error en la solicitud:', error);
//                 });


//             });
//             //en user tengo todos los dats de el usuario en providerData estan los datos 
//             console.log('Usuario autenticado:', user);
//             console.log('Datos del usuario:', user.displayName);

//             // axios.post('/register', {
//             //     name: user.displayName,
//             //     last_name: 'Predeterminado',
//             //     email: user.email,
//             //     password: user.uid, // Puedes usar el UID como una contraseña temporal
//             //     password_confirmation: user.uid, // Confirmación para cumplir con la validación
//             //     terms: true ,
//             //     current_team_id : 'Microsoft'
                
//             // })
//             // .then(response => {
//             //     console.log('Usuario autenticado con Microsoft:', response.data);
//             //     window.location.href = '/dashboard'; // Redirige al dashboard después del registro
//             // })

//             // .catch((error) => {
//             //     console.error('Error al registrar usuario:', error);
//             // });

//         })
//         .catch((error) => {
//             console.error('Error al autenticar con Microsoft:', error);
//         });
// }