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

// Buscar alimentação existente
$stmt = $conn->prepare("SELECT * FROM alimentacao WHERE id_pet = ?");
$stmt->execute([$id_pet]);
$alimentacao = $stmt->fetch();

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_racao = $_POST['tipo_racao'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $quantidade_gramas = $_POST['quantidade_gramas'] ?? 0;
    $frequencia_diaria = $_POST['frequencia_diaria'] ?? 0;
    $horarios = $_POST['horarios'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';
    $restricoes_alimentares = $_POST['restricoes_alimentares'] ?? '';
    
    if (empty($tipo_racao) || empty($quantidade_gramas) || empty($frequencia_diaria)) {
        $erro = 'Por favor, preencha os campos obrigatórios';
    } else {
        try {
            if ($alimentacao) {
                // Atualizar
                $stmt = $conn->prepare("UPDATE alimentacao SET tipo_racao = ?, marca = ?, quantidade_gramas = ?, frequencia_diaria = ?, horarios = ?, observacoes = ?, restricoes_alimentares = ? WHERE id_pet = ?");
                $stmt->execute([$tipo_racao, $marca, $quantidade_gramas, $frequencia_diaria, $horarios, $observacoes, $restricoes_alimentares, $id_pet]);
            } else {
                // Inserir
                $stmt = $conn->prepare("INSERT INTO alimentacao (id_pet, tipo_racao, marca, quantidade_gramas, frequencia_diaria, horarios, observacoes, restricoes_alimentares) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_pet, $tipo_racao, $marca, $quantidade_gramas, $frequencia_diaria, $horarios, $observacoes, $restricoes_alimentares]);
            }
            
            $sucesso = 'Alimentação configurada com sucesso!';
            header("refresh:2;url=pet_detalhes.php?id=$id_pet");
        } catch (PDOException $e) {
            $erro = 'Erro ao configurar alimentação: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Alimentação - PraPet</title>
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
            min-height: 100px;
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
            <h1 class="page-title">🍖 Configurar Alimentação</h1>
            <p class="page-subtitle">Defina o plano alimentar de <?= htmlspecialchars($pet['nome']) ?></p>
            
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
                <div class="form-row">
                    <div class="form-group">
                        <label>Tipo de Ração <span class="required">*</span></label>
                        <input type="text" name="tipo_racao" required placeholder="Ex: Ração Premium Adulto" value="<?= htmlspecialchars($alimentacao['tipo_racao'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" name="marca" placeholder="Ex: Royal Canin" value="<?= htmlspecialchars($alimentacao['marca'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Quantidade por Refeição (gramas) <span class="required">*</span></label>
                        <input type="number" name="quantidade_gramas" required min="1" placeholder="Ex: 200" value="<?= $alimentacao['quantidade_gramas'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Frequência Diária <span class="required">*</span></label>
                        <input type="number" name="frequencia_diaria" required min="1" max="10" placeholder="Vezes por dia" value="<?= $alimentacao['frequencia_diaria'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="form-group full">
                    <label>Horários</label>
                    <input type="text" name="horarios" placeholder="Ex: 08h, 12h, 18h" value="<?= htmlspecialchars($alimentacao['horarios'] ?? '') ?>">
                </div>
                
                <div class="form-group full">
                    <label>Restrições Alimentares</label>
                    <textarea name="restricoes_alimentares" placeholder="Alergias, intolerâncias ou alimentos que o pet não pode consumir..."><?= htmlspecialchars($alimentacao['restricoes_alimentares'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group full">
                    <label>Observações</label>
                    <textarea name="observacoes" placeholder="Informações adicionais sobre a alimentação..."><?= htmlspecialchars($alimentacao['observacoes'] ?? '') ?></textarea>
                </div>
                
                <div class="btn-group">
                    <a href="pet_detalhes.php?id=<?= $id_pet ?>" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><?= $alimentacao ? 'Atualizar' : 'Salvar' ?> Alimentação</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>