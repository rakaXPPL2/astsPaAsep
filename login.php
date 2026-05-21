<?php
require_once 'config.php';

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'teacher') {
        header('Location: dashboard.php');
    } else {
        header('Location: student_view.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Direct password comparison (plain text - untuk kemudahan testing)
        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['class_id'] = $user['class_id'];

            if ($user['role'] === 'teacher') {
                header('Location: dashboard.php');
            } else {
                header('Location: student_view.php');
            }
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Mohon isi semua field!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard Guru & Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-3xl shadow-lg p-8">
                <!-- Logo/Header -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full mb-4">
                        <span class="text-white text-2xl font-bold">📚</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-sm text-gray-500 mt-2">SMKN 1 Garut</p>
                </div>

                <!-- Login Form -->
                <form method="POST" class="space-y-4">
                    <!-- Error Message -->
                    <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Username Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input 
                            type="text" 
                            name="username" 
                            placeholder="Masukkan username Anda"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition"
                            required
                        >
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input 
                            type="password" 
                            name="password" 
                            placeholder="Masukkan password Anda"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition"
                            required
                        >
                    </div>

                    <!-- Login Button -->
                    <button 
                        type="submit"
                        class="w-full bg-gradient-to-r from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white font-semibold py-3 rounded-xl transition-all active:scale-95 mt-6"
                    >
                        Masuk
                    </button>
                </form>

                <!-- Test Credentials -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-600 font-medium mb-3">📋 Akun Test:</p>
                    <div class="space-y-2 text-xs">
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <p class="font-semibold text-gray-900">Guru:</p>
                            <p class="text-gray-600">Username: <span class="font-mono">guru001</span></p>
                            <p class="text-gray-600">Password: <span class="font-mono">password123</span></p>
                        </div>
                        <div class="bg-green-50 p-3 rounded-lg">
                            <p class="font-semibold text-gray-900">Siswa:</p>
                            <p class="text-gray-600">Username: <span class="font-mono">siswa001</span></p>
                            <p class="text-gray-600">Password: <span class="font-mono">password123</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
