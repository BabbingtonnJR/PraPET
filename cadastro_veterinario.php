<?php
require_once 'config.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $crmv = $_POST['crmv'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';
    $especialidade = $_POST['especialidade'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    
    if (empty($nome) || empty($crmv) || empty($email) || empty($senha) || empty($confirma_senha)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios';
    } elseif ($senha !== $confirma_senha) {
        $erro = 'As senhas não conferem';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter no mínimo 6 caracteres';
    } else {
        try {
            // Verificar se email ou CRMV já existe
            $stmt = $conn->prepare("SELECT id_veterinario FROM veterinarios WHERE email = ? OR crmv = ?");
            $stmt->execute([$email, $crmv]);
            
            if ($stmt->fetch()) {
                $erro = 'Este email ou CRMV já está cadastrado';
            } else {
                $senha_hash = md5($senha); // Em produção, use password_hash()
                
                $stmt = $conn->prepare("INSERT INTO veterinarios (nome, crmv, email, senha, especialidade, telefone) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $crmv, $email, $senha_hash, $especialidade, $telefone]);
                
                $sucesso = 'Cadastro realizado com sucesso! Aguarde a aprovação do administrador para acessar o sistema.';
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao cadastrar: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Veterinário - PraPet</title>
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
            padding: 40px 20px;
        }
        
        .cadastro-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            margin: 0 auto;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group.full {
            grid-column: 1 / -1;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .required {
            color: #e53e3e;
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
        
        .btn-cadastrar {
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
            margin-top: 1rem;
        }
        
        .btn-cadastrar:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 10px;
            border: 1px solid;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border-color: #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border-color: #cfc;
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
        
        .info-box {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #2196f3;
            color: #1976d2;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="cadastro-container">
        <div class="logo">
            <h1>🐾 PraPet</h1>
            <p>Cadastro de Veterinário</p>
        </div>
        
        <div class="info-box">
            <strong>ℹ️ Importante:</strong> Após o cadastro, sua conta será analisada por um administrador antes de ser aprovada.
        </div>
        
        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($sucesso) ?>
                <br><br>
                <a href="login.php" style="color: #2c7a2c; font-weight: bold;">Clique aqui para fazer login</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group full">
                    <label>Nome Completo <span class="required">*</span></label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>CRMV <span class="required">*</span></label>
                        <input type="text" name="crmv" required placeholder="Ex: 12345-SP" value="<?= htmlspecialchars($_POST['crmv'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Especialidade</label>
                        <input type="text" name="especialidade" placeholder="Ex: Clínico Geral" value="<?= htmlspecialchars($_POST['especialidade'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="tel" name="telefone" placeholder="(00) 00000-0000" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Senha <span class="required">*</span></label>
                        <input type="password" name="senha" required placeholder="Mínimo 6 caracteres">
                    </div>
                    
                    <div class="form-group">
                        <label>Confirmar Senha <span class="required">*</span></label>
                        <input type="password" name="confirma_senha" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-cadastrar">Cadastrar como Veterinário</button>
            </form>
        <?php endif; ?>
        
        <div class="links">
            <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
            <p style="margin-top: 0.5rem;"><a href="index.php">← Voltar para início</a></p>
        </div>
    </div>
</body>
</html>