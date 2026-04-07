<?php
declare(strict_types=1);

use App\Support\View;

$app = require __DIR__ . '/../src/bootstrap.php';

View::render('public/home', [
    'title' => $app['config']['app_name'],
    'metaDescription' => 'Professional profile for Tony Smith with a gated recruiter-facing portal.',
    'bodyClass' => 'page',
]);

return;
?>

<?php
require __DIR__ . '/../src/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tony Smith | Recruiter Portal</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="page">
<header class="hero">
    <div class="hero__content">
        <p class="eyebrow">Tony Smith — Talent Partner</p>
        <h1>High-signal recruiting for lean teams.</h1>
        <p class="lede">Get a concise, transparent view of talent pipelines, with fast feedback loops and sane process. Pending users receive demo access; approvals unlock deeper insights.</p>
        <div class="actions">
            <a class="btn primary" href="/signup.php">Request access</a>
            <a class="btn ghost" href="/login.php">Log in</a>
        </div>
    </div>
    </header>
    <section class="grid">
        <div class="card">
            <h3>Why</h3>
            <p>Purpose-built for recruiters and hiring managers who need clarity over noise.</p>
        </div>
        <div class="card">
            <h3>How</h3>
            <p>Lightweight portal with secure magic links, simple chat, and an approval workflow.</p>
        </div>
        <div class="card">
            <h3>What</h3>
            <p>Share role context, I respond with signals and sourcing angles. Full automation comes after approval.</p>
        </div>
    </section>
    <footer class="footer">
        <p>Demo access is rate-limited; request approval for full access.</p>
    </footer>
</body>
</html>
