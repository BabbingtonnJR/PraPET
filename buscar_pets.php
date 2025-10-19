<?php
require_once 'config.php';
requireLogin();

if (!isVeterinario()) {
    redirect('index.php');
}

$busca = $_GET['busca'] ?? '';
$pets = [];

if (!empty($busca)) {
    $stmt = $conn->prepare("SELECT p.*, u.nome as tutor_nome, u.telefone, u.email as tutor_email 
                           FROM pets p 
                           INNER JOIN usuarios u ON p.id_usuario = u.id_usuario 
                           WHERE (p.nome LIKE ? OR u.nome LIKE ? OR u.email LIKE ?) AND p.ativo = 1
                           LIMIT 20");
    $busca_param = "%$busca%";
    $stmt->execute([$busca_param, $busca_param, $busca_param]);
    $pets = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Pets - PraPet</title>
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
        
        .search-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .search-title {
            font-size: 1.8rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .search-form {
            display: flex;
            gap: 1rem;
        }
        
        .search-input {
            flex: 1;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-search {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
        }
        
        .results-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .results-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1.5rem;
        }
        
        .pets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .pet-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            border-left: 4px solid #667eea;
        }
        
        .pet-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .pet-icon {
            font-size: 3rem;
        }
        
        .pet-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        
        .pet-species {
            color: #667eea;
            font-size: 1rem;
        }
        
        .pet-details {
            margin-bottom: 1rem;
            color: #666;
            line-height: 1.6;
        }
        
        .tutor-info {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .tutor-info strong {
            color: #667eea;
        }
        
        .pet-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            flex: 1;
            padding: 0.7rem;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: opacity 0.3s;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        .btn-consulta {
            background: #667eea;
            color: white;
        }
        
        .btn-laudo {
            background: #4caf50;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        
        .empty-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        
        .info-box {
            background: #e3f2fd;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border-left: 4px solid #2196f3;
        }
        
        .info-box p {
            color: #1976d2;
            margin: 0;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🐾 PraPet</a>
            <ul>
                <li><a href="dashboard_vet.php">← Dashboard</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="search-section">
            <h1 class="search-title">🔍 Buscar Pet para Atendimento</h1>
            <form method="GET" class="search-form">
                <input 
                    type="text" 
                    name="busca" 
                    class="search-input" 
                    placeholder="Digite o nome do pet, tutor ou email..." 
                    value="<?= htmlspecialchars($busca) ?>"
                    required
                >
                <button type="submit" class="btn-search">Buscar</button>
            </form>
        </div>

        <?php if (!empty($busca)): ?>
            <div class="results-section">
                <h2 class="results-title">
                    <?= count($pets) ?> resultado(s) encontrado(s) para "<?= htmlspecialchars($busca) ?>"
                </h2>
                
                <?php if (empty($pets)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🔍</div>
                        <h3>Nenhum pet encontrado</h3>
                        <p>Tente buscar por outro nome, tutor ou email</p>
                    </div>
                <?php else: ?>
                    <div class="pets-grid">
                        <?php foreach ($pets as $pet): ?>
                            <div class="pet-card">
                                <div class="pet-header">
                                    <div class="pet-icon">
                                        <?php
                                        $emoji = match($pet['especie']) {
                                            'Cachorro' => '🐕',
                                            'Gato' => '🐈',
                                            'Pássaro' => '🐦',
                                            'Coelho' => '🐰',
                                            default => '🐾'
                                        };
                                        echo $emoji;
                                        ?>
                                    </div>
                                    <div>
                                        <div class="pet-name"><?= htmlspecialchars($pet['nome']) ?></div>
                                        <div class="pet-species">
                                            <?= htmlspecialchars($pet['especie']) ?>
                                            <?= $pet['raca'] ? ' - ' . htmlspecialchars($pet['raca']) : '' ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="pet-details">
                                    <?= ucfirst($pet['sexo']) ?>
                                    <?php if ($pet['idade']): ?>
                                        | <?= $pet['idade'] ?> ano(s)
                                    <?php endif; ?>
                                    <?php if ($pet['peso']): ?>
                                        | <?= number_format($pet['peso'], 1, ',', '.') ?> kg
                                    <?php endif; ?>
                                    <br>
                                    <?php if ($pet['cor']): ?>
                                        Cor: <?= htmlspecialchars($pet['cor']) ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="tutor-info">
                                    <strong>👤 Tutor:</strong> <?= htmlspecialchars($pet['tutor_nome']) ?><br>
                                    <strong>📧 Email:</strong> <?= htmlspecialchars($pet['tutor_email']) ?><br>
                                    <?php if ($pet['telefone']): ?>
                                        <strong>📱 Telefone:</strong> <?= htmlspecialchars($pet['telefone']) ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="pet-actions">
                                    <a href="registrar_consulta.php?pet=<?= $pet['id_pet'] ?>" class="btn btn-consulta">
                                        🩺 Consulta
                                    </a>
                                    <a href="emitir_laudo.php?pet=<?= $pet['id_pet'] ?>" class="btn btn-laudo">
                                        📋 Laudo
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="info-box">
                <p>💡 <strong>Dica:</strong> Use a busca acima para encontrar pets por nome, nome do tutor ou email do responsável.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>