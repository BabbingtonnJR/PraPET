<?php
require_once 'config.php';

// Buscar produtos em destaque
$stmt = $conn->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY data_cadastro DESC LIMIT 6");
$produtos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PraPet - Cuidando do seu melhor amigo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        /* Header */
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
            transition: opacity 0.3s;
        }
        
        nav a:hover {
            opacity: 0.8;
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
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 20px;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
            padding: 1rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            transition: transform 0.3s;
        }
        
        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
            padding: 1rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        .btn-primary:hover, .btn-secondary:hover {
            transform: translateY(-3px);
        }
        
        /* Features Section */
        .features {
            max-width: 1200px;
            margin: 4rem auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .feature-card h3 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        /* Products Section */
        .products-section {
            background: #f5f5f5;
            padding: 4rem 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #333;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
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
        
        .product-name {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .product-price {
            color: #667eea;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 1rem 0;
        }
        
        /* Footer */
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🐾 PraPet</a>
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="produtos.php">Produtos</a></li>
                <li><a href="planos.php">Planos</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn">Entrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <h1>🐾 Bem-vindo ao PraPet</h1>
        <p>A plataforma completa para cuidar da saúde do seu pet</p>
        <div class="hero-buttons">
            <a href="cadastro.php" class="btn-primary">Cadastre-se Grátis</a>
            <a href="planos.php" class="btn-secondary">Conheça os Planos</a>
        </div>
    </section>

    <section class="features">
        <div class="feature-card">
            <div class="feature-icon">📋</div>
            <h3>Histórico Completo</h3>
            <p>Mantenha todos os registros médicos do seu pet organizados em um só lugar</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💉</div>
            <h3>Controle de Vacinas</h3>
            <p>Nunca mais esqueça as datas de vacinação do seu amigo peludo</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🩺</div>
            <h3>Veterinários Certificados</h3>
            <p>Acesso a profissionais qualificados para cuidar do seu pet</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">👥</div>
            <h3>Comunidade</h3>
            <p>Compartilhe experiências e aprenda com outros tutores</p>
        </div>
    </section>

    <section class="products-section">
        <div class="container">
            <h2 class="section-title">Produtos em Destaque</h2>
            <div class="products-grid">
                <?php foreach ($produtos as $produto): ?>
                    <div class="product-card">
                        <div class="product-image">🛒</div>
                        <div class="product-info">
                            <div class="product-name"><?= htmlspecialchars($produto['nome']) ?></div>
                            <p><?= htmlspecialchars(substr($produto['descricao'], 0, 80)) ?>...</p>
                            <div class="product-price">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
                            <a href="produtos.php" style="color: #667eea; text-decoration: none; font-weight: bold;">Ver mais →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 PraPet - Todos os direitos reservados</p>
    </footer>
</body>
</html>