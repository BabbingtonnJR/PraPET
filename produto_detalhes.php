<?php
require_once 'config.php';

$id_produto = $_GET['id'] ?? 0;

// Buscar produto
$stmt = $conn->prepare("SELECT * FROM produtos WHERE id_produto = ? AND ativo = 1");
$stmt->execute([$id_produto]);
$produto = $stmt->fetch();

if (!$produto) {
    redirect('produtos.php');
}

// Produtos relacionados
$stmt = $conn->prepare("SELECT * FROM produtos WHERE categoria = ? AND id_produto != ? AND ativo = 1 LIMIT 4");
$stmt->execute([$produto['categoria'], $id_produto]);
$relacionados = $stmt->fetchAll();

$sucesso = '';
if (isset($_SESSION['add_carrinho'])) {
    $sucesso = $_SESSION['add_carrinho'];
    unset($_SESSION['add_carrinho']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produto['nome']) ?> - PraPet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding-bottom: 100px;
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
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .breadcrumb {
            margin-bottom: 2rem;
            color: #666;
        }
        
        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
        }
        
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
        }
        
        .product-image-section {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .main-image {
            width: 100%;
            height: 500px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10rem;
        }
        
        .product-info-section {
            display: flex;
            flex-direction: column;
        }
        
        .product-category-badge {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            width: fit-content;
        }
        
        .product-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .product-price {
            font-size: 3rem;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }
        
        .product-description {
            color: #666;
            line-height: 1.8;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .stock-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            padding: 1rem;
            background: #e8f5e9;
            border-radius: 10px;
            color: #2e7d32;
            font-weight: bold;
        }
        
        .stock-info.out {
            background: #ffebee;
            color: #c62828;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .quantity-label {
            font-weight: bold;
            color: #333;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .quantity-btn {
            background: #f5f5f5;
            border: none;
            padding: 0.8rem 1.2rem;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .quantity-btn:hover {
            background: #e0e0e0;
        }
        
        .quantity-input {
            border: none;
            width: 60px;
            text-align: center;
            font-size: 1.2rem;
            padding: 0.8rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .btn-add-cart {
            flex: 2;
            padding: 1.2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn-add-cart:hover {
            transform: translateY(-2px);
        }
        
        .btn-add-cart:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-buy-now {
            flex: 1;
            padding: 1.2rem;
            background: #4caf50;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn-buy-now:hover {
            transform: translateY(-2px);
        }
        
        .product-features {
            list-style: none;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .product-features li {
            padding: 0.8rem 0;
            border-bottom: 1px solid #e0e0e0;
            color: #555;
        }
        
        .product-features li:last-child {
            border-bottom: none;
        }
        
        .product-features li:before {
            content: "✓ ";
            color: #4caf50;
            font-weight: bold;
            margin-right: 0.5rem;
        }
        
        .related-section {
            margin-top: 3rem;
        }
        
        .section-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 2rem;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .related-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            text-decoration: none;
            color: inherit;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
        }
        
        .related-image {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }
        
        .related-info {
            padding: 1rem;
        }
        
        .related-name {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .related-price {
            color: #667eea;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border: 1px solid #c3e6cb;
        }
        
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
            }
            
            .main-image {
                height: 300px;
                font-size: 6rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
    <script>
        function updateQuantity(change) {
            const input = document.getElementById('quantity');
            let value = parseInt(input.value) + change;
            const max = parseInt(input.max);
            
            if (value < 1) value = 1;
            if (value > max) value = max;
            
            input.value = value;
        }

        function validateQuantity() {
            const input = document.getElementById('quantity');
            let value = parseInt(input.value);
            const max = parseInt(input.max);
            
            if (isNaN(value) || value < 1) value = 1;
            if (value > max) value = max;
            
            input.value = value;
        }
    </script>
</head>
<body>
    <header>
        <nav>
            <a href="<?= isLoggedIn() ? (isUsuario() ? 'dashboard.php' : (isVeterinario() ? 'dashboard_vet.php' : 'dashboard_admin.php')) : 'index.php' ?>" class="logo">🾠PraPet</a>
            <ul>
                <li><a href="produtos.php">← Voltar aos Produtos</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?= isUsuario() ? 'dashboard.php' : (isVeterinario() ? 'dashboard_vet.php' : 'dashboard_admin.php') ?>">Dashboard</a></li>
                    <li><a href="logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a href="login.php">Entrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="breadcrumb">
            <a href="produtos.php">Produtos</a> / 
            <?php if ($produto['categoria']): ?>
                <a href="produtos.php?categoria=<?= urlencode($produto['categoria']) ?>"><?= htmlspecialchars($produto['categoria']) ?></a> / 
            <?php endif; ?>
            <?= htmlspecialchars($produto['nome']) ?>
        </div>

        <?php if ($sucesso): ?>
            <div class="alert-success">✓ <?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <div class="product-detail">
            <div class="product-image-section">
                <div class="main-image">🛒</div>
            </div>
            
            <div class="product-info-section">
                <?php if ($produto['categoria']): ?>
                    <span class="product-category-badge"><?= htmlspecialchars($produto['categoria']) ?></span>
                <?php endif; ?>
                
                <h1 class="product-title"><?= htmlspecialchars($produto['nome']) ?></h1>
                
                <div class="product-price">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
                
                <div class="product-description">
                    <?= nl2br(htmlspecialchars($produto['descricao'])) ?>
                </div>
                
                <div class="stock-info <?= $produto['estoque'] > 0 ? '' : 'out' ?>">
                    <?php if ($produto['estoque'] > 0): ?>
                        ✓ Em estoque (<?= $produto['estoque'] ?> unidades disponíveis)
                    <?php else: ?>
                        ✗ Produto indisponível no momento
                    <?php endif; ?>
                </div>
                
                <?php if ($produto['estoque'] > 0): ?>
                    <form action="adicionar_carrinho.php" method="POST">
                        <input type="hidden" name="id_produto" value="<?= $produto['id_produto'] ?>">
                        
                        <div class="quantity-selector">
                            <span class="quantity-label">Quantidade:</span>
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                                <input 
                                    type="number" 
                                    id="quantity" 
                                    name="quantidade" 
                                    class="quantity-input" 
                                    value="1" 
                                    min="1" 
                                    max="<?= $produto['estoque'] ?>"
                                    onchange="validateQuantity()"
                                >
                                <button type="button" class="quantity-btn" onclick="updateQuantity(1)">+</button>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button type="submit" name="acao" value="adicionar" class="btn-add-cart">
                                🛒 Adicionar ao Carrinho
                            </button>
                            <button type="submit" name="acao" value="comprar" class="btn-buy-now">
                                Comprar Agora
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
                
                <ul class="product-features">
                    <li>Entrega para todo o Brasil</li>
                    <li>Frete grátis acima de R$ 150,00</li>
                    <li>Pagamento seguro</li>
                    <li>Troca grátis em até 30 dias</li>
                    <li>Garantia de qualidade</li>
                </ul>
            </div>
        </div>

        <?php if (!empty($relacionados)): ?>
            <div class="related-section">
                <h2 class="section-title">Produtos Relacionados</h2>
                <div class="related-grid">
                    <?php foreach ($relacionados as $rel): ?>
                        <a href="produto_detalhes.php?id=<?= $rel['id_produto'] ?>" class="related-card">
                            <div class="related-image">🛒</div>
                            <div class="related-info">
                                <div class="related-name"><?= htmlspecialchars($rel['nome']) ?></div>
                                <div class="related-price">R$ <?= number_format($rel['preco'], 2, ',', '.') ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Incluir Carrinho Flutuante -->
    <?php include 'carrinho_flutuante.php'; ?>
</body>
</html>