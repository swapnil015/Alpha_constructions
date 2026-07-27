<?php
if (!defined('ALPHA_BOOTSTRAP')) require __DIR__ . '/../../includes/bootstrap.php';
auth_logout();
redirect('/admin/login');
