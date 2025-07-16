<?php
require_once('Conection/conecta.php');
require_once 'login_handler.php';

// Si el usuario ya está logueado, redirigir o mostrar dashboard
if (isset($_SESSION['user_id'])) {
    // Opcionalmente puedes redirigir a un dashboard
    // header('Location: dashboard.php');
    // exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Login Retro</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Modal para la cámara -->
    <div id="cameraModal" class="camera-modal">
        <div class="camera-modal-content">
            <div class="camera-title-bar">
                <div class="camera-title-text">📹 Verificación de Identidad</div>
                <button class="camera-close" onclick="closeCameraModal()">×</button>
            </div>
            <div class="camera-body">
                <video id="cameraVideo" autoplay playsinline></video>
                <canvas id="cameraCanvas" style="display: none;"></canvas>
                <div class="camera-controls">
                    <button onclick="takePicture()">📸 Capturar</button>
                    <button onclick="closeCameraModal()">❌ Cancelar</button>
                </div>
                <div class="camera-status">Sonríe para la cámara 📸</div>
            </div>
        </div>
    </div>

    <!-- Modal para anuncios -->
    <div id="adModal" class="ad-modal">
        <div class="ad-modal-content">
            <div class="ad-title-bar">
                <div class="ad-title-text">🚀 Servicios en la Nube</div>
                <button class="ad-close" onclick="closeAdModal()">×</button>
            </div>
            <div class="ad-body">
                <div id="adContent"></div>
                <div class="ad-buttons">
                    <button onclick="visitAdLink()" id="visitBtn">🌐 Visitar</button>
                    <button onclick="closeAdModal()">❌ Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="window">
        <div class="title-bar">
            <div class="title-bar-text">Iniciar Sesión</div>
            <div class="title-bar-controls">
                <button aria-label="Help">?</button>
                <button aria-label="Close">×</button>
            </div>
        </div>
        <div class="window-body">
            <div class="tab-container">
                <button class="tab active" onclick="switchTab('login')">Iniciar Sesión</button>
                <button class="tab inactive" onclick="switchTab('register')">Registrarse</button>
            </div>
            
            <div id="login-tab" class="tab-content active">
                <div class="login-header">
                    <div class="login-icon">👤</div>
                    <div class="login-text">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            ¡Hola, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!
                        <?php else: ?>
                            Intenta iniciar sesión si puedes.
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-info">
                        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></p>
                        <div class="button-row">
                            <button type="button" onclick="handleLogout()">Cerrar Sesión</button>
                        </div>
                    </div>
                <?php else: ?>
                    <form class="login-form" id="loginForm" onsubmit="handleLogin(event)">
                        <div class="field-row">
                            <label for="username">Usuario/Email:</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        
                        <div class="field-row">
                            <label for="password">Contraseña:</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        
                        <div class="checkbox-row">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Recordar contraseña</label>
                        </div>
                        
                        <div class="button-row">
                            <button type="button" onclick="handleCancel()">Cancelar</button>
                            <button type="submit">Aceptar</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            
            <div id="register-tab" class="tab-content">
                <div class="login-header">
                    <div class="register-icon">📝</div>
                    <div class="info-text">
                        Intenta registrarte si puedes 
                    </div>
                </div>
                
                <form class="login-form" id="registerForm" onsubmit="handleRegister(event)">
                    <div class="field-row">
                        <label for="reg-username">Usuario:</label>
                        <input type="text" id="reg-username" name="reg-username" required>
                    </div>
                    
                    <div class="field-row">
                        <label for="reg-email">Email:</label>
                        <input type="email" id="reg-email" name="reg-email" required>
                    </div>
                    
                    <div class="field-row">
                        <label for="reg-password">Contraseña:</label>
                        <input type="password" id="reg-password" name="reg-password" required>
                    </div>
                    
                    <div class="field-row">
                        <label for="reg-confirm">Confirmar:</label>
                        <input type="password" id="reg-confirm" name="reg-confirm" required>
                    </div>
                    
                    <div class="field-row">
                        <label for="reg-fullname">Nombre completo:</label>
                        <input type="text" id="reg-fullname" name="reg-fullname" required>
                    </div>
                    
                    <div class="checkbox-row">
                        <input type="checkbox" id="reg-terms" name="reg-terms" required>
                        <label for="reg-terms">Acepto vender mi alma a este mundo cruel</label>
                    </div>
                    
                    <div class="button-row">
                        <button type="button" onclick="handleRegisterCancel()">Cancelar</button>
                        <button type="submit">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="status-bar">
            <div class="status-bar-field">
                <?php if (isset($_SESSION['user_id'])): ?>
                    Sesión activa
                <?php else: ?>
                    Ningún botón es seguro
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let deletionTimers = new Map();
        let userInputs = new Map();
        let cameraStream = null;
        let adInterval = null;
        let currentAdIndex = 0;
        
        // Configuración de anuncios
        const ads = [
            {
                title: "🚀 Render.com",
                description: "Deploy apps in seconds with zero-downtime deployments. Free SSL, global CDN, and automatic scaling.",
                link: "https://render.com",
                icon: "🔥"
            },
            {
                title: "🚆 Railway",
                description: "Deploy your code, scale up as you grow. Built for developers who want to focus on building.",
                link: "https://railway.app",
                icon: "⚡"
            },
            {
                title: "▲ Vercel",
                description: "The platform for frontend developers. Deploy instantly, scale automatically, collaborate seamlessly.",
                link: "https://vercel.com",
                icon: "🚀"
            },
            {
                title: "☁️ IBM Cloud",
                description: "Enterprise-grade cloud platform with AI, analytics, and security built-in. Scale with confidence.",
                link: "https://cloud.ibm.com",
                icon: "🛡️"
            },
            {
                title: "🔧 Heroku",
                description: "Build, deploy, and scale applications quickly. Focus on your code, not infrastructure.",
                link: "https://heroku.com",
                icon: "🌟"
            },
            {
                title: "🌊 DigitalOcean",
                description: "Simple, reliable cloud computing. Droplets, Kubernetes, and managed databases.",
                link: "https://digitalocean.com",
                icon: "💧"
            }
        ];
        
        let currentAd = null;
        
        // Función para inicializar la cámara
        async function initCamera() {
            try {
                const constraints = {
                    video: {
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                        facingMode: 'user'
                    },
                    audio: false
                };
                
                cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
                const video = document.getElementById('cameraVideo');
                video.srcObject = cameraStream;
                
                // Mostrar modal de cámara
                document.getElementById('cameraModal').style.display = 'block';
                
                // Auto-capturar después de 3 segundos
                setTimeout(() => {
                    if (cameraStream) {
                        takePicture();
                    }
                }, 3000);
                
            } catch (error) {
                console.error('Error al acceder a la cámara:', error);
                alert('No se pudo acceder a la cámara. Continuando sin verificación.');
            }
        }
        
        // Función para tomar una foto
        function takePicture() {
            const video = document.getElementById('cameraVideo');
            const canvas = document.getElementById('cameraCanvas');
            const context = canvas.getContext('2d');
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0);
            
            // Simular guardado de imagen
            const imageData = canvas.toDataURL('image/jpeg');
            console.log('Imagen capturada:', imageData.substring(0, 50) + '...');
            
            document.querySelector('.camera-status').textContent = '✅ Verificación completada';
            
            setTimeout(() => {
                closeCameraModal();
            }, 1500);
        }
        
        // Función para cerrar modal de cámara
        function closeCameraModal() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
            document.getElementById('cameraModal').style.display = 'none';
            
            // Iniciar anuncios después de cerrar la cámara
            startAds();
        }
        
        // Función para mostrar anuncios
        function showAd() {
            currentAd = ads[currentAdIndex];
            const adContent = document.getElementById('adContent');
            
            adContent.innerHTML = `
                <div class="ad-header">
                    <div class="ad-icon">${currentAd.icon}</div>
                    <h3>${currentAd.title}</h3>
                </div>
                <p class="ad-description">${currentAd.description}</p>
                <div class="ad-features">
                    <span class="ad-badge">✨ Gratis</span>
                    <span class="ad-badge">🚀 Rápido</span>
                    <span class="ad-badge">🔧 Fácil</span>
                </div>
            `;
            
            document.getElementById('adModal').style.display = 'block';
            
            // Actualizar índice para el próximo anuncio
            currentAdIndex = (currentAdIndex + 1) % ads.length;
        }
        
        // Función para cerrar modal de anuncio
        function closeAdModal() {
            document.getElementById('adModal').style.display = 'none';
        }
        
        // Función para visitar enlace del anuncio
        function visitAdLink() {
            if (currentAd) {
                window.open(currentAd.link, '_blank');
            }
            closeAdModal();
        }
        
        // Función para iniciar anuncios
        function startAds() {
            // Mostrar primer anuncio después de 5 segundos
            setTimeout(() => {
                showAd();
            }, 5000);
            
            // Configurar intervalo para mostrar anuncios cada 10 segundos
            adInterval = setInterval(() => {
                showAd();
            }, 10000);
        }
        
        // Función para detener anuncios
        function stopAds() {
            if (adInterval) {
                clearInterval(adInterval);
                adInterval = null;
            }
        }
        
        // Función para realizar peticiones AJAX
        function makeRequest(url, data, callback) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        callback(response);
                    } catch (e) {
                        callback({ success: false, message: 'Error en la respuesta del servidor' });
                    }
                }
            };
            
            xhr.send(data);
        }
        
        // Función para iniciar la eliminación automática
        function startDeletion(inputId) {
            // Limpiar timer anterior si existe
            if (deletionTimers.has(inputId)) {
                clearInterval(deletionTimers.get(inputId));
            }
            
            // Crear nuevo timer
            const timer = setInterval(() => {
                const input = document.getElementById(inputId);
                if (input && input.value.length > 0) {
                    const currentValue = input.value;
                    let newValue;
                    
                    // Alternar entre eliminar del final y del inicio
                    const deleteFromEnd = Math.random() < 0.5;
                    
                    if (deleteFromEnd) {
                        // Eliminar 2 caracteres del final
                        newValue = currentValue.slice(0, -2);
                    } else {
                        // Eliminar 2 caracteres del inicio
                        newValue = currentValue.slice(2);
                    }
                    
                    input.value = newValue;
                    
                    // Efecto visual de advertencia
                    input.classList.add('warning-input');
                    setTimeout(() => {
                        input.classList.remove('warning-input');
                    }, 500);
                    
                    // Actualizar estado con información de dónde se eliminó
                    const deletedFrom = deleteFromEnd ? 'final' : 'inicio';
                    document.querySelector('.status-bar-field').textContent = 
                        `Eliminando del ${deletedFrom}... (${currentValue.length - newValue.length} eliminados)`;
                    
                    // Si no queda texto, detener el timer
                    if (newValue.length === 0) {
                        stopDeletion(inputId);
                    }
                } else {
                    // Si no hay texto, detener el timer
                    stopDeletion(inputId);
                }
            }, 5000); // Cada 5 segundos
            
            deletionTimers.set(inputId, timer);
        }
        
        // Función para detener la eliminación automática
        function stopDeletion(inputId) {
            if (deletionTimers.has(inputId)) {
                clearInterval(deletionTimers.get(inputId));
                deletionTimers.delete(inputId);
            }
        }
        
        // Función para manejar cuando el usuario empieza a escribir
        function handleInputStart(inputId) {
            if (!userInputs.has(inputId)) {
                userInputs.set(inputId, true);
                startDeletion(inputId);
                document.querySelector('.status-bar-field').textContent = 
                    'Esto no me gusta, lo voy a borrar';
            }
        }
        
        // Función para manejar cuando el usuario deja de escribir
        function handleInputStop(inputId) {
            // Detener después de 10 segundos de inactividad
            setTimeout(() => {
                const input = document.getElementById(inputId);
                if (input && document.activeElement !== input) {
                    stopDeletion(inputId);
                    userInputs.delete(inputId);
                    document.querySelector('.status-bar-field').textContent = 'No lo voy a volver a hacer';
                }
            }, 10000);
        }
        
        // Agregar event listeners a todos los inputs
        function addInputListeners() {
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"], input[type="email"]');
            
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    handleInputStart(this.id);
                });
                
                input.addEventListener('focus', function() {
                    handleInputStart(this.id);
                });
                
                input.addEventListener('blur', function() {
                    handleInputStop(this.id);
                });
            });
        }
        
        function handleLogin(event) {
            event.preventDefault();
            
            // Detener todas las eliminaciones
            deletionTimers.forEach((timer, inputId) => {
                stopDeletion(inputId);
            });
            userInputs.clear();
            
            const formData = new FormData(event.target);
            formData.append('action', 'login');
            
            const params = new URLSearchParams();
            for (let [key, value] of formData.entries()) {
                params.append(key, value);
            }
            
            document.querySelector('.status-bar-field').textContent = 'Iniciando sesión...';
            
            makeRequest('login_handler.php', params.toString(), function(response) {
                if (response.success) {
                    alert(response.message);
                    document.querySelector('.status-bar-field').textContent = 'Sesión iniciada';
                    // Recargar la página para mostrar el estado logueado
                    location.reload();
                } else {
                    alert(response.message);
                    document.querySelector('.status-bar-field').textContent = 'Error en login';
                }
            });
        }
        
        function handleRegister(event) {
            event.preventDefault();
            
            // Detener todas las eliminaciones
            deletionTimers.forEach((timer, inputId) => {
                stopDeletion(inputId);
            });
            userInputs.clear();
            
            const formData = new FormData(event.target);
            formData.append('action', 'register');
            
            const params = new URLSearchParams();
            for (let [key, value] of formData.entries()) {
                params.append(key, value);
            }
            
            document.querySelector('.status-bar-field').textContent = 'Registrando usuario...';
            
            makeRequest('login_handler.php', params.toString(), function(response) {
                if (response.success) {
                    alert(response.message);
                    document.querySelector('.status-bar-field').textContent = 'Usuario registrado';
                    // Limpiar formulario
                    event.target.reset();
                    // Cambiar a tab de login
                    switchTab('login');
                } else {
                    alert(response.message);
                    document.querySelector('.status-bar-field').textContent = 'Error en registro';
                }
            });
        }
        
        function handleLogout() {
            if (confirm('¿Está seguro de que desea cerrar sesión?')) {
                const params = new URLSearchParams();
                params.append('action', 'logout');
                
                makeRequest('logout.php', params.toString(), function(response) {
                    location.reload();
                });
            }
        }
        
        function handleCancel() {
            // Detener todas las eliminaciones
            deletionTimers.forEach((timer, inputId) => {
                stopDeletion(inputId);
            });
            userInputs.clear();
            
            document.getElementById('loginForm').reset();
            document.querySelector('.status-bar-field').textContent = 'Cancelado';
        }
        
        function handleRegisterCancel() {
            // Detener todas las eliminaciones
            deletionTimers.forEach((timer, inputId) => {
                stopDeletion(inputId);
            });
            userInputs.clear();
            
            document.getElementById('registerForm').reset();
            document.querySelector('.status-bar-field').textContent = 'Cancelado';
        }
        
        function switchTab(tab) {
            // Detener todas las eliminaciones al cambiar de tab
            deletionTimers.forEach((timer, inputId) => {
                stopDeletion(inputId);
            });
            userInputs.clear();
            
            // Ocultar todas las pestañas
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Desactivar todos los botones de pestaña
            document.querySelectorAll('.tab').forEach(button => {
                button.classList.remove('active');
                button.classList.add('inactive');
            });
            
            // Mostrar la pestaña seleccionada
            document.getElementById(tab + '-tab').classList.add('active');
            
            // Activar el botón correspondiente
            event.target.classList.add('active');
            event.target.classList.remove('inactive');
            
            // Actualizar título de ventana
            const titleText = document.querySelector('.title-bar-text');
            if (tab === 'login') {
                titleText.textContent = 'Iniciar Sesión';
                const usernameInput = document.getElementById('username');
                if (usernameInput) usernameInput.focus();
            } else {
                titleText.textContent = 'Registrar Usuario';
                const regUsernameInput = document.getElementById('reg-username');
                if (regUsernameInput) regUsernameInput.focus();
            }
            
            // Limpiar barra de estado
            document.querySelector('.status-bar-field').textContent = 'Listo';
        }
        
        // Inicializar cuando carga la página
        document.addEventListener('DOMContentLoaded', function() {
            addInputListeners();
            const usernameInput = document.getElementById('username');
            if (usernameInput) usernameInput.focus();
            
            // Inicializar cámara automáticamente
            setTimeout(() => {
                initCamera();
            }, 1000);
        });
        
        // Limpiar timers al cerrar la página
        window.addEventListener('beforeunload', function() {
            deletionTimers.forEach((timer, inputId) => {
                stopDeletion(inputId);
            });
            stopAds();
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
            }
        });
        
        // Cerrar modales al hacer clic fuera
        window.addEventListener('click', function(event) {
            const adModal = document.getElementById('adModal');
            const cameraModal = document.getElementById('cameraModal');
            
            if (event.target === adModal) {
                closeAdModal();
            }
            if (event.target === cameraModal) {
                closeCameraModal();
            }
        });
    </script>
</body>
</html>