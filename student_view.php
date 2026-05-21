<?php
require_once 'config.php';
requireRole('student');

$user = getCurrentUser($pdo);
$page = $_GET['page'] ?? 'home';

// Handle submission status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id']) && isset($_POST['status'])) {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];
    
    // Check if submission exists
    $stmt = $pdo->prepare('SELECT id FROM submissions WHERE task_id = ? AND student_id = ?');
    $stmt->execute([$task_id, $_SESSION['user_id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing
        $stmt = $pdo->prepare('UPDATE submissions SET status = ? WHERE task_id = ? AND student_id = ?');
        $stmt->execute([$status, $task_id, $_SESSION['user_id']]);
    } else {
        // Create new
        $stmt = $pdo->prepare('INSERT INTO submissions (task_id, student_id, status) VALUES (?, ?, ?)');
        $stmt->execute([$task_id, $_SESSION['user_id'], $status]);
    }
    
    header('Location: student_view.php?page=detail_task&id=' . $task_id . '&success=1');
    exit;
}

// Get student's class
$student = getCurrentUser($pdo);
$class_id = $student['class_id'];

// Get all tasks for student's class
$stmt = $pdo->prepare('SELECT t.*, c.class_name FROM tasks t JOIN classes c ON t.class_id = c.id WHERE t.class_id = ? ORDER BY t.created_at DESC');
$stmt->execute([$class_id]);
$tasks = $stmt->fetchAll();

// Get task detail if viewing specific task
$task = null;
$studentSubmission = null;
if (in_array($page, ['detail_task'])) {
    $task_id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare('SELECT t.*, c.class_name FROM tasks t JOIN classes c ON t.class_id = c.id WHERE t.id = ? AND t.class_id = ?');
    $stmt->execute([$task_id, $class_id]);
    $task = $stmt->fetch();

    if ($task) {
        // Get current student's submission status
        $stmt = $pdo->prepare('SELECT * FROM submissions WHERE task_id = ? AND student_id = ?');
        $stmt->execute([$task_id, $_SESSION['user_id']]);
        $studentSubmission = $stmt->fetch();
        
        if (!$studentSubmission) {
            // Create empty submission record
            $studentSubmission = ['status' => 'belum_mengerjakan'];
        }
    }
}

// Get task stats
function getTaskStats($pdo, $class_id, $student_id) {
    $stmt = $pdo->prepare('
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status != "selesai" THEN 1 ELSE 0 END) as belum_selesai,
            SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as selesai
        FROM tasks t
        LEFT JOIN submissions s ON t.id = s.task_id AND s.student_id = ?
        WHERE t.class_id = ?
    ');
    $stmt->execute([$student_id, $class_id]);
    return $stmt->fetch();
}

$taskStats = getTaskStats($pdo, $class_id, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - SMKN 1 Garut</title>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .badge-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>
<body>
    <div class="flex justify-center min-h-screen pb-24">
        <div class="w-full max-w-md bg-white relative rounded-t-3xl">
            <!-- Android Status Bar Simulation -->
            <div class="bg-gradient-to-r from-purple-900 via-purple-800 to-purple-900 text-white px-4 py-2 flex justify-between items-center text-xs rounded-t-3xl">
                <span id="clock">09:30</span>
                <div class="flex gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M17.778 8.222c-4.296-4.296-11.26-4.296-15.556 0A1 1 0 01.808 6.808c5.076-5.077 13.308-5.077 18.384 0a1 1 0 01-1.414 1.414zM14.95 11.05a7 7 0 00-9.9 0 1 1 0 01-1.414-1.414 9 9 0 0112.728 0 1 1 0 01-1.414 1.414zM12.12 13.88a3 3 0 00-4.242 0 1 1 0 01-1.415-1.415 5 5 0 017.072 0 1 1 0 01-1.415 1.415zM9 16a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z"/></svg>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/><path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/></svg>
                </div>
            </div>

            <!-- Main Content -->
            <div class="px-4 pt-4">
                <?php if ($page === 'home'): ?>
                    <!-- HOME VIEW -->
                    <div class="space-y-4">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-6 bg-gradient-to-br from-green-100 to-emerald-100 rounded-3xl p-5 shadow-lg">
                            <div class="flex items-start gap-3">
                                <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-600">👋 Halo</p>
                                    <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                    <p class="text-xs text-green-700 font-semibold mt-1">📚 Kelas <?php 
                                        // Get class name
                                        $stmt = $pdo->prepare('SELECT class_name FROM classes WHERE id = ?');
                                        $stmt->execute([$class_id]);
                                        $cls = $stmt->fetch();
                                        echo htmlspecialchars($cls['class_name'] ?? '');
                                    ?> - SMKN 1 GARUT</p>
                                </div>
                            </div>
                            <div class="relative">
                                <button class="relative w-11 h-11 bg-white rounded-xl flex items-center justify-center hover:bg-gray-100 transition shadow-md">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-4 text-white shadow-xl card-hover relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-8 -mt-8"></div>
                                <p class="text-xs font-semibold mb-2 opacity-90">📝 Belum Selesai</p>
                                <p class="text-3xl font-bold relative z-10"><?php echo $taskStats['belum_selesai'] ?? 0; ?></p>
                            </div>
                            <div class="bg-gradient-to-br from-emerald-500 to-teal-700 rounded-2xl p-4 text-white shadow-xl card-hover relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-8 -mt-8"></div>
                                <p class="text-xs font-semibold mb-2 opacity-90">✓ Sudah Dikumpul</p>
                                <p class="text-3xl font-bold relative z-10"><?php echo $taskStats['selesai'] ?? 0; ?></p>
                            </div>
                        </div>

                        <!-- Filter -->
                        <div class="bg-white rounded-2xl p-3 border border-gray-200">
                            <select class="w-full bg-transparent text-sm font-medium text-gray-900 focus:outline-none">
                                <option>Semua Status Tugas ▾</option>
                                <option>Belum Selesai</option>
                                <option>Sedang Mengerjakan</option>
                                <option>Selesai</option>
                            </select>
                        </div>

                        <!-- Task List -->
                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-900 mb-2">Daftar Tugas</p>
                            <?php foreach ($tasks as $t):
                                // Get student's submission status for this task
                                $stmt = $pdo->prepare('SELECT status FROM submissions WHERE task_id = ? AND student_id = ?');
                                $stmt->execute([$t['id'], $_SESSION['user_id']]);
                                $subStatus = $stmt->fetch();
                                $status = $subStatus['status'] ?? 'belum_mengerjakan';
                                
                                $statusClass = match($status) {
                                    'selesai' => ['bg-green-100', 'text-green-700', '✓ Selesai'],
                                    'sedang_mengerjakan' => ['bg-yellow-100', 'text-yellow-700', '⏱ Sedang Mengerjakan'],
                                    'telat' => ['bg-red-100', 'text-red-700', '⚠ Telat'],
                                    default => ['bg-gray-100', 'text-gray-700', '○ Belum Mengerjakan']
                                };
                            ?>
                            <a href="student_view.php?page=detail_task&id=<?php echo $t['id']; ?>" class="block bg-white rounded-2xl p-4 hover:shadow-md transition border border-gray-100">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-grow">
                                        <h3 class="font-bold text-gray-900 text-sm"><?php echo htmlspecialchars($t['title']); ?></h3>
                                        <p class="text-xs text-gray-500 mt-1">Kelas <?php echo htmlspecialchars($t['class_name']); ?></p>
                                    </div>
                                    <?php if ($t['is_urgent']): ?>
                                    <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full ml-2">🔥</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-gray-500 mb-3"><?php echo htmlspecialchars($t['deadline_text']); ?></p>
                                <div class="flex items-center gap-2">
                                    <span class="<?php echo $statusClass[0]; ?> <?php echo $statusClass[1]; ?> text-xs font-semibold px-3 py-1 rounded-full">
                                        <?php echo $statusClass[2]; ?>
                                    </span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                <?php elseif ($page === 'detail_task' && $task): ?>
                    <!-- TASK DETAIL VIEW -->
                    <div class="space-y-4">
                        <!-- App Bar -->
                        <div class="flex items-center justify-between mb-4">
                            <a href="student_view.php?page=home" class="text-blue-500 text-2xl">←</a>
                            <h1 class="text-lg font-bold text-gray-900">Detail Tugas</h1>
                            <button class="text-gray-400 text-2xl">⋯</button>
                        </div>

                        <?php if (isset($_GET['success'])): ?>
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
                            ✓ Progress tugas berhasil disimpan!
                        </div>
                        <?php endif; ?>

                        <!-- Task Header -->
                        <div class="bg-blue-50 rounded-2xl p-4 border border-blue-200">
                            <h2 class="font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($task['title']); ?></h2>
                            <div class="space-y-1 text-xs text-gray-600">
                                <p>Deadline: <span class="font-semibold"><?php echo htmlspecialchars($task['deadline_text']); ?></span></p>
                                <p>Jam Ke-: <span class="font-semibold"><?php echo $task['lesson_hour']; ?></span></p>
                            </div>
                            <?php if ($task['is_urgent']): ?>
                            <div class="mt-3 text-sm font-semibold text-red-600">🔥 URGENT</div>
                            <?php endif; ?>
                        </div>

                        <!-- Instructions -->
                        <div class="bg-gray-100 rounded-2xl p-4">
                            <p class="text-xs font-semibold text-gray-600 mb-2">📝 INSTRUKSI</p>
                            <p class="text-sm text-gray-700"><?php echo htmlspecialchars($task['description']); ?></p>
                        </div>

                        <!-- Status Updater Section -->
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            
                            <div>
                                <p class="text-sm font-semibold text-gray-900 mb-3">Status Saya</p>
                                <div class="space-y-2">
                                    <!-- Belum Mengerjakan -->
                                    <label class="flex items-center p-4 bg-white rounded-2xl border-2 cursor-pointer transition <?php echo ($studentSubmission['status'] ?? 'belum_mengerjakan') === 'belum_mengerjakan' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'; ?>">
                                        <input type="radio" name="status" value="belum_mengerjakan" class="w-4 h-4" <?php echo ($studentSubmission['status'] ?? 'belum_mengerjakan') === 'belum_mengerjakan' ? 'checked' : ''; ?>>
                                        <div class="ml-3">
                                            <p class="font-semibold text-gray-900">Belum Mengerjakan</p>
                                            <p class="text-xs text-gray-500">Saya belum memulai tugas ini</p>
                                        </div>
                                    </label>

                                    <!-- Sedang Mengerjakan -->
                                    <label class="flex items-center p-4 bg-white rounded-2xl border-2 cursor-pointer transition <?php echo ($studentSubmission['status'] ?? 'belum_mengerjakan') === 'sedang_mengerjakan' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200'; ?>">
                                        <input type="radio" name="status" value="sedang_mengerjakan" class="w-4 h-4" <?php echo ($studentSubmission['status'] ?? 'belum_mengerjakan') === 'sedang_mengerjakan' ? 'checked' : ''; ?>>
                                        <div class="ml-3">
                                            <p class="font-semibold text-gray-900">Sedang Mengerjakan</p>
                                            <p class="text-xs text-gray-500">Saya masih mengerjakan tugas</p>
                                        </div>
                                    </label>

                                    <!-- Selesai -->
                                    <label class="flex items-center p-4 bg-white rounded-2xl border-2 cursor-pointer transition <?php echo ($studentSubmission['status'] ?? 'belum_mengerjakan') === 'selesai' ? 'border-green-500 bg-green-50' : 'border-gray-200'; ?>">
                                        <input type="radio" name="status" value="selesai" class="w-4 h-4" <?php echo ($studentSubmission['status'] ?? 'belum_mengerjakan') === 'selesai' ? 'checked' : ''; ?>>
                                        <div class="ml-3">
                                            <p class="font-semibold text-gray-900">Selesai</p>
                                            <p class="text-xs text-gray-500">Saya sudah selesai mengerjakan</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Save Button -->
                            <button 
                                type="submit"
                                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-4 rounded-2xl active:scale-95 transition-all"
                            >
                                Simpan Progres Tugas
                            </button>
                        </form>
                    </div>

                <?php elseif ($page === 'profil'): ?>
                    <!-- PROFILE VIEW -->
                    <div class="space-y-4">
                        <h1 class="text-xl font-bold text-gray-900 mb-4">Profil Saya</h1>

                        <div class="bg-white rounded-2xl p-6 text-center">
                            <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-bold text-3xl mx-auto mb-4">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                            <p class="text-gray-600 mt-2">Username: <span class="font-mono text-sm"><?php echo htmlspecialchars($user['username']); ?></span></p>
                        </div>

                        <!-- Logout Button -->
                        <a href="logout.php" class="block w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-3 rounded-2xl text-center active:scale-95 transition-all">
                            Logout
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Bottom Navigation -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 max-w-md mx-auto flex justify-around">
                <a href="student_view.php?page=home" class="flex-1 flex flex-col items-center justify-center py-3 <?php echo $page === 'home' ? 'text-blue-500' : 'text-gray-600'; ?> hover:bg-gray-50 transition">
                    <svg class="w-6 h-6 mb-1" fill="<?php echo $page === 'home' ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span class="text-xs">Tugas</span>
                </a>
                <a href="student_view.php?page=profil" class="flex-1 flex flex-col items-center justify-center py-3 <?php echo $page === 'profil' ? 'text-blue-500' : 'text-gray-600'; ?> hover:bg-gray-50 transition">
                    <svg class="w-6 h-6 mb-1" fill="<?php echo $page === 'profil' ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="text-xs">Profil</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Update clock
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('clock').textContent = hours + ':' + minutes;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</body>
</html>
