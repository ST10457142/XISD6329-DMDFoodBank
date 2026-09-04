<?php // includes/nav.php
session_start_safe();
$user = current_user();
?>
<a href="#main-content" class="skip-link">Skip to main content</a>
<nav class="nav" aria-label="Main navigation">
  <div class="nav__inner">
    <a href="index.php" class="nav__logo" aria-label="DMD Food Bank Home">
      <div class="nav__logo-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M12 2C7 2 3 6 3 11c0 3 1.5 5.5 3.8 7.1L8 22h8l1.2-3.9C19.5 16.5 21 14 21 11c0-5-4-9-9-9zm0 14c-2.8 0-5-2.2-5-5s2.2-5 5-5 5 2.2 5 5-2.2 5-5 5z"/></svg>
      </div>
      DMD Food Bank
    </a>
    <button class="nav__toggle" aria-expanded="false" aria-controls="nav-links" id="nav-toggle">
      <span aria-hidden="true">&#9776;</span><span class="sr-only">Open menu</span>
    </button>
    <ul class="nav__links" id="nav-links" role="list">
      <li><a href="about.php">About</a></li>
      <li><a href="donate.php">Donate</a></li>
      <li><a href="locations.php">Locations</a></li>
      <li><a href="contact.php">Contact</a></li>
      <?php if ($user): ?>
        <li><a href="profile.php">My Profile</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
        <?php if ($user['role'] === 'admin'): ?>
          <li><a href="admin/index.php" style="color:var(--clr-warm);font-weight:700;">Admin Panel</a></li>
        <?php endif; ?>
        <li>
          <form method="POST" action="logout.php" style="display:inline">
            <?php csrf_field(); ?>
            <button type="submit" class="nav__cta" style="border:none;cursor:pointer;font-family:inherit">Logout (<?= h($user['full_name']) ?>)</button>
          </form>
        </li>
      <?php else: ?>
        <li><a href="login.php" class="nav__cta">Sign In</a></li>
        <li><a href="register.php">Register</a></li>
      <?php endif; ?>
      <li><a href="donate.php" class="nav__cta" style="background:var(--clr-warm)!important">Donate Now</a></li>
    </ul>
  </div>
</nav>
<div class="ticker" role="marquee" aria-label="Live activity feed">
  <div class="ticker__inner" aria-hidden="true">
    <span>&#x1F96B; 250 meals distributed today in Soweto</span>
    <span class="ticker__sep">&#x25CF;</span>
    <span>&#x2764;&#xFE0F; New donor: Woolworths pledges 500kg monthly</span>
    <span class="ticker__sep">&#x25CF;</span>
    <span>&#x1F4E6; Low stock alert: Canned goods at Tembisa hub</span>
    <span class="ticker__sep">&#x25CF;</span>
    <span>&#x1F331; 42 new volunteers registered this week</span>
    <span class="ticker__sep">&#x25CF;</span>
    <span>&#x1F96B; 250 meals distributed today in Soweto</span>
    <span class="ticker__sep">&#x25CF;</span>
    <span>&#x2764;&#xFE0F; New donor: Woolworths pledges 500kg monthly</span>
    <span class="ticker__sep">&#x25CF;</span>
    <span>&#x1F4E6; Low stock alert: Canned goods at Tembisa hub</span>
    <span class="ticker__sep">&#x25CF;</span>
    <span>&#x1F331; 42 new volunteers registered this week</span>
  </div>
</div>
