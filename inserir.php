<?php
/**
 * Inserir Figurinha - StickerManager
 * 
 * Formulário para cadastro de novas figurinhas na coleção do usuário.
 * 
 * @package StickerManager
 * @subpackage CRUD
 */

// Incluir configurações
require_once 'db/conexao.php';
require_once 'sessao.php';

// ====================================================
// Validar Sessão
// ====================================================
validarSessao(true);

$id_usuario = obterIdUsuario();
$erro = '';
$sucesso = '';

// ====================================================
// Buscar Dados para Formulário
// ====================================================
try {
    $sql_figurinhas = "SELECT f.id_figurinha, f.numero_figurinha, f.nome_jogador, 
                              s.nome_selecao, p.nome_posicao, c.nome_categoria
                       FROM Figurinhas f
                       LEFT JOIN Selecoes s ON f.id_selecao = s.id_selecao
                       LEFT JOIN Posicao p ON f.id_posicao = p.id_posicao
                       LEFT JOIN Categoria c ON f.id_categoria = c.id_categoria
                       ORDER BY f.numero_figurinha";
    $resultado_figurinhas = $conexao->query($sql_figurinhas);
    $figurinhas = $resultado_figurinhas->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao buscar figurinhas: " . $e->getMessage());
    $figurinhas = [];
}

// ====================================================
// Processar Formulário
// ====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_figurinha = isset($_POST['id_figurinha']) ? intval($_POST['id_figurinha']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $quantidade_obtida = isset($_POST['quantidade_obtida']) ? intval($_POST['quantidade_obtida']) : 0;
    $quantidade_repetida = isset($_POST['quantidade_repetida']) ? intval($_POST['quantidade_repetida']) : 0;
    
    // Validação básica
    if ($id_figurinha <= 0) {
        $erro = "Selecione uma figurinha válida.";
    } elseif (empty($status) || !in_array($status, ['obtida', 'faltante', 'repetida'])) {
        $erro = "Selecione um status válido.";
    } else {
        
        try {
            // Verificar se já existe na coleção
            $sql_verif = "SELECT id_colecao FROM Minha_Colecao 
                         WHERE id_usuario = ? AND id_figurinha = ?";
            $stmt = preparar($sql_verif, 'ii', [$id_usuario, $id_figurinha]);
            $resultado = $stmt->get_result();
            $stmt->close();
            
            if ($resultado->num_rows > 0) {
                $erro = "Esta figurinha já está na sua coleção. Use a opção de editar.";
            } else {
                
                // Inserir nova figurinha na coleção
                $sql_insert = "INSERT INTO Minha_Colecao 
                              (id_usuario, id_figurinha, status, quantidade_obtida, quantidade_repetida)
                              VALUES (?, ?, ?, ?, ?)";
                
                $stmt = preparar($sql_insert, 'iisii', [
                    $id_usuario,
                    $id_figurinha,
                    $status,
                    $status === 'obtida' ? $quantidade_obtida : 0,
                    $status === 'repetida' ? $quantidade_repetida : 0
                ]);
                
                if ($stmt) {
                    $sucesso = "Figurinha adicionada com sucesso à sua coleção!";
                } else {
                    $erro = "Erro ao adicionar figurinha. Tente novamente.";
                }
                $stmt->close();
            }
            
        } catch (Exception $e) {
            error_log("Erro ao inserir figurinha: " . $e->getMessage());
            $erro = "Erro ao processar. Tente novamente.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Figurinha - StickerManager</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
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
        
        .container-main {
            padding-top: 30px;
            padding-bottom: 40px;
        }
        
        .card-form {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: none;
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
                        <a class="nav-link" href="buscar.php">
                            <i class="fas fa-search"></i> Buscar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="inserir.php">
                            <i class="fas fa-plus"></i> Cadastrar
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
            <div class="row justify-content-center">
                <div class="col-md-8">
                    
                    <!-- Card do Formulário -->
                    <div class="card card-form">
                        <div class="card-header bg-transparent border-bottom">
                            <h5 class="mb-0">
                                <i class="fas fa-plus-circle"></i> Adicionar Figurinha à Coleção
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- Mensagens -->
                            <?php if (!empty($erro)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($sucesso)): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($sucesso); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Formulário -->
                            <form method="POST" action="inserir.php">
                                
                                <!-- Figurinha -->
                                <div class="mb-3">
                                    <label for="id_figurinha" class="form-label">Figurinha <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_figurinha" name="id_figurinha" required>
                                        <option value="">-- Selecione uma figurinha --</option>
                                        <?php foreach ($figurinhas as $fig): ?>
                                            <option value="<?php echo $fig['id_figurinha']; ?>">
                                                #<?php echo $fig['numero_figurinha']; ?> - 
                                                <?php echo htmlspecialchars($fig['nome_jogador']); ?> 
                                                (<?php echo htmlspecialchars($fig['nome_selecao'] ?? 'N/A'); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Status -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required onchange="atualizarCampos()">
                                        <option value="">-- Selecione um status --</option>
                                        <option value="obtida">Obtida</option>
                                        <option value="faltante">Faltante</option>
                                        <option value="repetida">Repetida</option>
                                    </select>
                                </div>
                                
                                <!-- Quantidade Obtida -->
                                <div class="mb-3" id="div_quantidade_obtida" style="display: none;">
                                    <label for="quantidade_obtida" class="form-label">Quantidade Obtida</label>
                                    <input type="number" class="form-control" id="quantidade_obtida" name="quantidade_obtida" value="1" min="1">
                                </div>
                                
                                <!-- Quantidade Repetida -->
                                <div class="mb-3" id="div_quantidade_repetida" style="display: none;">
                                    <label for="quantidade_repetida" class="form-label">Quantidade Repetida</label>
                                    <input type="number" class="form-control" id="quantidade_repetida" name="quantidade_repetida" value="1" min="1">
                                </div>
                                
                                <!-- Botões -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-save"></i> Adicionar
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="index.php" class="btn btn-secondary w-100">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    </div>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function atualizarCampos() {
            const status = document.getElementById('status').value;
            const divObtida = document.getElementById('div_quantidade_obtida');
            const divRepetida = document.getElementById('div_quantidade_repetida');
            
            // Ocultar todos
            divObtida.style.display = 'none';
            divRepetida.style.display = 'none';
            
            // Mostrar conforme status
            if (status === 'obtida') {
                divObtida.style.display = 'block';
            } else if (status === 'repetida') {
                divRepetida.style.display = 'block';
            }
        }
    </script>
</body>
</html>
