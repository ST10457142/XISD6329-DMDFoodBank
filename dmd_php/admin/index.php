<?php
require_once 'layout.php';

// Pull all summary stats
$pdo = db();

$total_donors      = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
$total_raised      = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE donation_type='money'")->fetchColumn();
$total_donations   = $pdo->query("SELECT COUNT(*) FROM donations")->fetchColumn();
$month_raised      = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE donation_type='money' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();
$pending_bene      = $pdo->query("SELECT COUNT(*) FROM beneficiaries WHERE status='pending'")->fetchColumn();
$pending_vol       = $pdo->query("SELECT COUNT(*) FROM volunteers WHERE status='pending'")->fetchColumn();
$unread_msgs       = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
$total_beneficiary = $pdo->query("SELECT COUNT(*) FROM beneficiaries")->fetchColumn();

// Recent donations
$recent_donations = $pdo->query("
    SELECT d.*, u.full_name, u.email FROM donations d
    LEFT JOIN users u ON d.user_id = u.id
    ORDER BY d.created_at DESC LIMIT 8
")->fetchAll();

// Top donors
$top_donors = $pdo->query("
    SELECT full_name, email, total_donated, donation_count, donor_type
    FROM users WHERE total_donated > 0
    ORDER BY total_donated DESC LIMIT 5
")->fetchAll();

// Monthly donations (last 6 months) for chart
$monthly = $pdo->query("
    SELECT DATE_FORMAT(created_at,'%b %Y') AS mo,
           SUM(amount) AS total
    FROM donations
    WHERE donation_type='money' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY YEAR(created_at), MONTH(created_at)
")->fetchAll();

admin_head('Dashboard');
?>
<body>
<?php admin_topbar(); ?>
<div class="admin-layout">
<?php admin_sidebar('dashboard'); ?>
<main class="admin-main">

  <div class="admin-header">
    <h1>Admin Dashboard</h1>
    <p>Overview of DMD Food Bank activity — <?= date('l, d F Y') ?></p>
  </div>

  <!-- STAT CARDS -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
    <div class="stat-card">
      <h3><?= number_format($total_raised, 2, '.', ' ') ?></h3>
      <p>Total raised (R)</p>
    </div>
    <div class="stat-card">
      <h3><?= number_format($month_raised, 2, '.', ' ') ?></h3>
      <p>Raised in <?= date('M Y') ?></p>
    </div>
    <div class="stat-card">
      <h3><?= number_format($total_donors) ?></h3>
      <p>Registered users</p>
    </div>
    <div class="stat-card">
      <h3><?= number_format($total_donations) ?></h3>
      <p>Total donations</p>
    </div>
  </div>

  <!-- ALERTS ROW -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem;">
    <a href="messages.php" style="text-decoration:none;">
      <div class="stat-card" style="border-top-color:<?= $unread_msgs > 0 ? 'var(--clr-warm)' : 'var(--clr-earth3)' ?>;">
        <h3><?= $unread_msgs ?></h3>
        <p>Unread messages <?= $unread_msgs > 0 ? '🔔' : '✅' ?></p>
      </div>
    </a>
    <a href="beneficiaries.php" style="text-decoration:none;">
      <div class="stat-card" style="border-top-color:<?= $pending_bene > 0 ? 'var(--clr-warm)' : 'var(--clr-earth3)' ?>;">
        <h3><?= $pending_bene ?></h3>
        <p>Pending beneficiaries <?= $pending_bene > 0 ? '⏳' : '✅' ?></p>
      </div>
    </a>
    <a href="volunteers.php" style="text-decoration:none;">
      <div class="stat-card" style="border-top-color:<?= $pending_vol > 0 ? 'var(--clr-warm)' : 'var(--clr-earth3)' ?>;">
        <h3><?= $pending_vol ?></h3>
        <p>Pending volunteers <?= $pending_vol > 0 ? '⏳' : '✅' ?></p>
      </div>
    </a>
  </div>

  <!-- CHART + TOP DONORS -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-bottom:2rem;align-items:start;">

    <!-- MONTHLY CHART -->
    <div style="background:var(--clr-surface);border-radius:var(--radius-lg);padding:1.5rem;border:1px solid var(--clr-border);box-shadow:var(--shadow-sm);">
      <h2 style="font-size:1rem;margin-bottom:1.25rem;">📈 Monthly Donations (Last 6 Months)</h2>
      <?php if (empty($monthly)): ?>
        <p style="color:var(--clr-muted);font-size:0.9rem;">No data yet.</p>
      <?php else:
        $max = max(array_column($monthly, 'total')) ?: 1;
        foreach ($monthly as $m):
          $pct = round(($m['total'] / $max) * 100);
      ?>
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;">
          <div style="width:70px;font-size:0.78rem;color:var(--clr-muted);text-align:right;flex-shrink:0;"><?= h($m['mo']) ?></div>
          <div style="flex:1;background:var(--clr-surface2);border-radius:999px;height:22px;overflow:hidden;">
            <div style="height:100%;width:<?= $pct ?>%;background:linear-gradient(90deg,var(--clr-earth3),var(--clr-earth2));border-radius:999px;display:flex;align-items:center;padding-left:8px;">
              <span style="font-size:0.72rem;font-weight:700;color:var(--clr-earth);white-space:nowrap;">R <?= number_format($m['total'],0,'.',',') ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- TOP DONORS -->
    <div style="background:var(--clr-surface);border-radius:var(--radius-lg);padding:1.5rem;border:1px solid var(--clr-border);box-shadow:var(--shadow-sm);">
      <h2 style="font-size:1rem;margin-bottom:1.25rem;">🏆 Top Donors</h2>
      <?php if (empty($top_donors)): ?>
        <p style="color:var(--clr-muted);font-size:0.9rem;">No donors yet.</p>
      <?php else: ?>
        <?php foreach ($top_donors as $i => $d): ?>
          <div style="display:flex;align-items:center;gap:0.75rem;padding:0.65rem 0;border-bottom:1px solid var(--clr-border);">
            <div style="width:28px;height:28px;border-radius:50%;background:<?= ['var(--clr-warm)','var(--clr-earth3)','var(--clr-earth2)','var(--clr-border)','var(--clr-border)'][$i] ?>;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.75rem;color:var(--clr-earth);flex-shrink:0;"><?= $i+1 ?></div>
            <div style="flex:1;min-width:0;">
              <div style="font-size:0.88rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h($d['full_name']) ?></div>
              <div style="font-size:0.75rem;color:var(--clr-hint);"><?= h($d['donor_type'] ?? 'Individual') ?></div>
            </div>
            <div style="font-family:var(--font-display);font-weight:700;color:var(--clr-earth2);white-space:nowrap;">R <?= number_format($d['total_donated'],0) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- RECENT DONATIONS TABLE -->
  <div style="background:var(--clr-surface);border-radius:var(--radius-lg);padding:1.5rem;border:1px solid var(--clr-border);box-shadow:var(--shadow-sm);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.5rem;">
      <h2 style="font-size:1rem;">📋 Recent Donations</h2>
      <a href="donations.php" class="btn btn--ghost" style="font-size:0.82rem;padding:0.4rem 0.9rem;">View all →</a>
    </div>
    <?php if (empty($recent_donations)): ?>
      <p style="color:var(--clr-muted);font-size:0.9rem;">No donations yet.</p>
    <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th><th>Donor</th><th>Type</th><th>Amount</th><th>Frequency</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent_donations as $d): ?>
          <tr>
            <td><?= date('d M Y', strtotime($d['created_at'])) ?></td>
            <td><?= $d['full_name'] ? h($d['full_name']) : '<em style="color:var(--clr-hint)">Guest</em>' ?></td>
            <td><span class="badge <?= $d['donation_type'] === 'money' ? 'badge--green' : 'badge--orange' ?>"><?= h($d['donation_type']) ?></span></td>
            <td><strong><?= $d['donation_type'] === 'money' ? format_zar($d['amount']) : 'In-kind' ?></strong></td>
            <td><?= h($d['frequency'] ?? 'once') ?></td>
            <td><span class="badge badge--green"><?= h($d['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</main>
</div>
</body>
</html>
