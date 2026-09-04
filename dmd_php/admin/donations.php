<?php
require_once 'layout.php';

$filter = $_GET['type'] ?? 'all';
$where  = $filter !== 'all' ? "WHERE d.donation_type = '$filter'" : '';

$donations = db()->query("
    SELECT d.*, u.full_name, u.email
    FROM donations d
    LEFT JOIN users u ON d.user_id = u.id
    $where
    ORDER BY d.created_at DESC
    LIMIT 200
")->fetchAll();

$total_money = db()->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE donation_type='money'")->fetchColumn();
$total_food  = db()->query("SELECT COUNT(*) FROM donations WHERE donation_type='food'")->fetchColumn();

admin_head('Donations');
?>
<body>
<?php admin_topbar(); ?>
<div class="admin-layout">
<?php admin_sidebar('donations'); ?>
<main class="admin-main">
  <div class="admin-header">
    <h1>💰 Donation Records</h1>
    <p>All monetary and food donations across the platform.</p>
  </div>

  <!-- SUMMARY -->
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:2rem;">
    <div class="stat-card"><h3><?= format_zar($total_money) ?></h3><p>Total money raised</p></div>
    <div class="stat-card"><h3><?= $total_food ?></h3><p>Food donations</p></div>
    <div class="stat-card stat-card--accent"><h4><?= count($donations) ?></h4><p>Showing (max 200)</p></div>
  </div>

  <!-- FILTER TABS -->
  <div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="?type=all"   class="btn <?= $filter === 'all'   ? 'btn--dark' : 'btn--ghost' ?>" style="font-size:0.85rem;padding:0.5rem 1rem;">All</a>
    <a href="?type=money" class="btn <?= $filter === 'money' ? 'btn--dark' : 'btn--ghost' ?>" style="font-size:0.85rem;padding:0.5rem 1rem;">💰 Money</a>
    <a href="?type=food"  class="btn <?= $filter === 'food'  ? 'btn--dark' : 'btn--ghost' ?>" style="font-size:0.85rem;padding:0.5rem 1rem;">🥕 Food</a>
  </div>

  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead>
        <tr><th>#</th><th>Date</th><th>Donor</th><th>Type</th><th>Amount / Description</th><th>Frequency</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php if (empty($donations)): ?>
          <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--clr-hint);">No donations found.</td></tr>
        <?php else: ?>
          <?php foreach ($donations as $d): ?>
          <tr>
            <td><?= $d['id'] ?></td>
            <td><?= date('d M Y, H:i', strtotime($d['created_at'])) ?></td>
            <td>
              <?php if ($d['full_name']): ?>
                <div style="font-weight:600;font-size:0.88rem;"><?= h($d['full_name']) ?></div>
                <div style="font-size:0.75rem;color:var(--clr-hint);"><?= h($d['email']) ?></div>
              <?php else: ?>
                <em style="color:var(--clr-hint);font-size:0.88rem;">Guest</em>
              <?php endif; ?>
            </td>
            <td><span class="badge <?= $d['donation_type'] === 'money' ? 'badge--green' : 'badge--orange' ?>"><?= h($d['donation_type']) ?></span></td>
            <td>
              <?php if ($d['donation_type'] === 'money'): ?>
                <strong style="color:var(--clr-earth2);"><?= format_zar($d['amount']) ?></strong>
              <?php else: ?>
                <span style="font-size:0.82rem;"><?= h(substr($d['food_description'] ?? '—', 0, 80)) ?></span>
              <?php endif; ?>
            </td>
            <td><?= h($d['frequency'] ?? 'once') ?></td>
            <td><span class="badge badge--green"><?= h($d['status']) ?></span></td>
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
