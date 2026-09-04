<?php
require_once 'layout.php';

// Toggle user status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $uid = intval($_POST['user_id'] ?? 0);
    $act = $_POST['action'] ?? '';
    if ($uid > 0 && in_array($act, ['activate','deactivate'])) {
        db()->prepare('UPDATE users SET is_active=? WHERE id=?')
           ->execute([$act === 'activate' ? 1 : 0, $uid]);
        flash('success', 'User status updated.');
        header('Location: donors.php'); exit;
    }
}

// Search
$search = trim($_GET['q'] ?? '');
$where  = "WHERE role != 'admin'";
$params = [];
if ($search) {
    $where  .= " AND (full_name LIKE ? OR email LIKE ?)";
    $params  = ["%$search%", "%$search%"];
}
$stmt = db()->prepare("SELECT * FROM users $where ORDER BY total_donated DESC, created_at DESC");
$stmt->execute($params);
$donors = $stmt->fetchAll();

$flash_success = flash('success');
admin_head('Donors');
?>
<body>
<?php admin_topbar(); ?>
<div class="admin-layout">
<?php admin_sidebar('donors'); ?>
<main class="admin-main">
  <div class="admin-header">
    <h1>❤️ Donor Management</h1>
    <p>View, search and manage all registered users.</p>
  </div>

  <?php if ($flash_success): ?>
    <div class="alert alert--success" style="margin-bottom:1.5rem;">✅ <?= h($flash_success) ?></div>
  <?php endif; ?>

  <!-- SEARCH -->
  <form method="GET" style="display:flex;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
    <input type="text" name="q" value="<?= h($search) ?>" placeholder="Search by name or email…"
           style="flex:1;min-width:200px;padding:0.65rem 1rem;border-radius:var(--radius-sm);border:1.5px solid var(--clr-border);font-family:var(--font-body);font-size:0.9rem;background:var(--clr-surface)">
    <button type="submit" class="btn btn--dark">Search</button>
    <?php if ($search): ?><a href="donors.php" class="btn btn--ghost">Clear</a><?php endif; ?>
  </form>

  <p style="color:var(--clr-muted);font-size:0.85rem;margin-bottom:1rem;"><?= count($donors) ?> user(s) found<?= $search ? ' for "'.h($search).'"' : '' ?></p>

  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead>
        <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Donor Type</th><th>Total Donated</th><th>Donations</th><th>Joined</th><th>Status</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php if (empty($donors)): ?>
          <tr><td colspan="10" style="text-align:center;color:var(--clr-hint);padding:2rem;">No users found.</td></tr>
        <?php else: ?>
          <?php foreach ($donors as $u): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><strong><?= h($u['full_name']) ?></strong></td>
            <td><?= h($u['email']) ?></td>
            <td><span class="badge badge--blue"><?= h($u['role']) ?></span></td>
            <td><?= h($u['donor_type'] ?? '—') ?></td>
            <td><strong style="color:var(--clr-earth2);"><?= format_zar($u['total_donated']) ?></strong></td>
            <td><?= $u['donation_count'] ?></td>
            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td><span class="badge <?= $u['is_active'] ? 'badge--green' : 'badge--red' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td>
              <form method="POST" style="display:inline;">
                <?php csrf_field(); ?>
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <input type="hidden" name="action" value="<?= $u['is_active'] ? 'deactivate' : 'activate' ?>">
                <button type="submit" class="btn <?= $u['is_active'] ? 'btn--danger' : 'btn--green' ?>" style="padding:0.3rem 0.75rem;font-size:0.78rem;" onclick="return confirm('Are you sure?')">
                  <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</div>
</body>
</html>
