<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';
session_start_safe();

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid form submission.';
    } else {
        $type = $_POST['donation_type'] ?? 'money';

        if ($type === 'money') {
            $amount    = floatval($_POST['amount'] ?? 0);
            $frequency = $_POST['frequency'] ?? 'once';
            $custom    = floatval($_POST['custom_amount'] ?? 0);

            // Use custom amount if filled
            if ($custom > 0) $amount = $custom;

            if ($amount < 10) {
                $errors[] = 'Minimum donation is R 10.00.';
            } else {
                $user_id = $_SESSION['user_id'] ?? null;

                // Insert donation record
                $stmt = db()->prepare('INSERT INTO donations (user_id, amount, donation_type, frequency) VALUES (?,?,?,?)');
                $stmt->execute([$user_id, $amount, 'money', $frequency]);

                // Update user totals if logged in
                if ($user_id) {
                    db()->prepare('UPDATE users SET total_donated = total_donated + ?, donation_count = donation_count + 1 WHERE id = ?')
                        ->execute([$amount, $user_id]);
                }

                flash('success', 'Thank you for your donation of ' . format_zar($amount) . '! Your generosity makes a real difference.');
                header('Location: donate.php?donated=1');
                exit;
            }
        } else {
            // Food donation
            $food_desc = trim($_POST['food_description'] ?? '');
            $hub       = trim($_POST['drop_hub'] ?? '');
            if (empty($food_desc)) $errors[] = 'Please describe the food you are donating.';
            if (empty($hub))       $errors[] = 'Please select a drop-off location.';

            if (empty($errors)) {
                $user_id = $_SESSION['user_id'] ?? null;
                $stmt = db()->prepare('INSERT INTO donations (user_id, amount, donation_type, food_description) VALUES (?,0,\'food\',?)');
                $stmt->execute([$user_id, $food_desc . ' | Hub: ' . $hub]);
                flash('success', 'Food donation registered! Please drop it off at ' . $hub . '. Thank you!');
                header('Location: donate.php?donated=1');
                exit;
            }
        }
    }
}

