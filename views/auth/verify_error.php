<?php
use App\Support\Util;
?>
<main class="form-page">
    <div class="notice error">
        <h1>Link unavailable</h1>
        <p><?= Util::e($message ?? 'This login link is invalid or expired.') ?></p>
        <p><a class="btn ghost" href="/login.php">Request a new link</a></p>
    </div>
</main>

