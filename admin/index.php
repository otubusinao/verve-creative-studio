<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$user = admin_current_user();
$name = htmlspecialchars($user['name'] ?? 'Administrator', ENT_QUOTES, 'UTF-8');
$role = htmlspecialchars(ucwords(str_replace('_', ' ', $user['role'] ?? 'editor')), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard | Verve Creative Studio</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}
body{margin:0;background:#0b0a09;color:#f2ede4;font-family:Jost,sans-serif}
header{height:75px;background:#141210;border-bottom:1px solid #2a2620;display:flex;align-items:center;justify-content:space-between;padding:0 5%}
.logo{font:600 25px 'Cormorant Garamond',serif;letter-spacing:3px;color:#c9a24b}
.logo small{display:block;font:9px Jost,sans-serif;letter-spacing:3px;color:#a8a29a}
header a{color:#e8c873;text-decoration:none;font-size:12px}
main{max-width:1150px;margin:48px auto;padding:0 24px}
.eyebrow{color:#c9a24b;font-size:11px;letter-spacing:3px;text-transform:uppercase}
h1{font:500 38px 'Cormorant Garamond',serif;margin:8px 0}
.muted{color:#a8a29a;font-size:14px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-top:35px}
.card{background:#141210;border:1px solid #2a2620;padding:25px;border-radius:3px}
.card h2{font:500 23px 'Cormorant Garamond',serif;color:#e8c873;margin:0 0 9px}
.card p{font-size:13px;color:#a8a29a;line-height:1.6;min-height:42px}
.card a{display:inline-block;margin-top:12px;color:#c9a24b;font-size:12px;text-decoration:none;letter-spacing:1px}
.card a:hover{color:#e8c873}
.notice{margin-top:30px;border-left:2px solid #c9a24b;background:#141210;padding:18px;color:#a8a29a;font-size:13px}
.logout button{background:none;border:1px solid #8a6b23;color:#e8c873;padding:9px 15px;font:12px Jost,sans-serif;cursor:pointer}
</style>
</head>
<body>
<header>
  <div class="logo">VERVE<small>ADMINISTRATION</small></div>
  <form class="logout" method="post" action="<?= SITE_URL ?>/admin/logout.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit">Sign Out</button>
  </form>
</header>
<main>
  <p class="eyebrow">Administration</p>
  <h1>Welcome, <?= $name ?></h1>
  <p class="muted">Your Verve Creative Studio management workspace. Access: <?= $role ?></p>

  <div class="grid">
    <section class="card">
      <h2>Portfolio</h2>
      <p>Manage featured projects and creative work displayed on the website.</p>
      <a href="<?= SITE_URL ?>/admin/portfolio.php">MANAGE PORTFOLIO →</a>
    </section>
    <section class="card">
      <h2>Gallery & Media</h2>
      <p>Manage photographs and visual assets for the website.</p>
      <a href="<?= SITE_URL ?>/admin/gallery.php">MANAGE MEDIA →</a>
    </section>
    <section class="card">
      <h2>Bookings</h2>
      <p>Review and manage incoming project booking submissions.</p>
      <a href="<?= SITE_URL ?>/admin/bookings-flow-a.php">VIEW BOOKINGS →</a>
    </section>
    <section class="card">
      <h2>Enquiries</h2>
      <p>Review creative project enquiries and client requests.</p>
      <a href="<?= SITE_URL ?>/admin/bookings-flow-b.php">VIEW ENQUIRIES →</a>
    </section>
    <section class="card">
      <h2>Testimonials</h2>
      <p>Manage client feedback displayed across the website.</p>
      <a href="<?= SITE_URL ?>/admin/testimonials.php">MANAGE TESTIMONIALS →</a>
    </section>
    <section class="card">
      <h2>Settings & Users</h2>
      <p>Manage authorised administrators and access settings.</p>
      <a href="<?= SITE_URL ?>/admin/users.php">MANAGE USERS →</a>
    </section>
  </div>

  <div class="notice">Dashboard foundation is ready. The management modules will be connected to their database tables as each feature is implemented.</div>
</main>
</body>
</html>
