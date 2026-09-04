<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';
session_start_safe();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Our Locations – DMD Food Bank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php require 'includes/nav.php'; ?>
<section style="background:var(--clr-earth);padding:4rem 0;text-align:center;">
  <div class="container">
    <h1 style="color:#fff;font-size:clamp(2rem,4vw,3rem);">Our Distribution Hubs</h1>
    <p style="color:rgba(255,255,255,0.7);max-width:540px;margin:0.75rem auto 0;">Find your nearest DMD Food Bank hub for donations, food collection, or volunteering.</p>
  </div>
</section>
<main id="main-content">
<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;">
      <?php
      $hubs = [
        ['Soweto Community Hub','123 Ubuntu St, Soweto, 1804','Mon–Sat: 08:00–16:00','011 111 2222','🟢 Fully stocked'],
        ['Alexandra Distribution Centre','45 Pan Africa Ave, Alexandra, 2090','Mon–Fri: 09:00–15:00','011 222 3333','🟡 Low on canned goods'],
        ['Tembisa Food Bank','78 Ivory Park Rd, Tembisa, 1632','Mon–Sat: 08:00–17:00','011 333 4444','🟢 Fully stocked'],
        ['Roodepoort Centre','12 West Rand Dr, Roodepoort, 1725','Mon–Fri: 08:00–15:00','011 444 5555','🟢 Fully stocked'],
        ['Diepsloot Hub','99 Cluster A, Diepsloot, 2189','Tue &amp; Thu: 09:00–14:00','011 555 6666','🔴 Critical stock needed'],
        ['Orange Farm Depot','34 Main Rd, Orange Farm, 1805','Mon, Wed, Fri: 09:00–13:00','011 666 7777','🟡 Low on staples'],
        ['Katlehong Centre','56 Natalspruit Rd, Katlehong, 1431','Mon–Sat: 08:00–16:00','011 777 8888','🟢 Fully stocked'],
      ];
      foreach ($hubs as [$name, $addr, $hours, $phone, $stock]):
      ?>
        <div class="cta-card" style="text-align:left;align-items:flex-start;">
          <div style="font-size:1.5rem;margin-bottom:0.5rem;">📍</div>
          <h3 style="margin-bottom:0.5rem;font-size:1.1rem;"><?= $name ?></h3>
          <p style="font-size:0.85rem;color:var(--clr-muted);margin-bottom:0.5rem;"><?= $addr ?></p>
          <p style="font-size:0.82rem;color:var(--clr-muted);margin-bottom:0.25rem;">🕐 <?= $hours ?></p>
          <p style="font-size:0.82rem;color:var(--clr-muted);margin-bottom:0.75rem;">📞 <?= $phone ?></p>
          <p style="font-size:0.8rem;font-weight:600;"><?= $stock ?></p>
          <a href="donate.php#food" class="btn btn--ghost" style="margin-top:1rem;font-size:0.82rem;padding:0.4rem 1rem;">Donate Here</a>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:3rem;padding:2rem;background:var(--clr-surface);border-radius:var(--radius-xl);border:1px solid var(--clr-border);text-align:center;">
      <h2 style="font-size:1.2rem;margin-bottom:0.5rem;">Can't find a hub near you?</h2>
      <p style="color:var(--clr-muted);margin-bottom:1.25rem;">We can arrange a pickup for large food donations. Contact us and we'll come to you.</p>
      <a href="contact.php" class="btn btn--primary">Contact Us</a>
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
