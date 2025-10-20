<?php
require_once 'config.php';
requireLogin();

if (!isUsuario()) {
    redirect('index.php');
}

$user = getUserData($conn);
$id_usuario = $_SESSION['user_id'];

// Buscar pets do usuário
$stmt = $conn->prepare("SELECT * FROM pets WHERE id_usuario = ? AND ativo = 1 ORDER BY data_cadastro DESC");
$stmt->execute([$id_usuario]);
$pets = $stmt->fetchAll();

// Buscar estatísticas
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pets WHERE id_usuario = ? AND ativo = 1");
$stmt->execute([$id_usuario]);
$total_pets = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM consultas c 
                       INNER JOIN pets p ON c.id_pet = p.id_pet 
                       WHERE p.id_usuario = ?");
$stmt->execute([$id_usuario]);
$total_consultas = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM vacinas v 
                       INNER JOIN pets p ON v.id_pet = p.id_pet 
                       WHERE p.id_usuario = ?");
$stmt->execute([$id_usuario]);
$total_vacinas = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Dashboard - PraPet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding-bottom: 100px; /* Espaço para o carrinho flutuante */
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            transition: opacity 0.3s;
        }

        nav a:hover {
            opacity: 0.8;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .dashboard-container {
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
        
        .plano-info {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        
        .stat-icon {
            font-size: 3rem;
        }
        
        .stat-info h3 {
            color: #999;
            font-size: 0.9rem;
            font-weight: normal;
        }
        
        .stat-info p {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 1.8rem;
            color: #333;
        }
        
        .btn-add {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.3s;
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
        }
        
        .pets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .pet-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .pet-card:hover {
            transform: translateY(-5px);
        }
        
        .pet-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
        }
        
        .pet-info {
            padding: 1.5rem;
        }
        
        .pet-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .pet-details {
            color: #666;
            margin-bottom: 1rem;
        }
        
        .pet-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-small {
            flex: 1;
            padding: 0.6rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            font-size: 0.9rem;
            transition: opacity 0.3s;
        }
        
        .btn-small:hover {
            opacity: 0.8;
        }
        
        .btn-view {
            background: #667eea;
            color: white;
        }
        
        .btn-edit {
            background: #4caf50;
            color: white;
        }
        
        .no-pets {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .no-pets-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="dashboard.php" class="logo">🐾 PraPet</a>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="produtos.php">Produtos</a></li>
                <li><a href="comunidade.php">Comunidade</a></li>
                <li><a href="planos.php">Planos</a></li>
                <li><a href="meus_pedidos.php">Meus Pedidos</a></li>
                <li class="user-info">
                    <span>👤 <?= htmlspecialchars($user['nome']) ?></span>
                    <a href="logout.php">Sair</a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Bem-vindo, <?= htmlspecialchars($user['nome']) ?>! 👋</h1>
            <p>Gerencie a saúde e bem-estar dos seus pets</p>
            <span class="plano-info">
                📋 Plano: <?= htmlspecialchars($user['plano_nome']) ?>
                <?php if ($user['id_plano'] != 1): ?>
                    | Válido até: <?= date('d/m/Y', strtotime($user['data_fim_plano'])) ?>
                <?php endif; ?>
            </span>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🐕</div>
                <div class="stat-info">
                    <h3>Total de Pets</h3>
                    <p><?= $total_pets ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🩺</div>
                <div class="stat-info">
                    <h3>Consultas Realizadas</h3>
                    <p><?= $total_consultas ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💉</div>
                <div class="stat-info">
                    <h3>Vacinas Aplicadas</h3>
                    <p><?= $total_vacinas ?></p>
                </div>
            </div>
        </div>

        <div class="section-header">
            <h2 class="section-title">Meus Pets</h2>
            <?php if (verificarLimitePlano($conn, 'pets')): ?>
                <a href="cadastrar_pet.php" class="btn-add">+ Adicionar Pet</a>
            <?php else: ?>
                <a href="planos.php" class="btn-add">🔒 Limite Atingido - Upgrade</a>
            <?php endif; ?>
        </div>

        <?php if (empty($pets)): ?>
            <div class="no-pets">
                <div class="no-pets-icon">🐾</div>
                <h3>Você ainda não cadastrou nenhum pet</h3>
                <p style="margin: 1rem 0; color: #666;">Comece adicionando seu primeiro amigo peludo!</p>
                <a href="cadastrar_pet.php" class="btn-add">Cadastrar Primeiro Pet</a>
            </div>
        <?php else: ?>
            <div class="pets-grid">
                <?php foreach ($pets as $pet): ?>
                    <div class="pet-card">
                        <div class="pet-image">
                            <?php
                            $emoji = match($pet['especie']) {
                                'Cachorro' => '🐕',
                                'Gato' => '🐈',
                                'Pássaro' => '🦜',
                                'Coelho' => '🐰',
                                default => '🐾'
                            };
                            echo $emoji;
                            ?>
                        </div>
                        <div class="pet-info">
                            <div class="pet-name"><?= htmlspecialchars($pet['nome']) ?></div>
                            <div class="pet-details">
                                <strong><?= htmlspecialchars($pet['especie']) ?></strong> 
                                <?php if ($pet['raca']): ?>
                                    - <?= htmlspecialchars($pet['raca']) ?>
                                <?php endif; ?>
                                <br>
                                <?= htmlspecialchars($pet['sexo']) ?> 
                                <?php if ($pet['idade']): ?>
                                    | <?= $pet['idade'] ?> ano(s)
                                <?php endif; ?>
                                <?php if ($pet['peso']): ?>
                                    | <?= number_format($pet['peso'], 1, ',', '.') ?> kg
                                <?php endif; ?>
                            </div>
                            <div class="pet-actions">
                                <a href="pet_detalhes.php?id=<?= $pet['id_pet'] ?>" class="btn-small btn-view">Ver Detalhes</a>
                                <a href="editar_pet.php?id=<?= $pet['id_pet'] ?>" class="btn-small btn-edit">Editar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Incluir Carrinho Flutuante -->
    <?php include 'carrinho_flutuante.php'; ?>
</body>
</html>