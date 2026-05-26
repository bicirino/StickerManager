<?php
/**
 * Dashboard Principal - StickerManager
 * 
 * Página central que exibe o checklist dinâmico com
 * status das figurinhas (obtidas, faltantes, repetidas).
 * 
 * @package StickerManager
 * @subpackage Dashboard
 */

// Incluir configurações
require_once 'db/conexao.php';
require_once 'sessao.php';

// ====================================================
// Validar Sessão
// ====================================================
validarSessao(true);

$id_usuario = obterIdUsuario();
$nome_usuario = obterNomeUsuario();

// ====================================================
// Variáveis de Paginação e Filtros
// ====================================================
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$itens_por_pagina = 12;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

$filtro_categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$filtro_status = isset($_GET['status']) ? $_GET['status'] : '';

// ====================================================
// Buscar Estatísticas da Coleção
// ====================================================
try {
    // Total de figurinhas obtidas
    $sql_obtidas = "SELECT COUNT(*) as total FROM Minha_Colecao 
                    WHERE id_usuario = ? AND status = 'obtida'";
    $stmt = preparar($sql_obtidas, 'i', [$id_usuario]);
    $resultado = $stmt->get_result();
    $total_obtidas = $resultado->fetch_assoc()['total'];
    $stmt->close();
    
    // Total de figurinhas faltantes
    $sql_faltantes = "SELECT COUNT(*) as total FROM Minha_Colecao 
                      WHERE id_usuario = ? AND status = 'faltante'";
    $stmt = preparar($sql_faltantes, 'i', [$id_usuario]);
    $resultado = $stmt->get_result();
    $total_faltantes = $resultado->fetch_assoc()['total'];
    $stmt->close();
    
    // Total de figurinhas repetidas
    $sql_repetidas = "SELECT COUNT(*) as total FROM Minha_Colecao 
                      WHERE id_usuario = ? AND status = 'repetida'";
    $stmt = preparar($sql_repetidas, 'i', [$id_usuario]);
    $resultado = $stmt->get_result();
    $total_repetidas = $resultado->fetch_assoc()['total'];
    $stmt->close();
    
    // Total de figurinhas registradas
    $total_figurinhas = $total_obtidas + $total_faltantes + $total_repetidas;
    
    // Percentual de conclusão
    $percentual_conclusao = $total_figurinhas > 0 ? round(($total_obtidas / $total_figurinhas) * 100) : 0;
    
} catch (Exception $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
    $total_obtidas = 0;
    $total_faltantes = 0;
    $total_repetidas = 0;
    $total_figurinhas = 0;
    $percentual_conclusao = 0;
}

