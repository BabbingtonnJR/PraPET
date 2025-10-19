<?php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    redirect('index.php');
}

// Buscar estatísticas gerais
$stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE ativo = 1");
$total_usuarios = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM veterinarios WHERE status = 'aprovado'");
$total_veterinarios = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM veterinarios WHERE status = 'pendente'");
$vet_pendentes = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM posts WHERE status = 'pendente'");
$posts_pendentes = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM pets WHERE ativo = 1");
$total_pets = $stmt->fetch()['total'];

// Buscar veterinários pendentes
$stmt = $conn->query("SELECT * FROM veterinarios WHERE status = 'pendente' ORDER BY data_cadastro DESC LIMIT 5");
$vet_pendentes_lista = $stmt->fetchAll();

// Buscar posts pendentes
$stmt = $conn->query("SELECT p.*, 
                      CASE 
                        WHEN p.tipo_autor = 'usuario' THEN u.nome
                        WHEN p.tipo_autor = 'veterinario' THEN v.nome
                      END as autor_nome,
                      c.nome as comunidade_nome
                      FROM posts p
                      LEFT JOIN usuarios u ON p.tipo_autor = 'usuario' AND p.id_autor = u.id_usuario
                      LEFT JOIN veterinarios v ON p.tipo_autor = 'veterinario' AND p.id_autor = v.id_veterinario
                      LEFT JOIN comunidades c ON p.id_comunidade = c.id_comunidade
                      WHERE p.status = 'pendente'
                      ORDER BY p.data_postagem DESC
                      LIMIT 5");
$posts_pendentes_lista = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - PraPet</title>
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
            align-items: center;
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
        
        .welcome-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .welcome-section h1 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .badge-admin {
            display: inline-block;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .stat-card.alert {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }
        
        .stat-icon {
            font-size: 3rem;
        }
        
        .stat-info h3 {
            font-size: 0.9rem;
            font-weight: normal;
            opacity: 0.8;
        }
        
        .stat-info p {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .section-title {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 1.5rem;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .card-title {
            font-size: 1.4rem;
            color: #667eea;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .badge-count {
            background: #f093fb;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
        }
        
        .item-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .list-item {
            background: #f8f9fa;
            padding: 1.2rem;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.8rem;
        }
        
        .item-title {
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
        }
        
        .item-date {
            color: #999;
            font-size: 0.85rem;
        }
        
        .item-content {
            color: #666;
            line-height: 1.6;
            margin-bottom: 0.8rem;
        }
        
        .item-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
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
            padding: 2rem;
            color: #999;
        }
        
        .empty-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        
        @media (max-width: 900px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🐾 PraPet</a>
            <ul>
                <li><a href="dashboard_admin.php">Dashboard</a></li>
                <li><a href="gerenciar_veterinarios.php">Veterinários</a></li>
                <li><a href="gerenciar_posts.php">Posts</a></li>
                <li><a href="gerenciar_comunidades.php">Comunidades</a></li>
                <li>
                    <span>👑 Admin</span>
                </li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="welcome-section">
            <h1>Painel Administrativo 👑</h1>
            <p>Gerencie usuários, veterinários e conteúdo da plataforma</p>
            <span class="badge-admin">Acesso Master</span>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3>Total de Usuários</h3>
                    <p><?= $total_usuarios ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👨‍⚕️</div>
                <div class="stat-info">
                    <h3>Veterinários Ativos</h3>
                    <p><?= $total_veterinarios ?></p>
                </div>
            </div>
            
            <div class="stat-card <?= $vet_pendentes > 0 ? 'alert' : '' ?>">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3>Veterinários Pendentes</h3>
                    <p><?= $vet_pendentes ?></p>
                </div>
            </div>
            
            <div class="stat-card <?= $posts_pendentes > 0 ? 'alert' : '' ?>">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <h3>Posts Pendentes</h3>
                    <p><?= $posts_pendentes ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🐾</div>
                <div class="stat-info">
                    <h3>Total de Pets</h3>
                    <p><?= $total_pets ?></p>
                </div>
            </div>
        </div>

        <div class="cards-grid">
            <!-- Veterinários Pendentes -->
            <div class="content-card">
                <div class="card-title">
                    <span>👨‍⚕️ Veterinários Aguardando Aprovação</span>
                    <?php if ($vet_pendentes > 0): ?>
                        <span class="badge-count"><?= $vet_pendentes ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($vet_pendentes_lista)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">✅</div>
                        <p>Nenhum veterinário pendente</p>
                    </div>
                <?php else: ?>
                    <div class="item-list">
                        <?php foreach ($vet_pendentes_lista as $vet): ?>
                            <div class="list-item">
                                <div class="item-header">
                                    <div class="item-title"><?= htmlspecialchars($vet['nome']) ?></div>
                                    <div class="item-date"><?= date('d/m/Y', strtotime($vet['data_cadastro'])) ?></div>
                                </div>
                                <div class="item-content">
                                    <strong>CRMV:</strong> <?= htmlspecialchars($vet['crmv']) ?><br>
                                    <strong>Email:</strong> <?= htmlspecialchars($vet['email']) ?><br>
                                    <strong>Especialidade:</strong> <?= htmlspecialchars($vet['especialidade'] ?: 'Não informada') ?>
                                </div>
                                <div class="item-actions">
                                    <form method="POST" action="aprovar_veterinario.php" style="display: inline;">
                                        <input type="hidden" name="id_veterinario" value="<?= $vet['id_veterinario'] ?>">
                                        <input type="hidden" name="acao" value="aprovar">
                                        <button type="submit" class="btn btn-success">✓ Aprovar</button>
                                    </form>
                                    <form method="POST" action="aprovar_veterinario.php" style="display: inline;">
                                        <input type="hidden" name="id_veterinario" value="<?= $vet['id_veterinario'] ?>">
                                        <input type="hidden" name="acao" value="rejeitar">
                                        <button type="submit" class="btn btn-danger">✗ Rejeitar</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($vet_pendentes > 5): ?>
                        <p style="text-align: center; margin-top: 1rem;">
                            <a href="gerenciar_veterinarios.php" style="color: #667eea; font-weight: bold;">Ver todos (<?= $vet_pendentes ?>)</a>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Posts Pendentes -->
            <div class="content-card">
                <div class="card-title">
                    <span>📝 Posts Aguardando Moderação</span>
                    <?php if ($posts_pendentes > 0): ?>
                        <span class="badge-count"><?= $posts_pendentes ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($posts_pendentes_lista)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">✅</div>
                        <p>Nenhum post pendente</p>
                    </div>
                <?php else: ?>
                    <div class="item-list">
                        <?php foreach ($posts_pendentes_lista as $post): ?>
                            <div class="list-item">
                                <div class="item-header">
                                    <div class="item-title"><?= htmlspecialchars($post['titulo']) ?></div>
                                    <div class="item-date"><?= date('d/m/Y', strtotime($post['data_postagem'])) ?></div>
                                </div>
                                <div class="item-content">
                                    <strong>Autor:</strong> <?= htmlspecialchars($post['autor_nome']) ?> 
                                    (<?= ucfirst($post['tipo_autor']) ?>)<br>
                                    <strong>Comunidade:</strong> <?= htmlspecialchars($post['comunidade_nome']) ?><br>
                                    <?= htmlspecialchars(substr($post['conteudo'], 0, 100)) ?>...
                                </div>
                                <div class="item-actions">
                                    <form method="POST" action="moderar_post.php" style="display: inline;">
                                        <input type="hidden" name="id_post" value="<?= $post['id_post'] ?>">
                                        <input type="hidden" name="acao" value="aprovar">
                                        <button type="submit" class="btn btn-success">✓ Aprovar</button>
                                    </form>
                                    <a href="visualizar_post.php?id=<?= $post['id_post'] ?>" class="btn btn-info">👁️ Ver</a>
                                    <form method="POST" action="moderar_post.php" style="display: inline;">
                                        <input type="hidden" name="id_post" value="<?= $post['id_post'] ?>">
                                        <input type="hidden" name="acao" value="rejeitar">
                                        <button type="submit" class="btn btn-danger">✗ Rejeitar</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($posts_pendentes > 5): ?>
                        <p style="text-align: center; margin-top: 1rem;">
                            <a href="gerenciar_posts.php" style="color: #667eea; font-weight: bold;">Ver todos (<?= $posts_pendentes ?>)</a>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>