#!/usr/bin/env php
<?php
/**
 * Download demo book covers into demo/book-covers/ and images/books/.
 * Run: php deploy/fetch-demo-covers.php
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/book_images.php';

$books = [
    'FIC-1001' => ['isbn' => '9780525559474'],
    'FIC-1002' => ['isbn' => '9780593135204'],
    'FIC-1003' => ['isbn' => '9780385547347'],
    'FIC-1004' => ['isbn' => '9781984880978'],
    'FIC-1005' => ['isbn' => '9780735219090'],
    'NF-2001' => ['isbn' => '9780062316097'],
    'NF-2002' => ['isbn' => '9780735211292'],
    'NF-2003' => ['isbn' => '9780143127741'],
    'NF-2004' => ['isbn' => '9780399590504'],
    'YA-3001' => ['isbn' => '9780439023481'],
    'YA-3002' => ['isbn' => '9780062457799'],
    'YA-3003' => ['isbn' => '9781250071696'],
    'REF-4001' => ['title' => 'Merriam-Webster Collegiate Dictionary'],
    'REF-4002' => ['title' => 'World Almanac 2025'],
    'CHI-5001' => ['isbn' => '9780399226908'],
    'CHI-5002' => ['isbn' => '9780064400558'],
    'CHI-5003' => ['isbn' => '9780810993136'],
    'MYS-6001' => ['isbn' => '9780062676135'],
    'MYS-6002' => ['isbn' => '9781250301697'],
    'BIO-7001' => ['isbn' => '9781524763138'],
    'BIO-7002' => ['isbn' => '9781451648539'],
];

$repoRoot = dirname(__DIR__);
$bundleDir = $repoRoot . '/demo/book-covers';
$liveDir = book_images_dir();

foreach ([$bundleDir, $liveDir] as $dir) {
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        fwrite(STDERR, "Cannot create directory: {$dir}\n");
        exit(1);
    }
}

function http_get(string $url): ?string
{
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 20,
            'header' => "User-Agent: HTORBLS-DemoCoverFetcher/1.0\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false || strlen($body) < 500) {
        return null;
    }
    return $body;
}

function open_library_cover_url(array $meta): ?string
{
    if (!empty($meta['isbn'])) {
        return 'https://covers.openlibrary.org/b/isbn/' . rawurlencode($meta['isbn']) . '-L.jpg';
    }
    if (empty($meta['title'])) {
        return null;
    }
    $searchUrl = 'https://openlibrary.org/search.json?' . http_build_query([
        'title' => $meta['title'],
        'limit' => 1,
    ]);
    $json = http_get($searchUrl);
    if ($json === null) {
        return null;
    }
    $data = json_decode($json, true);
    $coverId = $data['docs'][0]['cover_i'] ?? null;
    if (!$coverId) {
        return null;
    }
    return 'https://covers.openlibrary.org/b/id/' . $coverId . '-L.jpg';
}

function save_cover_jpeg(string $bookId, string $label, string $rawImage, string $destPath): bool
{
    $image = @imagecreatefromstring($rawImage);
    if ($image === false) {
        return save_placeholder_jpeg($bookId, $label, $destPath);
    }

    $width = imagesx($image);
    $height = imagesy($image);
    $targetHeight = 500;
    $targetWidth = (int) max(1, ceil(($width * $targetHeight) / max(1, $height)));
    $resized = imagecreatetruecolor($targetWidth, $targetHeight);
    imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
    $ok = imagejpeg($resized, $destPath, 85);
    imagedestroy($image);
    imagedestroy($resized);
    return $ok;
}

function save_placeholder_jpeg(string $bookId, string $label, string $destPath): bool
{
    $width = 320;
    $height = 480;
    $image = imagecreatetruecolor($width, $height);
    $bg = imagecolorallocate($image, 248, 249, 250);
    $accent = imagecolorallocate($image, 230, 80, 30);
    $textColor = imagecolorallocate($image, 33, 37, 41);
    imagefilledrectangle($image, 0, 0, $width, $height, $bg);
    imagefilledrectangle($image, 0, 0, $width, 8, $accent);

    $lines = array_slice(preg_split('/\s+/', $label, -1, PREG_SPLIT_NO_EMPTY) ?: [$label], 0, 6);
    $y = 180;
    foreach ($lines as $line) {
        imagestring($image, 5, 24, $y, substr($line, 0, 36), $textColor);
        $y += 28;
    }
    imagestring($image, 4, 24, $height - 48, $bookId, $accent);

    $ok = imagejpeg($image, $destPath, 85);
    imagedestroy($image);
    return $ok;
}

$names = [];
$seed = file_get_contents($repoRoot . '/demo/seed.sql');
if ($seed !== false) {
    preg_match_all("/\('([A-Z]+-\d+)',\s*'([^']+)'/", $seed, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $names[$match[1]] = str_replace("''", "'", $match[2]);
    }
}

$failed = [];
foreach ($books as $bookId => $meta) {
    $label = $names[$bookId] ?? $bookId;
    $coverUrl = open_library_cover_url($meta);
    $raw = $coverUrl ? http_get($coverUrl) : null;

    $bundlePath = $bundleDir . '/' . $bookId . '.jpeg';
    $livePath = $liveDir . '/' . $bookId . '.jpeg';

    $saved = false;
    if ($raw !== null) {
        $saved = save_cover_jpeg($bookId, $label, $raw, $bundlePath);
    }
    if (!$saved) {
        $saved = save_placeholder_jpeg($bookId, $label, $bundlePath);
    }
    if (!$saved) {
        $failed[] = $bookId;
        continue;
    }
    copy($bundlePath, $livePath);
    echo "OK {$bookId}\n";
}

if ($failed !== []) {
    fwrite(STDERR, 'Failed: ' . implode(', ', $failed) . "\n");
    exit(1);
}

echo "Covers written to {$bundleDir} and {$liveDir}\n";
