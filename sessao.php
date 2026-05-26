<?php
/**
 * Controle de Sessão - StickerManager
 * 
 * Arquivo que implementa a trava de segurança verificando
 * se o usuário está devidamente autenticado. Deve ser incluído
 * no início de todas as páginas protegidas.
 * 
 * @package StickerManager
 * @subpackage Security
 */

// ====================================================
// Iniciar Sessão
// ====================================================

// Iniciar sessão apenas se ela ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ====================================================
// Configurações de Segurança da Sessão
// ====================================================

// Definir cookie seguro (apenas HTTPS em produção)
ini_set('session.cookie_secure', false);  // Mudar para true em produção com HTTPS
ini_set('session.cookie_httponly', true);  // Impede acesso via JavaScript
ini_set('session.cookie_samesite', 'Strict');  // Proteção contra CSRF

// Tempo de expiração da sessão (em segundos)
$tempo_expiracao = 3600;  // 1 hora

// ====================================================
// Função: Validar Sessão do Usuário
// ====================================================

/**
 * Verifica se o usuário está autenticado
 * Se não estiver, redireciona para login
 * 
 * @param bool $redirecionar Se deve redirecionar para login em caso de não autenticado
 * @return bool True se autenticado, False caso contrário
 */
function validarSessao($redirecionar = true) {
    
    // Verificar se id_usuario está na sessão
    if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
        if ($redirecionar) {
            header("Location: logar.php");
            exit();
        }
        return false;
    }
    
    // Verificar se token de sessão está válido (proteção contra fixação de sessão)
    if (!isset($_SESSION['token_sessao']) || empty($_SESSION['token_sessao'])) {
        session_destroy();
        if ($redirecionar) {
            header("Location: logar.php");
            exit();
        }
        return false;
    }
    
    // Verificar tempo de inatividade
    if (isset($_SESSION['ultimo_acesso'])) {
        $tempo_inativo = time() - $_SESSION['ultimo_acesso'];
        
        if ($tempo_inativo > 3600) {  // 1 hora de inatividade
            session_destroy();
            if ($redirecionar) {
                $_SESSION['mensagem_erro'] = "Sua sessão expirou. Faça login novamente.";
                header("Location: logar.php");
                exit();
            }
            return false;
        }
    }
    
    // Atualizar último acesso
    $_SESSION['ultimo_acesso'] = time();
    
    return true;
}

/**
 * Inicia uma nova sessão após login bem-sucedido
 * 
 * @param int $id_usuario ID do usuário
 * @param string $nome_usuario Nome do usuário
 * @param string $email Email do usuário
 * @return void
 */
function iniciarSessao($id_usuario, $nome_usuario, $email) {
    
    // Regenerar ID da sessão (proteção contra fixação)
    session_regenerate_id(true);
    
    // Armazenar dados do usuário na sessão
    $_SESSION['id_usuario'] = $id_usuario;
    $_SESSION['nome_usuario'] = $nome_usuario;
    $_SESSION['email'] = $email;
    $_SESSION['token_sessao'] = bin2hex(random_bytes(16));
    $_SESSION['ip_usuario'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['ultimo_acesso'] = time();
    $_SESSION['data_login'] = date('Y-m-d H:i:s');
}

/**
 * Encerra a sessão de forma segura
 * 
 * @return void
 */
function encerrarSessao() {
    
    // Limpar variáveis de sessão
    $_SESSION = array();
    
    // Destruir o cookie da sessão
    if (ini_get("session.use_cookies")) {
        $parametros = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametros["path"],
            $parametros["domain"],
            $parametros["secure"],
            $parametros["httponly"]
        );
    }
    
    // Destruir a sessão
    session_destroy();
}

/**
 * Obtém o ID do usuário autenticado
 * 
 * @return int|null ID do usuário ou null se não autenticado
 */
function obterIdUsuario() {
    if (isset($_SESSION['id_usuario'])) {
        return intval($_SESSION['id_usuario']);
    }
    return null;
}

/**
 * Obtém o nome do usuário autenticado
 * 
 * @return string|null Nome do usuário ou null se não autenticado
 */
function obterNomeUsuario() {
    if (isset($_SESSION['nome_usuario'])) {
        return htmlspecialchars($_SESSION['nome_usuario'], ENT_QUOTES, 'UTF-8');
    }
    return null;
}

?>
