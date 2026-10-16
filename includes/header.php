<?php
/**
 * UIU Career Portal - Shared Page Header
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$app_root = get_app_root();
$page_title = $page_title ?? 'UIU Career Portal | United International University';
$page_desc = $page_desc ?? 'Official career services, internship placement, and employer engagement portal for United International University.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($page_desc) ?>">
  <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>">
  <meta property="og:type" content="website">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $app_root ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= $app_root ?>/src/css/style.css">
</head>
<body>