$donated = isset($_GET['donated']);
$flash_success = flash('success');
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Donate – DMD Food Bank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;1,9..144,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
.donate-grid{display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:start;}
@media(max-width:900px){.donate-grid{grid-template-columns:1fr}}
.amount-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;margin-bottom:1rem}
.amount-btn{padding:0.85rem;border-radius:var(--radius-md);border:2px solid var(--clr-border);background:var(--clr-cream);cursor:pointer;font-family:var(--font-body);font-weight:600;font-size:1rem;color:var(--clr-text);transition:all var(--transition);text-align:center}
.amount-btn:hover,.amount-btn.selected{border-color:var(--clr-earth2);background:var(--clr-earth2);color:#fff}
.amount-btn .label{display:block;font-size:0.72rem;font-weight:400;color:inherit;opacity:0.8;margin-top:2px}
.donate-tabs{display:flex;gap:0;background:var(--clr-surface2);border-radius:var(--radius-md);padding:0.25rem;margin-bottom:2rem}
.donate-tab{flex:1;padding:0.65rem;border-radius:var(--radius-sm);border:none;cursor:pointer;font-family:var(--font-body);font-size:0.88rem;font-weight:600;color:var(--clr-muted);background:transparent;transition:all var(--transition);min-height:44px}
.donate-tab.active{background:var(--clr-surface);color:var(--clr-earth);box-shadow:var(--shadow-sm)}
.donate-panel{display:none}
.donate-panel.active{display:block}
.impact-bullet{display:flex;align-items:flex-start;gap:0.75rem;margin-bottom:0.85rem;padding:0.85rem;background:rgba(82,183,136,0.06);border:1px solid rgba(82,183,136,0.15);border-radius:var(--radius-sm)}
.impact-bullet .icon{width:34px;height:34px;background:var(--clr-earth3);border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.impact-bullet .icon svg{width:18px;height:18px;fill:var(--clr-earth)}
.impact-bullet p{margin:0;font-size:0.88rem;color:var(--clr-muted)}
.impact-bullet strong{display:block;color:var(--clr-text);font-size:0.92rem;margin-bottom:0.15rem}
.success-banner{background:linear-gradient(135deg,var(--clr-earth2),var(--clr-earth));color:#fff;border-radius:var(--radius-xl);padding:3rem;text-align:center;margin-bottom:3rem}
.success-banner h2{color:#fff;font-size:2rem;margin-bottom:0.75rem}
.success-banner p{color:rgba(255,255,255,0.8);margin-bottom:1.5rem}
.trust-badge{display:flex;align-items:center;gap:0.4rem;font-size:0.78rem;color:var(--clr-hint)}
.trust-badge svg{width:16px;height:16px;fill:var(--clr-earth3)}
</style>
</head>
<body>
<?php require 'includes/nav.php'; ?>

<section style="padding:3rem 0;background:var(--clr-earth);">
  <div class="container" style="text-align:center;">
    <div class="hero__tag" style="margin:0 auto 1rem;display:inline-flex;">❤️ Make a Difference</div>
    <h1 style="color:#fff;font-size:clamp(2rem,4vw,3rem);">Every Rand Feeds a Family</h1>
    <p style="color:rgba(255,255,255,0.7);max-width:540px;margin:0.75rem auto 0;">Secure, transparent donations. 93¢ of every rand goes directly to food distribution.</p>
  </div>
</section>

<main id="main-content">
<section class="section">
  <div class="container">

    <?php if ($flash_success): ?>
      <div class="success-banner">
        <div style="font-size:3rem;margin-bottom:1rem;">🎉</div>
        <h2>Thank you for your generosity!</h2>
        <p><?= h($flash_success) ?></p>
        <?php if ($user): ?>
          <a href="profile.php" class="btn btn--outline" style="margin-right:1rem;">View My Donations</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn--outline">Back to Home</a>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert--error" style="max-width:700px;margin:0 auto 2rem;">
        <?php foreach($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!$flash_success): ?>
    <div class="donate-grid">
      <!-- FORM SIDE -->
      <div>
        <div class="form-card">
          <div class="donate-tabs">
            <button class="donate-tab active" onclick="switchDonate('money',this)">💰 Donate Money</button>
            <button class="donate-tab" id="food-tab" onclick="switchDonate('food',this)">🥕 Donate Food</button>
          </div>

          <!-- MONEY PANEL -->
          <div id="panel-money" class="donate-panel active">
            <h2 style="font-size:1.3rem;margin-bottom:1.5rem;">Choose an amount</h2>
            <form method="POST" id="money-form">
              <?php csrf_field(); ?>
              <input type="hidden" name="donation_type" value="money">
              <input type="hidden" name="amount" id="chosen-amount" value="100">
              <div class="amount-grid">
                <button type="button" class="amount-btn selected" onclick="selectAmount(50,this)">R 50<span class="label">5 meals</span></button>
                <button type="button" class="amount-btn" onclick="selectAmount(100,this)">R 100<span class="label">10 meals</span></button>
                <button type="button" class="amount-btn" onclick="selectAmount(250,this)">R 250<span class="label">25 meals</span></button>
                <button type="button" class="amount-btn" onclick="selectAmount(500,this)">R 500<span class="label">50 meals</span></button>
                <button type="button" class="amount-btn" onclick="selectAmount(1000,this)">R 1,000<span class="label">100 meals</span></button>
                <button type="button" class="amount-btn" onclick="selectAmount(0,this)">Custom<span class="label">your choice</span></button>
              </div>
              <div class="form-group" id="custom-group" style="display:none;">
                <label for="custom_amount">Enter amount (ZAR) <span class="required-mark">*</span></label>
                <input type="number" id="custom_amount" name="custom_amount" min="10" step="1" placeholder="e.g. 750">
              </div>
              <div class="form-group">
                <label for="frequency">Donation frequency</label>
                <select id="frequency" name="frequency">
                  <option value="once">One-time donation</option>
                  <option value="monthly">Monthly recurring</option>
                </select>
              </div>
              <?php if (!is_logged_in()): ?>
                <div class="alert alert--info" style="font-size:0.85rem;">
                  💡 <a href="login.php">Sign in</a> or <a href="register.php">register</a> to track your donation history and receive a Section 18A tax receipt.
                </div>
              <?php endif; ?>
              <button type="submit" class="btn btn--primary btn--full" style="margin-top:0.5rem;font-size:1.05rem;">
                🔒 Donate Securely
              </button>
              <div style="display:flex;gap:1rem;justify-content:center;margin-top:1rem;flex-wrap:wrap;">
                <span class="trust-badge"><svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg> SSL Secured</span>
                <span class="trust-badge"><svg viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg> S18A Receipt</span>
                <span class="trust-badge"><svg viewBox="0 0 24 24"><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/></svg> NPO Registered</span>
              </div>
            </form>
          </div>

          <!-- FOOD PANEL -->
          <div id="panel-food" class="donate-panel">
            <h2 style="font-size:1.3rem;margin-bottom:0.5rem;">Donate Food</h2>
            <p style="color:var(--clr-muted);font-size:0.9rem;margin-bottom:1.5rem;">We accept non-perishable items, canned goods, dry goods, and fresh produce in good condition.</p>
            <form method="POST">
              <?php csrf_field(); ?>
              <input type="hidden" name="donation_type" value="food">
              <div class="form-group">
                <label for="food_description">What are you donating? <span class="required-mark">*</span></label>
                <textarea id="food_description" name="food_description" rows="3" placeholder="e.g. 20 cans of baked beans, 5kg rice, 10 loaves of bread..."></textarea>
              </div>
              <div class="form-group">
                <label for="drop_hub">Drop-off location <span class="required-mark">*</span></label>
                <select id="drop_hub" name="drop_hub" required>
                  <option value="">Select nearest hub</option>
                  <option>Soweto Community Hub</option>
                  <option>Alexandra Distribution Centre</option>
                  <option>Tembisa Food Bank</option>
                  <option>Roodepoort Centre</option>
                </select>
              </div>
              <button type="submit" class="btn btn--green btn--full">Register Food Donation</button>
            </form>
          </div>
        </div>
      </div>

      <!-- INFO SIDE -->
      <div>
        <h2 style="font-size:1.4rem;margin-bottom:1.5rem;">Your donation in action</h2>
        <div class="impact-bullet">
          <div class="icon"><svg viewBox="0 0 24 24"><path d="M18 3v2h-2V3H8v2H6V3H4v18h2v-2h2v2h8v-2h2v2h2V3h-2zM8 17H6v-2h2v2zm0-4H6v-2h2v2zm0-4H6V7h2v2zm10 8h-2v-2h2v2zm0-4h-2v-2h2v2zm0-4h-2V7h2v2z"/></svg></div>
          <div><strong>R 50 = 5 nutritious meals</strong><p>Includes protein, starch and vegetable portions for one family.</p></div>
        </div>
        <div class="impact-bullet">
          <div class="icon"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg></div>
          <div><strong>R 250 = 25 school lunch packs</strong><p>Children learn better when they're not hungry.</p></div>
        </div>
        <div class="impact-bullet">
          <div class="icon"><svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg></div>
          <div><strong>R 1000 = Monthly food parcel for one family</strong><p>Staples for a household of 4 for an entire month.</p></div>
        </div>

        <?php if ($user): ?>
        <div style="background:var(--clr-surface);border:1px solid var(--clr-border);border-radius:var(--radius-lg);padding:1.5rem;margin-top:2rem;">
          <h3 style="font-size:1rem;margin-bottom:0.5rem;">Your giving so far, <?= h($user['full_name']) ?></h3>
          <div style="font-size:2rem;font-weight:700;color:var(--clr-earth2);font-family:var(--font-display);"><?= format_zar($user['total_donated']) ?></div>
          <p style="color:var(--clr-muted);font-size:0.85rem;"><?= $user['donation_count'] ?> donation<?= $user['donation_count'] != 1 ? 's' : '' ?> made &middot; Thank you! ❤️</p>
          <a href="profile.php" class="btn btn--ghost" style="margin-top:1rem;font-size:0.85rem;padding:0.5rem 1rem;">View full history</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>
</main>

<?php footer_html(); ?>

<script>
function selectAmount(val, btn) {
  document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById('chosen-amount').value = val;
  document.getElementById('custom-group').style.display = val === 0 ? 'flex' : 'none';
  if (val === 0) document.getElementById('custom_amount').focus();
}
function switchDonate(panel, btn) {
  document.querySelectorAll('.donate-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.donate-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('panel-' + panel).classList.add('active');
}
<?php if (isset($_GET['donated'])): ?>
  window.history.replaceState({}, '', 'donate.php');
<?php endif; ?>
// Activate food tab if #food in URL
if(window.location.hash === '#food') {
  document.getElementById('food-tab').click();
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
