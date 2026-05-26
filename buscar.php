<?php
/**
 * Busca Avançada - StickerManager
 * 
 * Página com 3 filtros avançados:
 * - País (Seleção)
 * - Posição do Jogador
 * - Status (obtida, faltante, repetida)
 * 
 * @package StickerManager
 * @subpackage Search
 */

// Incluir configurações
require_once 'db/conexao.php';
require_once 'sessao.php';

// ====================================================
// Validar Sessão
// ====================================================
validarSessao(true);

$id_usuario = obterIdUsuario();

// ====================================================
// Variáveis de Paginação e Filtros
// ====================================================
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$itens_por_pagina = 12;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

$filtro_pais = isset($_GET['pais']) ? intval($_GET['pais']) : 0;
$filtro_posicao = isset($_GET['posicao']) ? intval($_GET['posicao']) : 0;
$filtro_status = isset($_GET['status']) ? $_GET['status'] : '';

// ====================================================
// Buscar Opções para Filtros
// ====================================================
try {
    // Seleções (Países)
    $sql_paises = "SELECT id_selecao, nome_selecao FROM Selecoes ORDER BY nome_selecao";
    $resultado_paises = $conexao->query($sql_paises);
    $paises = $resultado_paises->fetch_all(MYSQLI_ASSOC);
    
    // Posições
    $sql_posicoes = "SELECT id_posicao, nome_posicao FROM Posicao ORDER BY nome_posicao";
    $resultado_posicoes = $conexao->query($sql_posicoes);
    $posicoes = $resultado_posicoes->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    error_log("Erro ao buscar filtros: " . $e->getMessage());
    $paises = [];
    $posicoes = [];
}

