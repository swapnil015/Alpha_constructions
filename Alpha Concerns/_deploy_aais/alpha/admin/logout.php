<?php
if (!defined('ALPHA_BOOTSTRAP')) require __DIR__ . '/../../alpha_private/includes/bootstrap.php';
auth_logout();
redirect('/admin/login');