// ====================================================
// Buscar Categorias para Filtro
// ====================================================
try {
    $sql_categorias = "SELECT id_categoria, nome_categoria FROM Categoria ORDER BY nome_categoria";
    $resultado_categorias = $conexao->query($sql_categorias);
    $categorias = $resultado_categorias->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao buscar categorias: " . $e->getMessage());
    $categorias = [];
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
    
    // Aplicar filtro de categoria
    if ($filtro_categoria > 0) {
        $sql_base .= " AND f.id_categoria = ?";
        $tipos .= 'i';
        $parametros[] = $filtro_categoria;
    }
    
    // Aplicar filtro de status
    if (!empty($filtro_status)) {
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
    <title>Dashboard - StickerManager</title>
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
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: color 0.3s;
        }
        
        .navbar-custom .nav-link:hover {
            color: white !important;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 5px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card.obtidas {
            border-left-color: var(--success);
        }
        
        .stat-card.faltantes {
            border-left-color: var(--danger);
        }
        
        .stat-card.repetidas {
            border-left-color: var(--warning);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary);
        }
        
        .stat-card.obtidas .stat-number {
            color: var(--success);
        }
        
        .stat-card.faltantes .stat-number {
            color: var(--danger);
        }
        
        .stat-card.repetidas .stat-number {
            color: var(--warning);
        }
        
        .stat-label {
            color: #999;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
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
        
        .filtro-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .container-main {
            padding-top: 30px;
            padding-bottom: 40px;
        }
        
        .pagination {
            margin-top: 30px;
        }
        
        .btn-acao {
            font-size: 12px;
            padding: 5px 10px;
            margin: 2px;
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
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buscar.php">
                            <i class="fas fa-search"></i> Buscar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inserir.php">
                            <i class="fas fa-plus"></i> Cadastrar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorio.php" target="_blank">
                            <i class="fas fa-file-pdf"></i> Relatório
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usuarioDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $nome_usuario; ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="usuarioDropdown">
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Conteúdo Principal -->
    <div class="container-main">
        <div class="container">
            
            <!-- Estatísticas -->
            <div class="row mb-5">
                <div class="col-md-3 mb-3">
                    <div class="stat-card obtidas">
                        <div class="stat-number"><?php echo $total_obtidas; ?></div>
                        <div class="stat-label">Obtidas</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card faltantes">
                        <div class="stat-number"><?php echo $total_faltantes; ?></div>
                        <div class="stat-label">Faltantes</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card repetidas">
                        <div class="stat-number"><?php echo $total_repetidas; ?></div>
                        <div class="stat-label">Repetidas</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $percentual_conclusao; ?>%</div>
                        <div class="stat-label">Progresso</div>
                    </div>
                </div>
            </div>
            
            <!-- Barra de Progresso -->
            <div class="card mb-4" style="box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
                <div class="card-body">
                    <h6 class="mb-3">Progresso da Coleção</h6>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $percentual_conclusao; ?>%" aria-valuenow="<?php echo $percentual_conclusao; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo $percentual_conclusao; ?>% Completo
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Seção de Filtros -->
            <div class="filtro-section">
                <form method="GET" action="index.php" class="row g-3">
                    <div class="col-md-6">
                        <label for="categoria" class="form-label">Filtrar por Categoria</label>
                        <select class="form-select" id="categoria" name="categoria">
                            <option value="">Todas as categorias</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>" 
                                    <?php echo $filtro_categoria === $cat['id_categoria'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nome_categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Filtrar por Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos os status</option>
                            <option value="obtida" <?php echo $filtro_status === 'obtida' ? 'selected' : ''; ?>>Obtidas</option>
                            <option value="faltante" <?php echo $filtro_status === 'faltante' ? 'selected' : ''; ?>>Faltantes</option>
                            <option value="repetida" <?php echo $filtro_status === 'repetida' ? 'selected' : ''; ?>>Repetidas</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Grid de Figurinhas -->
            <h5 class="mb-4">Figurinhas (Total: <?php echo $total_registros; ?>)</h5>
            
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
                                    <div style="margin-top: 10px;">
                                        <a href="editar.php?id=<?php echo $fig['id_figurinha']; ?>" class="btn btn-sm btn-warning btn-acao">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="excluir.php?id=<?php echo $fig['id_figurinha']; ?>" class="btn btn-sm btn-danger btn-acao" onclick="return confirm('Deseja excluir?')">
                                            <i class="fas fa-trash"></i> Excluir
                                        </a>
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
                                    <a class="page-link" href="index.php?pagina=1<?php echo !empty($filtro_categoria) ? '&categoria=' . $filtro_categoria : ''; ?><?php echo !empty($filtro_status) ? '&status=' . $filtro_status : ''; ?>">
                                        Primeira
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $pagina_atual - 2); $i <= min($total_paginas, $pagina_atual + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $pagina_atual ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?pagina=<?php echo $i; ?><?php echo !empty($filtro_categoria) ? '&categoria=' . $filtro_categoria : ''; ?><?php echo !empty($filtro_status) ? '&status=' . $filtro_status : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagina_atual < $total_paginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?pagina=<?php echo $total_paginas; ?><?php echo !empty($filtro_categoria) ? '&categoria=' . $filtro_categoria : ''; ?><?php echo !empty($filtro_status) ? '&status=' . $filtro_status : ''; ?>">
                                        Última
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhuma figurinha encontrada.
                </div>
            <?php endif; ?>
            
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
