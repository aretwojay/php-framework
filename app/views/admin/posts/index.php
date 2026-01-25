<h1>Liste des articles</h1>

<table class="admin-table">
    <thead>
        <tr class="admin-table__header">
            <th class="admin-table__cell">ID</th>
            <th class="admin-table__cell">Titre</th>
            <th class="admin-table__cell">Statut</th>
            <th class="admin-table__cell">Auteur</th>
            <th class="admin-table__cell">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($posts as $post): ?>
            <tr class="admin-table__row">
                <td class="admin-table__cell"><?= htmlspecialchars($post->getId()) ?></td>
                <td class="admin-table__cell"><?= htmlspecialchars($post->getTitle()) ?></td>
                <td class="admin-table__cell">
                    <?= $post->isPublished() ? 'Publié' : 'Brouillon' ?>
                </td>
                <td class="admin-table__cell">
                    <?= htmlspecialchars($post->getAuthor() ?? 'N/A') ?>
                </td>
                <td class="admin-table__cell admin-table__actions">
                    <a href="/admin/posts/edit/<?= $post->getId() ?>">Éditer</a>
                    <a href="/admin/posts/delete/<?= $post->getId() ?>">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
