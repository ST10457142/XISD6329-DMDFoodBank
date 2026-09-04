<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';
session_start_safe();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About Us – DMD Food Bank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;1,9..144,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php require 'includes/nav.php'; ?>
<section style="background:var(--clr-earth);padding:4rem 0;text-align:center;">
  <div class="container">
    <h1 style="color:#fff;font-size:clamp(2rem,4vw,3rem);">Our Story</h1>
    <p style="color:rgba(255,255,255,0.7);max-width:580px;margin:0.75rem auto 0;">Born from a belief that no South African should go to bed hungry.</p>
  </div>
</section>
<main id="main-content">
<section class="section">
  <div class="container">
    <div class="grid-2" style="align-items:center;gap:4rem;">
      <div>
        <span class="section-label">Who we are</span>
        <h2 class="section-title">Fighting hunger since 2020</h2>
        <p style="color:var(--clr-muted);margin-bottom:1rem;">DMD Food Bank was founded in response to the food crisis amplified by the COVID-19 pandemic. What started as a neighbourhood initiative in Soweto quickly grew into a multi-hub organisation serving thousands of families across Gauteng.</p>
        <p style="color:var(--clr-muted);margin-bottom:1rem;">We partner with supermarkets, restaurants, farms, and generous donors to rescue surplus food and distribute it to those who need it most — with dignity and respect.</p>
        <p style="color:var(--clr-muted);">We are a registered NPO (123-456) and PBO (930 012 345), and all monetary donations qualify for Section 18A tax deductions.</p>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="impact__card"><div class="impact__icon"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg></div><dd class="impact__num">6</dd><dt class="impact__label">Years of service</dt></div>
        <div class="impact__card"><div class="impact__icon"><svg viewBox="0 0 24 24"><path d="M12 2C7 2 3 6 3 11c0 3 1.5 5.5 3.8 7.1L8 22h8l1.2-3.9C19.5 16.5 21 14 21 11c0-5-4-9-9-9z"/></svg></div><dd class="impact__num">7</dd><dt class="impact__label">Distribution hubs</dt></div>
        <div class="impact__card"><div class="impact__icon"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div><dd class="impact__num">320+</dd><dt class="impact__label">Active volunteers</dt></div>
        <div class="impact__card"><div class="impact__icon"><svg viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg></div><dd class="impact__num">48K+</dd><dt class="impact__label">Meals served</dt></div>
      </div>
    </div>
  </div>
</section>
<section class="section section--alt">
  <div class="container">
    <div class="section-header section-header--center">
      <h2 class="section-title">Our Values</h2>
    </div>
    <div class="grid-3">
      <?php
      $vals = [
        ['🌱','Dignity','Every person deserves to receive food with respect and without stigma.'],
        ['🤝','Community','We believe communities are stronger when they lift each other up.'],
        ['♻️','Sustainability','Rescuing food waste and reducing environmental impact is at our core.'],
      ];
      foreach ($vals as [$icon,$title,$desc]): ?>
        <div class="cta-card">
          <div style="font-size:2.5rem;margin-bottom:0.75rem;"><?= $icon ?></div>
          <h3><?= $title ?></h3>
          <p><?= $desc ?></p>
        </div>
      <?php endforeach; ?>
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
