<?php
require_once 'conexion.php';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
 <link rel="stylesheet" href="style.css">
<body>
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
                    Intenta iniciar sesión si puedes.
                </div>
            </div>
            
            <form class="login-form" onsubmit="handleLogin(event)">
                <div class="field-row">
                    <label for="username">Usuario:</label>
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
        </div>
        
            <div id="register-tab" class="tab-content">
                <div class="login-header">
                    <div class="register-icon">📝</div>
                    <div class="info-text">
                        Intenta registrarte si puedes 
                    </div>
                </div>
                
                <form class="login-form" onsubmit="handleRegister(event)">
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
                        <button type="button" onclick="handleRegisterCancel()">Registrarse</button>
                        <button type="submit">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="status-bar">
            <div class="status-bar-field">Ningún botón es seguro</div>
        </div>
    </div>

    <script>
        // Variables para manejar la eliminación automática
        let deletionTimers = new Map();
        let userInputs = new Map();
        
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
                    document.querySelector('.status-bar-field').textContent = 'No lo voy a volver a hacer   ';
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
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            if (username && password) {
                // Simular autenticación
                document.querySelector('.status-bar-field').textContent = 'Iniciando sesión...';
                
                setTimeout(() => {
                    alert(`¡Bienvenido, ${username}!`);
                    document.querySelector('.status-bar-field').textContent = 'Sesión iniciada';
                }, 1000);
            } else {
                alert('Por favor, complete todos los campos.');
            }
        }
        
        function handleCancel() {
            // Detener todas las eliminaciones
            deletionTimers.forEach((timer, inputId) => {
                stopDeletion(inputId);
            });
            userInputs.clear();
            
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('remember').checked = false;
            document.querySelector('.status-bar-field').textContent = 'Cancelado';
        }
        
        function handleRegister(event) {
            event.preventDefault();
            
            // Detener todas las eliminaciones
            deletionTimers.forEach((timer, inputId) => {
                stopDeletion(inputId);
            });
            userInputs.clear();
            
            const username = document.getElementById('reg-username').value;
            const email = document.getElementById('reg-email').value;
            const password = document.getElementById('reg-password').value;
            const confirmPassword = document.getElementById('reg-confirm').value;
            const fullname = document.getElementById('reg-fullname').value;
            const terms = document.getElementById('reg-terms').checked;
            
            if (!terms) {
                alert('Debe aceptar los términos y condiciones.');
                return;
            }
            
            if (password !== confirmPassword) {
                alert('Las contraseñas no coinciden.');
                return;
            }
            
            if (password.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres.');
                return;
            }
            
            if (username && email && password && fullname) {
                // Simular registro
                document.querySelector('.status-bar-field').textContent = 'Registrando usuario...';
                
                setTimeout(() => {
                    alert(`¡Usuario ${username} registrado exitosamente!`);
                    document.querySelector('.status-bar-field').textContent = 'Usuario registrado';
                    // Limpiar formulario
                    document.getElementById('reg-username').value = '';
                    document.getElementById('reg-email').value = '';
                    document.getElementById('reg-password').value = '';
                    document.getElementById('reg-confirm').value = '';
                    document.getElementById('reg-fullname').value = '';
                    document.getElementById('reg-terms').checked = false;
                    // Cambiar a tab de login
                    switchTab('login');
                }, 1500);
            } else {
                alert('Por favor, complete todos los campos.');
            }
        }
        
        function handleRegisterCancel() {
            // Detener todas las eliminaciones
            deletionTimers.forEach((timer, inputId) => {
                stopDeletion(inputId);
            });
            userInputs.clear();
            
            document.getElementById('reg-username').value = '';
            document.getElementById('reg-email').value = '';
            document.getElementById('reg-password').value = '';
            document.getElementById('reg-confirm').value = '';
            document.getElementById('reg-fullname').value = '';
            document.getElementById('reg-terms').checked = false;
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
                document.getElementById('username').focus();
            } else {
                titleText.textContent = 'Registrar Usuario';
                document.getElementById('reg-username').focus();
            }
            
            // Limpiar barra de estado
            document.querySelector('.status-bar-field').textContent = 'Listo';
        }
        
        // Inicializar cuando carga la página
        document.addEventListener('DOMContentLoaded', function() {
            addInputListeners();
            document.getElementById('username').focus();
        });
        
        // Limpiar timers al cerrar la página
        window.addEventListener('beforeunload', function() {
            deletionTimers.forEach((timer, inputId) => {
                stopDeletion(inputId);
            });
        });
    </script>

</body>
</html>