<?php
session_start();
require_once __DIR__ . '/../config.php';

// Eğer zaten login ise dashboard'a
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basit sabit kullanıcı (ileride DB'den de çekebiliriz)
    $validEmail = 'admin@smaaky.nl';
    $validPassword = '123456';

    if ($email === $validEmail && $password === $validPassword) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $email;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Ongeldige inloggegevens';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Smaaky Admin – Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center text-slate-100">

<div class="w-full max-w-md px-6">
    <div class="bg-slate-900/60 border border-slate-700 rounded-2xl shadow-2xl p-8 backdrop-blur">
        <div class="flex items-center gap-3 mb-6">
            <div class="h-10 w-10 rounded-full bg-white text-slate-900 flex items-center justify-center font-black text-xs">
                SM
            </div>
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Admin</div>
                <div class="font-black text-2xl tracking-tight">Smaaky</div>
            </div>
        </div>

        <h1 class="text-xl font-semibold mb-1">Inloggen</h1>
        <p class="text-sm text-slate-400 mb-5">Beheer bestellingen en producten.</p>

        <?php if ($error): ?>
            <div class="mb-4 rounded-xl border border-red-500/40 bg-red-500/10 text-sm text-red-200 px-4 py-3">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold mb-1" for="email">E-mail</label>
                <input id="email" name="email" type="email" required
                       class="w-full rounded-xl border border-slate-600 bg-slate-900/60 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1" for="password">Wachtwoord</label>
                <input id="password" name="password" type="password" required
                       class="w-full rounded-xl border border-slate-600 bg-slate-900/60 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <button type="submit"
                    class="w-full mt-2 bg-orange-500 hover:bg-orange-600 text-slate-900 font-bold py-2.5 rounded-xl text-sm transition">
                Inloggen
            </button>
        </form>
    </div>
</div>

</body>
</html>
