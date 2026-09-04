<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';
session_start_safe();

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if (empty($email) || empty($pass)) {
            $errors[] = 'Email and password are required.';
        } else {
            $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['full_name'];

                // Update last login
                db()->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

                flash('success', 'Welcome back, ' . $user['full_name'] . '!');

                $redirect = $_GET['redirect'] ?? '';
                if ($redirect && strpos($redirect, '/') === 0) {
                    header('Location: ' . $redirect);
                } elseif ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit;
            } else {
                $errors[] = 'Incorrect email or password. Please try again.';
            }
        }
    }
}

$flash_success = flash('success');
$flash_error   = flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In – DMD Food Bank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
.login-wrap{min-height:100vh;display:grid;grid-template-columns:1fr 1fr;}
@media(max-width:768px){.login-wrap{grid-template-columns:1fr}}
.login-brand{background:var(--clr-earth);display:flex;flex-direction:column;justify-content:center;padding:4rem 3rem;position:relative;overflow:hidden;}
@media(max-width:768px){.login-brand{display:none}}
.login-brand::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 60% at 70% 40%,rgba(82,183,136,0.2),transparent);}
.login-brand__content{position:relative;z-index:1;}
.login-brand h1{font-family:var(--font-display);font-size:2.8rem;color:#fff;margin-bottom:1rem;line-height:1.1;}
.login-brand h1 em{color:var(--clr-earth3);font-style:italic;}
.login-brand p{color:rgba(255,255,255,0.65);font-size:1rem;margin-bottom:2.5rem;line-height:1.7;}
.login-stat{display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;}
.login-stat-icon{width:40px;height:40px;background:rgba(82,183,136,0.2);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.login-stat-icon svg{width:20px;height:20px;fill:var(--clr-earth3);}
.login-stat-text strong{display:block;color:#fff;font-size:0.95rem;}
.login-stat-text span{color:rgba(255,255,255,0.5);font-size:0.8rem;}
.login-form-side{display:flex;flex-direction:column;justify-content:center;padding:3rem 2.5rem;background:var(--clr-cream);}
.login-box{max-width:420px;width:100%;margin:0 auto;}
.login-logo{display:flex;align-items:center;gap:0.5rem;margin-bottom:2rem;font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--clr-earth);text-decoration:none;}
.login-logo-icon{width:36px;height:36px;background:var(--clr-earth3);border-radius:8px;display:flex;align-items:center;justify-content:center;}
.login-logo-icon svg{width:20px;height:20px;fill:var(--clr-earth);}
</style>
</head>
<body>

<div class="login-wrap">
  <!-- LEFT BRAND PANEL -->
  <div class="login-brand" aria-hidden="true">
    <div class="login-brand__content">
      <h1>Fighting hunger, <em>one meal</em> at a time.</h1>
      <p>Sign in to track your donations, manage your profile, and see the impact you're making across South Africa.</p>
      <div class="login-stat">
        <div class="login-stat-icon"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg></div>
        <div class="login-stat-text"><strong>48,000+ meals</strong><span>distributed this year</span></div>
      </div>
      <div class="login-stat">
        <div class="login-stat-icon"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>
        <div class="login-stat-text"><strong>320 volunteers</strong><span>across 7 hubs</span></div>
      </div>
      <div class="login-stat">
        <div class="login-stat-icon"><svg viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg></div>
        <div class="login-stat-text"><strong>R 2.1M raised</strong><span>in <?= date('Y') ?></span></div>
      </div>
    </div>
  </div>

  <!-- RIGHT FORM PANEL -->
  <div class="login-form-side">
    <div class="login-box">
      <a href="index.php" class="login-logo">
        <div class="login-logo-icon"><svg viewBox="0 0 24 24"><path d="M12 2C7 2 3 6 3 11c0 3 1.5 5.5 3.8 7.1L8 22h8l1.2-3.9C19.5 16.5 21 14 21 11c0-5-4-9-9-9zm0 14c-2.8 0-5-2.2-5-5s2.2-5 5-5 5 2.2 5 5-2.2 5-5 5z"/></svg></div>
        DMD Food Bank
      </a>

      <h2 style="font-size:1.6rem;margin-bottom:0.3rem;">Welcome back</h2>
      <p style="color:var(--clr-muted);margin-bottom:1.75rem;font-size:0.95rem;">Sign in to your account to continue</p>

      <?php if ($flash_success): ?>
        <div class="alert alert--success">✅ <?= h($flash_success) ?></div>
      <?php endif; ?>
      <?php if ($flash_error): ?>
        <div class="alert alert--error">❌ <?= h($flash_error) ?></div>
      <?php endif; ?>
      <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
          <?php foreach ($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <?php csrf_field(); ?>
        <div class="form-group">
          <label for="email">Email Address <span class="required-mark">*</span></label>
          <input type="email" id="email" name="email" required autocomplete="email"
                 value="<?= h($_POST['email'] ?? '') ?>" placeholder="you@example.com">
        </div>
        <div class="form-group">
          <label for="password">Password <span class="required-mark">*</span></label>
          <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn--primary btn--full" style="margin-top:0.5rem;">Sign In</button>
      </form>

      <div style="text-align:center;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--clr-border);">
        <p style="color:var(--clr-muted);font-size:0.9rem;">
          Don't have an account? <a href="register.php" style="font-weight:600;color:var(--clr-earth2);">Create one free</a>
        </p>
        <p style="margin-top:0.5rem;font-size:0.82rem;color:var(--clr-hint);">
          <a href="index.php">← Back to home</a>
        </p>
      </div>

      <div style="margin-top:2rem;padding:1rem;background:var(--clr-surface2);border-radius:var(--radius-md);font-size:0.8rem;color:var(--clr-hint);">
        <strong style="color:var(--clr-muted);">Admin login:</strong> admin@dmdfoodbank.org / Admin@1234<br>
        <em>(Change password after first login)</em>
      </div>
    </div>
  </div>
</div>

</body>
</html>
