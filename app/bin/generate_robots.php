
<?php

$baseUrl = 'https://nextcms-latest.onrender.com';
$robots = "User-agent: *\nDisallow: /admin\nDisallow: /api\nAllow: /\nSitemap: $baseUrl/sitemap.xml\n";
file_put_contents(__DIR__ . '/../public/robots.txt', $robots);
echo "robots.txt généré avec succès.\n";

?>