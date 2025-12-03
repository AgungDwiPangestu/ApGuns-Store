<?php
// Simple redirect shim so old links keep working
$q = isset($_GET['q']) ? urlencode($_GET['q']) : '';
$category = isset($_GET['category']) ? urlencode($_GET['category']) : '';
$target = '/Munif/pages/books.php';
$params = [];
if ($q !== '') {
    $params[] = 'q=' . $q;
}
if ($category !== '') {
    $params[] = 'category=' . $category;
}
if (!empty($params)) {
    $target .= '?' . implode('&', $params);
}
header('Location: ' . $target);
exit;
