import { initializeApp } from "https://www.gstatic.com/firebasejs/9.17.1/firebase-app.js";
import {
    getAuth,
    GoogleAuthProvider,
    signInWithPopup,
} from "https://www.gstatic.com/firebasejs/9.17.1/firebase-auth.js";
import axios from "https://cdn.skypack.dev/axios";

// Your Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyD4wsjXjw8UsofiX1lt_wJoZ2jH5DCYalc",
    authDomain: "prueba-a3263.firebaseapp.com",
    projectId: "prueba-a3263",
    storageBucket: "prueba-a3263.firebasestorage.app",
    messagingSenderId: "785781536496",
    appId: "1:785781536496:web:235df38014db8d626b3435",
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

// Configure the Google provider
const provider = new GoogleAuthProvider();

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
export function loginWithGoogle() {
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


// Associate the click event with the loginWithGoogle function
document.addEventListener("DOMContentLoaded", () => {
    const googleButton = document.getElementById("google-login-button");
    if (googleButton) {
        googleButton.addEventListener("click", loginWithGoogle);
    }
});
