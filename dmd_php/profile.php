<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';
session_start_safe();
require_login();

$user = current_user();

// Handle profile update
$errors = []; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verify_csrf()) { $errors[] = 'Invalid form token.'; }
    else {
        $name  = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $dtype = $_POST['donor_type'] ?? $user['donor_type'];
        if (empty($name)) $errors[] = 'Full name is required.';
        if (empty($errors)) {
            db()->prepare('UPDATE users SET full_name=?, phone=?, donor_type=? WHERE id=?')
                ->execute([$name, $phone, $dtype, $user['id']]);
            // re-fetch
            $stmt = db()->prepare('SELECT * FROM users WHERE id=?');
            $stmt->execute([$user['id']]);
            $user = $stmt->fetch();
            $_SESSION['user_name'] = $user['full_name'];
            flash('success', 'Profile updated successfully!');
            header('Location: profile.php');
            exit;
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verify_csrf()) { $errors[] = 'Invalid form token.'; }
    else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $errors[] = 'New passwords do not match.';
        } else {
            db()->prepare('UPDATE users SET password=? WHERE id=?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            flash('success', 'Password changed successfully!');
            header('Location: profile.php');
            exit;
        }
    }
}

// Fetch donation history
$stmt = db()->prepare('SELECT * FROM donations WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$user['id']]);
$donations = $stmt->fetchAll();

$flash_success = flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile – DMD Food Bank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
.profile-layout{display:grid;grid-template-columns:280px 1fr;gap:2rem;align-items:start;}
@media(max-width:900px){.profile-layout{grid-template-columns:1fr}}
.profile-sidebar{background:var(--clr-surface);border-radius:var(--radius-xl);border:1px solid var(--clr-border);overflow:hidden;box-shadow:var(--shadow-sm)}
.profile-avatar{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--clr-earth3),var(--clr-earth2));display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:2rem;font-weight:700;color:var(--clr-earth);margin:0 auto 1rem;}
.profile-card{padding:2rem;text-align:center;border-bottom:1px solid var(--clr-border)}
.profile-badge{display:inline-flex;align-items:center;gap:0.3rem;background:rgba(82,183,136,0.15);border:1px solid rgba(82,183,136,0.3);border-radius:999px;padding:0.25rem 0.75rem;font-size:0.75rem;font-weight:600;color:var(--clr-earth2);text-transform:uppercase;letter-spacing:0.05em}
.profile-nav a{display:block;padding:0.75rem 1.5rem;color:var(--clr-muted);text-decoration:none;font-size:0.9rem;border-left:3px solid transparent;transition:all var(--transition)}
.profile-nav a:hover,.profile-nav a.active{background:rgba(82,183,136,0.06);color:var(--clr-earth);border-left-color:var(--clr-earth3)}
.tab-content{display:none}
.tab-content.active{display:block}
.donation-row{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--clr-border);gap:1rem;}
.donation-row:last-child{border-bottom:none}
.donation-row:hover{background:var(--clr-cream)}
.donation-amount{font-family:var(--font-display);font-size:1.25rem;font-weight:700;color:var(--clr-earth2)}
.donation-meta{font-size:0.82rem;color:var(--clr-hint)}
.milestone{display:flex;align-items:center;gap:0.75rem;padding:1rem;background:rgba(82,183,136,0.06);border:1px solid rgba(82,183,136,0.15);border-radius:var(--radius-md);margin-bottom:0.75rem;}
.milestone.reached{background:rgba(82,183,136,0.12);border-color:var(--clr-earth3)}
.milestone-icon{font-size:1.5rem;flex-shrink:0}
.milestone-text strong{display:block;font-size:0.9rem;color:var(--clr-text)}
.milestone-text span{font-size:0.78rem;color:var(--clr-hint)}
</style>
</head>
<body>
<?php require 'includes/nav.php'; ?>

<main id="main-content">
<div style="background:var(--clr-earth);padding:2.5rem 0;">
  <div class="container">
    <h1 style="color:#fff;font-size:1.8rem;margin-bottom:0.25rem;">My Profile</h1>
    <p style="color:rgba(255,255,255,0.6);font-size:0.9rem;">Manage your account &amp; track your impact</p>
  </div>
</div>

