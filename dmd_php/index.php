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
<title>DMD Food Bank – Fighting Hunger in South Africa</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php require 'includes/nav.php'; ?>

<main id="main-content">

<!-- HERO -->
<section class="hero" aria-labelledby="hero-heading">
  <div class="hero__bg" aria-hidden="true"></div>
  <div class="hero__pattern" aria-hidden="true"></div>
  <div class="hero__content">
    <div>
      <div class="hero__tag" aria-hidden="true">
        <svg width="10" height="10" viewBox="0 0 10 10"><circle cx="5" cy="5" r="5" fill="currentColor"/></svg>
        Non-Profit &middot; South Africa
      </div>
      <h1 class="hero__title" id="hero-heading">No one should go to bed <em>hungry.</em></h1>
      <p class="hero__sub">DMD Food Bank collects and distributes food donations to vulnerable families across South Africa &mdash; fighting hunger, reducing waste, and restoring dignity.</p>
      <div class="hero__actions">
        <a href="donate.php" class="btn btn--primary">Donate Now</a>
        <a href="about.php" class="btn btn--outline">Learn More</a>
        <?php if (!is_logged_in()): ?>
          <a href="register.php" class="btn btn--outline">Join Us</a>
        <?php else: ?>
          <a href="dashboard.php" class="btn btn--outline">My Dashboard</a>
        <?php endif; ?>
      </div>
    </div>
    <div>
      <dl class="hero__stats" aria-label="Impact numbers">
        <div class="hero__stat">
          <dt class="hero__stat-label">Meals served</dt>
          <dd class="hero__stat-num">48K+</dd>
        </div>
        <div class="hero__stat">
          <dt class="hero__stat-label">Active volunteers</dt>
          <dd class="hero__stat-num">320</dd>
        </div>
        <div class="hero__stat">
          <dt class="hero__stat-label">Distribution hubs</dt>
          <dd class="hero__stat-num">7</dd>
        </div>
      </dl>
      <div style="margin-top:1.5rem;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:1rem;padding:1.25rem;">
        <p style="color:rgba(255,255,255,0.6);font-size:0.8rem;margin-bottom:0.5rem;">Monthly meal target progress</p>
        <div style="height:6px;background:rgba(255,255,255,0.1);border-radius:999px;overflow:hidden;margin:1rem 0 0.5rem;">
          <div style="height:100%;background:var(--clr-earth3);border-radius:999px;width:73%;" role="progressbar" aria-valuenow="73" aria-valuemin="0" aria-valuemax="100" aria-label="73% of monthly meal target reached"></div>
        </div>
        <p style="color:var(--clr-earth3);font-size:0.9rem;font-weight:600;">73% of <?= date('F') ?> target reached</p>
      </div>
    </div>
  </div>
</section>

<!-- IMPACT STATS -->
<section style="padding:4rem 0;background:var(--clr-surface);" aria-labelledby="impact-heading">
  <div class="container">
    <h2 class="sr-only" id="impact-heading">Our impact at a glance</h2>
    <dl class="impact__grid">
      <div class="impact__card reveal">
        <div class="impact__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg></div>
        <dt class="impact__label">Years operating</dt>
        <dd class="impact__num">6</dd>
      </div>
      <div class="impact__card reveal">
        <div class="impact__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>
        <dt class="impact__label">Registered beneficiaries</dt>
        <dd class="impact__num">12,400</dd>
      </div>
      <div class="impact__card reveal">
        <div class="impact__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.36 0-2.55-2.06-4.64-4.6-4.64-1.38 0-2.62.57-3.52 1.48L9 3.36l-.88-.88C7.22 1.57 5.98 1 4.6 1 2.06 1 0 3.09 0 5.64c0 .48.11.92.18 1.36H0v2h24V6h-4z"/></svg></div>
        <dt class="impact__label">Tonnes of food saved</dt>
        <dd class="impact__num">380</dd>
      </div>
      <div class="impact__card reveal">
        <div class="impact__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg></div>
        <dt class="impact__label">Raised this year (ZAR)</dt>
        <dd class="impact__num">R2.1M</dd>
      </div>
    </dl>
  </div>
