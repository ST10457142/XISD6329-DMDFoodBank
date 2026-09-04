<?php
require_once 'layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $id     = intval($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($id > 0 && in_array($action, ['approved','rejected','pending'])) {
        db()->prepare("UPDATE volunteers SET status=? WHERE id=?")->execute([$action, $id]);
        flash('success', 'Volunteer status updated to ' . $action . '.');
        header('Location: volunteers.php'); exit;
    }
}

$volunteers = db()->query("SELECT * FROM volunteers ORDER BY created_at DESC")->fetchAll();
$flash_success = flash('success');
admin_head('Volunteers');
?>
<body>
<?php admin_topbar(); ?>
<div class="admin-layout">
<?php admin_sidebar('volunteers'); ?>
<main class="admin-main">
  <div class="admin-header">
    <h1>🙋 Volunteer Applications</h1>
    <p>Review and manage volunteer sign-ups.</p>
  </div>

  <?php if ($flash_success): ?>
    <div class="alert alert--success" style="margin-bottom:1.5rem;">✅ <?= h($flash_success) ?></div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:2rem;">
    <div class="stat-card"><h3><?= count($volunteers) ?></h3><p>Total applications</p></div>
    <div class="stat-card"><h3><?= db()->query("SELECT COUNT(*) FROM volunteers WHERE status='pending'")->fetchColumn() ?></h3><p>Pending</p></div>
    <div class="stat-card stat-card--accent"><h4><?= db()->query("SELECT COUNT(*) FROM volunteers WHERE status='approved'")->fetchColumn() ?></h4><p>Approved volunteers</p></div>
  </div>

  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Skills</th><th>Availability</th><th>Applied</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php if (empty($volunteers)): ?>
          <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--clr-hint);">No volunteer applications yet.</td></tr>
        <?php else: ?>
          <?php foreach ($volunteers as $v): ?>
          <tr>
            <td><?= $v['id'] ?></td>
            <td><strong><?= h($v['full_name']) ?></strong></td>
            <td><?= h($v['email']) ?></td>
            <td><?= h($v['skills'] ?? '—') ?></td>
            <td><?= h($v['availability'] ?? '—') ?></td>
            <td><?= date('d M Y', strtotime($v['created_at'])) ?></td>
            <td><span class="badge <?= $v['status'] === 'approved' ? 'badge--green' : ($v['status'] === 'rejected' ? 'badge--red' : 'badge--orange') ?>"><?= h($v['status']) ?></span></td>
            <td>
              <?php if ($v['status'] === 'pending'): ?>
                <form method="POST" style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="id" value="<?= $v['id'] ?>">
                  <button name="action" value="approved" class="btn btn--green" style="padding:0.25rem 0.6rem;font-size:0.75rem;">Approve</button>
                  <button name="action" value="rejected" class="btn btn--danger" style="padding:0.25rem 0.6rem;font-size:0.75rem;" onclick="return confirm('Reject?')">Reject</button>
                </form>
              <?php else: ?>
                <form method="POST">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="id" value="<?= $v['id'] ?>">
                  <button name="action" value="pending" class="btn btn--ghost" style="padding:0.25rem 0.6rem;font-size:0.75rem;">Reset</button>
                </form>
              <?php endif; ?>
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
