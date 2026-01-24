<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Admin Dashboard' ?></title>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body class="admin-layout">

<div class="admin-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <h2>CMS Admin</h2>
        <nav>
            <ul>
                <li><a href="/admin">Dashboard</a></li>
                <li><a href="/admin/artists">Artists</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main content -->
    <div class="main">
        <!-- Topbar -->
        <header class="topbar">
            <div>
                👤 <?= htmlspecialchars($user['name'] ?? 'Admin') ?>
            </div>
            <form method="POST" action="/logout">
                <button type="submit">Déconnexion</button>
            </form>
        </header>

        <!-- Page content -->
        <section class="content">
            <?= $content ?>
        </section>
    </div>
</div>

</body>
</html>