// ====================================================
// Buscar Figurinhas com Filtros
// ====================================================
try {
    // Construir query base
    $sql_base = "SELECT f.*, c.nome_categoria, s.nome_selecao, p.nome_posicao,
                        mc.status, mc.quantidade_obtida, mc.quantidade_repetida
                 FROM Figurinhas f
                 LEFT JOIN Categoria c ON f.id_categoria = c.id_categoria
                 LEFT JOIN Selecoes s ON f.id_selecao = s.id_selecao
                 LEFT JOIN Posicao p ON f.id_posicao = p.id_posicao
                 LEFT JOIN Minha_Colecao mc ON f.id_figurinha = mc.id_figurinha AND mc.id_usuario = ?
                 WHERE 1=1";
    
    $tipos = 'i';
    $parametros = [$id_usuario];
    
    // Aplicar filtro de país
    if ($filtro_pais > 0) {
        $sql_base .= " AND f.id_selecao = ?";
        $tipos .= 'i';
        $parametros[] = $filtro_pais;
    }
    
    // Aplicar filtro de posição
    if ($filtro_posicao > 0) {
        $sql_base .= " AND f.id_posicao = ?";
        $tipos .= 'i';
        $parametros[] = $filtro_posicao;
    }
    
    // Aplicar filtro de status
    if (!empty($filtro_status) && in_array($filtro_status, ['obtida', 'faltante', 'repetida'])) {
        $sql_base .= " AND (mc.status = ? OR (mc.id_colecao IS NULL AND ? = 'faltante'))";
        $tipos .= 'ss';
        $parametros[] = $filtro_status;
        $parametros[] = $filtro_status;
    }
    
    // Contar total de registros
    $sql_count = "SELECT COUNT(*) as total FROM (" . $sql_base . ") as temp";
    $stmt_count = preparar($sql_count, $tipos, $parametros);
    $resultado_count = $stmt_count->get_result();
    $total_registros = $resultado_count->fetch_assoc()['total'];
    $stmt_count->close();
    
    $total_paginas = ceil($total_registros / $itens_por_pagina);
    
    // Buscar figurinhas com paginação
    $sql_base .= " ORDER BY f.numero_figurinha ASC LIMIT ? OFFSET ?";
    $tipos .= 'ii';
    $parametros[] = $itens_por_pagina;
    $parametros[] = $offset;
    
    $stmt = preparar($sql_base, $tipos, $parametros);
    $resultado = $stmt->get_result();
    $figurinhas = $resultado->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Erro ao buscar figurinhas: " . $e->getMessage());
    $figurinhas = [];
    $total_registros = 0;
    $total_paginas = 1;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busca Avançada - StickerManager</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
        }
        
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-custom .navbar-brand {
            font-size: 22px;
            font-weight: bold;
            color: white !important;
        }
        
        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        
        .navbar-custom .nav-link:hover {
            color: white !important;
        }
        
        .container-main {
            padding-top: 30px;
            padding-bottom: 40px;
        }
        
        .filtro-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 10px 12px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
        }
        
        .card-figurinha {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            height: 100%;
        }
        
        .card-figurinha:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .figurinha-imagem {
            width: 100%;
            height: 150px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: #ddd;
            position: relative;
        }
        
        .figurinha-status {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
        }
        
        .figurinha-status.obtida {
            background: var(--success);
        }
        
        .figurinha-status.faltante {
            background: var(--danger);
        }
        
        .figurinha-status.repetida {
            background: var(--warning);
        }
        
        .figurinha-info {
            padding: 12px;
        }
        
        .figurinha-numero {
            font-size: 16px;
            font-weight: bold;
            color: var(--primary);
        }
        
        .figurinha-nome {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .badge-filtro {
            display: inline-block;
            background: #e0e0e0;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-right: 8px;
            margin-bottom: 8px;
        }
        
        .badge-filtro .remove {
            cursor: pointer;
            margin-left: 6px;
            color: #666;
        }
        
        .resultados-info {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-sticker-mule"></i> StickerManager
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="buscar.php">
                            <i class="fas fa-search"></i> Buscar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Conteúdo Principal -->
    <div class="container-main">
        <div class="container">
            
            <!-- Titulo -->
            <h3 class="mb-4">
                <i class="fas fa-search"></i> Busca Avançada
            </h3>
            
            <!-- Filtros -->
            <div class="filtro-card">
                <form method="GET" action="buscar.php" class="row g-3">
                    
                    <!-- Filtro: País -->
                    <div class="col-md-4">
                        <label for="pais" class="form-label">País / Seleção</label>
                        <select class="form-select" id="pais" name="pais">
                            <option value="">-- Todas as seleções --</option>
                            <?php foreach ($paises as $pais): ?>
                                <option value="<?php echo $pais['id_selecao']; ?>" 
                                    <?php echo $filtro_pais === $pais['id_selecao'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pais['nome_selecao']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Filtro: Posição -->
                    <div class="col-md-4">
                        <label for="posicao" class="form-label">Posição do Jogador</label>
                        <select class="form-select" id="posicao" name="posicao">
                            <option value="">-- Todas as posições --</option>
                            <?php foreach ($posicoes as $pos): ?>
                                <option value="<?php echo $pos['id_posicao']; ?>" 
                                    <?php echo $filtro_posicao === $pos['id_posicao'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pos['nome_posicao']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Filtro: Status -->
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">-- Todos os status --</option>
                            <option value="obtida" <?php echo $filtro_status === 'obtida' ? 'selected' : ''; ?>>Obtidas</option>
                            <option value="faltante" <?php echo $filtro_status === 'faltante' ? 'selected' : ''; ?>>Faltantes</option>
                            <option value="repetida" <?php echo $filtro_status === 'repetida' ? 'selected' : ''; ?>>Repetidas</option>
                        </select>
                    </div>
                    
                    <!-- Botões -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="buscar.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpar Filtros
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Filtros Aplicados -->
            <?php if ($filtro_pais > 0 || $filtro_posicao > 0 || !empty($filtro_status)): ?>
                <div class="resultados-info">
                    <strong>Filtros Aplicados:</strong>
                    <?php
                    $filtros_aplicados = [];
                    foreach ($paises as $p) {
                        if ($p['id_selecao'] === $filtro_pais) {
                            $filtros_aplicados[] = "País: " . $p['nome_selecao'];
                            break;
                        }
                    }
                    foreach ($posicoes as $pos) {
                        if ($pos['id_posicao'] === $filtro_posicao) {
                            $filtros_aplicados[] = "Posição: " . $pos['nome_posicao'];
                            break;
                        }
                    }
                    if (!empty($filtro_status)) {
                        $status_display = ['obtida' => 'Obtidas', 'faltante' => 'Faltantes', 'repetida' => 'Repetidas'];
                        $filtros_aplicados[] = "Status: " . ($status_display[$filtro_status] ?? $filtro_status);
                    }
                    ?>
                    <div>
                        <?php foreach ($filtros_aplicados as $f): ?>
                            <span class="badge-filtro"><?php echo htmlspecialchars($f); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Resultados -->
            <div class="resultados-info">
                <strong>Total de Resultados:</strong> <?php echo $total_registros; ?> figurinha(s)
            </div>
            
            <!-- Grid de Figurinhas -->
            <?php if (!empty($figurinhas)): ?>
                <div class="row mb-5">
                    <?php foreach ($figurinhas as $fig): ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="card card-figurinha">
                                <div class="figurinha-imagem">
                                    <i class="fas fa-image"></i>
                                    <?php if (isset($fig['status']) && $fig['status']): ?>
                                        <span class="figurinha-status <?php echo htmlspecialchars($fig['status']); ?>">
                                            <i class="fas fa-check"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="figurinha-info">
                                    <div class="figurinha-numero">
                                        #<?php echo htmlspecialchars($fig['numero_figurinha']); ?>
                                    </div>
                                    <div class="figurinha-nome">
                                        <?php echo htmlspecialchars($fig['nome_jogador']); ?>
                                    </div>
                                    <div class="figurinha-nome" style="color: #999; font-size: 12px;">
                                        <?php echo htmlspecialchars($fig['nome_selecao'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="figurinha-nome" style="color: #999; font-size: 11px;">
                                        <?php echo htmlspecialchars($fig['nome_posicao'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginação -->
                <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Paginação">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagina_atual > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="buscar.php?pagina=1<?php echo !empty($filtro_pais) ? '&pais=' . $filtro_pais : ''; ?><?php echo !empty($filtro_posicao) ? '&posicao=' . $filtro_posicao : ''; ?><?php echo !empty($filtro_status) ? '&status=' . $filtro_status : ''; ?>">
                                        Primeira
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $pagina_atual - 2); $i <= min($total_paginas, $pagina_atual + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $pagina_atual ? 'active' : ''; ?>">
                                    <a class="page-link" href="buscar.php?pagina=<?php echo $i; ?><?php echo !empty($filtro_pais) ? '&pais=' . $filtro_pais : ''; ?><?php echo !empty($filtro_posicao) ? '&posicao=' . $filtro_posicao : ''; ?><?php echo !empty($filtro_status) ? '&status=' . $filtro_status : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagina_atual < $total_paginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="buscar.php?pagina=<?php echo $total_paginas; ?><?php echo !empty($filtro_pais) ? '&pais=' . $filtro_pais : ''; ?><?php echo !empty($filtro_posicao) ? '&posicao=' . $filtro_posicao : ''; ?><?php echo !empty($filtro_status) ? '&status=' . $filtro_status : ''; ?>">
                                        Última
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhuma figurinha encontrada com os filtros aplicados.
                </div>
            <?php endif; ?>
            
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
