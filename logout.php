<?php
require_once 'conection/conecta.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'logout') {
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        
        try {
            // Registrar actividad de logout
            logActivity($pdo, $userId, 'logout', 'Logout manual');
            
            // Eliminar token de remember me si existe
            if (isset($_COOKIE['remember_token'])) {
                $token = $_COOKIE['remember_token'];
                
                // Eliminar de la base de datos
                $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE session_token = ?");
                $stmt->execute([$token]);
                
                // Eliminar cookie
                setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            }
            
            // Actualizar remember_me en users
            $stmt = $pdo->prepare("UPDATE users SET remember_me = FALSE WHERE id = ?");
            $stmt->execute([$userId]);
            
        } catch (PDOException $e) {
            error_log("Error en logout: " . $e->getMessage());
        }
    }
    
    // Destruir sesión
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'Sesión cerrada exitosamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>