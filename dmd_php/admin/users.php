<?php
require_once 'layout.php';

// Promote/demote/toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $id  = intval($_POST['user_id'] ?? 0);
    $act = $_POST['action'] ?? '';
    if ($id > 0) {
        if ($act === 'make_admin') {
            db()->prepare("UPDATE users SET role='admin' WHERE id=?")->execute([$id]);
            flash('success', 'User promoted to admin.');
        } elseif ($act === 'make_donor') {
            db()->prepare("UPDATE users SET role='donor' WHERE id=?")->execute([$id]);
            flash('success', 'User role changed to donor.');
        } elseif ($act === 'toggle_active') {
            db()->prepare("UPDATE users SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
            flash('success', 'User status toggled.');
        }
        header('Location: users.php'); exit;
    }
}

$users = db()->query("SELECT * FROM users ORDER BY role DESC, created_at DESC")->fetchAll();
$flash_success = flash('success');
admin_head('All Users');
?>
<body>
<?php admin_topbar(); ?>
<div class="admin-layout">
<?php admin_sidebar('users'); ?>
<main class="admin-main">
  <div class="admin-header">
    <h1>👥 All Users</h1>
    <p>Full user management including role assignment.</p>
  </div>

  <?php if ($flash_success): ?>
    <div class="alert alert--success" style="margin-bottom:1.5rem;">✅ <?= h($flash_success) ?></div>
  <?php endif; ?>

  <p style="margin-bottom:1rem;color:var(--clr-muted);font-size:0.88rem;"><?= count($users) ?> total user(s)</p>

  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Total Donated</th><th>Joined</th><th>Last Login</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><strong><?= h($u['full_name']) ?></strong></td>
          <td><?= h($u['email']) ?></td>
          <td><span class="badge <?= $u['role'] === 'admin' ? 'badge--red' : 'badge--blue' ?>"><?= h($u['role']) ?></span></td>
          <td><?= format_zar($u['total_donated']) ?></td>
          <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td><?= $u['last_login'] ? date('d M Y', strtotime($u['last_login'])) : '<em style="color:var(--clr-hint)">Never</em>' ?></td>
          <td><span class="badge <?= $u['is_active'] ? 'badge--green' : 'badge--red' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
          <td>
            <form method="POST" style="display:flex;gap:0.35rem;flex-wrap:wrap;">
              <?php csrf_field(); ?>
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <?php if ($u['role'] !== 'admin'): ?>
                <button name="action" value="make_admin" class="btn btn--ghost" style="padding:0.2rem 0.5rem;font-size:0.72rem;" onclick="return confirm('Make this user an admin?')">+Admin</button>
              <?php elseif ($u['id'] !== $_SESSION['user_id']): ?>
                <button name="action" value="make_donor" class="btn btn--ghost" style="padding:0.2rem 0.5rem;font-size:0.72rem;" onclick="return confirm('Remove admin role?')">-Admin</button>
              <?php endif; ?>
              <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                <button name="action" value="toggle_active" class="btn <?= $u['is_active'] ? 'btn--danger' : 'btn--green' ?>" style="padding:0.2rem 0.5rem;font-size:0.72rem;" onclick="return confirm('Toggle status?')">
                  <?= $u['is_active'] ? 'Disable' : 'Enable' ?>
                </button>
              <?php else: ?>
                <em style="font-size:0.75rem;color:var(--clr-hint);">You</em>
              <?php endif; ?>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
</div>
</body>
</html>
