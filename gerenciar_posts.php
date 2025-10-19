<?php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    redirect('index.php');
}

$filtro = $_GET['filtro'] ?? 'pendente';

// Buscar posts
$sql = "SELECT p.*, 
        CASE 
          WHEN p.tipo_autor = 'usuario' THEN u.nome
          WHEN p.tipo_autor = 'veterinario' THEN v.nome
        END as autor_nome,
        c.nome as comunidade_nome
        FROM posts p
        LEFT JOIN usuarios u ON p.tipo_autor = 'usuario' AND p.id_autor = u.id_usuario
        LEFT JOIN veterinarios v ON p.tipo_autor = 'veterinario' AND p.id_autor = v.id_veterinario
        LEFT JOIN comunidades c ON p.id_comunidade = c.id_comunidade
        WHERE 1=1";

$params = [];

if ($filtro !== 'todos') {
    $sql .= " AND p.status = ?";
    $params[] = $filtro;
}

$sql .= " ORDER BY p.data_postagem DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Posts - PraPet</title>
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
        }
        
        .page-title {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .filter-tabs {
            display: flex;
            gap: 1rem;
        }
        
        .filter-tab {
            padding: 0.8rem 1.5rem;
            background: #f5f5f5;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
        }
        
        .filter-tab:hover {
            background: #e0e0e0;
        }
        
        .filter-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .posts-list {
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
        
        .post-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .post-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .post-content {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
            max-height: 100px;
            overflow: hidden;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        
        .status-pendente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-aprovado {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejeitado {
            background: #f8d7da;
            color: #721c24;
        }
        
        .post-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        .btn-success {
            background: #4caf50;
            color: white;
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-info {
            background: #2196f3;
            color: white;
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
                <li><a href="dashboard_admin.php">← Dashboard</a></li>
                <li><a href="gerenciar_veterinarios.php">Veterinários</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">📝 Gerenciar Posts</h1>
            
            <div class="filter-tabs">
                <a href="?filtro=pendente" class="filter-tab <?= $filtro === 'pendente' ? 'active' : '' ?>">
                    Pendentes
                </a>
                <a href="?filtro=aprovado" class="filter-tab <?= $filtro === 'aprovado' ? 'active' : '' ?>">
                    Aprovados
                </a>
                <a href="?filtro=rejeitado" class="filter-tab <?= $filtro === 'rejeitado' ? 'active' : '' ?>">
                    Rejeitados
                </a>
                <a href="?filtro=todos" class="filter-tab <?= $filtro === 'todos' ? 'active' : '' ?>">
                    Todos
                </a>
            </div>
        </div>

        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <h3>Nenhum post encontrado</h3>
                <p style="color: #666;">Não há posts com o filtro selecionado</p>
            </div>
        <?php else: ?>
            <div class="posts-list">
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <div>
                                <h2 class="post-title"><?= htmlspecialchars($post['titulo']) ?></h2>
                                <div class="post-meta">
                                    <strong>Autor:</strong> <?= htmlspecialchars($post['autor_nome']) ?> 
                                    (<?= ucfirst($post['tipo_autor']) ?>) | 
                                    <strong>Comunidade:</strong> <?= htmlspecialchars($post['comunidade_nome']) ?> | 
                                    <strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($post['data_postagem'])) ?>
                                </div>
                            </div>
                            <span class="status-badge status-<?= $post['status'] ?>">
                                <?= ucfirst($post['status']) ?>
                            </span>
                        </div>
                        
                        <div class="post-content">
                            <?= nl2br(htmlspecialchars($post['conteudo'])) ?>
                        </div>
                        
                        <?php if ($post['motivo_rejeicao']): ?>
                            <div style="background: #fff3cd; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                                <strong>Motivo da rejeição:</strong> <?= htmlspecialchars($post['motivo_rejeicao']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-actions">
                            <?php if ($post['status'] === 'pendente'): ?>
                                <form method="POST" action="moderar_post.php" style="display: inline;">
                                    <input type="hidden" name="id_post" value="<?= $post['id_post'] ?>">
                                    <input type="hidden" name="acao" value="aprovar">
                                    <button type="submit" class="btn btn-success">✓ Aprovar</button>
                                </form>
                                <a href="visualizar_post.php?id=<?= $post['id_post'] ?>" class="btn btn-info">👁️ Ver Completo</a>
                                <button onclick="rejeitar(<?= $post['id_post'] ?>)" class="btn btn-danger">✗ Rejeitar</button>
                            <?php endif; ?>
                            
                            <?php if ($post['status'] === 'aprovado'): ?>
                                <a href="ver_post.php?id=<?= $post['id_post'] ?>" class="btn btn-info">👁️ Ver Post</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function rejeitar(idPost) {
            const motivo = prompt('Digite o motivo da rejeição:');
            if (motivo) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'moderar_post.php';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id_post';
                inputId.value = idPost;
                
                const inputAcao = document.createElement('input');
                inputAcao.type = 'hidden';
                inputAcao.name = 'acao';
                inputAcao.value = 'rejeitar';
                
                const inputMotivo = document.createElement('input');
                inputMotivo.type = 'hidden';
                inputMotivo.name = 'motivo_rejeicao';
                inputMotivo.value = motivo;
                
                form.appendChild(inputId);
                form.appendChild(inputAcao);
                form.appendChild(inputMotivo);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>