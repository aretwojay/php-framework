<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon CMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if ($this->getCss()): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($this->getCss()) ?>">
    <?php endif; ?>
</head>
<body>

<header>
    <h1>Mon CMS</h1>
</header>

<main>
    <?= $content ?>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> - Mon CMS</p>
</footer>

</body>
</html>
