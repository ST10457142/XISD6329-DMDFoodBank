<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';
session_start_safe();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    session_unset();
    session_destroy();
    session_start();
    flash('success', 'You have been logged out successfully.');
    header('Location: login.php');
    exit;
}

header('Location: index.php');
exit;
