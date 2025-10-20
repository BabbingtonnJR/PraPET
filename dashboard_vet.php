<?php
require_once 'config.php';
requireLogin();

if (!isVeterinario()) {
    redirect('index.php');
}

$vet = getUserData($conn);
$id_veterinario = $_SESSION['user_id'];

// Buscar estatísticas
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM consultas WHERE id_veterinario = ?");
$stmt->execute([$id_veterinario]);
$total_consultas = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM laudos WHERE id_veterinario = ?");
$stmt->execute([$id_veterinario]);
$total_laudos = $stmt->fetch()['total'];

// Buscar consultas recentes
$stmt = $conn->prepare("SELECT c.*, p.nome as pet_nome, p.especie, u.nome as tutor_nome 
                       FROM consultas c
                       INNER JOIN pets p ON c.id_pet = p.id_pet
                       INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                       WHERE c.id_veterinario = ?
                       ORDER BY c.data_registro DESC
                       LIMIT 10");
$stmt->execute([$id_veterinario]);
$consultas_recentes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Veterinário - PraPet</title>
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
            transition: opacity 0.3s;
        }

        nav a:hover {
            opacity: 0.8;
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
        
        .badge {
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
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .action-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
            text-decoration: none;
            color: inherit;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
        }
        
        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .action-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .consultas-table {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 1rem;
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .pet-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="dashboard_vet.php" class="logo">🐾 PraPet</a>
            <ul>
                <li><a href="dashboard_vet.php">Dashboard</a></li>
                <li><a href="buscar_pets.php">Buscar Pets</a></li>
                <li><a href="comunidade.php">Comunidade</a></li>
                <li>
                    <span>👨‍⚕️ Dr(a). <?= htmlspecialchars($vet['nome']) ?></span>
                </li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="welcome-section">
            <h1>Olá, Dr(a). <?= htmlspecialchars($vet['nome']) ?>! 👋</h1>
            <p>Gerencie consultas, laudos e atendimentos veterinários</p>
            <span class="badge">
                CRMV: <?= htmlspecialchars($vet['crmv']) ?> | 
                <?= htmlspecialchars($vet['especialidade'] ?: 'Veterinário Geral') ?>
            </span>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🩺</div>
                <div class="stat-info">
                    <h3>Consultas Realizadas</h3>
                    <p><?= $total_consultas ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <h3>Laudos Emitidos</h3>
                    <p><?= $total_laudos ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-info">
                    <h3>Status</h3>
                    <p style="font-size: 1.5rem; color: #4caf50;">Ativo</p>
                </div>
            </div>
        </div>

        <h2 class="section-title" style="margin-bottom: 1.5rem;">Ações Rápidas</h2>
        <div class="quick-actions">
            <a href="buscar_pets.php" class="action-card">
                <div class="action-icon">🔍</div>
                <div class="action-title">Buscar Pet</div>
                <p style="color: #666; margin-top: 0.5rem;">Encontre um pet para atendimento</p>
            </a>
            
            <a href="registrar_consulta.php" class="action-card">
                <div class="action-icon">🩺</div>
                <div class="action-title">Registrar Consulta</div>
                <p style="color: #666; margin-top: 0.5rem;">Adicionar nova consulta</p>
            </a>
            
            <a href="emitir_laudo.php" class="action-card">
                <div class="action-icon">📋</div>
                <div class="action-title">Emitir Laudo</div>
                <p style="color: #666; margin-top: 0.5rem;">Criar laudo ou exame</p>
            </a>
            
            <a href="comunidade.php" class="action-card">
                <div class="action-icon">💬</div>
                <div class="action-title">Comunidade</div>
                <p style="color: #666; margin-top: 0.5rem;">Participar das discussões</p>
            </a>
        </div>

        <div class="section-header">
            <h2 class="section-title">Consultas Recentes</h2>
        </div>

        <div class="consultas-table">
            <?php if (empty($consultas_recentes)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🩺</div>
                    <h3>Nenhuma consulta registrada ainda</h3>
                    <p>Comece registrando sua primeira consulta</p>
                    <br>
                    <a href="registrar_consulta.php" class="btn-add">Registrar Consulta</a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Pet</th>
                            <th>Tutor</th>
                            <th>Motivo</th>
                            <th>Diagnóstico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consultas_recentes as $consulta): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($consulta['data_consulta'])) ?></td>
                                <td>
                                    <span class="pet-badge">
                                        <?php
                                        $emoji = match($consulta['especie']) {
                                            'Cachorro' => '🐕',
                                            'Gato' => '🐈',
                                            'Pássaro' => '🦜',
                                            'Coelho' => '🐰',
                                            default => '�'
                                        };
                                        echo $emoji . ' ' . htmlspecialchars($consulta['pet_nome']);
                                        ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($consulta['tutor_nome']) ?></td>
                                <td><?= htmlspecialchars($consulta['motivo'] ?: 'Consulta Geral') ?></td>
                                <td><?= htmlspecialchars(substr($consulta['diagnostico'], 0, 50)) ?>...</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>