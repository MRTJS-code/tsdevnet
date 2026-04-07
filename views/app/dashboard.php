<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Recruiter Portal</p>
        <h1><?= Util::e($user['name']) ?></h1>
        <p class="help-text">Status: <?= Util::e($user['status']) ?> | Tier: <?= Util::e($tier['label']) ?> | Daily limit: <?= (int) $tier['limit'] ?> messages</p>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/login.php">Admin</a>
        <a class="btn ghost" href="/logout.php">Logout</a>
    </div>
</header>

<main class="portal-shell">
    <section class="card portal-summary">
        <h2>Assistant access</h2>
        <p>This Phase 1 portal keeps the chatbot server-side and rule-based. It demonstrates access control, logging, and future AI seams without exposing public chatbot access.</p>
    </section>

    <section class="chat">
        <div class="chat__window" id="chat-window">
            <div class="message system">Welcome. Ask about delivery scope, systems leadership, governance, or Tony's operating style.</div>
        </div>
        <form id="chat-form" class="chat__form">
            <input type="text" id="chat-input" name="message" placeholder="Ask a recruiter-facing question..." autocomplete="off" required>
            <button type="submit" class="btn primary">Send</button>
        </form>
        <meta name="csrf-token" content="<?= Util::e(Security::csrfToken()) ?>">
    </section>
</main>

