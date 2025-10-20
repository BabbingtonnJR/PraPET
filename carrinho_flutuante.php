<?php

// Calcular itens do carrinho
$cart_count = 0;
$cart_total = 0;
if (isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
    foreach ($_SESSION['carrinho'] as $item) {
        $cart_count += $item['quantidade'];
        $cart_total += $item['preco'] * $item['quantidade'];
    }
}
?>

<style>
.cart-float {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 9999;
}

.cart-float-btn {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    position: relative;
}

.cart-float-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 30px rgba(102, 126, 234, 0.6);
}

.cart-float-icon {
    font-size: 1.8rem;
}

.cart-float-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #f44336;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
    border: 2px solid white;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.cart-float-popup {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 320px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    display: none;
    max-height: 400px;
    overflow-y: auto;
}

.cart-float-popup.show {
    display: block;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.cart-popup-header {
    padding: 1rem;
    border-bottom: 2px solid #f0f0f0;
    font-weight: bold;
    color: #333;
}

.cart-popup-items {
    padding: 1rem;
    max-height: 200px;
    overflow-y: auto;
}

.cart-popup-item {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.cart-popup-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.cart-item-image {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.cart-item-info {
    flex: 1;
    min-width: 0;
}

.cart-item-name {
    font-size: 0.9rem;
    font-weight: bold;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 0.2rem;
}

.cart-item-details {
    font-size: 0.8rem;
    color: #666;
}

.cart-popup-empty {
    padding: 2rem;
    text-align: center;
    color: #999;
}

.cart-popup-footer {
    padding: 1rem;
    border-top: 2px solid #f0f0f0;
}

.cart-total {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.cart-popup-btn {
    width: 100%;
    padding: 0.8rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    display: block;
    text-align: center;
    transition: opacity 0.3s;
}

.cart-popup-btn:hover {
    opacity: 0.9;
}

@media (max-width: 768px) {
    .cart-float {
        bottom: 20px;
        right: 20px;
    }
    
    .cart-float-btn {
        width: 55px;
        height: 55px;
    }
    
    .cart-float-popup {
        width: calc(100vw - 40px);
        right: -10px;
    }
}
</style>

<div class="cart-float" id="cartFloat">
    <button class="cart-float-btn" onclick="toggleCartPopup()">
        <span class="cart-float-icon">🛒</span>
        <?php if ($cart_count > 0): ?>
            <span class="cart-float-badge"><?= $cart_count ?></span>
        <?php endif; ?>
    </button>
    
    <div class="cart-float-popup" id="cartPopup">
        <div class="cart-popup-header">
            🛒 Carrinho (<?= $cart_count ?> <?= $cart_count == 1 ? 'item' : 'itens' ?>)
        </div>
        
        <div class="cart-popup-items">
            <?php if (empty($_SESSION['carrinho'])): ?>
                <div class="cart-popup-empty">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🛒</div>
                    <p>Carrinho vazio</p>
                </div>
            <?php else: ?>
                <?php foreach ($_SESSION['carrinho'] as $item): ?>
                    <div class="cart-popup-item">
                        <div class="cart-item-image">🛍️</div>
                        <div class="cart-item-info">
                            <div class="cart-item-name"><?= htmlspecialchars($item['nome']) ?></div>
                            <div class="cart-item-details">
                                <?= $item['quantidade'] ?>x R$ <?= number_format($item['preco'], 2, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($_SESSION['carrinho'])): ?>
            <div class="cart-popup-footer">
                <div class="cart-total">
                    <span>Total:</span>
                    <span>R$ <?= number_format($cart_total, 2, ',', '.') ?></span>
                </div>
                <a href="carrinho.php" class="cart-popup-btn">Ver Carrinho Completo</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
let cartPopupOpen = false;

function toggleCartPopup() {
    const popup = document.getElementById('cartPopup');
    cartPopupOpen = !cartPopupOpen;
    
    if (cartPopupOpen) {
        popup.classList.add('show');
    } else {
        popup.classList.remove('show');
    }
}

// Fechar ao clicar fora
document.addEventListener('click', function(event) {
    const cartFloat = document.getElementById('cartFloat');
    const popup = document.getElementById('cartPopup');
    
    if (cartPopupOpen && !cartFloat.contains(event.target)) {
        popup.classList.remove('show');
        cartPopupOpen = false;
    }
});
</script>