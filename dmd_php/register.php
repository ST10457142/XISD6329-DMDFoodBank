<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';
session_start_safe();

// Already logged in
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $type    = $_POST['reg_type'] ?? '';
        $name    = trim($_POST['full_name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $pass    = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Validate common fields
        if (empty($name))  $errors[] = 'Full name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email address required.';

        if ($type === 'donor') {
            if (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';
            if (!preg_match('/[A-Z]/', $pass)) $errors[] = 'Password must contain at least one uppercase letter.';
            if (!preg_match('/[0-9]/', $pass)) $errors[] = 'Password must contain at least one number.';
            if ($pass !== $confirm) $errors[] = 'Passwords do not match.';

            if (empty($errors)) {
                // Check email unique
                $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $errors[] = 'An account with this email already exists. <a href="login.php">Sign in instead</a>.';
                } else {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $donor_type = $_POST['donor_type'] ?? 'Individual';
                    $stmt = db()->prepare('INSERT INTO users (full_name, email, password, role, donor_type) VALUES (?,?,?,?,?)');
                    $stmt->execute([$name, $email, $hash, 'donor', $donor_type]);
                    flash('success', 'Account created successfully! Please sign in to continue.');
                    header('Location: login.php');
                    exit;
                }
            }
        } elseif ($type === 'volunteer') {
            $skills = $_POST['skills'] ?? '';
            $avail  = $_POST['availability'] ?? '';
            if (empty($skills)) $errors[] = 'Skills / interests are required.';
            if (empty($avail))  $errors[] = 'Availability is required.';
            if (empty($errors)) {
                $stmt = db()->prepare('INSERT INTO volunteers (full_name, email, skills, availability) VALUES (?,?,?,?)');
                $stmt->execute([$name, $email, $skills, $avail]);
                flash('success', 'Volunteer application received! We\'ll be in touch soon.');
                header('Location: login.php');
                exit;
            }
        } elseif ($type === 'beneficiary') {
            $phone     = $_POST['phone'] ?? '';
            $household = $_POST['household_size'] ?? '';
            $hub       = $_POST['nearest_hub'] ?? '';
            if (empty($phone))     $errors[] = 'Phone number is required.';
            if (empty($household)) $errors[] = 'Household size is required.';
            if (empty($hub))       $errors[] = 'Nearest hub is required.';
            if (empty($errors)) {
                $stmt = db()->prepare('INSERT INTO beneficiaries (full_name, phone, household_size, nearest_hub) VALUES (?,?,?,?)');
                $stmt->execute([$name, $phone, $household, $hub]);
                flash('success', 'Beneficiary application received! We\'ll review and contact you within 48 hours.');
                header('Location: login.php');
                exit;
            }
        } else {
            $errors[] = 'Please select a registration type.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register – DMD Food Bank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
.reg-tabs{display:flex;gap:0;background:var(--clr-surface2);border-radius:var(--radius-md);padding:0.3rem;margin-bottom:2rem;width:100%}
.reg-tab{flex:1;padding:0.7rem;border-radius:var(--radius-sm);border:none;cursor:pointer;font-family:var(--font-body);font-size:0.88rem;font-weight:600;color:var(--clr-muted);background:transparent;transition:all var(--transition);text-align:center;min-height:44px}
.reg-tab.active{background:var(--clr-surface);color:var(--clr-earth);box-shadow:var(--shadow-sm)}
.reg-panel{display:none}
.reg-panel.active{display:block}
.form-box{background:var(--clr-surface);border-radius:var(--radius-xl);padding:2.5rem;border:1px solid var(--clr-border);box-shadow:var(--shadow-md);max-width:560px;margin:0 auto}
</style>
</head>
<body>
<?php require 'includes/nav.php'; ?>

<section style="padding:4rem 0;background:var(--clr-surface2);">
  <div class="container" style="text-align:center;">
    <h1 class="section-title">Join Our Community</h1>
    <p class="section-sub" style="margin:0 auto;">Simple, secure registration. All data protected under POPIA.</p>
  </div>
</section>

<main id="main-content">
<section class="section">
  <div class="container">

    <?php if (!empty($errors)): ?>
      <div class="alert alert--error" style="max-width:560px;margin:0 auto 2rem;">
        <div>
          <?php foreach($errors as $e): ?>
            <div><?= $e ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="form-box">
      <div class="reg-tabs" role="tablist" aria-label="Registration type">
        <button class="reg-tab active" role="tab" aria-selected="true"  data-panel="donor"       onclick="switchTab('donor',this)">❤️ Donor Account</button>
        <button class="reg-tab"        role="tab" aria-selected="false" data-panel="volunteer"   onclick="switchTab('volunteer',this)">🙋 Volunteer</button>
        <button class="reg-tab"        role="tab" aria-selected="false" data-panel="beneficiary" onclick="switchTab('beneficiary',this)">🤲 Beneficiary</button>
      </div>

      <!-- DONOR -->
      <div id="panel-donor" class="reg-panel active">
        <h2 style="font-size:1.3rem;margin-bottom:1.5rem;">Create Donor Account</h2>
        <form method="POST" novalidate>
          <?php csrf_field(); ?>
          <input type="hidden" name="reg_type" value="donor">
          <div class="form-group">
            <label for="d-name">Full Name <span class="required-mark">*</span></label>
            <input type="text" id="d-name" name="full_name" required autocomplete="name" value="<?= h($_POST['full_name'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="d-email">Email Address <span class="required-mark">*</span></label>
            <input type="email" id="d-email" name="email" required autocomplete="email" value="<?= h($_POST['email'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="d-type">Donor Type <span class="required-mark">*</span></label>
            <select id="d-type" name="donor_type" required>
              <option value="Individual">Individual</option>
              <option value="Corporate / Business">Corporate / Business</option>
              <option value="Government / Parastatal">Government / Parastatal</option>
              <option value="Religious / Community Org">Religious / Community Org</option>
            </select>
          </div>
          <div class="form-group">
            <label for="d-pass">Password <span class="required-mark">*</span></label>
            <input type="password" id="d-pass" name="password" required minlength="8" autocomplete="new-password">
            <span class="hint">Min 8 chars &middot; 1 uppercase &middot; 1 number</span>
          </div>
          <div class="form-group">
            <label for="d-confirm">Confirm Password <span class="required-mark">*</span></label>
            <input type="password" id="d-confirm" name="confirm_password" required autocomplete="new-password">
          </div>
          <button type="submit" class="btn btn--primary btn--full">Create Account</button>
        </form>
      </div>

      <!-- VOLUNTEER -->
      <div id="panel-volunteer" class="reg-panel">
        <h2 style="font-size:1.3rem;margin-bottom:1.5rem;">Register as Volunteer</h2>
        <form method="POST" novalidate>
          <?php csrf_field(); ?>
          <input type="hidden" name="reg_type" value="volunteer">
          <div class="form-group">
            <label for="v-name">Full Name <span class="required-mark">*</span></label>
            <input type="text" id="v-name" name="full_name" required autocomplete="name">
          </div>
          <div class="form-group">
            <label for="v-email">Email Address <span class="required-mark">*</span></label>
            <input type="email" id="v-email" name="email" required autocomplete="email">
          </div>
          <div class="form-group">
            <label for="v-skills">Skills / Interests <span class="required-mark">*</span></label>
            <select id="v-skills" name="skills" required>
              <option value="">Select</option>
              <option>Sorting &amp; Packing</option>
              <option>Food Distribution</option>
              <option>Administration</option>
              <option>Driving / Logistics</option>
              <option>Fundraising</option>
              <option>Social Media</option>
            </select>
          </div>
          <div class="form-group">
            <label for="v-avail">Availability <span class="required-mark">*</span></label>
            <select id="v-avail" name="availability" required>
              <option value="">Select</option>
              <option>Weekdays only</option>
              <option>Weekends only</option>
              <option>Flexible</option>
            </select>
          </div>
          <button type="submit" class="btn btn--green btn--full">Register as Volunteer</button>
        </form>
      </div>

      <!-- BENEFICIARY -->
      <div id="panel-beneficiary" class="reg-panel">
        <h2 style="font-size:1.3rem;margin-bottom:1.5rem;">Apply for Food Assistance</h2>
        <form method="POST" novalidate>
          <?php csrf_field(); ?>
          <input type="hidden" name="reg_type" value="beneficiary">
          <div class="form-group">
            <label for="b-name">Full Name <span class="required-mark">*</span></label>
            <input type="text" id="b-name" name="full_name" required autocomplete="name">
          </div>
          <div class="form-group">
            <label for="b-phone">Phone Number <span class="required-mark">*</span></label>
            <input type="tel" id="b-phone" name="phone" required placeholder="0xxxxxxxxx">
          </div>
          <div class="form-group">
            <label for="b-household">Household Size <span class="required-mark">*</span></label>
            <select id="b-household" name="household_size" required>
              <option value="">Select</option>
              <option>1–2 people</option><option>3–4 people</option>
              <option>5–6 people</option><option>7+ people</option>
            </select>
          </div>
          <div class="form-group">
            <label for="b-hub">Nearest Hub <span class="required-mark">*</span></label>
            <select id="b-hub" name="nearest_hub" required>
              <option value="">Select hub</option>
              <option>Soweto</option><option>Alexandra</option>
              <option>Tembisa</option><option>Roodepoort</option>
            </select>
          </div>
          <button type="submit" class="btn btn--dark btn--full">Apply for Assistance</button>
        </form>
      </div>

      <p style="text-align:center;margin-top:1.5rem;color:var(--clr-muted);font-size:0.9rem;">
        Already have an account? <a href="login.php" style="font-weight:600;">Sign in here</a>
      </p>
    </div>

  </div>
</section>
</main>

<?php footer_html(); ?>

<script>
function switchTab(panel, btn) {
  document.querySelectorAll('.reg-tab').forEach(t => { t.classList.remove('active'); t.setAttribute('aria-selected','false'); });
  document.querySelectorAll('.reg-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  btn.setAttribute('aria-selected','true');
  document.getElementById('panel-' + panel).classList.add('active');
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
