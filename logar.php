<?php
/**
 * Tela de Login - StickerManager
 * 
 * Página responsável por autenticar o usuário no sistema.
 * Processa o formulário de login e valida credenciais.
 * 
 * @package StickerManager
 * @subpackage Authentication
 */

// Incluir configurações
require_once 'db/conexao.php';
require_once 'sessao.php';

// ====================================================
// Se já está logado, redirecionar para dashboard
// ====================================================
if (validarSessao(false)) {
    header("Location: index.php");
    exit();
}

// ====================================================
// Variáveis de controle
// ====================================================
$erro = '';
$sucesso = '';
$campo_usuario = '';

// ====================================================
// Processar Formulário de Login
// ====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validar se os campos foram preenchidos
    if (empty($_POST['usuario']) || empty($_POST['senha'])) {
        $erro = "Usuário e senha são obrigatórios.";
    } else {
        
        $usuario = trim($_POST['usuario']);
        $senha = $_POST['senha'];
        $campo_usuario = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');
        
        // Validar comprimento mínimo da senha
        if (strlen($senha) < 3) {
            $erro = "Senha inválida.";
        } else {
            
            try {
                // Preparar consulta segura
                $sql = "SELECT id_usuario, nome_usuario, email, senha_hash 
                        FROM Usuarios 
                        WHERE nome_usuario = ? AND ativo = 1";
                
                $stmt = preparar($sql, 's', [$usuario]);
                
                if (!$stmt) {
                    throw new Exception("Erro ao preparar consulta");
                }
                
                $resultado = $stmt->get_result();
                
                // Verificar se usuário existe
                if ($resultado->num_rows === 1) {
                    $linha = $resultado->fetch_assoc();
                    
                    // Verificar se a senha está correta
                    if (password_verify($senha, $linha['senha_hash'])) {
                        
                        // Iniciar sessão
                        iniciarSessao($linha['id_usuario'], $linha['nome_usuario'], $linha['email']);
                        
                        // Atualizar último acesso
                        $sql_update = "UPDATE Usuarios SET ultimo_acesso = NOW() WHERE id_usuario = ?";
                        preparar($sql_update, 'i', [$linha['id_usuario']]);
                        
                        // Redirecionar para dashboard
                        header("Location: index.php");
                        exit();
                        
                    } else {
                        $erro = "Usuário ou senha incorretos.";
                    }
                } else {
                    $erro = "Usuário ou senha incorretos.";
                }
                
                $stmt->close();
                
            } catch (Exception $e) {
                error_log("Erro no login: " . $e->getMessage());
                $erro = "Erro ao processar login. Tente novamente.";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StickerManager</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .login-header p {
            color: #999;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .alert-custom {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }
        
        .icon-input {
            position: relative;
        }
        
        .icon-input i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .icon-input .form-control {
            padding-left: 40px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Cabeçalho -->
        <div class="login-header">
            <h1><i class="fas fa-sticker-mule"></i> StickerManager</h1>
            <p>Gerenciador de Coleção de Figurinhas</p>
        </div>
        
        <!-- Mensagens de Erro/Sucesso -->
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger alert-custom" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($sucesso)): ?>
            <div class="alert alert-success alert-custom" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($sucesso); ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulário de Login -->
        <form method="POST" action="logar.php" novalidate>
            
            <!-- Campo: Usuário -->
            <div class="form-group">
                <label for="usuario" class="form-label">Usuário</label>
                <div class="icon-input">
                    <i class="fas fa-user"></i>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="usuario" 
                        name="usuario" 
                        placeholder="Digite seu usuário"
                        value="<?php echo $campo_usuario; ?>"
                        required
                        autocomplete="username"
                    >
                </div>
            </div>
            
            <!-- Campo: Senha -->
            <div class="form-group">
                <label for="senha" class="form-label">Senha</label>
                <div class="icon-input">
                    <i class="fas fa-lock"></i>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="senha" 
                        name="senha" 
                        placeholder="Digite sua senha"
                        required
                        autocomplete="current-password"
                    >
                </div>
            </div>
            
            <!-- Botão de Login -->
            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>
        
        <!-- Rodapé -->
        <div style="text-align: center; margin-top: 30px; color: #999; font-size: 13px;">
            <p>StickerManager © 2026 | Desenvolvido com ❤️</p>
            <p>Para desenvolvimento, use: <strong>usuario_teste</strong></p>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
