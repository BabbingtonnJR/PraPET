<?php
require_once 'config.php';
requireLogin();

if (!isUsuario()) {
    redirect('index.php');
}

$id_usuario = $_SESSION['user_id'];

// Buscar pedidos do usuário
$stmt = $conn->prepare("SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY data_pedido DESC");
$stmt->execute([$id_usuario]);
$pedidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - PraPet</title>
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
        
        .page-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 2rem;
        }
        
        .pedidos-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .pedido-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .pedido-numero {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
        }
        
        .pedido-data {
            color: #666;
            font-size: 0.9rem;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .status-pendente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-pago {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-enviado {
            background: #cfe2ff;
            color: #084298;
        }
        
        .status-entregue {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelado {
            background: #f8d7da;
            color: #721c24;
        }
        
        .pedido-body {
            margin-bottom: 1rem;
        }
        
        .pedido-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.3rem;
        }
        
        .info-value {
            font-weight: bold;
            color: #333;
        }
        
        .btn-details {
            padding: 0.8rem 1.5rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }
        
        .btn-details:hover {
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="produtos.php">Produtos</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1 class="page-title">📦 Meus Pedidos</h1>

        <?php if (empty($pedidos)): ?>
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h2>Você ainda não fez nenhum pedido</h2>
                <p style="color: #666; margin: 1rem 0;">Comece suas compras agora!</p>
                <a href="produtos.php" class="btn-details" style="display: inline-block; margin-top: 1rem;">
                    Ver Produtos
                </a>
            </div>
        <?php else: ?>
            <div class="pedidos-list">
                <?php foreach ($pedidos as $pedido): ?>
                    <?php
                    // Buscar itens do pedido
                    $stmt = $conn->prepare("SELECT ip.*, p.nome FROM itens_pedido ip 
                                           INNER JOIN produtos p ON ip.id_produto = p.id_produto 
                                           WHERE ip.id_pedido = ?");
                    $stmt->execute([$pedido['id_pedido']]);
                    $itens = $stmt->fetchAll();
                    ?>
                    
                    <div class="pedido-card">
                        <div class="pedido-header">
                            <div>
                                <div class="pedido-numero">
                                    Pedido #<?= str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT) ?>
                                </div>
                                <div class="pedido-data">
                                    <?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?>
                                </div>
                            </div>
                            <span class="status-badge status-<?= $pedido['status'] ?>">
                                <?= ucfirst($pedido['status']) ?>
                            </span>
                        </div>
                        
                        <div class="pedido-body">
                            <div class="pedido-info">
                                <div class="info-item">
                                    <div class="info-label">Valor Total</div>
                                    <div class="info-value">R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Método de Pagamento</div>
                                    <div class="info-value">
                                        <?php
                                        echo match($pedido['metodo_pagamento']) {
                                            'pix' => '📱 PIX',
                                            'cartao_credito' => '💳 Cartão',
                                            'boleto' => '🧾 Boleto',
                                            default => $pedido['metodo_pagamento']
                                        };
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Endereço de Entrega</div>
                                    <div class="info-value" style="font-size: 0.9rem;">
                                        <?= htmlspecialchars($pedido['endereco_entrega']) ?><br>
                                        <?= htmlspecialchars($pedido['cidade']) ?>/<?= $pedido['estado'] ?>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Itens</div>
                                    <div class="info-value"><?= count($itens) ?> produto(s)</div>
                                </div>
                            </div>
                            
                            <details style="margin-top: 1rem;">
                                <summary style="cursor: pointer; font-weight: bold; color: #667eea; padding: 0.5rem 0;">
                                    Ver itens do pedido
                                </summary>
                                <div style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                                    <?php foreach ($itens as $item): ?>
                                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e0e0e0;">
                                            <span><?= htmlspecialchars($item['nome']) ?> (x<?= $item['quantidade'] ?>)</span>
                                            <span style="font-weight: bold;">R$ <?= number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if (isLoggedIn()): ?>
        <?php include 'carrinho_flutuante.php'; ?>
    <?php else: ?>
    <?php endif; ?>
</body>
</html>