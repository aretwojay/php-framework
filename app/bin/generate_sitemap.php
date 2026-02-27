<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\PostRepository;

$baseUrl = 'https://nextcms-latest.onrender.com';
$date = date('Y-m-d');

$postRepo = new PostRepository();
$posts = $postRepo->findPublished();

$sitemap = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

function addUrl($sitemap, $loc, $lastmod, $priority) {
    $url = $sitemap->addChild('url');
    $url->addChild('loc', $loc);
    $url->addChild('lastmod', $lastmod);
    $url->addChild('priority', $priority);
}

addUrl($sitemap, "$baseUrl/", $date, '1.0');
addUrl($sitemap, "$baseUrl/posts", $date, '0.8');
addUrl($sitemap, "$baseUrl/login", $date, '0.5');
addUrl($sitemap, "$baseUrl/register", $date, '0.5');

foreach ($posts as $post) {
    $id = $post->getId();
    $slug = $post->getSlug() ?? $id;
    $lastmod = $post->getCreatedAt() ? date('Y-m-d', strtotime($post->getCreatedAt())) : $date;
    addUrl($sitemap, "$baseUrl/posts/$id", $lastmod, '0.7');
}

file_put_contents(__DIR__ . '/../public/sitemap.xml', $sitemap->asXML());
echo "Sitemap généré avec succès.\n";

?>