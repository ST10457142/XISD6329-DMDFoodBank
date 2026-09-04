<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';
session_start_safe();
require_login();

$user = current_user();

// Recent donations
$stmt = db()->prepare('SELECT * FROM donations WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$stmt->execute([$user['id']]);
$recent = $stmt->fetchAll();

// Total site donations this month
$month_stmt = db()->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW()) AND donation_type='money'");
$month_total = $month_stmt->fetchColumn();

$flash_success = flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – DMD Food Bank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
.dash-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:2rem;}
@media(max-width:900px){.dash-grid{grid-template-columns:1fr 1fr}}
@media(max-width:540px){.dash-grid{grid-template-columns:1fr}}
.quick-link{display:flex;flex-direction:column;align-items:center;gap:0.5rem;padding:1.5rem 1rem;background:var(--clr-surface);border:1px solid var(--clr-border);border-radius:var(--radius-lg);text-decoration:none;color:var(--clr-text);transition:all var(--transition);text-align:center}
.quick-link:hover{border-color:var(--clr-earth3);box-shadow:var(--shadow-md);color:var(--clr-earth)}
.quick-link .icon{font-size:2rem;margin-bottom:0.25rem}
.quick-link span{font-size:0.85rem;font-weight:600;color:var(--clr-muted)}
.progress-bar-wrap{background:var(--clr-surface2);border-radius:999px;height:10px;overflow:hidden;margin:0.75rem 0}
.progress-bar-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--clr-earth3),var(--clr-earth2));transition:width 1s ease}
</style>
</head>
<body>
<?php require 'includes/nav.php'; ?>

