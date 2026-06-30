// js/supabase_auth.js
// Supabase Configuration
// REMPLACEZ 'YOUR_SUPABASE_ANON_KEY' par votre clé "anon" publique depuis Supabase > Settings > API.
const SUPABASE_URL = 'https://egdltwovnapbjgfmjmoi.supabase.co';
const SUPABASE_ANON_KEY = 'sb_publishable_OkgQSdS18pTOf1lNiFYdzw_QJw6ufZx';

const supabaseClient = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

document.addEventListener('DOMContentLoaded', async () => {
    
    // Vérifier s'il s'agit d'une déconnexion
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('mode') === 'logout') {
        await supabaseClient.auth.signOut();
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Vérifier si une session Supabase existe déjà (retour de OAuth ou session locale)
    supabaseClient.auth.getSession().then(({ data: { session } }) => {
        if (session) {
            syncSessionWithBackend(session);
        }
    });
    
    // Écouter les changements d'état d'authentification (ex: retour de Google/GitHub)
    supabaseClient.auth.onAuthStateChange((event, session) => {
        if (event === 'SIGNED_IN' && session) {
            syncSessionWithBackend(session);
        }
    });

    // 1. Inscription
    const signUpForm = document.querySelector('.sign-up-panel form');
    if (signUpForm) {
        signUpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = signUpForm.querySelector('input[name="name"]').value;
            const email = signUpForm.querySelector('input[name="email"]').value;
            const password = signUpForm.querySelector('input[name="password"]').value;
            const confirmPassword = signUpForm.querySelector('input[name="confirm_password"]').value;

            if (password !== confirmPassword) {
                alert("Les mots de passe ne correspondent pas.");
                return;
            }

            const { data, error } = await supabaseClient.auth.signUp({
                email,
                password,
                options: {
                    data: {
                        name: name
                    }
                }
            });

            if (error) {
                alert("Erreur d'inscription : " + error.message);
            } else {
                alert("Inscription réussie ! Veuillez vérifier votre email (si requis) ou patientez pour la connexion automatique.");
            }
        });
    }

    // 2. Connexion
    const signInForm = document.getElementById('signInForm');
    if (signInForm) {
        signInForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = signInForm.querySelector('input[name="email"]').value;
            const password = signInForm.querySelector('input[name="password"]').value;
            const rememberMe = signInForm.querySelector('input[name="remember_me"]')?.checked || false;

            const { data, error } = await supabaseClient.auth.signInWithPassword({
                email,
                password
            });

            if (error) {
                if (error.message.toLowerCase().includes('invalid login credentials')) {
                    alert("Erreur : Identifiants invalides OU compte non migré.\n\nSi vous aviez déjà un compte sur l'ancienne version, vous devez vous INSCRIRE à nouveau avec la même adresse e-mail ou utiliser Google/GitHub pour le récupérer de manière sécurisée.");
                } else {
                    alert("Erreur de connexion : " + error.message);
                }
            } else {
                // Session is active, onAuthStateChange will trigger syncSessionWithBackend
            }
        });
    }

    // 3. Boutons OAuth (Google & GitHub)
    const setupOAuthBtn = (btnClass, provider) => {
        const btns = document.querySelectorAll(btnClass);
        btns.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const { data, error } = await supabaseClient.auth.signInWithOAuth({
                    provider: provider,
                    options: {
                        redirectTo: window.location.origin + window.location.pathname,
                        queryParams: {
                            prompt: 'consent'
                        }
                    }
                });
                if (error) {
                    alert(`Erreur avec ${provider} : ` + error.message);
                }
            });
        });
    };

    setupOAuthBtn('.btn-google', 'google');
    setupOAuthBtn('.btn-github', 'github');

    // 4. Mot de passe oublié
    const forgotPwdForm = document.getElementById('forgotPwdForm');
    if (forgotPwdForm) {
        forgotPwdForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = forgotPwdForm.querySelector('input[name="email"]').value;
            
            const { data, error } = await supabaseClient.auth.resetPasswordForEmail(email, {
                redirectTo: window.location.origin + window.location.pathname + '?mode=reset',
            });

            if (error) {
                alert("Erreur : " + error.message);
            } else {
                alert("Si cet email existe, un lien de réinitialisation vous a été envoyé.");
                document.getElementById('cancelForgotPwd').click();
            }
        });
    }
});

function syncSessionWithBackend(session) {
    // Send token to our PHP backend to create PHP session and sync local users table
    fetch('backend/auth_sync.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            access_token: session.access_token,
            user: session.user
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php';
        } else {
            console.error("Backend sync error: ", data);
            
            // If the token is invalid, clear the local session so it doesn't loop forever
            if (data.error === 'Invalid or expired token') {
                supabaseClient.auth.signOut().then(() => {
                    alert("Votre session a expiré ou est invalide. Veuillez vous reconnecter.");
                    window.location.href = 'login.html';
                });
            } else {
                alert("Erreur lors de la synchronisation de session : " + data.error + "\nHTTP: " + data.debug_http_code + "\ncURL: " + data.debug_curl_error);
            }
        }
    })
    .catch(err => {
        console.error("Fetch error: ", err);
        // Only alert if it's a real network error, don't spam
    });
}
