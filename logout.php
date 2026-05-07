<?php
require_once __DIR__ . '/includes/config.php';
require_login();
audit_log('LOGOUT', 'logout', 'Sessão encerrada');
session_destroy();
header('Location: /index.php');
exit;
