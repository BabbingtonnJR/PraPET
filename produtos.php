<?php
require_once 'config.php';

// Buscar produtos
$categoria = $_GET['categoria'] ?? '';
$busca = $_GET['busca'] ?? '';

$sql = "SELECT * FROM produtos WHERE ativo = 1";
$params = [];

if (!empty($categoria)) {
    $sql .= " AND categoria = ?";
    $params[] = $categoria;
}

if (!empty($busca)) {
    $sql .= " AND (nome LIKE ? OR descricao LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

$sql .= " ORDER BY data_cadastro DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();

// Buscar categorias
$stmt = $conn->query("SELECT DISTINCT categoria FROM produtos WHERE ativo = 1 AND categoria IS NOT NULL");
$categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Contar itens no carrinho
$cart_count = 0;
if (isset($_SESSION['carrinho'])) {
    $cart_count = array_sum(array_column($_SESSION['carrinho'], 'quantidade'));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - PraPet</title>
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

        .cart-link {
            position: relative;
            font-size: 1.5rem;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #f44336;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
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
            margin-bottom: 1rem;
        }
        
        .search-filter {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .search-input {
            flex: 1;
            min-width: 300px;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
        }
        
        .filter-select {
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
        }
        
        .btn-search {
            padding: 0.8rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-category {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        
        .product-name {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .product-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .product-price {
            color: #667eea;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .product-stock {
            color: #4caf50;
            font-size: 0.85rem;
        }

        .product-stock.out {
            color: #f44336;
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
                    <li><a href="<?= isUsuario() ? 'dashboard.php' : (isVeterinario() ? 'dashboard_vet.php' : 'dashboard_admin.php') ?>">Dashboard</a></li>
                    <li><a href="logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn">Entrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">🛒 Produtos para Pets</h1>
            
            <form method="GET" class="search-filter">
                <input 
                    type="text" 
                    name="busca" 
                    class="search-input" 
                    placeholder="Buscar produtos..." 
                    value="<?= htmlspecialchars($busca) ?>"
                >
                
                <select name="categoria" class="filter-select">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $categoria === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn-search">Buscar</button>
            </form>
        </div>

        <?php if (empty($produtos)): ?>
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h3>Nenhum produto encontrado</h3>
                <p style="color: #666; margin-top: 0.5rem;">Tente buscar por outros termos ou categorias</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($produtos as $produto): ?>
                    <a href="produto_detalhes.php?id=<?= $produto['id_produto'] ?>" class="product-card">
                        <div class="product-image">🛒</div>
                        <div class="product-info">
                            <?php if ($produto['categoria']): ?>
                                <span class="product-category"><?= htmlspecialchars($produto['categoria']) ?></span>
                            <?php endif; ?>
                            
                            <div class="product-name"><?= htmlspecialchars($produto['nome']) ?></div>
                            
                            <div class="product-description">
                                <?= htmlspecialchars(substr($produto['descricao'], 0, 100)) ?>...
                            </div>
                            
                            <div class="product-footer">
                                <div class="product-price">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
                                <div class="product-stock <?= $produto['estoque'] > 0 ? '' : 'out' ?>">
                                    <?php if ($produto['estoque'] > 0): ?>
                                        ✓ Em estoque
                                    <?php else: ?>
                                        ✗ Indisponível
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </a>
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