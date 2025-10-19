<?php
require_once 'config.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $tipo = $_POST['tipo'] ?? 'usuario';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos';
    } else {
        $senha_hash = md5($senha); // Em produção, use password_hash()
        
        try {
            if ($tipo === 'usuario') {
                $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? AND senha = ? AND ativo = 1");
                $stmt->execute([$email, $senha_hash]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id_usuario'];
                    $_SESSION['user_type'] = 'usuario';
                    $_SESSION['user_name'] = $user['nome'];
                    redirect('dashboard.php');
                }
            } elseif ($tipo === 'veterinario') {
                $stmt = $conn->prepare("SELECT * FROM veterinarios WHERE email = ? AND senha = ?");
                $stmt->execute([$email, $senha_hash]);
                $user = $stmt->fetch();
                
                if ($user) {
                    if ($user['status'] !== 'aprovado') {
                        $erro = 'Sua conta ainda não foi aprovada pelo administrador';
                    } else {
                        $_SESSION['user_id'] = $user['id_veterinario'];
                        $_SESSION['user_type'] = 'veterinario';
                        $_SESSION['user_name'] = $user['nome'];
                        redirect('dashboard_vet.php');
                    }
                }
            } elseif ($tipo === 'admin') {
                $stmt = $conn->prepare("SELECT * FROM administradores WHERE email = ? AND senha = ?");
                $stmt->execute([$email, $senha_hash]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id_admin'];
                    $_SESSION['user_type'] = 'admin';
                    $_SESSION['user_name'] = $user['nome'];
                    redirect('dashboard_admin.php');
                }
            }
            
            if (!isset($user) || !$user) {
                $erro = 'Email ou senha incorretos';
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao processar login: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PraPet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
            padding: 3rem;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h1 {
            color: #667eea;
            font-size: 2.5rem;
        }
        
        .logo p {
            color: #666;
            margin-top: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        input, select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 10px;
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .links {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .divider {
            text-align: center;
            margin: 1rem 0;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🐾 PraPet</h1>
            <p>Faça login na sua conta</p>
        </div>
        
        <?php if ($erro): ?>
            <div class="alert"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Tipo de Conta</label>
                <select name="tipo" required>
                    <option value="usuario">Tutor (Usuário)</option>
                    <option value="veterinario">Veterinário</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="seu@email.com">
            </div>
            
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" required placeholder="••••••••">
            </div>
            
            <button type="submit" class="btn-login">Entrar</button>
        </form>
        
        <div class="links">
            <p>Não tem uma conta? 
                <a href="cadastro.php">Cadastre-se como Tutor</a>
            </p>
            <div class="divider">ou</div>
            <p>
                <a href="cadastro_veterinario.php">Cadastre-se como Veterinário</a>
            </p>
            <div class="divider">•</div>
            <p>
                <a href="index.php">← Voltar para início</a>
            </p>
        </div>
    </div>
</body>
</html>