<section class="section">
  <div class="container">

    <?php if ($flash_success): ?>
      <div class="alert alert--success" style="margin-bottom:2rem;">✅ <?= h($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert--error" style="margin-bottom:2rem;">
        <?php foreach($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="profile-layout">
      <!-- SIDEBAR -->
      <div class="profile-sidebar">
        <div class="profile-card">
          <div class="profile-avatar"><?= mb_strtoupper(mb_substr($user['full_name'], 0, 2)) ?></div>
          <h2 style="font-size:1.1rem;margin-bottom:0.25rem;"><?= h($user['full_name']) ?></h2>
          <p style="color:var(--clr-hint);font-size:0.85rem;margin-bottom:0.75rem;"><?= h($user['email']) ?></p>
          <div class="profile-badge">❤️ <?= h($user['donor_type'] ?? 'Donor') ?></div>
          <div style="margin-top:1.5rem;background:var(--clr-cream);border-radius:var(--radius-md);padding:1rem;">
            <div style="font-family:var(--font-display);font-size:2rem;font-weight:700;color:var(--clr-earth2);"><?= format_zar($user['total_donated']) ?></div>
            <div style="font-size:0.8rem;color:var(--clr-hint);">Total donated</div>
            <div style="margin-top:0.5rem;font-size:1.1rem;font-weight:700;color:var(--clr-earth);"><?= $user['donation_count'] ?></div>
            <div style="font-size:0.8rem;color:var(--clr-hint);">Donation<?= $user['donation_count'] != 1 ? 's' : '' ?> made</div>
          </div>
        </div>
        <nav class="profile-nav" aria-label="Profile sections">
          <a href="#" class="active" onclick="showTab('overview',this)">📊 Overview</a>
          <a href="#" onclick="showTab('history',this)">📋 Donation History</a>
          <a href="#" onclick="showTab('milestones',this)">🏆 Milestones</a>
          <a href="#" onclick="showTab('settings',this)">⚙️ Settings</a>
          <a href="#" onclick="showTab('security',this)">🔐 Security</a>
        </nav>
      </div>

      <!-- MAIN CONTENT -->
      <div>

        <!-- OVERVIEW -->
        <div id="tab-overview" class="tab-content active">
          <div class="form-card" style="margin-bottom:2rem;">
            <h2 style="font-size:1.2rem;margin-bottom:1.5rem;">Your Impact Summary</h2>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem;">
              <div class="stat-card">
                <h3><?= format_zar($user['total_donated']) ?></h3>
                <p>Total donated</p>
              </div>
              <div class="stat-card">
                <h3><?= $user['donation_count'] ?></h3>
                <p>Donations made</p>
              </div>
              <div class="stat-card stat-card--accent">
                <h4><?= $user['donation_count'] > 0 ? number_format(($user['total_donated'] / 10)) : 0 ?>+</h4>
                <p>Estimated meals funded</p>
              </div>
            </div>
            <?php if ($user['donation_count'] === 0): ?>
              <div class="alert alert--info">You haven't made a donation yet. <a href="donate.php" style="font-weight:600;">Make your first donation →</a></div>
            <?php else: ?>
              <p style="color:var(--clr-muted);font-size:0.9rem;">Thank you for your generosity! Your donations have helped feed hundreds of families.</p>
            <?php endif; ?>

            <!-- Recent donations -->
            <?php if (!empty($donations)): ?>
            <h3 style="font-size:1rem;margin:1.5rem 0 1rem;">Recent Donations</h3>
            <?php foreach(array_slice($donations, 0, 3) as $d): ?>
              <div class="donation-row" style="border-radius:var(--radius-sm);border:1px solid var(--clr-border);margin-bottom:0.5rem;border-bottom:1px solid var(--clr-border)!important;">
                <div>
                  <div style="font-weight:600;font-size:0.9rem;"><?= $d['donation_type'] === 'money' ? '💰 Money' : '🥕 Food' ?> Donation</div>
                  <div class="donation-meta"><?= date('d M Y', strtotime($d['created_at'])) ?> &middot; <?= ucfirst($d['frequency'] ?? 'once') ?></div>
                  <?php if($d['donation_type'] === 'food' && $d['food_description']): ?>
                    <div class="donation-meta"><?= h(substr($d['food_description'], 0, 60)) ?></div>
                  <?php endif; ?>
                </div>
                <div class="donation-amount"><?= $d['donation_type'] === 'money' ? format_zar($d['amount']) : 'Food' ?></div>
              </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <div style="margin-top:1.5rem;">
              <a href="donate.php" class="btn btn--primary" style="margin-right:0.75rem;">Make a Donation</a>
            </div>
          </div>
        </div>

        <!-- HISTORY -->
        <div id="tab-history" class="tab-content">
          <div class="form-card">
            <h2 style="font-size:1.2rem;margin-bottom:1.5rem;">Full Donation History</h2>
            <?php if (empty($donations)): ?>
              <div class="alert alert--info">No donations yet. <a href="donate.php">Make your first donation →</a></div>
            <?php else: ?>
              <div style="border:1px solid var(--clr-border);border-radius:var(--radius-md);overflow:hidden;">
                <?php foreach($donations as $d): ?>
                  <div class="donation-row">
                    <div>
                      <div style="font-weight:600;font-size:0.9rem;"><?= $d['donation_type'] === 'money' ? '💰 Monetary Donation' : '🥕 Food Donation' ?></div>
                      <div class="donation-meta"><?= date('d M Y, H:i', strtotime($d['created_at'])) ?></div>
                      <?php if($d['donation_type'] === 'money'): ?>
                        <div class="donation-meta"><?= ucfirst($d['frequency']) ?> &middot; <span class="badge badge--green"><?= h($d['status']) ?></span></div>
                      <?php else: ?>
                        <div class="donation-meta"><?= h(substr($d['food_description'] ?? '', 0, 80)) ?></div>
                      <?php endif; ?>
                    </div>
                    <div class="donation-amount"><?= $d['donation_type'] === 'money' ? format_zar($d['amount']) : 'In-kind' ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
              <p style="color:var(--clr-hint);font-size:0.82rem;margin-top:1rem;">Showing last <?= count($donations) ?> donation(s)</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- MILESTONES -->
        <div id="tab-milestones" class="tab-content">
          <div class="form-card">
            <h2 style="font-size:1.2rem;margin-bottom:1.5rem;">Giving Milestones 🏆</h2>
            <?php
            $total = $user['total_donated'];
            $count = $user['donation_count'];
            $milestones = [
                ['icon'=>'🌱','label'=>'First Donation','desc'=>'Made your very first donation','reached'=>$count >= 1],
                ['icon'=>'🌟','label'=>'R 500 Milestone','desc'=>'Total giving exceeded R 500','reached'=>$total >= 500],
                ['icon'=>'🔥','label'=>'Regular Donor','desc'=>'Made 5 or more donations','reached'=>$count >= 5],
                ['icon'=>'💫','label'=>'R 1,000 Club','desc'=>'Total giving exceeded R 1,000','reached'=>$total >= 1000],
                ['icon'=>'🏅','label'=>'R 5,000 Benefactor','desc'=>'Total giving exceeded R 5,000','reached'=>$total >= 5000],
                ['icon'=>'🏆','label'=>'R 10,000 Champion','desc'=>'Total giving exceeded R 10,000','reached'=>$total >= 10000],
            ];
            foreach($milestones as $m): ?>
              <div class="milestone <?= $m['reached'] ? 'reached' : '' ?>">
                <div class="milestone-icon"><?= $m['icon'] ?></div>
                <div class="milestone-text">
                  <strong><?= $m['label'] ?> <?= $m['reached'] ? '✅' : '🔒' ?></strong>
                  <span><?= $m['desc'] ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- SETTINGS -->
        <div id="tab-settings" class="tab-content">
          <div class="form-card">
            <h2 style="font-size:1.2rem;margin-bottom:1.5rem;">Profile Settings</h2>
            <form method="POST">
              <?php csrf_field(); ?>
              <input type="hidden" name="update_profile" value="1">
              <div class="form-group">
                <label for="full_name">Full Name <span class="required-mark">*</span></label>
                <input type="text" id="full_name" name="full_name" required value="<?= h($user['full_name']) ?>">
              </div>
              <div class="form-group">
                <label for="email_disp">Email Address</label>
                <input type="email" id="email_disp" value="<?= h($user['email']) ?>" disabled>
                <span class="hint">Email cannot be changed. Contact admin if needed.</span>
              </div>
              <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?= h($user['phone'] ?? '') ?>" placeholder="0xxxxxxxxx">
              </div>
              <div class="form-group">
                <label for="donor_type">Donor Type</label>
                <select id="donor_type" name="donor_type">
                  <?php foreach(['Individual','Corporate / Business','Government / Parastatal','Religious / Community Org'] as $dt): ?>
                    <option <?= ($user['donor_type'] ?? '') === $dt ? 'selected' : '' ?>><?= $dt ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button type="submit" class="btn btn--primary">Save Changes</button>
            </form>
          </div>
        </div>

        <!-- SECURITY -->
        <div id="tab-security" class="tab-content">
          <div class="form-card">
            <h2 style="font-size:1.2rem;margin-bottom:1.5rem;">Change Password</h2>
            <form method="POST">
              <?php csrf_field(); ?>
              <input type="hidden" name="change_password" value="1">
              <div class="form-group">
                <label for="current_password">Current Password <span class="required-mark">*</span></label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
              </div>
              <div class="form-group">
                <label for="new_password">New Password <span class="required-mark">*</span></label>
                <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
                <span class="hint">Min 8 characters.</span>
              </div>
              <div class="form-group">
                <label for="confirm_password">Confirm New Password <span class="required-mark">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
              </div>
              <button type="submit" class="btn btn--dark">Change Password</button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>
</main>

<?php footer_html(); ?>

<script>
function showTab(tab, link) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.profile-nav a').forEach(a => a.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  link.classList.add('active');
  return false;
}
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
