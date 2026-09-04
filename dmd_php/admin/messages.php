<?php
require_once 'layout.php';

// Mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf() && isset($_POST['mark_read'])) {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        db()->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([$id]);
        header('Location: messages.php'); exit;
    }
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf() && isset($_POST['delete_msg'])) {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        db()->prepare("DELETE FROM contact_messages WHERE id=?")->execute([$id]);
        flash('success', 'Message deleted.');
        header('Location: messages.php'); exit;
    }
}

$show = $_GET['id'] ?? null;
$view_msg = null;
if ($show) {
    $stmt = db()->prepare("SELECT * FROM contact_messages WHERE id=?");
    $stmt->execute([$show]);
    $view_msg = $stmt->fetch();
    if ($view_msg && !$view_msg['is_read']) {
        db()->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([$show]);
    }
}

$messages = db()->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 100")->fetchAll();
$unread   = db()->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
$flash_success = flash('success');
admin_head('Messages');
?>
<body>
<?php admin_topbar(); ?>
<div class="admin-layout">
<?php admin_sidebar('messages'); ?>
<main class="admin-main">
  <div class="admin-header">
    <h1>📬 Contact Messages</h1>
    <p><?= $unread ?> unread message<?= $unread != 1 ? 's' : '' ?>.</p>
  </div>

  <?php if ($flash_success): ?>
    <div class="alert alert--success" style="margin-bottom:1.5rem;">✅ <?= h($flash_success) ?></div>
  <?php endif; ?>

  <?php if ($view_msg): ?>
    <div class="form-card" style="margin-bottom:2rem;border-left:4px solid var(--clr-earth2);">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;">
        <div>
          <h2 style="font-size:1.1rem;margin-bottom:0.25rem;"><?= h($view_msg['subject'] ?: '(No subject)') ?></h2>
          <p style="color:var(--clr-muted);font-size:0.85rem;">From: <strong><?= h($view_msg['name']) ?></strong> &lt;<?= h($view_msg['email']) ?>&gt; &middot; <?= date('d M Y H:i', strtotime($view_msg['created_at'])) ?></p>
        </div>
        <div style="display:flex;gap:0.5rem;">
          <form method="POST" style="display:inline;">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" value="<?= $view_msg['id'] ?>">
            <button name="delete_msg" value="1" class="btn btn--danger" style="padding:0.35rem 0.8rem;font-size:0.8rem;" onclick="return confirm('Delete this message?')">Delete</button>
          </form>
          <a href="messages.php" class="btn btn--ghost" style="padding:0.35rem 0.8rem;font-size:0.8rem;">← Back</a>
        </div>
      </div>
      <div style="background:var(--clr-cream);border-radius:var(--radius-md);padding:1.25rem;white-space:pre-wrap;font-size:0.9rem;line-height:1.7;">
        <?= h($view_msg['message']) ?>
      </div>
      <div style="margin-top:1rem;">
        <a href="mailto:<?= h($view_msg['email']) ?>?subject=Re: <?= urlencode($view_msg['subject'] ?? '') ?>" class="btn btn--primary" style="font-size:0.85rem;">Reply via Email</a>
      </div>
    </div>
  <?php endif; ?>

  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead><tr><th></th><th>From</th><th>Email</th><th>Subject</th><th>Received</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php if (empty($messages)): ?>
          <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--clr-hint);">No messages yet.</td></tr>
        <?php else: ?>
          <?php foreach ($messages as $m): ?>
          <tr style="<?= !$m['is_read'] ? 'background:rgba(82,183,136,0.04);font-weight:600;' : '' ?>">
            <td><?= $m['is_read'] ? '' : '<span style="color:var(--clr-earth3);font-size:1.2rem;">●</span>' ?></td>
            <td><?= h($m['name']) ?></td>
            <td><?= h($m['email']) ?></td>
            <td><?= h(substr($m['subject'] ?: '(No subject)', 0, 50)) ?></td>
            <td><?= date('d M Y, H:i', strtotime($m['created_at'])) ?></td>
            <td><span class="badge <?= $m['is_read'] ? 'badge--blue' : 'badge--orange' ?>"><?= $m['is_read'] ? 'Read' : 'Unread' ?></span></td>
            <td style="display:flex;gap:0.5rem;flex-wrap:wrap;">
              <a href="?id=<?= $m['id'] ?>" class="btn btn--dark" style="padding:0.25rem 0.6rem;font-size:0.75rem;">View</a>
              <?php if (!$m['is_read']): ?>
              <form method="POST" style="display:inline;">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                <button name="mark_read" value="1" class="btn btn--ghost" style="padding:0.25rem 0.6rem;font-size:0.75rem;">Mark Read</button>
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
