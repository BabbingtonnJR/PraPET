<?php
require_once 'config.php';
requireLogin();

$id_comunidade = $_GET['comunidade'] ?? 0;
$tipo_usuario = isVeterinario() ? 'veterinario' : 'usuario';
$user_id = $_SESSION['user_id'];

// Verificar se é membro da comunidade
$stmt = $conn->prepare("SELECT * FROM membros_comunidade WHERE id_comunidade = ? AND id_usuario = ? AND tipo_usuario = ?");
$stmt->execute([$id_comunidade, $user_id, $tipo_usuario]);
$is_membro = $stmt->fetch();

if (!$is_membro) {
    redirect('ver_comunidade.php?id=' . $id_comunidade);
}

// Buscar comunidade
$stmt = $conn->prepare("SELECT * FROM comunidades WHERE id_comunidade = ?");
$stmt->execute([$id_comunidade]);
$comunidade = $stmt->fetch();

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $conteudo = $_POST['conteudo'] ?? '';
    
    if (empty($titulo) || empty($conteudo)) {
        $erro = 'Por favor, preencha todos os campos';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO posts (id_autor, tipo_autor, id_comunidade, titulo, conteudo, status) 
                                   VALUES (?, ?, ?, ?, ?, 'pendente')");
            $stmt->execute([$user_id, $tipo_usuario, $id_comunidade, $titulo, $conteudo]);
            
            $sucesso = 'Post criado com sucesso! Aguardando aprovação do moderador.';
            header("refresh:3;url=ver_comunidade.php?id=$id_comunidade");
        } catch (PDOException $e) {
            $erro = 'Erro ao criar post: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Post - PraPet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
        }
        
        nav {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }
        
        nav ul {
            list-style: none;
            display: flex;
            gap: 2rem;
        }
        
        nav a {
            color: white;
            text-decoration: none;
        }
        
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .page-title {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #666;
            margin-bottom: 2rem;
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
        
        .required {
            color: #e53e3e;
        }
        
        input, textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
            font-family: inherit;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        textarea {
            resize: vertical;
            min-height: 250px;
        }
        
        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            color: #856404;
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
            text-decoration: none;
            text-align: center;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
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
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🐾 PraPet</a>
            <ul>
                <li><a href="ver_comunidade.php?id=<?= $id_comunidade ?>">← Voltar</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="form-card">
            <h1 class="page-title">📝 Criar Novo Post</h1>
            <p class="page-subtitle">Compartilhe em: <?= htmlspecialchars($comunidade['nome']) ?></p>
            
            <div class="info-box">
                <strong>⚠️ Atenção:</strong> Seu post será analisado por um moderador antes de ser publicado na comunidade.
            </div>
            
            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            
            <?php if ($sucesso): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($sucesso) ?>
                    <br>Redirecionando para a comunidade...
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Título do Post <span class="required">*</span></label>
                    <input type="text" name="titulo" required placeholder="Digite um título chamativo..." maxlength="255">
                </div>
                
                <div class="form-group">
                    <label>Conteúdo <span class="required">*</span></label>
                    <textarea name="conteudo" required placeholder="Compartilhe sua experiência, dúvida ou dica..."></textarea>
                </div>
                
                <div class="btn-group">
                    <a href="ver_comunidade.php?id=<?= $id_comunidade ?>" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Publicar Post</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>