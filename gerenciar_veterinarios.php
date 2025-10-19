<?php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    redirect('index.php');
}

$filtro = $_GET['filtro'] ?? 'todos';

// Buscar veterinários
$sql = "SELECT * FROM veterinarios WHERE 1=1";
$params = [];

if ($filtro !== 'todos') {
    $sql .= " AND status = ?";
    $params[] = $filtro;
}

$sql .= " ORDER BY data_cadastro DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$veterinarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Veterinários - PraPet</title>
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
        
        .content-card {
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
        
        .status-suspenso {
            background: #d1ecf1;
            color: #0c5460;
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
            margin-right: 0.5rem;
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
        
        .btn-warning {
            background: #ff9800;
            color: white;
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
            <a href="index.php" class="logo">🐾 PraPet</a>
            <ul>
                <li><a href="dashboard_admin.php">← Dashboard</a></li>
                <li><a href="gerenciar_posts.php">Posts</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">👨‍⚕️ Gerenciar Veterinários</h1>
            
            <div class="filter-tabs">
                <a href="?filtro=todos" class="filter-tab <?= $filtro === 'todos' ? 'active' : '' ?>">
                    Todos
                </a>
                <a href="?filtro=pendente" class="filter-tab <?= $filtro === 'pendente' ? 'active' : '' ?>">
                    Pendentes
                </a>
                <a href="?filtro=aprovado" class="filter-tab <?= $filtro === 'aprovado' ? 'active' : '' ?>">
                    Aprovados
                </a>
                <a href="?filtro=rejeitado" class="filter-tab <?= $filtro === 'rejeitado' ? 'active' : '' ?>">
                    Rejeitados
                </a>
                <a href="?filtro=suspenso" class="filter-tab <?= $filtro === 'suspenso' ? 'active' : '' ?>">
                    Suspensos
                </a>
            </div>
        </div>

        <div class="content-card">
            <?php if (empty($veterinarios)): ?>
                <div class="empty-state">
                    <div class="empty-icon">👨‍⚕️</div>
                    <h3>Nenhum veterinário encontrado</h3>
                    <p>Não há veterinários com o filtro selecionado</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CRMV</th>
                            <th>Email</th>
                            <th>Especialidade</th>
                            <th>Data Cadastro</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($veterinarios as $vet): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($vet['nome']) ?></strong></td>
                                <td><?= htmlspecialchars($vet['crmv']) ?></td>
                                <td><?= htmlspecialchars($vet['email']) ?></td>
                                <td><?= htmlspecialchars($vet['especialidade'] ?: 'Não informada') ?></td>
                                <td><?= date('d/m/Y', strtotime($vet['data_cadastro'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $vet['status'] ?>">
                                        <?= ucfirst($vet['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($vet['status'] === 'pendente'): ?>
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
                                    <?php elseif ($vet['status'] === 'aprovado'): ?>
                                        <form method="POST" action="suspender_veterinario.php" style="display: inline;">
                                            <input type="hidden" name="id_veterinario" value="<?= $vet['id_veterinario'] ?>">
                                            <button type="submit" class="btn btn-warning">⏸ Suspender</button>
                                        </form>
                                    <?php elseif ($vet['status'] === 'suspenso'): ?>
                                        <form method="POST" action="reativar_veterinario.php" style="display: inline;">
                                            <input type="hidden" name="id_veterinario" value="<?= $vet['id_veterinario'] ?>">
                                            <button type="submit" class="btn btn-success">▶ Reativar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>