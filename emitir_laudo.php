<?php
require_once 'config.php';
requireLogin();

if (!isVeterinario()) {
    redirect('index.php');
}

$id_pet = $_GET['pet'] ?? 0;
$erro = '';
$sucesso = '';

// Buscar informações do pet
if ($id_pet) {
    $stmt = $conn->prepare("SELECT p.*, u.nome as tutor_nome FROM pets p 
                           INNER JOIN usuarios u ON p.id_usuario = u.id_usuario 
                           WHERE p.id_pet = ?");
    $stmt->execute([$id_pet]);
    $pet = $stmt->fetch();
    
    if (!$pet) {
        redirect('buscar_pets.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pet = $_POST['id_pet'] ?? 0;
    $tipo_laudo = $_POST['tipo_laudo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $resultado = $_POST['resultado'] ?? '';
    $data_laudo = $_POST['data_laudo'] ?? '';
    
    if (empty($id_pet) || empty($tipo_laudo) || empty($descricao) || empty($data_laudo)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO laudos (id_pet, id_veterinario, tipo_laudo, descricao, resultado, data_laudo) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $id_pet,
                $_SESSION['user_id'],
                $tipo_laudo,
                $descricao,
                $resultado,
                $data_laudo
            ]);
            
            $sucesso = 'Laudo emitido com sucesso!';
            header("refresh:2;url=dashboard_vet.php");
        } catch (PDOException $e) {
            $erro = 'Erro ao emitir laudo: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emitir Laudo - PraPet</title>
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
            max-width: 900px;
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
            min-height: 150px;
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
                <li><a href="dashboard_vet.php">Dashboard</a></li>
                <li><a href="buscar_pets.php">Buscar Pets</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <?php if (isset($pet)): ?>
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
                    <p style="color: #666;">
                        <strong>Tutor:</strong> <?= htmlspecialchars($pet['tutor_nome']) ?> | 
                        <?= ucfirst($pet['sexo']) ?>
                        <?php if ($pet['idade']): ?>
                            | <?= $pet['idade'] ?> ano(s)
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <h1 class="page-title">📋 Emitir Laudo</h1>
            <p class="page-subtitle">Registre exames e laudos veterinários</p>
            
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
                <input type="hidden" name="id_pet" value="<?= $id_pet ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Tipo de Laudo <span class="required">*</span></label>
                        <select name="tipo_laudo" required>
                            <option value="">Selecione</option>
                            <option value="Hemograma">Hemograma</option>
                            <option value="Raio-X">Raio-X</option>
                            <option value="Ultrassom">Ultrassom</option>
                            <option value="Exame de Urina">Exame de Urina</option>
                            <option value="Exame de Fezes">Exame de Fezes</option>
                            <option value="Bioquímico">Bioquímico</option>
                            <option value="Citologia">Citologia</option>
                            <option value="Dermatológico">Dermatológico</option>
                            <option value="Cardiológico">Cardiológico</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Data do Laudo <span class="required">*</span></label>
                        <input type="date" name="data_laudo" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                
                <div class="form-group full">
                    <label>Descrição do Exame <span class="required">*</span></label>
                    <textarea name="descricao" required placeholder="Descreva o exame realizado, métodos utilizados e procedimentos..."></textarea>
                </div>
                
                <div class="form-group full">
                    <label>Resultado/Conclusão</label>
                    <textarea name="resultado" placeholder="Resultado do exame e conclusões..."></textarea>
                </div>
                
                <div class="btn-group">
                    <a href="buscar_pets.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Emitir Laudo</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>