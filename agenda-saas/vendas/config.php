<?php
// Constantes para o banco de dados de Vendas, com prefixo para evitar conflitos
if (!defined('VENDAS_DB_HOST')) {
    define('VENDAS_DB_HOST', 'localhost');
}
if (!defined('VENDAS_DB_NAME')) {
    define('VENDAS_DB_NAME', 'agenda_adminacesso');
}
if (!defined('VENDAS_DB_USER')) {
    define('VENDAS_DB_USER', 'root');
}
if (!defined('VENDAS_DB_PASS')) {
    define('VENDAS_DB_PASS', '');
}

// Configuração do Mercado Pago
if (!defined('MP_ACCESS_TOKEN')) {
    define('MP_ACCESS_TOKEN', 'APP_USR-207779743612928-100101-a9bdbffdadea0e0af34ea297c2bf0dd8-789705155');
}
if (!defined('MP_WEBHOOK_SECRET')) {
    define('MP_WEBHOOK_SECRET', 'SUA_SECRET_KEY_DO_WEBHOOK_AQUI');
}
?>
