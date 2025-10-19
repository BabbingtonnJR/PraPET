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
    $nome = $_POST['nome'] ?? '';
    $especie = $_POST['especie'] ?? '';
    $raca = $_POST['raca'] ?? '';
    $sexo = $_POST['sexo'] ?? '';
    $idade = $_POST['idade'] ?? null;
    $peso = $_POST['peso'] ?? null;
    $cor = $_POST['cor'] ?? '';
    $data_nascimento = $_POST['data_nascimento'] ?? null;
    $observacoes = $_POST['observacoes'] ?? '';
    
    if (empty($nome) || empty($especie)) {
        $erro = 'Por favor, preencha os campos obrigatórios';
    } else {
        try {
            $stmt = $conn->prepare("UPDATE pets SET nome = ?, especie = ?, raca = ?, sexo = ?, idade = ?, peso = ?, cor = ?, data_nascimento = ?, observacoes = ? WHERE id_pet = ? AND id_usuario = ?");
            $stmt->execute([$nome, $especie, $raca, $sexo, $idade, $peso, $cor, $data_nascimento, $observacoes, $id_pet, $id_usuario]);
            
            $sucesso = 'Pet atualizado com sucesso!';
            header("refresh:2;url=pet_detalhes.php?id=$id_pet");
        } catch (PDOException $e) {
            $erro = 'Erro ao atualizar pet: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pet - PraPet</title>
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
        
        .btn-danger {
            background: #f44336;
            color: white;
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
        <div class="form-card">
            <h1 class="page-title">✏️ Editar Pet</h1>
            <p class="page-subtitle">Atualize as informações de <?= htmlspecialchars($pet['nome']) ?></p>
            
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
                        <label>Nome do Pet <span class="required">*</span></label>
                        <input type="text" name="nome" required value="<?= htmlspecialchars($pet['nome']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Espécie <span class="required">*</span></label>
                        <select name="especie" required>
                            <option value="">Selecione</option>
                            <option value="Cachorro" <?= $pet['especie'] === 'Cachorro' ? 'selected' : '' ?>>Cachorro</option>
                            <option value="Gato" <?= $pet['especie'] === 'Gato' ? 'selected' : '' ?>>Gato</option>
                            <option value="Pássaro" <?= $pet['especie'] === 'Pássaro' ? 'selected' : '' ?>>Pássaro</option>
                            <option value="Coelho" <?= $pet['especie'] === 'Coelho' ? 'selected' : '' ?>>Coelho</option>
                            <option value="Hamster" <?= $pet['especie'] === 'Hamster' ? 'selected' : '' ?>>Hamster</option>
                            <option value="Peixe" <?= $pet['especie'] === 'Peixe' ? 'selected' : '' ?>>Peixe</option>
                            <option value="Outro" <?= $pet['especie'] === 'Outro' ? 'selected' : '' ?>>Outro</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Raça</label>
                        <input type="text" name="raca" value="<?= htmlspecialchars($pet['raca']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Sexo</label>
                        <select name="sexo">
                            <option value="">Selecione</option>
                            <option value="macho" <?= $pet['sexo'] === 'macho' ? 'selected' : '' ?>>Macho</option>
                            <option value="femea" <?= $pet['sexo'] === 'femea' ? 'selected' : '' ?>>Fêmea</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Data de Nascimento</label>
                        <input type="date" name="data_nascimento" value="<?= $pet['data_nascimento'] ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Idade (anos)</label>
                        <input type="number" name="idade" min="0" max="30" value="<?= $pet['idade'] ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Peso (kg)</label>
                        <input type="number" name="peso" step="0.1" min="0" value="<?= $pet['peso'] ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Cor</label>
                        <input type="text" name="cor" value="<?= htmlspecialchars($pet['cor']) ?>">
                    </div>
                </div>
                
                <div class="form-group full">
                    <label>Observações</label>
                    <textarea name="observacoes"><?= htmlspecialchars($pet['observacoes']) ?></textarea>
                </div>
                
                <div class="btn-group">
                    <a href="pet_detalhes.php?id=<?= $id_pet ?>" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>