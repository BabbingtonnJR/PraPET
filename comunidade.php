<?php
require_once 'config.php';
requireLogin();

// Buscar comunidades
$stmt = $conn->query("SELECT * FROM comunidades WHERE ativa = 1 ORDER BY total_membros DESC");
$comunidades = $stmt->fetchAll();

$user = getUserData($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunidade - PraPet</title>
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
            max-width: 1200px;
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
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        
        .communities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .community-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .community-card:hover {
            transform: translateY(-5px);
        }
        
        .community-image {
            width: 100%;
            height: 150px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
        }
        
        .community-info {
            padding: 1.5rem;
        }
        
        .community-category {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-bottom: 0.8rem;
        }
        
        .community-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .community-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .community-stats {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .btn-join {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            display: block;
            transition: opacity 0.3s;
        }
        
        .btn-join:hover {
            opacity: 0.9;
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
                <?php if (isUsuario()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php elseif (isVeterinario()): ?>
                    <li><a href="dashboard_vet.php">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="comunidade.php">Comunidade</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">💬 Comunidades</h1>
            <p class="page-subtitle">Conecte-se com outros tutores e compartilhe experiências</p>
        </div>

        <?php if (empty($comunidades)): ?>
            <div class="empty-state">
                <div class="empty-icon">🏘️</div>
                <h3>Nenhuma comunidade disponível</h3>
                <p style="color: #666; margin-top: 0.5rem;">Novas comunidades serão criadas em breve!</p>
            </div>
        <?php else: ?>
            <div class="communities-grid">
                <?php foreach ($comunidades as $comunidade): ?>
                    <div class="community-card">
                        <div class="community-image">
                            <?php
                            $emoji = match($comunidade['categoria']) {
                                'Cachorros' => '🐕',
                                'Gatos' => '🐈',
                                'Pássaros' => '🦜',
                                'Outros' => '🐾',
                                default => '💬'
                            };
                            echo $emoji;
                            ?>
                        </div>
                        <div class="community-info">
                            <?php if ($comunidade['categoria']): ?>
                                <span class="community-category"><?= htmlspecialchars($comunidade['categoria']) ?></span>
                            <?php endif; ?>
                            
                            <div class="community-name"><?= htmlspecialchars($comunidade['nome']) ?></div>
                            
                            <div class="community-description">
                                <?= htmlspecialchars($comunidade['descricao']) ?>
                            </div>
                            
                            <div class="community-stats">
                                <span>👥 <?= $comunidade['total_membros'] ?> membros</span>
                            </div>
                            
                            <a href="ver_comunidade.php?id=<?= $comunidade['id_comunidade'] ?>" class="btn-join">
                                Acessar Comunidade
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>