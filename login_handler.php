<?php
require_once('Conection/conecta.php');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'login') {
        handleLogin();
    } elseif ($action === 'register') {
        handleRegister();
    }
}

function handleLogin() {
    global $pdo;
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos.']);
        return;
    }
    
    try {
        // Buscar usuario por nombre de usuario o email
        $stmt = $pdo->prepare("
            SELECT id, username, email, password_hash, first_name, last_name, is_active 
            FROM users 
            WHERE (username = ? OR email = ?) AND is_active = TRUE
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password_hash'])) {
            // Login exitoso
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            
            // Actualizar último login
            $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Manejar "recordar contraseña"
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                $stmt = $pdo->prepare("
                    INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address, user_agent)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user['id'],
                    $token,
                    $expiresAt,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                // Establecer cookie
                setcookie('remember_token', $token, strtotime('+30 days'), '/', '', false, true);
                
                // Actualizar remember_me en la base de datos
                $stmt = $pdo->prepare("UPDATE users SET remember_me = TRUE WHERE id = ?");
                $stmt->execute([$user['id']]);
            }
            
            // Registrar actividad
            logActivity($pdo, $user['id'], 'login', 'Login exitoso');
            
            echo json_encode([
                'success' => true, 
                'message' => '¡Bienvenido, ' . $user['first_name'] . ' ' . $user['last_name'] . '!',
                'user' => [
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name']
                ]
            ]);
        } else {
            // Login fallido
            echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos.']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error del servidor. Intente más tarde.']);
        error_log("Error en login: " . $e->getMessage());
    }
}

function handleRegister() {
    global $pdo;
    
    $username = trim($_POST['reg-username'] ?? '');
    $email = trim($_POST['reg-email'] ?? '');
    $password = $_POST['reg-password'] ?? '';
    $confirmPassword = $_POST['reg-confirm'] ?? '';
    $fullname = trim($_POST['reg-fullname'] ?? '');
    $terms = isset($_POST['reg-terms']);
    
    // Validaciones
    if (empty($username) || empty($email) || empty($password) || empty($fullname)) {
        echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos.']);
        return;
    }
    
    if (!$terms) {
        echo json_encode(['success' => false, 'message' => 'Debe aceptar los términos y condiciones.']);
        return;
    }
    
    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
        return;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
        return;
    }
    
    if (strlen($username) < 3) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario debe tener al menos 3 caracteres.']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'El formato del email no es válido.']);
        return;
    }
    
    try {
        // Verificar si el usuario o email ya existen
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El usuario o email ya están registrados.']);
            return;
        }
        
        // Separar nombre y apellido
        $nameParts = explode(' ', $fullname, 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
        
        // Hashear contraseña
        $hashedPassword = hashPassword($password);
        
        // Insertar usuario
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, first_name, last_name)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName]);
        
        $userId = $pdo->lastInsertId();
        
        // Registrar actividad
        logActivity($pdo, $userId, 'register', 'Usuario registrado exitosamente');
        
        echo json_encode([
            'success' => true, 
            'message' => '¡Usuario ' . $username . ' registrado exitosamente!',
            'user' => [
                'username' => $username,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName
            ]
        ]);
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23505) { // Unique violation
            echo json_encode(['success' => false, 'message' => 'El usuario o email ya están registrados.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error del servidor. Intente más tarde.']);
            error_log("Error en registro: " . $e->getMessage());
        }
    }
}

// Función para verificar sesión recordada
function checkRememberMe() {
    global $pdo;
    
    if (isset($_COOKIE['remember_token']) && !isset($_SESSION['user_id'])) {
        $token = $_COOKIE['remember_token'];
        
        try {
            $stmt = $pdo->prepare("
                SELECT u.id, u.username, u.email, u.first_name, u.last_name
                FROM users u
                INNER JOIN user_sessions s ON u.id = s.user_id
                WHERE s.session_token = ? AND s.expires_at > CURRENT_TIMESTAMP
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                
                // Actualizar último login
                $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Registrar actividad
                logActivity($pdo, $user['id'], 'login', 'Login automático via remember token');
            }
        } catch (PDOException $e) {
            error_log("Error en remember me: " . $e->getMessage());
        }
    }
}

// Verificar sesión recordada si no es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    checkRememberMe();
}
?>