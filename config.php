<?php
// config.php - Configuração do Banco de Dados e Sessão

session_start();

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'prapet_db');

// Conectar ao banco de dados
try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Configurações do Site
define('SITE_URL', 'http://localhost/prapet');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Criar diretório de uploads se não existir
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
    mkdir(UPLOAD_DIR . 'pets/', 0777, true);
    mkdir(UPLOAD_DIR . 'laudos/', 0777, true);
}

// Funções Auxiliares
function redirect($url) {
    header("Location: " . SITE_URL . "/" . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function isVeterinario() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'veterinario';
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isUsuario() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'usuario';
}

function getUserData($conn) {
    if (!isLoggedIn()) return null;
    
    $type = $_SESSION['user_type'];
    $id = $_SESSION['user_id'];
    
    if ($type === 'usuario') {
        $stmt = $conn->prepare("SELECT u.*, p.nome as plano_nome FROM usuarios u 
                                LEFT JOIN planos p ON u.id_plano = p.id_plano 
                                WHERE u.id_usuario = ?");
        $stmt->execute([$id]);
    } elseif ($type === 'veterinario') {
        $stmt = $conn->prepare("SELECT * FROM veterinarios WHERE id_veterinario = ?");
        $stmt->execute([$id]);
    } elseif ($type === 'admin') {
        $stmt = $conn->prepare("SELECT * FROM administradores WHERE id_admin = ?");
        $stmt->execute([$id]);
    }
    
    return $stmt->fetch();
}

function verificarLimitePlano($conn, $tipo_limite) {
    if (!isUsuario()) return true; // Veterinários e admins não têm limite
    
    $user = getUserData($conn);
    $id_usuario = $_SESSION['user_id'];
    
    $dataAtual = date('Y-m-01'); // Primeiro dia do mês
    
    switch($tipo_limite) {
        case 'pets':
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pets WHERE id_usuario = ? AND ativo = 1");
            $stmt->execute([$id_usuario]);
            $total = $stmt->fetch()['total'];
            
            $stmt = $conn->prepare("SELECT limite_pets FROM planos WHERE id_plano = ?");
            $stmt->execute([$user['id_plano']]);
            $limite = $stmt->fetch()['limite_pets'];
            
            return $total < $limite;
            
        case 'consultas':
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM consultas c 
                                   INNER JOIN pets p ON c.id_pet = p.id_pet 
                                   WHERE p.id_usuario = ? AND c.data_registro >= ?");
            $stmt->execute([$id_usuario, $dataAtual]);
            $total = $stmt->fetch()['total'];
            
            $stmt = $conn->prepare("SELECT limite_consultas FROM planos WHERE id_plano = ?");
            $stmt->execute([$user['id_plano']]);
            $limite = $stmt->fetch()['limite_consultas'];
            
            return $total < $limite;
            
        case 'laudos':
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM laudos l 
                                   INNER JOIN pets p ON l.id_pet = p.id_pet 
                                   WHERE p.id_usuario = ? AND l.data_registro >= ?");
            $stmt->execute([$id_usuario, $dataAtual]);
            $total = $stmt->fetch()['total'];
            
            $stmt = $conn->prepare("SELECT limite_laudos FROM planos WHERE id_plano = ?");
            $stmt->execute([$user['id_plano']]);
            $limite = $stmt->fetch()['limite_laudos'];
            
            return $total < $limite;
    }
    
    return false;
}
?>