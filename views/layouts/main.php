<?php
use App\Support\Util;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Util::e($title ?? 'Tony Smith') ?></title>
    <meta name="description" content="<?= Util::e($metaDescription ?? 'Professional profile and gated recruiter portal for Tony Smith.') ?>">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <?php if (!empty($turnstileEnabled)): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
    <?php if (!empty($headScripts)): ?>
        <?php foreach ($headScripts as $script): ?>
            <script<?= !empty($script['defer']) ? ' defer' : '' ?> src="<?= Util::e($script['src']) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?= Util::e($bodyClass ?? 'page') ?>">
<?= $content ?>
</body>
</html>

