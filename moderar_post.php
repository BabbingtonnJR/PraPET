<?php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_post = $_POST['id_post'] ?? 0;
    $acao = $_POST['acao'] ?? '';
    $motivo_rejeicao = $_POST['motivo_rejeicao'] ?? '';
    
    if ($id_post && in_array($acao, ['aprovar', 'rejeitar'])) {
        try {
            if ($acao === 'aprovar') {
                $stmt = $conn->prepare("UPDATE posts SET status = 'aprovado', data_moderacao = NOW(), id_moderador = ? WHERE id_post = ?");
                $stmt->execute([$_SESSION['user_id'], $id_post]);
                $_SESSION['sucesso'] = 'Post aprovado com sucesso!';
            } else {
                $stmt = $conn->prepare("UPDATE posts SET status = 'rejeitado', motivo_rejeicao = ?, data_moderacao = NOW(), id_moderador = ? WHERE id_post = ?");
                $stmt->execute([$motivo_rejeicao, $_SESSION['user_id'], $id_post]);
                $_SESSION['sucesso'] = 'Post rejeitado.';
            }
        } catch (PDOException $e) {
            $_SESSION['erro'] = 'Erro ao processar ação: ' . $e->getMessage();
        }
    }
}

redirect('dashboard_admin.php');
?>