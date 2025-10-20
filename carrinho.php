<?php
require_once 'config.php';

// Inicializar carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remover'])) {
        $id_produto = $_POST['id_produto'] ?? 0;
        $_SESSION['carrinho'] = array_filter($_SESSION['carrinho'], function($item) use ($id_produto) {
            return $item['id_produto'] != $id_produto;
        });
        $_SESSION['carrinho'] = array_values($_SESSION['carrinho']); // Reindexar
    } elseif (isset($_POST['atualizar'])) {
        $id_produto = $_POST['id_produto'] ?? 0;
        $quantidade = $_POST['quantidade'] ?? 1;
        
        foreach ($_SESSION['carrinho'] as &$item) {
            if ($item['id_produto'] == $id_produto) {
                $item['quantidade'] = max(1, min($quantidade, $item['estoque']));
                break;
            }
        }
    } elseif (isset($_POST['limpar'])) {
        $_SESSION['carrinho'] = [];
    }
    
    header("refresh:0");
    exit;
}

// Calcular totais
$subtotal = 0;
foreach ($_SESSION['carrinho'] as $item) {
    $subtotal += $item['preco'] * $item['quantidade'];
}

$frete = $subtotal >= 150 ? 0 : 15.00;
$total = $subtotal + $frete;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras - PraPet</title>
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
        
        .cart-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .cart-items {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 1.5rem;
            padding: 1.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }
        
        .item-details {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .item-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .item-price {
            color: #667eea;
            font-size: 1.3rem;
            font-weight: bold;
        }
        
        .item-stock {
            color: #4caf50;
            font-size: 0.9rem;
        }
        
        .item-actions {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-input {
            width: 60px;
            padding: 0.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            text-align: center;
        }
        
        .btn-update {
            padding: 0.5rem 1rem;
            background: #2196f3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-remove {
            padding: 0.5rem 1rem;
            background: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-remove:hover, .btn-update:hover {
            opacity: 0.9;
        }
        
        .item-subtotal {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        
        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .summary-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            color: #666;
        }
        
        .summary-row.total {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            padding-top: 1rem;
            border-top: 2px solid #f0f0f0;
            margin-top: 1rem;
        }
        
        .frete-info {
            background: #e8f5e9;
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
            color: #2e7d32;
            font-size: 0.9rem;
        }
        
        .btn-checkout {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 2px solid #667eea;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 1rem;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        
        .btn-checkout:hover {
            transform: translateY(-2px);
        }
        
        .btn-continue {
            width: 100%;
            padding: 1rem;
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 1rem;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .empty-cart {
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
        
        .btn-clear {
            padding: 0.8rem 1.5rem;
            background: #ff9800;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 968px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 80px 1fr;
            }
            
            .item-actions {
                grid-column: 1 / -1;
                flex-direction: row;
                justify-content: space-between;
                margin-top: 1rem;
            }
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
                <?php if (isLoggedIn()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1 class="page-title">🛒 Carrinho de Compras</h1>

        <?php if (empty($_SESSION['carrinho'])): ?>
            <div class="empty-cart">
                <div class="empty-icon">🛒</div>
                <h2>Seu carrinho está vazio</h2>
                <p style="color: #666; margin: 1rem 0;">Adicione produtos ao carrinho para continuar comprando</p>
                <a href="produtos.php" class="btn-checkout">Ir às Compras</a>
            </div>
        <?php else: ?>
            <form method="POST" style="margin-bottom: 1rem;">
                <button type="submit" name="limpar" class="btn-clear" onclick="return confirm('Deseja limpar todo o carrinho?')">
                    🗑️ Limpar Carrinho
                </button>
            </form>

            <div class="cart-layout">
                <div class="cart-items">
                    <?php foreach ($_SESSION['carrinho'] as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">🛒</div>
                            
                            <div class="item-details">
                                <a href="produto_detalhes.php?id=<?= $item['id_produto'] ?>" class="item-name" style="text-decoration: none; color: #333;">
                                    <?= htmlspecialchars($item['nome']) ?>
                                </a>
                                <div class="item-price">R$ <?= number_format($item['preco'], 2, ',', '.') ?></div>
                                <div class="item-stock">Estoque: <?= $item['estoque'] ?> unidades</div>
                            </div>
                            
                            <div class="item-actions">
                                <div class="item-subtotal">
                                    R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?>
                                </div>
                                
                                <form method="POST" class="quantity-control">
                                    <input type="hidden" name="id_produto" value="<?= $item['id_produto'] ?>">
                                    <input 
                                        type="number" 
                                        name="quantidade" 
                                        class="quantity-input" 
                                        value="<?= $item['quantidade'] ?>" 
                                        min="1" 
                                        max="<?= $item['estoque'] ?>"
                                    >
                                    <button type="submit" name="atualizar" class="btn-update">Atualizar</button>
                                </form>
                                
                                <form method="POST">
                                    <input type="hidden" name="id_produto" value="<?= $item['id_produto'] ?>">
                                    <button type="submit" name="remover" class="btn-remove" onclick="return confirm('Remover item do carrinho?')">
                                        Remover
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h2 class="summary-title">Resumo do Pedido</h2>
                    
                    <div class="summary-row">
                        <span>Subtotal (<?= count($_SESSION['carrinho']) ?> itens):</span>
                        <span>R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Frete:</span>
                        <span><?= $frete > 0 ? 'R$ ' . number_format($frete, 2, ',', '.') : 'GRÁTIS' ?></span>
                    </div>
                    
                    <?php if ($subtotal < 150): ?>
                        <div class="frete-info">
                            💡 Faltam R$ <?= number_format(150 - $subtotal, 2, ',', '.') ?> para frete grátis!
                        </div>
                    <?php else: ?>
                        <div class="frete-info">
                            ✓ Você ganhou frete grátis!
                        </div>
                    <?php endif; ?>
                    
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                    </div>
                    <br>
                    <a href="finalizar_compra.php" class="btn-checkout" style="text-decoration: none;">
                        Finalizar Compra
                    </a>
                    
                    <a href="produtos.php" class="btn-continue">
                        Continuar Comprando
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>