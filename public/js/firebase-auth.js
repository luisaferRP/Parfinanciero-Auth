import { initializeApp } from "https://www.gstatic.com/firebasejs/9.17.1/firebase-app.js";
// import { initializeApp } from 'firebase/app';
import {
    getAuth,
    GoogleAuthProvider,
    signInWithPopup,
    OAuthProvider,
} from "https://www.gstatic.com/firebasejs/9.17.1/firebase-auth.js";
import axios from "https://cdn.skypack.dev/axios";

//buttons to start section
//microsoft
document.addEventListener("DOMContentLoaded", () => {
    const microsoftButton = document.getElementById("microsoft-login-button");
    if (microsoftButton) {
        console.log("Botón de login con Microsoft está listo.");
        microsoftButton.addEventListener("click", loginWithMicrosoft);
    }
});

//Google
document.addEventListener("DOMContentLoaded", () => {
    const googleButton = document.getElementById("google-login-button");
    if (googleButton) {
        googleButton.addEventListener("click", loginWithGoogle);
    }
});

//Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyDldaPf5gc10ZHH1z9ymjCuMq2lKHcbqgg",
    authDomain: "parfinancieroauth-76f9d.firebaseapp.com",
    projectId: "parfinancieroauth-76f9d",
    storageBucket: "parfinancieroauth-76f9d.firebasestorage.app",
    messagingSenderId: "629167238351",
    appId: "1:629167238351:web:7f41c8cbb3b972fc151bba",
    measurementId: "G-ZTR3Q63V17",
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

// Function to log in with Google
export function loginWithGoogle() {

    // Configurar el proveedor de Google
    const provider = new GoogleAuthProvider();

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
                        providerId: user.providerData[0].providerId,
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

//microsoft authentication ------------------

export async function getBearerToken(email) {
    try {
        const response = await axios.post("/api/v1/get-bearer-token", {
            email,
        });
        return response.data; //we return the token response
    } catch (error) {
        console.error("Error al verificar el usuario:", error);
        throw error;
    }
}

//we validate the existence of the user
export async function checkIfUserExists(email) {
    try {
        const response = await axios.post("/api/v1/check-user", {
            email,
        });
        return response.data.exists;
    } catch (error) {
        console.error("Error al verificar el usuario:", error);
        return false;
    }
}

//we do the login for the user
export async function loginUser(user) {
    try {
        const loginResponse = await axios.post("/login", {
            email: user.email,
            password: user.uid,
            providerId: user.providerData[0].providerId,
        });
        // we wait and generate the jwt
        const Jwt = await getBearerToken(user.email);

        // Redirect to dashboard with JWT in URL
        window.location.href = `/dashboard?jwt=${Jwt.jwt}`;
    } catch (error) {
        console.error("Error al iniciar sesión:", error);
    }
}

// Function to log in with Microsoft
export function loginWithMicrosoft() {
    const provider = new OAuthProvider("microsoft.com");

    signInWithPopup(auth, provider)
        .then(async function (result) {
            const user = result.user;
            console.log("Usuario autenticado:", user);

            const fullName = user.displayName;
            const [firstName, ...lastNameParts] = fullName.split(" ");
            const lastName = lastNameParts.join(" ");

            try {
                //validate user exit
                const userExist = await checkIfUserExists(user.email);
                //if the user exists we log in
                if (userExist) {
                    console.log("Usuario ya registrado, iniciando sesión...");
                    await loginUser(user);
                } else {
                    console.log("Usuario no registrado, registrando...");
                    //register user,send data
                    const registerResponse = await axios.post("/api/register", {
                        name: firstName,
                        last_name: lastName || "Predeterminado",
                        email: user.email,
                        password: user.uid,
                        current_team_id:
                            user.providerData && user.providerData.length > 0
                                ? user.providerData[0].providerId
                                : null,
                        profile_photo_path: user.PhotoURL,
                        auth_provider: user.providerData[0].providerId,
                    });

                    console.log("Usuario registrado:", registerResponse.data);
                    await loginUser(user);
                }
            } catch (error) {
                console.error("Error en el proceso de autenticación:", error);
            }
        })
        .catch(function (error) {
            console.error("Error al autenticar con Microsoft", error);
        });
}
