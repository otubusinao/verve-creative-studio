
<?php
require_once __DIR__ . '/../includes/auth.php';

admin_require_login();

$pdo = get_db();
$admin = admin_current_user();

function dashboard_count(PDO $pdo, string $sql): int
{
    return (int) $pdo->query($sql)->fetchColumn();
}

try {
    $stats = [
        'bookings' => dashboard_count($pdo, "SELECT COUNT(*) FROM bookings"),
        'pending_bookings' => dashboard_count(
            $pdo,
            "SELECT COUNT(*) FROM bookings WHERE booking_status = 'pending_payment'"
        ),
        'enquiries' => dashboard_count($pdo, "SELECT COUNT(*) FROM project_enquiries"),
        'new_enquiries' => dashboard_count(
            $pdo,
            "SELECT COUNT(*) FROM project_enquiries WHERE status = 'new'"
        ),
        'portfolio' => dashboard_count(
            $pdo,
            "SELECT COUNT(*) FROM portfolio_projects WHERE status = 'published'"
        ),
        'gallery' => dashboard_count(
            $pdo,
            "SELECT COUNT(*) FROM gallery_items WHERE status = 'published'"
        ),
        'media' => dashboard_count(
            $pdo,
            "SELECT COUNT(*) FROM media_assets WHERE is_active = 1"
        ),
        'payments' => dashboard_count(
            $pdo,
            "SELECT COUNT(*) FROM payments WHERE status = 'succeeded'"
        ),
    ];
} catch (Throwable $e) {
    error_log('Dashboard statistics error: ' . $e->getMessage());
    http_response_code(500);
    exit('The dashboard could not load. Please check the database configuration.');
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard | Verve Creative Studio</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0B0A09;
            --sidebar: #100F0D;
            --panel: #141210;
            --line: #2A2620;
            --gold: #C9A24B;
            --gold-light: #E8C873;
            --cream: #F2EDE4;
            --muted: #A8A29A;
            --green: #8CB99A;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--cream);
            font-family: 'Jost', sans-serif;
        }

        .layout {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 245px minmax(0, 1fr);
        }

        aside {
            padding: 30px 20px;
            background: var(--sidebar);
            border-right: 1px solid var(--line);
        }

        .brand {
            padding: 5px 10px 30px;
            border-bottom: 1px solid var(--line);
        }

        .brand-name {
            margin: 0;
            color: var(--gold-light);
            font-family: 'Cormorant Garamond', serif;
            font-size: 30px;
            letter-spacing: 4px;
        }

        .brand-subtitle {
            margin-top: 2px;
            color: var(--muted);
            font-size: 9px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
        }

        .nav-label {
            margin: 30px 10px 12px;
            color: #777067;
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 13px;
            margin-bottom: 5px;
            border-radius: 3px;
            color: var(--muted);
            font-size: 13px;
        }

        .nav-item.active {
            border-left: 2px solid var(--gold);
            background: rgba(201,162,75,.09);
            color: var(--gold-light);
        }

        .nav-item .symbol {
            width: 18px;
            color: var(--gold);
            text-align: center;
        }

        .sidebar-note {
            margin: 35px 10px 0;
            color: #777067;
            font-size: 11px;
            line-height: 1.7;
        }

        main {
            min-width: 0;
            padding: 30px 38px 50px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding-bottom: 23px;
            border-bottom: 1px solid var(--line);
        }

        .topbar-label {
            color: var(--muted);
            font-size: 12px;
        }

        .account {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .avatar {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border: 1px solid var(--gold);
            border-radius: 50%;
            color: var(--gold-light);
            font-family: 'Cormorant Garamond', serif;
            font-size: 19px;
        }

        .account-name {
            font-size: 12px;
            font-weight: 500;
        }

        .account-role {
            margin-top: 2px;
            color: var(--muted);
            font-size: 10px;
        }

        .logout {
            margin-left: 8px;
            color: var(--gold);
            font-size: 12px;
            text-decoration: none;
        }

        .page-heading {
            margin: 35px 0 28px;
        }

        .eyebrow {
            margin: 0 0 8px;
            color: var(--gold);
            font-size: 10px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-family: 'Cormorant Garamond', serif;
            font-size: 38px;
            font-weight: 500;
        }

        .intro {
            margin: 7px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .section-heading {
            margin: 34px 0 16px;
            font-family: 'Cormorant Garamond', serif;
            font-size: 23px;
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .stat-card {
            min-height: 130px;
            padding: 21px;
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 4px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 11px;
        }

        .stat-value {
            margin-top: 12px;
            color: var(--cream);
            font-family: 'Cormorant Garamond', serif;
            font-size: 36px;
            line-height: 1;
        }

        .stat-detail {
            margin-top: 10px;
            color: var(--gold);
            font-size: 10px;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .module-card {
            padding: 20px;
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 4px;
        }

        .module-card h3 {
            margin: 0 0 7px;
            color: var(--cream);
            font-size: 13px;
            font-weight: 500;
        }

        .module-card p {
            margin: 0;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.6;
        }

        .module-status {
            display: inline-block;
            margin-top: 14px;
            color: var(--gold);
            font-size: 10px;
            letter-spacing: .5px;
        }

        .system-note {
            margin-top: 28px;
            padding: 17px 20px;
            border-left: 2px solid var(--gold);
            background: rgba(201,162,75,.06);
            color: var(--muted);
            font-size: 12px;
            line-height: 1.7;
        }

        .system-note strong {
            color: var(--cream);
            font-weight: 500;
        }

        @media (max-width: 1100px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .modules-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 700px) {
            .layout { grid-template-columns: 1fr; }
            aside { padding: 18px 20px; }
            .brand { padding-bottom: 15px; }
            .nav-label, .nav-item, .sidebar-note { display: none; }
            main { padding: 23px 18px 40px; }
            .topbar { align-items: flex-start; }
            .account { flex-wrap: wrap; justify-content: flex-end; }
            h1 { font-size: 32px; }
            .stats-grid, .modules-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
            .stat-card { padding: 16px; }
        }

        @media (max-width: 390px) {
            .stats-grid, .modules-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="layout">

    <aside>
        <div class="brand">
            <p class="brand-name">VERVE</p>
            <div class="brand-subtitle">Creative Studio</div>
        </div>

        <div class="nav-label">Workspace</div>

        <div class="nav-item active">
            <span class="symbol">◆</span>
            Dashboard
        </div>

        <div class="nav-label">Management modules</div>

        <div class="nav-item"><span class="symbol">▣</span> Bookings</div>
        <div class="nav-item"><span class="symbol">◇</span> Project enquiries</div>
        <div class="nav-item"><span class="symbol">▧</span> Portfolio</div>
        <div class="nav-item"><span class="symbol">▦</span> Gallery</div>
        <div class="nav-item"><span class="symbol">▤</span> Media library</div>
        <div class="nav-item"><span class="symbol">£</span> Payments</div>
        <div class="nav-item"><span class="symbol">⚙</span> Settings</div>

        <p class="sidebar-note">
            Verve Creative Studio<br>
            Administration workspace
        </p>
    </aside>

    <main>
        <header class="topbar">
            <div class="topbar-label">Studio administration</div>

            <div class="account">
                <div class="avatar">
                    <?= h(strtoupper(substr($admin['name'], 0, 1))) ?>
                </div>

                <div>
                    <div class="account-name"><?= h($admin['name']) ?></div>
                    <div class="account-role"><?= h($admin['role_name']) ?></div>
                </div>

                <a class="logout"
                   href="<?= h(SITE_URL) ?>/admin/logout.php">Sign out</a>
            </div>
        </header>

        <section class="page-heading">
            <p class="eyebrow">Overview</p>
            <h1>Welcome, <?= h($admin['name']) ?></h1>
            <p class="intro">
                Here's an overview of your studio's activity and management workspace.
            </p>
        </section>

        <h2 class="section-heading">Studio overview</h2>

        <section class="stats-grid">

            <div class="stat-card">
                <div class="stat-label">Total bookings</div>
                <div class="stat-value"><?= number_format($stats['bookings']) ?></div>
                <div class="stat-detail">
                    <?= number_format($stats['pending_bookings']) ?> awaiting payment
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Project enquiries</div>
                <div class="stat-value"><?= number_format($stats['enquiries']) ?></div>
                <div class="stat-detail">
                    <?= number_format($stats['new_enquiries']) ?> new enquiries
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Published portfolio projects</div>
                <div class="stat-value"><?= number_format($stats['portfolio']) ?></div>
                <div class="stat-detail">Published projects</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Published gallery items</div>
                <div class="stat-value"><?= number_format($stats['gallery']) ?></div>
                <div class="stat-detail">Visible gallery entries</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Media library</div>
                <div class="stat-value"><?= number_format($stats['media']) ?></div>
                <div class="stat-detail">Active media assets</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Successful payments</div>
                <div class="stat-value"><?= number_format($stats['payments']) ?></div>
                <div class="stat-detail">Verified successful records</div>
            </div>

        </section>

        <h2 class="section-heading">Management workspace</h2>

        <section class="modules-grid">

            <div class="module-card">
                <h3>Booking management</h3>
                <p>Review wedding and birthday bookings, update statuses and manage customer details.</p>
                <span class="module-status">Module to be implemented</span>
            </div>

            <div class="module-card">
                <h3>Project enquiries</h3>
                <p>Review project briefs, track enquiries and manage client communication.</p>
                <span class="module-status">Module to be implemented</span>
            </div>

            <div class="module-card">
                <h3>Portfolio management</h3>
                <p>Create and manage projects displayed on the public website.</p>
                <span class="module-status">Module to be implemented</span>
            </div>

            <div class="module-card">
                <h3>Gallery management</h3>
                <p>Organise and publish approved images in the studio gallery.</p>
                <span class="module-status">Module to be implemented</span>
            </div>

            <div class="module-card">
                <h3>Media library</h3>
                <p>Upload, organise and manage the studio's photographs and other media assets.</p>
                <span class="module-status">Module to be implemented</span>
            </div>

            <div class="module-card">
                <h3>Payments and records</h3>
                <p>Review payment records and monitor booking payment statuses.</p>
                <span class="module-status">Module to be implemented</span>
            </div>

        </section>

        <div class="system-note">
            <strong>Administration workspace:</strong>
            You are signed in successfully. The overview reads its figures directly
            from the studio database. The individual management modules will be
            connected as they are implemented.
        </div>

    </main>
</div>

</body>
</html>
