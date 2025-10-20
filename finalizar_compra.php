<?php
require_once 'config.php';
requireLogin();

if (!isUsuario()) {
    redirect('index.php');
}

// Verificar se há itens no carrinho
if (empty($_SESSION['carrinho'])) {
    redirect('carrinho.php');
}

$user = getUserData($conn);

// Calcular totais
$subtotal = 0;
foreach ($_SESSION['carrinho'] as $item) {
    $subtotal += $item['preco'] * $item['quantidade'];
}

$frete = $subtotal >= 150 ? 0 : 15.00;
$total = $subtotal + $frete;

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $endereco_entrega = $_POST['endereco_entrega'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $metodo_pagamento = $_POST['metodo_pagamento'] ?? '';
    
    if (empty($endereco_entrega) || empty($cidade) || empty($estado) || empty($cep) || empty($metodo_pagamento)) {
        $erro = 'Por favor, preencha todos os campos';
    } else {
        try {
            // Iniciar transação
            $conn->beginTransaction();
            
            // Criar pedido
            $stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, valor_total, frete, endereco_entrega, cidade, estado, cep, metodo_pagamento, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendente')");
            $stmt->execute([$_SESSION['user_id'], $total, $frete, $endereco_entrega, $cidade, $estado, $cep, $metodo_pagamento]);
            
            $id_pedido = $conn->lastInsertId();
            
            // Inserir itens do pedido
            foreach ($_SESSION['carrinho'] as $item) {
                $stmt = $conn->prepare("INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) 
                                       VALUES (?, ?, ?, ?)");
                $stmt->execute([$id_pedido, $item['id_produto'], $item['quantidade'], $item['preco']]);
                
                // Atualizar estoque
                $stmt = $conn->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id_produto = ?");
                $stmt->execute([$item['quantidade'], $item['id_produto']]);
            }
            
            // Commit da transação
            $conn->commit();
            
            // Limpar carrinho
            $_SESSION['carrinho'] = [];
            
            $sucesso = 'Pedido realizado com sucesso! Número do pedido: #' . str_pad($id_pedido, 6, '0', STR_PAD_LEFT);
            
            // Redirecionar após 3 segundos
            header("refresh:3;url=meus_pedidos.php");
        } catch (PDOException $e) {
            $conn->rollBack();
            $erro = 'Erro ao processar pedido: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - PraPet</title>
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
        
        .checkout-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .form-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .required {
            color: #e53e3e;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
            font-family: inherit;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .order-summary {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.3rem;
        }
        
        .item-quantity {
            color: #666;
            font-size: 0.9rem;
        }
        
        .item-price {
            color: #667eea;
            font-weight: bold;
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
        
        .payment-options {
            display: grid;
            gap: 1rem;
        }
        
        .payment-option {
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-option:hover {
            border-color: #667eea;
        }
        
        .payment-option input[type="radio"] {
            width: auto;
            margin-right: 0.5rem;
        }
        
        .payment-option label {
            cursor: pointer;
            display: flex;
            align-items: center;
            margin: 0;
        }
        
        .btn-submit {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
            margin-top: 2rem;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 10px;
            border: 1px solid;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border-color: #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border-color: #cfc;
        }
        
        @media (max-width: 968px) {
            .checkout-layout {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🐾 PraPet</a>
            <ul>
                <li><a href="carrinho.php">← Voltar ao Carrinho</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1 class="page-title">🛒 Finalizar Compra</h1>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($sucesso) ?>
                <br>Redirecionando para seus pedidos...
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="checkout-layout">
                <div>
                    <div class="form-section" style="margin-bottom: 2rem;">
                        <h2 class="section-title">📍 Endereço de Entrega</h2>
                        
                        <div class="form-group">
                            <label>Endereço Completo <span class="required">*</span></label>
                            <input type="text" name="endereco_entrega" required placeholder="Rua, número, complemento" value="<?= htmlspecialchars($user['endereco'] ?? '') ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Cidade <span class="required">*</span></label>
                                <input type="text" name="cidade" required value="<?= htmlspecialchars($user['cidade'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Estado <span class="required">*</span></label>
                                <select name="estado" required>
                                    <option value="">Selecione</option>
                                    <option value="PR" <?= ($user['estado'] ?? '') === 'PR' ? 'selected' : '' ?>>Paraná</option>
                                    <option value="SP">São Paulo</option>
                                    <option value="RJ">Rio de Janeiro</option>
                                    <option value="MG">Minas Gerais</option>
                                    <!-- Outros estados -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>CEP <span class="required">*</span></label>
                            <input type="text" name="cep" required placeholder="00000-000" maxlength="9">
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2 class="section-title">💳 Método de Pagamento</h2>
                        
                        <div class="payment-options">
                            <div class="payment-option">
                                <label>
                                    <input type="radio" name="metodo_pagamento" value="pix" required>
                                    <span style="font-size: 1.5rem; margin: 0 0.5rem;">📱</span>
                                    <div>
                                        <strong>PIX</strong><br>
                                        <small style="color: #666;">Pagamento instantâneo</small>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="payment-option">
                                <label>
                                    <input type="radio" name="metodo_pagamento" value="cartao_credito" required>
                                    <span style="font-size: 1.5rem; margin: 0 0.5rem;">💳</span>
                                    <div>
                                        <strong>Cartão de Crédito</strong><br>
                                        <small style="color: #666;">Em até 12x sem juros</small>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="payment-option">
                                <label>
                                    <input type="radio" name="metodo_pagamento" value="boleto" required>
                                    <span style="font-size: 1.5rem; margin: 0 0.5rem;">🧾</span>
                                    <div>
                                        <strong>Boleto Bancário</strong><br>
                                        <small style="color: #666;">Vencimento em 3 dias úteis</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="order-summary">
                    <h2 class="section-title">Resumo do Pedido</h2>
                    
                    <?php foreach ($_SESSION['carrinho'] as $item): ?>
                        <div class="summary-item">
                            <div class="item-info">
                                <div class="item-name"><?= htmlspecialchars($item['nome']) ?></div>
                                <div class="item-quantity">Qtd: <?= $item['quantidade'] ?> × R$ <?= number_format($item['preco'], 2, ',', '.') ?></div>
                            </div>
                            <div class="item-price">
                                R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Frete:</span>
                        <span><?= $frete > 0 ? 'R$ ' . number_format($frete, 2, ',', '.') : 'GRÁTIS' ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        Confirmar Pedido
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>