<?php
session_start();
require_once "../config.php"; // DB bağlantısı

// Varsayılan admin bilgilerimiz
$ADMIN_EMAIL = "admin@smaaky.nl";
$ADMIN_PASS = "123456";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if ($email === $ADMIN_EMAIL && $password === $ADMIN_PASS) {
        $_SESSION["admin_logged_in"] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Onjuiste gegevens. Probeer opnieuw.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Smaaky Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white shadow-xl rounded-2xl p-10 w-full max-w-md">
        <h1 class="text-3xl font-extrabold text-center mb-8 text-gray-800">
            🔐 Smaaky Admin
        </h1>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-600 p-3 mb-4 rounded-lg text-center font-semibold">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="font-semibold text-gray-700">E-mail</label>
                <input type="email" name="email" required
                       class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-black outline-none">
            </div>

            <div>
                <label class="font-semibold text-gray-700">Wachtwoord</label>
                <input type="password" name="password" required
                       class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-black outline-none">
            </div>

            <button type="submit"
                    class="w-full bg-black text-white p-3 rounded-lg font-bold text-lg hover:bg-gray-800 transition">
                Inloggen
            </button>
        </form>
    </div>

</body>
</html>