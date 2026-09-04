<?php
require_once 'layout.php';

// Handle status changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $id     = intval($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $table  = $_POST['table'] ?? '';

    if ($id > 0 && in_array($action, ['approved','rejected','pending']) && in_array($table, ['beneficiaries','volunteers'])) {
        db()->prepare("UPDATE $table SET status=? WHERE id=?")->execute([$action, $id]);
        flash('success', 'Status updated to ' . $action . '.');
        header('Location: beneficiaries.php'); exit;
    }
}

$beneficiaries = db()->query("SELECT * FROM beneficiaries ORDER BY created_at DESC")->fetchAll();
$pending_count = db()->query("SELECT COUNT(*) FROM beneficiaries WHERE status='pending'")->fetchColumn();

$flash_success = flash('success');
admin_head('Beneficiaries');
?>
<body>
<?php admin_topbar(); ?>
<div class="admin-layout">
<?php admin_sidebar('beneficiaries'); ?>
<main class="admin-main">
  <div class="admin-header">
    <h1>🤲 Beneficiary Applications</h1>
    <p>Review and manage food assistance applications.</p>
  </div>

  <?php if ($flash_success): ?>
    <div class="alert alert--success" style="margin-bottom:1.5rem;">✅ <?= h($flash_success) ?></div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:2rem;">
    <div class="stat-card"><h3><?= count($beneficiaries) ?></h3><p>Total applications</p></div>
    <div class="stat-card"><h3><?= $pending_count ?></h3><p>Pending review</p></div>
    <div class="stat-card stat-card--accent"><h4><?= db()->query("SELECT COUNT(*) FROM beneficiaries WHERE status='approved'")->fetchColumn() ?></h4><p>Approved</p></div>
  </div>

  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Household</th><th>Nearest Hub</th><th>Applied</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php if (empty($beneficiaries)): ?>
          <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--clr-hint);">No applications yet.</td></tr>
        <?php else: ?>
          <?php foreach ($beneficiaries as $b): ?>
          <tr>
            <td><?= $b['id'] ?></td>
            <td><strong><?= h($b['full_name']) ?></strong></td>
            <td><?= h($b['phone']) ?></td>
            <td><?= h($b['household_size'] ?? '—') ?></td>
            <td><?= h($b['nearest_hub'] ?? '—') ?></td>
            <td><?= date('d M Y', strtotime($b['created_at'])) ?></td>
            <td><span class="badge <?= $b['status'] === 'approved' ? 'badge--green' : ($b['status'] === 'rejected' ? 'badge--red' : 'badge--orange') ?>"><?= h($b['status']) ?></span></td>
            <td>
              <?php if ($b['status'] === 'pending'): ?>
                <form method="POST" style="display:inline;gap:0.5rem;display:flex;flex-wrap:wrap;">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="id" value="<?= $b['id'] ?>">
                  <input type="hidden" name="table" value="beneficiaries">
                  <button name="action" value="approved" class="btn btn--green" style="padding:0.25rem 0.6rem;font-size:0.75rem;">Approve</button>
                  <button name="action" value="rejected" class="btn btn--danger" style="padding:0.25rem 0.6rem;font-size:0.75rem;" onclick="return confirm('Reject this application?')">Reject</button>
                </form>
              <?php else: ?>
                <form method="POST" style="display:inline;">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="id" value="<?= $b['id'] ?>">
                  <input type="hidden" name="table" value="beneficiaries">
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