<main id="main-content">
<!-- HEADER -->
<div style="background:linear-gradient(135deg,var(--clr-earth) 0%,var(--clr-earth2) 100%);padding:2.5rem 0;">
  <div class="container" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div>
      <p style="color:rgba(255,255,255,0.6);font-size:0.85rem;margin-bottom:0.25rem;">Welcome back</p>
      <h1 style="color:#fff;font-size:1.8rem;margin-bottom:0.25rem;"><?= h($user['full_name']) ?> 👋</h1>
      <p style="color:rgba(255,255,255,0.6);font-size:0.85rem;"><?= date('l, d F Y') ?></p>
    </div>
    <div style="text-align:right;">
      <div style="color:var(--clr-earth3);font-family:var(--font-display);font-size:2rem;font-weight:700;"><?= format_zar($user['total_donated']) ?></div>
      <div style="color:rgba(255,255,255,0.6);font-size:0.82rem;">Total donated</div>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">

    <?php if ($flash_success): ?>
      <div class="alert alert--success" style="margin-bottom:2rem;">✅ <?= h($flash_success) ?></div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="dash-grid">
      <div class="stat-card">
        <h3><?= format_zar($user['total_donated']) ?></h3>
        <p>My total donated</p>
      </div>
      <div class="stat-card">
        <h3><?= $user['donation_count'] ?></h3>
        <p>Donations made</p>
      </div>
      <div class="stat-card">
        <h3><?= $user['donation_count'] > 0 ? number_format(($user['total_donated'] / 10)) : 0 ?></h3>
        <p>Est. meals funded</p>
      </div>
      <div class="stat-card stat-card--accent">
        <h4><?= format_zar($month_total) ?></h4>
        <p>Site raised in <?= date('M') ?></p>
      </div>
    </div>

    <!-- MONTHLY GOAL PROGRESS -->
    <div class="form-card" style="margin-bottom:2rem;">
      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.25rem;">
        <h2 style="font-size:1.1rem;">🎯 <?= date('F') ?> Campaign Goal</h2>
        <span style="font-weight:700;color:var(--clr-earth2)"><?= format_zar($month_total) ?> / R 50,000</span>
      </div>
      <?php $pct = min(100, round(($month_total / 50000) * 100)); ?>
      <div class="progress-bar-wrap" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= $pct ?>% of monthly goal reached">
        <div class="progress-bar-fill" style="width:<?= $pct ?>%"></div>
      </div>
      <p style="font-size:0.85rem;color:var(--clr-muted)"><?= $pct ?>% reached &middot; <?= format_zar(max(0, 50000 - $month_total)) ?> still needed</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;align-items:start;">

      <!-- RECENT DONATIONS -->
      <div class="form-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.5rem;">
          <h2 style="font-size:1.1rem;">📋 Recent Donations</h2>
          <a href="profile.php" style="font-size:0.82rem;color:var(--clr-earth2);">View all →</a>
        </div>
        <?php if (empty($recent)): ?>
          <div class="alert alert--info">No donations yet. <a href="donate.php">Make your first →</a></div>
        <?php else: ?>
          <?php foreach($recent as $d): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 0;border-bottom:1px solid var(--clr-border);">
              <div>
                <div style="font-size:0.9rem;font-weight:600;"><?= $d['donation_type'] === 'money' ? '💰' : '🥕' ?> <?= $d['donation_type'] === 'money' ? format_zar($d['amount']) : 'Food Donation' ?></div>
                <div style="font-size:0.78rem;color:var(--clr-hint);"><?= date('d M Y', strtotime($d['created_at'])) ?></div>
              </div>
              <span class="badge badge--green"><?= h($d['status']) ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
        <div style="margin-top:1.25rem;">
          <a href="donate.php" class="btn btn--primary btn--full">Make a Donation</a>
        </div>
      </div>

      <!-- QUICK LINKS + MILESTONE -->
      <div>
        <div class="form-card" style="margin-bottom:1.5rem;">
          <h2 style="font-size:1.1rem;margin-bottom:1.25rem;">⚡ Quick Links</h2>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
            <a href="donate.php" class="quick-link"><span class="icon">💰</span><span>Donate Money</span></a>
            <a href="donate.php#food" class="quick-link"><span class="icon">🥕</span><span>Donate Food</span></a>
            <a href="profile.php" class="quick-link"><span class="icon">👤</span><span>My Profile</span></a>
            <a href="locations.php" class="quick-link"><span class="icon">📍</span><span>Our Hubs</span></a>
          </div>
        </div>

        <div class="form-card" style="background:linear-gradient(135deg,var(--clr-earth3),var(--clr-earth2));border:none;color:#fff;">
          <h2 style="font-size:1rem;color:#fff;margin-bottom:0.5rem;">🏆 Next Milestone</h2>
          <?php
          $next = null;
          $milestones = [
            [500,'R 500 Milestone'],
            [1000,'R 1,000 Club'],
            [5000,'R 5,000 Benefactor'],
            [10000,'R 10,000 Champion'],
          ];
          foreach ($milestones as [$amt, $label]) {
            if ($user['total_donated'] < $amt) { $next = [$amt, $label]; break; }
          }
          ?>
          <?php if ($next): ?>
            <p style="color:rgba(255,255,255,0.85);font-size:0.9rem;margin-bottom:0.5rem;"><?= $next[1] ?></p>
            <?php $rem = $next[0] - $user['total_donated']; ?>
            <p style="color:rgba(255,255,255,0.7);font-size:0.82rem;"><?= format_zar($rem) ?> to go!</p>
            <div style="background:rgba(255,255,255,0.15);border-radius:999px;height:6px;margin-top:0.75rem;overflow:hidden;">
              <?php $mpct = round(($user['total_donated'] / $next[0]) * 100); ?>
              <div style="height:100%;background:#fff;border-radius:999px;width:<?= $mpct ?>%;"></div>
            </div>
          <?php else: ?>
            <p style="color:rgba(255,255,255,0.85);">🎉 You've reached all milestones! Champion donor!</p>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</section>
</main>

<?php footer_html(); ?>

<script>
const toggle = document.getElementById('nav-toggle');
const navLinks = document.getElementById('nav-links');
if(toggle) toggle.addEventListener('click', () => {
  const exp = toggle.getAttribute('aria-expanded') === 'true';
  toggle.setAttribute('aria-expanded', String(!exp));
  navLinks.classList.toggle('open');
});
</script>
</body>
</html>
