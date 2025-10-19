<?php
require_once 'config.php';
requireLogin();

$id_post = $_GET['id'] ?? 0;

// Buscar post
$stmt = $conn->prepare("SELECT p.*, 
                       CASE 
                         WHEN p.tipo_autor = 'usuario' THEN u.nome
                         WHEN p.tipo_autor = 'veterinario' THEN v.nome
                       END as autor_nome,
                       c.nome as comunidade_nome,
                       c.id_comunidade
                       FROM posts p
                       LEFT JOIN usuarios u ON p.tipo_autor = 'usuario' AND p.id_autor = u.id_usuario
                       LEFT JOIN veterinarios v ON p.tipo_autor = 'veterinario' AND p.id_autor = v.id_veterinario
                       LEFT JOIN comunidades c ON p.id_comunidade = c.id_comunidade
                       WHERE p.id_post = ? AND p.status = 'aprovado'");
$stmt->execute([$id_post]);
$post = $stmt->fetch();

if (!$post) {
    redirect('comunidade.php');
}

// Atualizar visualizações
$stmt = $conn->prepare("UPDATE posts SET visualizacoes = visualizacoes + 1 WHERE id_post = ?");
$stmt->execute([$id_post]);

// Buscar comentários
$stmt = $conn->prepare("SELECT c.*, 
                       CASE 
                         WHEN c.tipo_autor = 'usuario' THEN u.nome
                         WHEN c.tipo_autor = 'veterinario' THEN v.nome
                       END as autor_nome
                       FROM comentarios c
                       LEFT JOIN usuarios u ON c.tipo_autor = 'usuario' AND c.id_autor = u.id_usuario
                       LEFT JOIN veterinarios v ON c.tipo_autor = 'veterinario' AND c.id_autor = v.id_veterinario
                       WHERE c.id_post = ?
                       ORDER BY c.data_comentario ASC");
$stmt->execute([$id_post]);
$comentarios = $stmt->fetchAll();

// Processar novo comentário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {
    $conteudo = $_POST['conteudo'] ?? '';
    $tipo_usuario = isVeterinario() ? 'veterinario' : 'usuario';
    $user_id = $_SESSION['user_id'];
    
    if (!empty($conteudo)) {
        try {
            $stmt = $conn->prepare("INSERT INTO comentarios (id_post, id_autor, tipo_autor, conteudo) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_post, $user_id, $tipo_usuario, $conteudo]);
            header("refresh:0");
        } catch (PDOException $e) {
            $erro = 'Erro ao adicionar comentário';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['titulo']) ?> - PraPet</title>
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
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .post-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .post-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .author-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        
        .author-info h2 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 0.3rem;
        }
        
        .author-info p {
            color: #999;
            font-size: 0.9rem;
        }
        
        .post-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .post-meta {
            display: flex;
            gap: 2rem;
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        
        .post-content {
            color: #444;
            line-height: 1.8;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .comments-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1.5rem;
        }
        
        .comment-form {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 1rem;
        }
        
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-comment {
            padding: 0.8rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn-comment:hover {
            transform: translateY(-2px);
        }
        
        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .comment-item {
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
        }
        
        .comment-author {
            font-weight: bold;
            color: #333;
        }
        
        .comment-date {
            color: #999;
            font-size: 0.85rem;
        }
        
        .comment-content {
            color: #666;
            line-height: 1.6;
        }
        
        .empty-comments {
            text-align: center;
            padding: 2rem;
            color: #999;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🐾 PraPet</a>
            <ul>
                <li><a href="ver_comunidade.php?id=<?= $post['id_comunidade'] ?>">← Voltar</a></li>
                <li><a href="comunidade.php">Comunidades</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="post-card">
            <div class="post-header">
                <div class="author-avatar">
                    <?= $post['tipo_autor'] === 'veterinario' ? '👨‍⚕️' : '👤' ?>
                </div>
                <div class="author-info">
                    <h2><?= htmlspecialchars($post['autor_nome']) ?></h2>
                    <p><?= ucfirst($post['tipo_autor']) ?> • <?= htmlspecialchars($post['comunidade_nome']) ?></p>
                </div>
            </div>
            
            <h1 class="post-title"><?= htmlspecialchars($post['titulo']) ?></h1>
            
            <div class="post-meta">
                <span>📅 <?= date('d/m/Y H:i', strtotime($post['data_postagem'])) ?></span>
                <span>👁️ <?= $post['visualizacoes'] ?> visualizações</span>
                <span>💬 <?= count($comentarios) ?> comentários</span>
            </div>
            
            <div class="post-content">
                <?= nl2br(htmlspecialchars($post['conteudo'])) ?>
            </div>
        </div>

        <div class="comments-section">
            <h3 class="section-title">💬 Comentários (<?= count($comentarios) ?>)</h3>
            
            <div class="comment-form">
                <form method="POST">
                    <textarea name="conteudo" placeholder="Adicione seu comentário..." required></textarea>
                    <button type="submit" name="comentario" class="btn-comment">Comentar</button>
                </form>
            </div>
            
            <?php if (empty($comentarios)): ?>
                <div class="empty-comments">
                    <p>Nenhum comentário ainda. Seja o primeiro a comentar!</p>
                </div>
            <?php else: ?>
                <div class="comments-list">
                    <?php foreach ($comentarios as $comentario): ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <div>
                                    <span class="comment-author">
                                        <?= $comentario['tipo_autor'] === 'veterinario' ? '👨‍⚕️ ' : '👤 ' ?>
                                        <?= htmlspecialchars($comentario['autor_nome']) ?>
                                    </span>
                                    <span style="color: #999; font-size: 0.85rem;">
                                        (<?= ucfirst($comentario['tipo_autor']) ?>)
                                    </span>
                                </div>
                                <span class="comment-date">
                                    <?= date('d/m/Y H:i', strtotime($comentario['data_comentario'])) ?>
                                </span>
                            </div>
                            <div class="comment-content">
                                <?= nl2br(htmlspecialchars($comentario['conteudo'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>