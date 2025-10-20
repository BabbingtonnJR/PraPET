<?php
require_once 'config.php';
requireLogin();

$id_comunidade = $_GET['id'] ?? 0;

// Buscar comunidade
$stmt = $conn->prepare("SELECT * FROM comunidades WHERE id_comunidade = ? AND ativa = 1");
$stmt->execute([$id_comunidade]);
$comunidade = $stmt->fetch();

if (!$comunidade) {
    redirect('comunidade.php');
}

// Verificar se é membro
$tipo_usuario = isVeterinario() ? 'veterinario' : 'usuario';
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM membros_comunidade WHERE id_comunidade = ? AND id_usuario = ? AND tipo_usuario = ?");
$stmt->execute([$id_comunidade, $user_id, $tipo_usuario]);
$is_membro = $stmt->fetch();

// Processar entrada na comunidade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entrar'])) {
    if (!$is_membro) {
        try {
            $stmt = $conn->prepare("INSERT INTO membros_comunidade (id_comunidade, id_usuario, tipo_usuario) VALUES (?, ?, ?)");
            $stmt->execute([$id_comunidade, $user_id, $tipo_usuario]);
            
            // Atualizar total de membros
            $stmt = $conn->prepare("UPDATE comunidades SET total_membros = total_membros + 1 WHERE id_comunidade = ?");
            $stmt->execute([$id_comunidade]);
            
            header("refresh:0");
        } catch (PDOException $e) {
            $erro = 'Erro ao entrar na comunidade';
        }
    }
}

// Buscar posts aprovados
$stmt = $conn->prepare("SELECT p.*, 
                       CASE 
                         WHEN p.tipo_autor = 'usuario' THEN u.nome
                         WHEN p.tipo_autor = 'veterinario' THEN v.nome
                       END as autor_nome
                       FROM posts p
                       LEFT JOIN usuarios u ON p.tipo_autor = 'usuario' AND p.id_autor = u.id_usuario
                       LEFT JOIN veterinarios v ON p.tipo_autor = 'veterinario' AND p.id_autor = v.id_veterinario
                       WHERE p.id_comunidade = ? AND p.status = 'aprovado'
                       ORDER BY p.data_postagem DESC
                       LIMIT 20");
$stmt->execute([$id_comunidade]);
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($comunidade['nome']) ?> - PraPet</title>
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
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .community-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .community-title {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .community-meta {
            display: flex;
            align-items: center;
            gap: 2rem;
            color: #666;
            margin-bottom: 1rem;
        }
        
        .community-description {
            color: #666;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }
        
        .btn-join {
            padding: 0.8rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn-join:hover {
            transform: translateY(-2px);
        }
        
        .btn-member {
            background: #4caf50;
        }
        
        .action-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-new-post {
            padding: 0.8rem 1.5rem;
            background: #4caf50;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .btn-new-post:hover {
            opacity: 0.9;
        }
        
        .posts-section {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .post-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .post-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .author-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .author-info h3 {
            color: #333;
            font-size: 1.1rem;
        }
        
        .author-info p {
            color: #999;
            font-size: 0.85rem;
        }
        
        .post-date {
            color: #999;
            font-size: 0.85rem;
        }
        
        .post-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .post-content {
            color: #666;
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        
        .post-stats {
            display: flex;
            gap: 2rem;
            color: #999;
            font-size: 0.9rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .empty-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🐾 PraPet</a>
            <ul>
                <li><a href="comunidade.php">← Comunidades</a></li>
                <?php if (isUsuario()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php elseif (isVeterinario()): ?>
                    <li><a href="dashboard_vet.php">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="community-header">
            <h1 class="community-title"><?= htmlspecialchars($comunidade['nome']) ?></h1>
            
            <div class="community-meta">
                <?php if ($comunidade['categoria']): ?>
                    <span>📁 <?= htmlspecialchars($comunidade['categoria']) ?></span>
                <?php endif; ?>
                <span>👥 <?= $comunidade['total_membros'] ?> membros</span>
            </div>
            
            <div class="community-description">
                <?= htmlspecialchars($comunidade['descricao']) ?>
            </div>
            
            <?php if (!$is_membro): ?>
                <form method="POST">
                    <button type="submit" name="entrar" class="btn-join">Entrar na Comunidade</button>
                </form>
            <?php else: ?>
                <button class="btn-join btn-member" disabled>✓ Você é membro</button>
            <?php endif; ?>
        </div>

        <?php if ($is_membro): ?>
            <div class="action-bar">
                <h3 style="color: #333;">Posts da Comunidade</h3>
                <a href="criar_post.php?comunidade=<?= $id_comunidade ?>" class="btn-new-post">
                    ➕ Criar Post
                </a>
            </div>
        <?php endif; ?>

        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <h3>Nenhum post ainda</h3>
                <p style="color: #666; margin-top: 0.5rem;">
                    <?php if ($is_membro): ?>
                        Seja o primeiro a criar um post!
                    <?php else: ?>
                        Entre na comunidade para ver os posts
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="posts-section">
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <div class="post-author">
                                <div class="author-avatar">
                                    <?= $post['tipo_autor'] === 'veterinario' ? '👨‍⚕️' : '👤' ?>
                                </div>
                                <div class="author-info">
                                    <h3><?= htmlspecialchars($post['autor_nome']) ?></h3>
                                    <p><?= ucfirst($post['tipo_autor']) ?></p>
                                </div>
                            </div>
                            <div class="post-date">
                                <?= date('d/m/Y H:i', strtotime($post['data_postagem'])) ?>
                            </div>
                        </div>
                        
                        <h2 class="post-title"><?= htmlspecialchars($post['titulo']) ?></h2>
                        
                        <div class="post-content">
                            <?= nl2br(htmlspecialchars($post['conteudo'])) ?>
                        </div>
                        
                        <div class="post-stats">
                            <span>👁️ <?= $post['visualizacoes'] ?> visualizações</span>
                            <a href="ver_post.php?id=<?= $post['id_post'] ?>" style="color: #667eea; text-decoration: none; font-weight: bold;">
                                Ver mais →
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 