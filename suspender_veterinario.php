<?php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_veterinario = $_POST['id_veterinario'] ?? 0;
    
    if ($id_veterinario) {
        try {
            $stmt = $conn->prepare("UPDATE veterinarios SET status = 'suspenso' WHERE id_veterinario = ?");
            $stmt->execute([$id_veterinario]);
            $_SESSION['sucesso'] = 'Veterinário suspenso com sucesso!';
        } catch (PDOException $e) {
            $_SESSION['erro'] = 'Erro ao suspender veterinário: ' . $e->getMessage();
        }
    }
}

redirect('gerenciar_veterinarios.php');
?>