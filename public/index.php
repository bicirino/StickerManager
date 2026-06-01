<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../src/models/Models.php';
require_once __DIR__ . '/../src/controllers/Controllers.php';

$r = $_GET['r'] ?? (current_user() ? 'dashboard' : 'login');

$routes = [
    'login'              => 'ctrl_login',
    'registrar'          => 'ctrl_registrar',
    'logout'             => 'ctrl_logout',
    'dashboard'          => 'ctrl_dashboard',
    'colecao'            => 'ctrl_colecao',
    'repetidas'          => 'ctrl_repetidas',
    'toggle'             => 'ctrl_toggle',
    'ajustar'            => 'ctrl_ajustar',
    'figurinhas'         => 'ctrl_figurinhas',
    'figurinha_form'     => 'ctrl_figurinha_form',
    'figurinha_salvar'   => 'ctrl_figurinha_salvar',
    'figurinha_excluir'  => 'ctrl_figurinha_excluir',
    'relatorio'          => 'ctrl_relatorio',
];

if (!isset($routes[$r])) { http_response_code(404); $r = current_user() ? 'dashboard' : 'login'; }
$routes[$r]();
