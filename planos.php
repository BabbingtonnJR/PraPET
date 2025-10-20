<?php
require_once 'config.php';

// Buscar planos
$stmt = $conn->query("SELECT * FROM planos WHERE ativo = 1 ORDER BY preco ASC");
$planos = $stmt->fetchAll();

// Processar mudança de plano
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isUsuario()) {
    $id_plano = $_POST['id_plano'] ?? 0;
    $id_usuario = $_SESSION['user_id'];
    
    try {
        $stmt = $conn->prepare("UPDATE usuarios SET id_plano = ?, data_inicio_plano = NOW(), data_fim_plano = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id_usuario = ?");
        $stmt->execute([$id_plano, $id_usuario]);
        
        $sucesso = 'Plano atualizado com sucesso!';
    } catch (PDOException $e) {
        $erro = 'Erro ao atualizar plano';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos - PraPet</title>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        nav {
            max-width: 1200px;
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
            margin: 4rem auto;
            padding: 0 20px;
        }
        
        .page-title {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .page-subtitle {
            text-align: center;
            font-size: 1.3rem;
            color: #666;
            margin-bottom: 3rem;
        }
        
        .planos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .plano-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        
        .plano-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        
        .plano-card.destaque {
            border: 3px solid #667eea;
            transform: scale(1.05);
        }
        
        .badge-destaque {
            position: absolute;
            top: -15px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .plano-nome {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #667eea;
        }
        
        .plano-preco {
            font-size: 3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .plano-preco span {
            font-size: 1.2rem;
            color: #999;
        }
        
        .plano-descricao {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .plano-features {
            list-style: none;
            margin-bottom: 2rem;
        }
        
        .plano-features li {
            padding: 0.8rem 0;
            border-bottom: 1px solid #f0f0f0;
            color: #555;
        }
        
        .plano-features li:before {
            content: "✓ ";
            color: #4caf50;
            font-weight: bold;
            margin-right: 0.5rem;
        }

        .btn {
            background: white;
            color: #667eea;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-selecionar {
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
        
        .btn-selecionar:hover {
            transform: translateY(-2px);
        }
        
        .btn-atual {
            background: #4caf50;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🐾 PraPet</a>
            <ul>
                <?php if (isLoggedIn()): ?>
                <?php else: ?>
                    <li><a href="index.php">Início</a></li>
                <?php endif; ?>
                <li><a href="produtos.php">Produtos</a></li>
                <li><a href="planos.php">Planos</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn">Entrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1 class="page-title">Escolha o Plano Ideal</h1>
        <p class="page-subtitle">Cuidados completos para o seu pet, do básico ao premium</p>
        
        <?php if (isset($sucesso)): ?>
            <div class="alert alert-success"><?= $sucesso ?></div>
        <?php endif; ?>
        
        <div class="planos-grid">
            <?php 
            $user_plano_id = null;
            if (isUsuario()) {
                $user = getUserData($conn);
                $user_plano_id = $user['id_plano'];
            }
            
            foreach ($planos as $index => $plano): 
                $is_destaque = $index === 1; // Plano do meio em destaque
                $is_atual = $user_plano_id == $plano['id_plano'];
            ?>
                <div class="plano-card <?= $is_destaque ? 'destaque' : '' ?>">
                    <?php if ($is_destaque): ?>
                        <div class="badge-destaque">MAIS POPULAR</div>
                    <?php endif; ?>
                    
                    <div class="plano-nome"><?= htmlspecialchars($plano['nome']) ?></div>
                    <div class="plano-preco">
                        <?php if ($plano['preco'] == 0): ?>
                            Grátis
                        <?php else: ?>
                            R$ <?= number_format($plano['preco'], 2, ',', '.') ?>
                            <span>/mês</span>
                        <?php endif; ?>
                    </div>
                    <div class="plano-descricao"><?= htmlspecialchars($plano['descricao']) ?></div>
                    
                    <ul class="plano-features">
                        <li><?= $plano['limite_pets'] == 999 ? 'Pets ilimitados' : $plano['limite_pets'] . ' pet(s)' ?></li>
                        <li><?= $plano['limite_consultas'] == 999 ? 'Consultas ilimitadas' : $plano['limite_consultas'] . ' consultas/mês' ?></li>
                        <li><?= $plano['limite_laudos'] == 999 ? 'Laudos ilimitados' : $plano['limite_laudos'] . ' laudos/mês' ?></li>
                        <?php if ($plano['acesso_comunidade']): ?>
                            <li>Acesso às comunidades</li>
                        <?php endif; ?>
                        <?php if ($plano['suporte_prioritario']): ?>
                            <li>Suporte prioritário</li>
                        <?php endif; ?>
                    </ul>
                    
                    <?php if (isUsuario()): ?>
                        <?php if ($is_atual): ?>
                            <button class="btn-selecionar btn-atual" disabled>Plano Atual</button>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="id_plano" value="<?= $plano['id_plano'] ?>">
                                <button type="submit" class="btn-selecionar">Selecionar Plano</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="cadastro.php" style="text-decoration: none;">
                            <button class="btn-selecionar">Começar Agora</button>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>