<?php
// Redirect to dashboard if logged in, otherwise to login
require_once __DIR__ . '/includes/PathHelper.php';
require_once __DIR__ . '/includes/SessionManager.php';

$sessionManager = new SessionManager();

if ($sessionManager->isLoggedIn()) {
    header('Location: ' . PathHelper::page('dashboard.php'));
} else {
    header('Location: ' . PathHelper::page('login.php'));
}
exit;