</section>

<!-- HOW YOU CAN HELP -->
<section class="section" aria-labelledby="cta-heading">
  <div class="container">
    <div class="section-header section-header--center">
      <h2 class="section-title" id="cta-heading">How you can help</h2>
      <p class="section-sub">Choose how you want to make a difference in the fight against hunger.</p>
    </div>
    <div class="cta-grid">
      <div class="cta-card reveal">
        <div style="font-size:2.5rem;margin-bottom:1rem;" aria-hidden="true">💰</div>
        <h3>Donate Money</h3>
        <p>Make a monetary donation to help us purchase and distribute food to vulnerable families. Every rand makes a difference.</p>
        <a href="donate.php" class="btn btn--primary">Donate Now</a>
      </div>
      <div class="cta-card reveal" style="transition-delay:0.1s;">
        <div style="font-size:2.5rem;margin-bottom:1rem;" aria-hidden="true">🥕</div>
        <h3>Donate Food</h3>
        <p>Have excess food? Donate it to our distribution hubs. We accept canned goods, dry goods, and fresh produce.</p>
        <a href="donate.php#food" class="btn btn--green">Donate Food</a>
      </div>
      <div class="cta-card reveal" style="transition-delay:0.2s;">
        <div style="font-size:2.5rem;margin-bottom:1rem;" aria-hidden="true">🙋</div>
        <h3>Volunteer</h3>
        <p>Give your time and skills to help sort, pack, and distribute food. We need passionate people like you.</p>
        <a href="register.php" class="btn btn--ghost">Get Involved</a>
      </div>
    </div>
  </div>
</section>

<?php if (!is_logged_in()): ?>
<!-- REGISTER PROMPT -->
<section style="padding:3rem 0;background:var(--clr-earth);">
  <div class="container" style="text-align:center;">
    <h2 style="color:#fff;margin-bottom:0.75rem;">Create a free account to track your impact</h2>
    <p style="color:rgba(255,255,255,0.7);margin-bottom:2rem;max-width:500px;margin-left:auto;margin-right:auto;">See your total donations, download Section 18A receipts, and stay connected with our community.</p>
    <a href="register.php" class="btn btn--primary" style="margin-right:1rem;">Register Free</a>
    <a href="login.php" class="btn btn--outline">Sign In</a>
  </div>
</section>
<?php endif; ?>

</main>

<?php footer_html(); ?>

<div class="notif" id="notif" role="alert" aria-live="assertive">
  <div class="notif__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/></svg></div>
  <div class="notif__text"><strong id="notif-title"></strong><span id="notif-msg"></span></div>
  <button class="notif__close" onclick="this.parentElement.classList.remove('show')" aria-label="Close">&times;</button>
</div>

<?php if ($msg = flash('success')): ?>
<script>
  window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('notif-title').textContent = 'Success!';
    document.getElementById('notif-msg').textContent = <?= json_encode($msg) ?>;
    document.getElementById('notif').classList.add('show');
    setTimeout(() => document.getElementById('notif').classList.remove('show'), 5000);
  });
</script>
<?php endif; ?>

<script>
const toggle = document.getElementById('nav-toggle');
const navLinks = document.getElementById('nav-links');
if(toggle) toggle.addEventListener('click', () => {
  const exp = toggle.getAttribute('aria-expanded') === 'true';
  toggle.setAttribute('aria-expanded', String(!exp));
  navLinks.classList.toggle('open');
});
const revealObs = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); });
}, {threshold:0.12});
document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));
const ticker = document.querySelector('.ticker__inner');
if(ticker){
  ticker.addEventListener('mouseenter', () => ticker.style.animationPlayState='paused');
  ticker.addEventListener('mouseleave', () => ticker.style.animationPlayState='running');
}
</script>
</body>
</html>
