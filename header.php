<?php
// ============================================================
//  header.php – Intestazione e navigazione globale
// ============================================================
$current = basename($_SERVER['PHP_SELF'], '.php');
$nav = [
    'index'     => ['icon' => '⬡', 'label' => 'Dashboard'],
    'centri'    => ['icon' => '◈', 'label' => 'Centri'],
    'corsi'     => ['icon' => '◉', 'label' => 'Corsi'],
    'iscritti'  => ['icon' => '◎', 'label' => 'Iscritti'],
    'iscrizioni'=> ['icon' => '◆', 'label' => 'Iscrizioni'],
    'accessi'   => ['icon' => '▸', 'label' => 'Accessi'],
    'analisi'   => ['icon' => '◐', 'label' => 'Analisi'],
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Gestione Centro Sportivo') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="sidebar">
  <div class="sidebar-brand">
    <span class="brand-icon">⬡</span>
    <div>
      <span class="brand-name">SportHub</span>
      <span class="brand-sub">Gestionale</span>
    </div>
  </div>
  <ul class="nav-list">
    <?php foreach ($nav as $page => $item): ?>
    <li>
      <a href="<?= $page ?>.php" class="nav-link <?= $current === $page ? 'active' : '' ?>">
        <span class="nav-icon"><?= $item['icon'] ?></span>
        <span><?= $item['label'] ?></span>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
  <div class="sidebar-footer">
    <span>© <?= date('Y') ?> SportHub</span>
  </div>
</nav>

<div class="main-wrap">
  <header class="top-bar">
    <div class="top-bar-left">
      <h1 class="page-heading"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
    </div>
    <div class="top-bar-right">
      <span class="date-pill"><?= strftime('%A %d %B %Y') ?? date('d/m/Y') ?></span>
    </div>
  </header>

  <?php
  $flash = getFlash();
  if ($flash):
  ?>
  <div class="alert alert-<?= $flash['type'] ?>">
    <?= htmlspecialchars($flash['msg']) ?>
  </div>
  <?php endif; ?>

  <main class="content">
