<?php
require_once 'config.php';
requireLogin();

if (!isUsuario()) {
    redirect('index.php');
}

$id_pet = $_GET['id'] ?? 0;
$id_usuario = $_SESSION['user_id'];

// Buscar pet
$stmt = $conn->prepare("SELECT * FROM pets WHERE id_pet = ? AND id_usuario = ?");
$stmt->execute([$id_pet, $id_usuario]);
$pet = $stmt->fetch();

if (!$pet) {
    redirect('dashboard.php');
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_vacina = $_POST['tipo_vacina'] ?? '';
    $fabricante = $_POST['fabricante'] ?? '';
    $lote = $_POST['lote'] ?? '';
    $data_aplicacao = $_POST['data_aplicacao'] ?? '';
    $proxima_dose = $_POST['proxima_dose'] ?? null;
    $veterinario = $_POST['veterinario'] ?? '';
    $local_aplicacao = $_POST['local_aplicacao'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';
    
    if (empty($tipo_vacina) || empty($data_aplicacao)) {
        $erro = 'Por favor, preencha os campos obrigatórios';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO vacinas (id_pet, tipo_vacina, fabricante, lote, data_aplicacao, proxima_dose, veterinario, local_aplicacao, observacoes) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_pet, $tipo_vacina, $fabricante, $lote, $data_aplicacao, $proxima_dose, $veterinario, $local_aplicacao, $observacoes]);
            
            $sucesso = 'Vacina registrada com sucesso!';
            header("refresh:2;url=pet_detalhes.php?id=$id_pet");
        } catch (PDOException $e) {
            $erro = 'Erro ao registrar vacina: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Vacina - PraPet</title>
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
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .pet-info-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .pet-avatar {
            font-size: 4rem;
        }
        
        .pet-info h2 {
            color: #667eea;
            margin-bottom: 0.3rem;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .page-title {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group.full {
            grid-column: 1 / -1;
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
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
            text-decoration: none;
            text-align: center;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
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
        
        @media (max-width: 600px) {
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
                <li><a href="pet_detalhes.php?id=<?= $id_pet ?>">← Voltar</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="pet-info-card">
            <div class="pet-avatar">
                <?php
                $emoji = match($pet['especie']) {
                    'Cachorro' => '🐕',
                    'Gato' => '🐈',
                    'Pássaro' => '🦜',
                    'Coelho' => '🐰',
                    default => '🐾'
                };
                echo $emoji;
                ?>
            </div>
            <div class="pet-info">
                <h2><?= htmlspecialchars($pet['nome']) ?></h2>
                <p style="color: #667eea; font-weight: bold;">
                    <?= htmlspecialchars($pet['especie']) ?> 
                    <?= $pet['raca'] ? '- ' . htmlspecialchars($pet['raca']) : '' ?>
                </p>
            </div>
        </div>

        <div class="form-card">
            <h1 class="page-title">💉 Adicionar Vacina</h1>
            <p class="page-subtitle">Registre a vacinação de <?= htmlspecialchars($pet['nome']) ?></p>
            
            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            
            <?php if ($sucesso): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($sucesso) ?>
                    <br>Redirecionando...
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group full">
                    <label>Tipo de Vacina <span class="required">*</span></label>
                    <input type="text" name="tipo_vacina" required placeholder="Ex: V10, Antirrábica, Giardia">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Fabricante</label>
                        <input type="text" name="fabricante" placeholder="Ex: Zoetis, Virbac">
                    </div>
                    
                    <div class="form-group">
                        <label>Lote</label>
                        <input type="text" name="lote" placeholder="Número do lote">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Data de Aplicação <span class="required">*</span></label>
                        <input type="date" name="data_aplicacao" required value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Próxima Dose</label>
                        <input type="date" name="proxima_dose">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Veterinário Responsável</label>
                        <input type="text" name="veterinario" placeholder="Nome do veterinário">
                    </div>
                    
                    <div class="form-group">
                        <label>Local de Aplicação</label>
                        <input type="text" name="local_aplicacao" placeholder="Ex: Clínica VetPet">
                    </div>
                </div>
                
                <div class="form-group full">
                    <label>Observações</label>
                    <textarea name="observacoes" placeholder="Reações, observações ou informações adicionais..."></textarea>
                </div>
                
                <div class="btn-group">
                    <a href="pet_detalhes.php?id=<?= $id_pet ?>" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Registrar Vacina</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>