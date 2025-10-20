<?php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    redirect('index.php');
}

$erro = '';
$sucesso = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['criar'])) {
        $nome = $_POST['nome'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $categoria = $_POST['categoria'] ?? '';
        
        if (empty($nome) || empty($descricao)) {
            $erro = 'Preencha todos os campos obrigatórios';
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO comunidades (nome, descricao, categoria) VALUES (?, ?, ?)");
                $stmt->execute([$nome, $descricao, $categoria]);
                $sucesso = 'Comunidade criada com sucesso!';
            } catch (PDOException $e) {
                $erro = 'Erro ao criar comunidade: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['desativar'])) {
        $id_comunidade = $_POST['id_comunidade'] ?? 0;
        try {
            $stmt = $conn->prepare("UPDATE comunidades SET ativa = 0 WHERE id_comunidade = ?");
            $stmt->execute([$id_comunidade]);
            $sucesso = 'Comunidade desativada!';
        } catch (PDOException $e) {
            $erro = 'Erro ao desativar comunidade';
        }
    } elseif (isset($_POST['ativar'])) {
        $id_comunidade = $_POST['id_comunidade'] ?? 0;
        try {
            $stmt = $conn->prepare("UPDATE comunidades SET ativa = 1 WHERE id_comunidade = ?");
            $stmt->execute([$id_comunidade]);
            $sucesso = 'Comunidade ativada!';
        } catch (PDOException $e) {
            $erro = 'Erro ao ativar comunidade';
        }
    }
}

// Buscar comunidades
$stmt = $conn->query("SELECT * FROM comunidades ORDER BY ativa DESC, data_criacao DESC");
$comunidades = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Comunidades - PraPet</title>
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
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-size: 2rem;
            color: #667eea;
        }
        
        .btn-new {
            padding: 0.8rem 1.5rem;
            background: #4caf50;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.3s;
        }
        
        .btn-new:hover {
            transform: translateY(-2px);
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
        
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: none;
        }
        
        .form-card.active {
            display: block;
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
        
        input, select, textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
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
        
        .communities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .community-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .community-card.inactive {
            opacity: 0.6;
            background: #f5f5f5;
        }
        
        .community-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .community-name {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.3rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        
        .status-ativa {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inativa {
            background: #f8d7da;
            color: #721c24;
        }
        
        .community-category {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        
        .community-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .community-stats {
            display: flex;
            gap: 1rem;
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .community-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-small {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        .btn-small:hover {
            opacity: 0.8;
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-success {
            background: #4caf50;
            color: white;
        }
        
        .btn-info {
            background: #2196f3;
            color: white;
        }
    </style>
    <script>
        function toggleForm() {
            const form = document.getElementById('formNova');
            form.classList.toggle('active');
        }
    </script>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🐾 PraPet</a>
            <ul>
                <li><a href="dashboard_admin.php">← Dashboard</a></li>
                <li><a href="gerenciar_veterinarios.php">Veterinários</a></li>
                <li><a href="gerenciar_posts.php">Posts</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">🏘️ Gerenciar Comunidades</h1>
            <button onclick="toggleForm()" class="btn-new">+ Nova Comunidade</button>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <div id="formNova" class="form-card">
            <h2 style="margin-bottom: 1.5rem; color: #667eea;">Criar Nova Comunidade</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nome da Comunidade <span class="required">*</span></label>
                    <input type="text" name="nome" required placeholder="Ex: Tutores de Cachorros">
                </div>
                
                <div class="form-group">
                    <label>Categoria</label>
                    <select name="categoria">
                        <option value="">Selecione</option>
                        <option value="Cachorros">Cachorros</option>
                        <option value="Gatos">Gatos</option>
                        <option value="Pássaros">Pássaros</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Descrição <span class="required">*</span></label>
                    <textarea name="descricao" required placeholder="Descreva o propósito da comunidade..."></textarea>
                </div>
                
                <div class="btn-group">
                    <button type="button" onclick="toggleForm()" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" name="criar" class="btn btn-primary">Criar Comunidade</button>
                </div>
            </form>
        </div>

        <div class="communities-grid">
            <?php foreach ($comunidades as $comunidade): ?>
                <div class="community-card <?= $comunidade['ativa'] ? '' : 'inactive' ?>">
                    <div class="community-header">
                        <div>
                            <div class="community-name"><?= htmlspecialchars($comunidade['nome']) ?></div>
                            <?php if ($comunidade['categoria']): ?>
                                <span class="community-category"><?= htmlspecialchars($comunidade['categoria']) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="status-badge status-<?= $comunidade['ativa'] ? 'ativa' : 'inativa' ?>">
                            <?= $comunidade['ativa'] ? 'Ativa' : 'Inativa' ?>
                        </span>
                    </div>
                    
                    <div class="community-description">
                        <?= htmlspecialchars($comunidade['descricao']) ?>
                    </div>
                    
                    <div class="community-stats">
                        <span>👥 <?= $comunidade['total_membros'] ?> membros</span>
                        <span>📅 <?= date('d/m/Y', strtotime($comunidade['data_criacao'])) ?></span>
                    </div>
                    
                    <div class="community-actions">
                        <a href="ver_comunidade.php?id=<?= $comunidade['id_comunidade'] ?>" class="btn-small btn-info">
                            👁️ Ver
                        </a>
                        <?php if ($comunidade['ativa']): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id_comunidade" value="<?= $comunidade['id_comunidade'] ?>">
                                <button type="submit" name="desativar" class="btn-small btn-danger" 
                                        onclick="return confirm('Desativar esta comunidade?')">
                                    🚫 Desativar
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id_comunidade" value="<?= $comunidade['id_comunidade'] ?>">
                                <button type="submit" name="ativar" class="btn-small btn-success">
                                    ✓ Ativar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>