<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

// Login kontrolü (login.php hariç her yerde kullanacağız)
$currentFile = basename($_SERVER['PHP_SELF']);
$publicPages = ['login.php'];

if (!in_array($currentFile, $publicPages)) {
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: login.php');
        exit;
    }
}

// Sayfa başlığı ve aktif menü
$pageTitle = $pageTitle ?? 'Smaaky Admin';
$activeMenu = $activeMenu ?? '';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?> – Smaaky Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900">

<div class="min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-50 flex flex-col">
        <div class="px-6 py-5 border-b border-slate-800 flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-white text-slate-900 flex items-center justify-center font-black text-xs">
                SM
            </div>
            <div>
                <div class="text-sm uppercase tracking-[0.2em] text-slate-400">Admin</div>
                <div class="font-black text-xl tracking-tight">Smaaky</div>
            </div>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 text-sm font-medium">
            <a href="dashboard.php"
               class="flex items-center gap-3 px-3 py-2 rounded-lg
                      <?= $activeMenu === 'dashboard' ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800/60' ?>">
                <span class="text-lg">📊</span> <span>Dashboard</span>
            </a>

            <a href="orders.php"
               class="flex items-center gap-3 px-3 py-2 rounded-lg
                      <?= $activeMenu === 'orders' ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800/60' ?>">
                <span class="text-lg">🧾</span> <span>Bestellingen</span>
            </a>

            <a href="products.php"
               class="flex items-center gap-3 px-3 py-2 rounded-lg
                      <?= $activeMenu === 'products' ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800/60' ?>">
                <span class="text-lg">🍔</span> <span>Producten</span>
            </a>

            <a href="categories.php"
               class="flex items-center gap-3 px-3 py-2 rounded-lg
                      <?= $activeMenu === 'categories' ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800/60' ?>">
                <span class="text-lg">🗂</span> <span>Categorieën</span>
            </a>

            <a href="extras.php"
               class="flex items-center gap-3 px-3 py-2 rounded-lg
                      <?= $activeMenu === 'extras' ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800/60' ?>">
                <span class="text-lg">➕</span> <span>Extras (Toppings)</span>
            </a>
        </nav>

        <div class="px-4 py-4 border-t border-slate-800">
            <a href="logout.php"
               class="flex items-center justify-between px-3 py-2 rounded-lg bg-red-500/10 text-red-300 hover:bg-red-500/20 text-sm font-semibold">
                <span>Uitloggen</span>
                <span>⟶</span>
            </a>
        </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 flex flex-col">
        <header class="px-8 py-5 border-b bg-white border-slate-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight"><?= htmlspecialchars($pageTitle) ?></h1>
                <?php if (!empty($pageSubtitle)): ?>
                    <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($pageSubtitle) ?></p>
                <?php endif; ?>
            </div>
            <div class="text-xs text-slate-500">
                Ingelogd als <span class="font-semibold"><?= htmlspecialchars($_SESSION['admin_user'] ?? 'admin') ?></span>
            </div>
        </header>

        <div class="flex-1 p-6 md:p-8 space-y-6">
