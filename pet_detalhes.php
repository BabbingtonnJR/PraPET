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

// Buscar consultas
$stmt = $conn->prepare("SELECT c.*, v.nome as veterinario_nome FROM consultas c 
                       INNER JOIN veterinarios v ON c.id_veterinario = v.id_veterinario 
                       WHERE c.id_pet = ? ORDER BY c.data_consulta DESC LIMIT 5");
$stmt->execute([$id_pet]);
$consultas = $stmt->fetchAll();

// Buscar vacinas
$stmt = $conn->prepare("SELECT * FROM vacinas WHERE id_pet = ? ORDER BY data_aplicacao DESC LIMIT 5");
$stmt->execute([$id_pet]);
$vacinas = $stmt->fetchAll();

// Buscar laudos
$stmt = $conn->prepare("SELECT l.*, v.nome as veterinario_nome FROM laudos l 
                       INNER JOIN veterinarios v ON l.id_veterinario = v.id_veterinario 
                       WHERE l.id_pet = ? ORDER BY l.data_laudo DESC LIMIT 5");
$stmt->execute([$id_pet]);
$laudos = $stmt->fetchAll();

// Buscar alimentação
$stmt = $conn->prepare("SELECT * FROM alimentacao WHERE id_pet = ?");
$stmt->execute([$id_pet]);
$alimentacao = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pet['nome']) ?> - Detalhes</title>
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
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .pet-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }
        
        .pet-avatar {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            flex-shrink: 0;
        }
        
        .pet-info {
            flex: 1;
        }
        
        .pet-name {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .pet-species {
            font-size: 1.2rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .pet-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .detail-item {
            background: #f8f9fa;
            padding: 0.8rem;
            border-radius: 8px;
        }
        
        .detail-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.3rem;
        }
        
        .detail-value {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
        }
        
        .pet-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #4caf50;
            color: white;
        }
        
        .tabs {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .tab-btn {
            flex: 1;
            padding: 1rem;
            background: none;
            border: none;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            color: #666;
        }
        
        .tab-btn.active {
            color: #667eea;
            border-bottom: 3px solid #667eea;
        }
        
        .tab-content {
            padding: 2rem;
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .records-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .record-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .record-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }
        
        .record-date {
            color: #999;
            font-size: 0.9rem;
        }
        
        .record-content {
            color: #666;
            line-height: 1.6;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .info-box {
            background: #e3f2fd;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #2196f3;
        }
        
        .info-box h3 {
            color: #1976d2;
            margin-bottom: 0.5rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ddd;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
    </style>
    <script>
        function showTab(tabName) {
            // Esconder todos os conteúdos
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remover active de todos os botões
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Mostrar conteúdo selecionado
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🐾 PraPet</a>
            <ul>
                <li><a href="dashboard.php">← Voltar ao Dashboard</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="pet-header">
            <div class="pet-avatar">
                <?php
                $emoji = match($pet['especie']) {
                    'Cachorro' => '🐕',
                    'Gato' => '🐈',
                    'Pássaro' => '🐦',
                    'Coelho' => '🐰',
                    default => '🐾'
                };
                echo $emoji;
                ?>
            </div>
            
            <div class="pet-info">
                <h1 class="pet-name"><?= htmlspecialchars($pet['nome']) ?></h1>
                <div class="pet-species"><?= htmlspecialchars($pet['especie']) ?> <?= $pet['raca'] ? '- ' . htmlspecialchars($pet['raca']) : '' ?></div>
                
                <div class="pet-details-grid">
                    <?php if ($pet['sexo']): ?>
                        <div class="detail-item">
                            <div class="detail-label">Sexo</div>
                            <div class="detail-value"><?= ucfirst($pet['sexo']) ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($pet['idade']): ?>
                        <div class="detail-item">
                            <div class="detail-label">Idade</div>
                            <div class="detail-value"><?= $pet['idade'] ?> ano(s)</div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($pet['peso']): ?>
                        <div class="detail-item">
                            <div class="detail-label">Peso</div>
                            <div class="detail-value"><?= number_format($pet['peso'], 1, ',', '.') ?> kg</div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($pet['cor']): ?>
                        <div class="detail-item">
                            <div class="detail-label">Cor</div>
                            <div class="detail-value"><?= htmlspecialchars($pet['cor']) ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($pet['data_nascimento']): ?>
                        <div class="detail-item">
                            <div class="detail-label">Nascimento</div>
                            <div class="detail-value"><?= date('d/m/Y', strtotime($pet['data_nascimento'])) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($pet['observacoes']): ?>
                    <p style="margin-top: 1rem; color: #666;">
                        <strong>Observações:</strong> <?= htmlspecialchars($pet['observacoes']) ?>
                    </p>
                <?php endif; ?>
                
                <div class="pet-actions">
                    <a href="editar_pet.php?id=<?= $pet['id_pet'] ?>" class="btn btn-secondary">✏️ Editar Informações</a>
                    <a href="adicionar_vacina.php?id=<?= $pet['id_pet'] ?>" class="btn btn-primary">💉 Adicionar Vacina</a>
                </div>
            </div>
        </div>

        <div class="tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="showTab('consultas')">🩺 Consultas</button>
                <button class="tab-btn" onclick="showTab('vacinas')">💉 Vacinas</button>
                <button class="tab-btn" onclick="showTab('laudos')">📋 Laudos</button>
                <button class="tab-btn" onclick="showTab('alimentacao')">🍖 Alimentação</button>
            </div>
            
            <!-- Tab Consultas -->
            <div id="consultas" class="tab-content active">
                <?php if (empty($consultas)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🩺</div>
                        <h3>Nenhuma consulta registrada</h3>
                        <p>As consultas realizadas por veterinários aparecerão aqui</p>
                    </div>
                <?php else: ?>
                    <div class="records-list">
                        <?php foreach ($consultas as $consulta): ?>
                            <div class="record-card">
                                <div class="record-header">
                                    <div class="record-title"><?= htmlspecialchars($consulta['motivo'] ?: 'Consulta Geral') ?></div>
                                    <div class="record-date"><?= date('d/m/Y', strtotime($consulta['data_consulta'])) ?></div>
                                </div>
                                <div class="record-content">
                                    <p><strong>Veterinário:</strong> <?= htmlspecialchars($consulta['veterinario_nome']) ?></p>
                                    <p><strong>Diagnóstico:</strong> <?= htmlspecialchars($consulta['diagnostico']) ?></p>
                                    <?php if ($consulta['prescricao']): ?>
                                        <p><strong>Prescrição:</strong> <?= htmlspecialchars($consulta['prescricao']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($consulta['peso_consulta']): ?>
                                        <p><strong>Peso:</strong> <?= number_format($consulta['peso_consulta'], 1, ',', '.') ?> kg</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Tab Vacinas -->
            <div id="vacinas" class="tab-content">
                <?php if (empty($vacinas)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">💉</div>
                        <h3>Nenhuma vacina registrada</h3>
                        <p>Mantenha o cartão de vacinas do seu pet sempre atualizado</p>
                        <br>
                        <a href="adicionar_vacina.php?id=<?= $pet['id_pet'] ?>" class="btn btn-primary">Adicionar Primeira Vacina</a>
                    </div>
                <?php else: ?>
                    <div class="records-list">
                        <?php foreach ($vacinas as $vacina): ?>
                            <div class="record-card">
                                <div class="record-header">
                                    <div class="record-title"><?= htmlspecialchars($vacina['tipo_vacina']) ?></div>
                                    <div class="record-date"><?= date('d/m/Y', strtotime($vacina['data_aplicacao'])) ?></div>
                                </div>
                                <div class="record-content">
                                    <?php if ($vacina['fabricante']): ?>
                                        <p><strong>Fabricante:</strong> <?= htmlspecialchars($vacina['fabricante']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($vacina['lote']): ?>
                                        <p><strong>Lote:</strong> <?= htmlspecialchars($vacina['lote']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($vacina['proxima_dose']): ?>
                                        <p><strong>Próxima Dose:</strong> <?= date('d/m/Y', strtotime($vacina['proxima_dose'])) ?></p>
                                    <?php endif; ?>
                                    <?php if ($vacina['veterinario']): ?>
                                        <p><strong>Veterinário:</strong> <?= htmlspecialchars($vacina['veterinario']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Tab Laudos -->
            <div id="laudos" class="tab-content">
                <?php if (empty($laudos)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <h3>Nenhum laudo registrado</h3>
                        <p>Exames e laudos realizados por veterinários aparecerão aqui</p>
                    </div>
                <?php else: ?>
                    <div class="records-list">
                        <?php foreach ($laudos as $laudo): ?>
                            <div class="record-card">
                                <div class="record-header">
                                    <div class="record-title"><?= htmlspecialchars($laudo['tipo_laudo']) ?></div>
                                    <div class="record-date"><?= date('d/m/Y', strtotime($laudo['data_laudo'])) ?></div>
                                </div>
                                <div class="record-content">
                                    <p><strong>Veterinário:</strong> <?= htmlspecialchars($laudo['veterinario_nome']) ?></p>
                                    <p><strong>Descrição:</strong> <?= htmlspecialchars($laudo['descricao']) ?></p>
                                    <?php if ($laudo['resultado']): ?>
                                        <p><strong>Resultado:</strong> <?= htmlspecialchars($laudo['resultado']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($laudo['arquivo_url']): ?>
                                        <p><a href="<?= htmlspecialchars($laudo['arquivo_url']) ?>" target="_blank" style="color: #667eea;">📎 Ver Arquivo</a></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Tab Alimentação -->
            <div id="alimentacao" class="tab-content">
                <?php if (!$alimentacao): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🍖</div>
                        <h3>Plano alimentar não configurado</h3>
                        <p>Configure a alimentação do seu pet para manter o controle</p>
                        <br>
                        <a href="configurar_alimentacao.php?id=<?= $pet['id_pet'] ?>" class="btn btn-primary">Configurar Alimentação</a>
                    </div>
                <?php else: ?>
                    <div class="info-box">
                        <h3>🍖 Plano Alimentar</h3>
                        <div class="info-row">
                            <span>Tipo de Ração:</span>
                            <strong><?= htmlspecialchars($alimentacao['tipo_racao']) ?></strong>
                        </div>
                        <?php if ($alimentacao['marca']): ?>
                            <div class="info-row">
                                <span>Marca:</span>
                                <strong><?= htmlspecialchars($alimentacao['marca']) ?></strong>
                            </div>
                        <?php endif; ?>
                        <div class="info-row">
                            <span>Quantidade por Refeição:</span>
                            <strong><?= $alimentacao['quantidade_gramas'] ?> gramas</strong>
                        </div>
                        <div class="info-row">
                            <span>Frequência Diária:</span>
                            <strong><?= $alimentacao['frequencia_diaria'] ?>x ao dia</strong>
                        </div>
                        <?php if ($alimentacao['horarios']): ?>
                            <div class="info-row">
                                <span>Horários:</span>
                                <strong><?= htmlspecialchars($alimentacao['horarios']) ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if ($alimentacao['restricoes_alimentares']): ?>
                            <div class="info-row">
                                <span>Restrições:</span>
                                <strong><?= htmlspecialchars($alimentacao['restricoes_alimentares']) ?></strong>
                            </div>
                        <?php endif; ?>
                        <br>
                        <a href="configurar_alimentacao.php?id=<?= $pet['id_pet'] ?>" class="btn btn-secondary">✏️ Editar Alimentação</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>