<?php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_veterinario = $_POST['id_veterinario'] ?? 0;
    $acao = $_POST['acao'] ?? '';
    
    if ($id_veterinario && in_array($acao, ['aprovar', 'rejeitar'])) {
        try {
            if ($acao === 'aprovar') {
                $stmt = $conn->prepare("UPDATE veterinarios SET status = 'aprovado', data_aprovacao = NOW() WHERE id_veterinario = ?");
                $stmt->execute([$id_veterinario]);
                $_SESSION['sucesso'] = 'Veterinário aprovado com sucesso!';
            } else {
                $stmt = $conn->prepare("UPDATE veterinarios SET status = 'rejeitado' WHERE id_veterinario = ?");
                $stmt->execute([$id_veterinario]);
                $_SESSION['sucesso'] = 'Veterinário rejeitado.';
            }
        } catch (PDOException $e) {
            $_SESSION['erro'] = 'Erro ao processar ação: ' . $e->getMessage();
        }
    }
}

redirect('dashboard_admin.php');
?>