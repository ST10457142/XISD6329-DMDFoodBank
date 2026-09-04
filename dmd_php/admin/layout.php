<?php // admin/layout.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
session_start_safe();
require_admin();
$admin_user = current_user();

function admin_head(string $title): void { ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title) ?> – DMD Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
body{background:var(--clr-cream)}
.admin-topbar{background:var(--clr-earth);padding:0.75rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;position:sticky;top:0;z-index:100}
.admin-topbar-brand{font-family:var(--font-display);font-size:1.2rem;color:#fff;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:0.5rem}
.admin-topbar-brand span{background:var(--clr-warm);color:var(--clr-earth);font-size:0.7rem;font-weight:800;padding:0.1rem 0.4rem;border-radius:4px;letter-spacing:0.05em;text-transform:uppercase}
.admin-topbar-right{display:flex;align-items:center;gap:1rem;font-size:0.85rem;color:rgba(255,255,255,0.7)}
.admin-topbar-right a{color:rgba(255,255,255,0.7);text-decoration:none;transition:color 0.2s}
.admin-topbar-right a:hover{color:#fff}
.admin-topbar-right form button{background:none;border:1px solid rgba(255,255,255,0.25);color:rgba(255,255,255,0.7);border-radius:var(--radius-sm);padding:0.3rem 0.75rem;cursor:pointer;font-family:var(--font-body);font-size:0.82rem;transition:all 0.2s}
.admin-topbar-right form button:hover{background:rgba(255,255,255,0.1);color:#fff}
</style>
<?php }

function admin_topbar(): void {
  global $admin_user; ?>
<div class="admin-topbar">
  <a href="index.php" class="admin-topbar-brand">🏛️ DMD Food Bank <span>Admin</span></a>
  <div class="admin-topbar-right">
    <span>👤 <?= htmlspecialchars($admin_user['full_name']) ?></span>
    <a href="../index.php">View Site</a>
    <form method="POST" action="../logout.php">
      <?php csrf_field(); ?>
      <button type="submit">Logout</button>
    </form>
  </div>
</div>
<?php }

function admin_sidebar(string $current = ''): void { ?>
<aside class="admin-sidebar" aria-label="Admin navigation">
  <p class="sidebar-label">Overview</p>
  <a href="index.php" class="<?= $current === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
  <p class="sidebar-label">Management</p>
  <a href="donors.php" class="<?= $current === 'donors' ? 'active' : '' ?>">❤️ Donors</a>
  <a href="donations.php" class="<?= $current === 'donations' ? 'active' : '' ?>">💰 Donations</a>
  <a href="beneficiaries.php" class="<?= $current === 'beneficiaries' ? 'active' : '' ?>">🤲 Beneficiaries</a>
  <a href="volunteers.php" class="<?= $current === 'volunteers' ? 'active' : '' ?>">🙋 Volunteers</a>
  <a href="messages.php" class="<?= $current === 'messages' ? 'active' : '' ?>">📬 Messages</a>
  <p class="sidebar-label">Settings</p>
  <a href="users.php" class="<?= $current === 'users' ? 'active' : '' ?>">👥 All Users</a>
  <a href="../index.php">🌐 View Site</a>
</aside>
<?php }
