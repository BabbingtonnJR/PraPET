<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produto = $_POST['id_produto'] ?? 0;
    $quantidade = $_POST['quantidade'] ?? 1;
    $acao = $_POST['acao'] ?? 'adicionar';
    
    // Buscar produto
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE id_produto = ? AND ativo = 1");
    $stmt->execute([$id_produto]);
    $produto = $stmt->fetch();
    
    if ($produto) {
        // Inicializar carrinho se não existir
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }
        
        // Verificar se produto já está no carrinho
        $produto_existe = false;
        foreach ($_SESSION['carrinho'] as &$item) {
            if ($item['id_produto'] == $id_produto) {
                $item['quantidade'] += $quantidade;
                // Limitar ao estoque disponível
                if ($item['quantidade'] > $produto['estoque']) {
                    $item['quantidade'] = $produto['estoque'];
                }
                $produto_existe = true;
                break;
            }
        }
        
        // Se não existe, adicionar novo item
        if (!$produto_existe) {
            $_SESSION['carrinho'][] = [
                'id_produto' => $produto['id_produto'],
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'quantidade' => min($quantidade, $produto['estoque']),
                'estoque' => $produto['estoque']
            ];
        }
        
        $_SESSION['add_carrinho'] = 'Produto adicionado ao carrinho!';
        
        // Redirecionar baseado na ação
        if ($acao === 'comprar') {
            redirect('carrinho.php');
        } else {
            redirect('produto_detalhes.php?id=' . $id_produto);
        }
    }
}

redirect('produtos.php');