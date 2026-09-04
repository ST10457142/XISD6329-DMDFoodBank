<?php // includes/helpers.php
function csrf_token(): string {
    session_start_safe();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): void {
    echo '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(): bool {
    return isset($_POST['csrf_token']) && hash_equals(csrf_token(), $_POST['csrf_token']);
}

function footer_html(): void { ?>
<footer>
  <div class="container">
    <div class="footer__grid">
      <div>
        <div class="footer__brand">DMD Food Bank</div>
        <p class="footer__tagline">Fighting hunger and food insecurity across South Africa since 2020. Registered NPO.</p>
        <p style="font-size:0.82rem;color:rgba(255,255,255,0.4);">NPO Reg: 123-456 &middot; PBO Reg: 930 012 345<br>POPIA compliant &middot; Section 18A tax receipts</p>
      </div>
      <nav class="footer__col" aria-label="Get involved">
        <h4>Get involved</h4>
        <a href="donate.php">Donate money</a>
        <a href="donate.php">Donate food</a>
        <a href="register.php">Volunteer</a>
        <a href="contact.php">Contact us</a>
      </nav>
      <nav class="footer__col" aria-label="About us">
        <h4>About us</h4>
        <a href="about.php">Our story</a>
        <a href="locations.php">Our locations</a>
      </nav>
      <nav class="footer__col" aria-label="Account">
        <h4>Account</h4>
        <a href="login.php">Sign in</a>
        <a href="register.php">Register</a>
        <a href="profile.php">My profile</a>
        <a href="dashboard.php">Dashboard</a>
      </nav>
    </div>
    <div class="footer__bottom">
      <p>&copy; <?= date('Y') ?> DMD Food Bank. All rights reserved.</p>
    </div>
  </div>
</footer>
<?php }
