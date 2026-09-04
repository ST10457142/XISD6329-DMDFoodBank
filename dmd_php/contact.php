<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';
session_start_safe();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { $errors[] = 'Invalid form submission.'; }
    else {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $msg     = trim($_POST['message'] ?? '');
        if (empty($name))    $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
        if (empty($msg))     $errors[] = 'Message is required.';
        if (empty($errors)) {
            db()->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?,?,?,?)')->execute([$name, $email, $subject, $msg]);
            flash('success', 'Message sent! We\'ll get back to you within 2 business days.');
            header('Location: contact.php'); exit;
        }
    }
}
$flash_success = flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us – DMD Food Bank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php require 'includes/nav.php'; ?>
<section style="background:var(--clr-earth);padding:4rem 0;text-align:center;">
  <div class="container">
    <h1 style="color:#fff;font-size:clamp(2rem,4vw,3rem);">Contact Us</h1>
    <p style="color:rgba(255,255,255,0.7);max-width:500px;margin:0.75rem auto 0;">Get in touch for donations, partnerships, volunteering, or media enquiries.</p>
  </div>
</section>
<main id="main-content">
<section class="section">
  <div class="container">
    <?php if ($flash_success): ?>
      <div class="alert alert--success" style="max-width:700px;margin:0 auto 2rem;">✅ <?= h($flash_success) ?></div>
    <?php endif; ?>
    <div class="grid-2" style="gap:4rem;align-items:start;">
      <div>
        <h2 style="font-size:1.4rem;margin-bottom:1.5rem;">Get in touch</h2>
        <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:2rem;">
          <?php $contacts = [
            ['📍','Address','123 Ubuntu Street, Soweto, Johannesburg, 1804'],
            ['📞','Phone','011 123 4567'],
            ['📧','Email','info@dmdfoodbank.org'],
            ['🕐','Office Hours','Mon–Fri: 08:00–17:00 &middot; Sat: 09:00–13:00'],
          ]; foreach ($contacts as [$icon,$label,$val]): ?>
            <div style="display:flex;gap:1rem;align-items:flex-start;">
              <div style="width:40px;height:40px;background:var(--clr-earth3);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;"><?= $icon ?></div>
              <div><strong style="display:block;font-size:0.88rem;color:var(--clr-muted);"><?= $label ?></strong><span><?= $val ?></span></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="form-card">
        <h2 style="font-size:1.2rem;margin-bottom:1.5rem;">Send us a message</h2>
        <?php if (!empty($errors)): ?>
          <div class="alert alert--error"><?php foreach($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?></div>
        <?php endif; ?>
        <form method="POST" novalidate>
          <?php csrf_field(); ?>
          <div class="form-row">
            <div class="form-group"><label for="name">Full Name <span class="required-mark">*</span></label><input type="text" id="name" name="name" required value="<?= h($_POST['name'] ?? '') ?>"></div>
            <div class="form-group"><label for="email">Email <span class="required-mark">*</span></label><input type="email" id="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>"></div>
          </div>
          <div class="form-group"><label for="subject">Subject</label><input type="text" id="subject" name="subject" value="<?= h($_POST['subject'] ?? '') ?>" placeholder="e.g. Food donation enquiry"></div>
          <div class="form-group"><label for="message">Message <span class="required-mark">*</span></label><textarea id="message" name="message" rows="5" required><?= h($_POST['message'] ?? '') ?></textarea></div>
          <button type="submit" class="btn btn--primary btn--full">Send Message</button>
        </form>
      </div>
    </div>
  </div>
</section>
</main>
<?php footer_html(); ?>
<script>
const toggle = document.getElementById('nav-toggle');
const navLinks = document.getElementById('nav-links');
if(toggle) toggle.addEventListener('click', () => { const exp = toggle.getAttribute('aria-expanded')==='true'; toggle.setAttribute('aria-expanded',String(!exp)); navLinks.classList.toggle('open'); });
</script>
</body>
</html>
