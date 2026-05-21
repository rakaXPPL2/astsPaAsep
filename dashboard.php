<?php
require_once 'config.php';
requireRole('teacher');

$user = getCurrentUser($pdo);
$page = $_GET['page'] ?? 'home';

// Handle Task Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $page === 'create_task') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $class_id = $_POST['class_id'] ?? '';
    $deadline_text = $_POST['deadline_text'] ?? '';
    $lesson_hour = $_POST['lesson_hour'] ?? '';
    $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;

    if ($title && $description && $class_id && $deadline_text && $lesson_hour) {
        $stmt = $pdo->prepare('INSERT INTO tasks (title, description, class_id, deadline_text, lesson_hour, is_urgent, teacher_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$title, $description, $class_id, $deadline_text, $lesson_hour, $is_urgent, $_SESSION['user_id']])) {
            header('Location: dashboard.php?page=task&success=1');
            exit;
        }
    }
}

// Get all classes
$stmt = $pdo->prepare('SELECT * FROM classes ORDER BY class_name');
$stmt->execute();
$classes = $stmt->fetchAll();

// Get all tasks for this teacher
$stmt = $pdo->prepare('SELECT t.*, c.class_name FROM tasks t JOIN classes c ON t.class_id = c.id WHERE t.teacher_id = ? ORDER BY t.created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();

// Get task detail if viewing specific task
$task = null;
$taskSubmissions = null;
if (in_array($page, ['detail_task', 'submission_list'])) {
    $task_id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare('SELECT t.*, c.class_name FROM tasks t JOIN classes c ON t.class_id = c.id WHERE t.id = ? AND t.teacher_id = ?');
    $stmt->execute([$task_id, $_SESSION['user_id']]);
    $task = $stmt->fetch();

    if ($task) {
        // Get submissions for this task
        $stmt = $pdo->prepare('SELECT u.full_name, s.status, s.updated_at FROM submissions s JOIN users u ON s.student_id = u.id WHERE s.task_id = ? ORDER BY u.full_name');
        $stmt->execute([$task_id]);
        $taskSubmissions = $stmt->fetchAll();
    }
}

// Calculate statistics
$totalTasks = count($tasks);
$totalClasses = count($classes);
$urgentTasks = count(array_filter($tasks, fn($t) => $t['is_urgent']));

// Get recent tasks with submission stats
$recentTasks = array_slice($tasks, 0, 3);

// Count submissions by status for a task
function getSubmissionStats($submissions) {
    $stats = ['selesai' => 0, 'sedang_mengerjakan' => 0, 'telat' => 0, 'belum_mengerjakan' => 0];
    foreach ($submissions as $sub) {
        $status = str_replace('belum_mengerjakan', 'belum_mengerjakan', $sub['status']);
        if (isset($stats[$status])) $stats[$status]++;
    }
    return $stats;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - SMKN 1 Garut</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest"></script>
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
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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
            <div class="px-4 pt-4 pb-4">
                <?php if ($page === 'home'): ?>
                    <!-- HOME VIEW -->
                    <div class="space-y-4">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-6 bg-gradient-to-br from-purple-100 to-pink-100 rounded-3xl p-5 shadow-lg">
                            <div class="flex items-start gap-3">
                                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-600">👋 Selamat Pagi</p>
                                    <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                    <p class="text-xs text-purple-700 font-semibold mt-1">📚 Produktif - SMKN 1 GARUT</p>
                                </div>
                            </div>
                            <div class="relative">
                                <button class="relative w-11 h-11 bg-white rounded-xl flex items-center justify-center hover:bg-gray-100 transition shadow-md">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    <div class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-r from-red-400 to-red-600 rounded-full text-white text-xs flex items-center justify-center font-bold shadow-lg badge-pulse">3</div>
                                </button>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="space-y-3">
                            <!-- Card 1: Tugas Aktif (Full Width) -->
                            <div class="bg-gradient-to-br from-blue-500 via-blue-600 to-purple-700 rounded-3xl p-6 text-white shadow-2xl card-hover relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
                                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-10 rounded-full -ml-12 -mb-12"></div>
                                <div class="flex justify-between items-start relative z-10">
                                    <div>
                                        <p class="text-sm opacity-90 mb-2 font-semibold">📋 Tugas Aktif</p>
                                        <p class="text-4xl font-bold"><?php echo $totalTasks; ?></p>
                                        <p class="text-xs opacity-80 mt-2">/ 1 Deadline Hari Ini</p>
                                    </div>
                                    <div class="text-5xl opacity-20">📚</div>
                                </div>
                            </div>

                            <!-- Grid 2 Columns -->
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Card 2: Sisa Kelas -->
                                <div class="bg-gradient-to-br from-emerald-400 to-teal-600 rounded-2xl p-4 text-white shadow-xl card-hover relative overflow-hidden">
                                    <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-8 -mt-8"></div>
                                    <p class="text-xs font-semibold mb-2 opacity-90">🏃 Sisa Kelas</p>
                                    <p class="text-3xl font-bold relative z-10"><?php echo $totalClasses; ?></p>
                                    <p class="text-xs opacity-75 mt-1">Kelas Aktif</p>
                                </div>

                                <!-- Card 3: Mendekati Deadline -->
                                <div class="bg-gradient-to-br from-orange-400 to-red-600 rounded-2xl p-4 text-white shadow-xl card-hover relative overflow-hidden">
                                    <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-8 -mt-8"></div>
                                    <p class="text-xs font-semibold mb-2 opacity-90">🔥 Deadline Besok</p>
                                    <p class="text-3xl font-bold relative z-10"><?php echo $urgentTasks; ?></p>
                                    <p class="text-xs opacity-75 mt-1">Tugas Urgent</p>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Tasks -->
                        <div class="mt-8">
                            <p class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">✨ Tugas Terbaru</p>
                            <div class="space-y-3">
                                <?php foreach ($recentTasks as $t):
                                    $taskId = $pdo->prepare('SELECT COUNT(*) as count FROM submissions WHERE task_id = ?');
                                    $taskId->execute([$t['id']]);
                                    $subCount = $taskId->fetch()['count'];
                                ?>
                                <a href="dashboard.php?page=detail_task&id=<?php echo $t['id']; ?>" class="block bg-white rounded-2xl p-4 shadow-lg card-hover border-l-4 border-purple-500">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-grow">
                                            <h3 class="font-bold text-gray-900 text-sm"><?php echo htmlspecialchars($t['title']); ?></h3>
                                            <p class="text-xs text-gray-500 mt-1">📌 Kelas <?php echo htmlspecialchars($t['class_name']); ?></p>
                                        </div>
                                        <?php if ($t['is_urgent']): ?>
                                        <span class="text-xs bg-gradient-to-r from-red-400 to-orange-500 text-white px-3 py-1 rounded-full ml-2 font-semibold shadow-md">🔥 Urgent</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-xs text-gray-600 font-semibold">⏱️ <?php echo htmlspecialchars($t['deadline_text']); ?></p>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Action Button -->
                    <a href="dashboard.php?page=create_task" class="fixed bottom-28 right-4 bg-gradient-to-br from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white rounded-full w-16 h-16 flex items-center justify-center shadow-2xl active:scale-95 transition-all transform hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </a>

                <?php elseif ($page === 'task'): ?>
                    <!-- TASK LIST VIEW -->
                    <div class="space-y-4">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-4">
                            <h1 class="text-xl font-bold text-gray-900">Tugas Siswa</h1>
                            <a href="dashboard.php?page=create_task" class="text-blue-500 hover:text-blue-700 font-medium text-sm">+ Buat</a>
                        </div>

                        <?php if (isset($_GET['success'])): ?>
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
                            ✓ Tugas berhasil dibuat!
                        </div>
                        <?php endif; ?>

                        <!-- Filter Dropdown -->
                        <div class="bg-white rounded-2xl p-3 border border-gray-200 shadow-md card-hover">
                            <select class="w-full bg-transparent text-sm font-medium text-gray-900 focus:outline-none">
                                <option>📚 Semua Kelas ▾</option>
                                <?php foreach ($classes as $c): ?>
                                <option><?php echo htmlspecialchars($c['class_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Task List -->
                        <div class="space-y-3">
                            <p class="text-sm font-bold text-gray-900 mb-3">📋 Daftar Tugas</p>
                            <?php foreach ($tasks as $t): ?>
                            <a href="dashboard.php?page=detail_task&id=<?php echo $t['id']; ?>" class="block bg-white rounded-2xl p-4 shadow-lg card-hover border-l-4 border-blue-500">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-gray-900"><?php echo htmlspecialchars($t['title']); ?></h3>
                                    <?php if ($t['is_urgent']): ?>
                                    <span class="text-xs bg-gradient-to-r from-red-400 to-orange-500 text-white px-3 py-1 rounded-full font-semibold shadow-md">🔥 Urgent</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-gray-600 mb-3 font-semibold">📌 <?php echo htmlspecialchars($t['class_name']); ?> • ⏱️ <?php echo htmlspecialchars($t['deadline_text']); ?></p>
                                <!-- Task Status Badges -->
                                <?php 
                                    $stmt = $pdo->prepare('SELECT status, COUNT(*) as count FROM submissions WHERE task_id = ? GROUP BY status');
                                    $stmt->execute([$t['id']]);
                                    $subStats = [];
                                    $totalSub = 0;
                                    foreach ($stmt->fetchAll() as $row) {
                                        $subStats[$row['status']] = $row['count'];
                                        $totalSub += $row['count'];
                                    }
                                ?>
                                <div class="flex gap-2 text-xs font-semibold flex-wrap">
                                    <?php if (isset($subStats['selesai'])): ?>
                                    <span class="bg-gradient-to-r from-emerald-100 to-teal-100 text-emerald-700 px-3 py-1 rounded-full shadow-sm">✓ <?php echo $subStats['selesai']; ?> Siswa</span>
                                    <?php endif; ?>
                                    <?php if (isset($subStats['sedang_mengerjakan'])): ?>
                                    <span class="bg-gradient-to-r from-amber-100 to-yellow-100 text-amber-700 px-3 py-1 rounded-full shadow-sm">🟡 <?php echo $subStats['sedang_mengerjakan']; ?> Mengerjakan</span>
                                    <?php endif; ?>
                                    <?php if (isset($subStats['telat']) || isset($subStats['belum_mengerjakan'])): ?>
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full">🔴 <?php echo ($subStats['telat'] ?? 0) + ($subStats['belum_mengerjakan'] ?? 0); ?> Telat</span>
                                    <?php endif; ?>
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
                            <a href="dashboard.php?page=task" class="text-blue-500 text-2xl">←</a>
                            <h1 class="text-lg font-bold text-gray-900">Detail Tugas</h1>
                            <button class="text-gray-400 text-2xl">⋯</button>
                        </div>

                        <!-- Priority Card -->
                        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3">
                            <span class="text-2xl">🔥</span>
                            <div>
                                <p class="font-semibold text-red-700">URGENT</p>
                                <p class="text-xs text-red-600"><?php echo htmlspecialchars($task['deadline_text']); ?></p>
                            </div>
                        </div>

                        <!-- Task Info -->
                        <div class="bg-gray-50 rounded-2xl p-4">
                            <h2 class="font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($task['title']); ?></h2>
                            <p class="text-xs text-gray-600">Kelas: <span class="font-semibold"><?php echo htmlspecialchars($task['class_name']); ?></span></p>
                            <p class="text-xs text-gray-600">Jam Ke-: <span class="font-semibold"><?php echo $task['lesson_hour']; ?></span></p>
                        </div>

                        <!-- Instructions -->
                        <div class="bg-gray-100 rounded-2xl p-4">
                            <p class="text-xs font-semibold text-gray-600 mb-2">📝 INSTRUKSI</p>
                            <p class="text-sm text-gray-700"><?php echo htmlspecialchars($task['description']); ?></p>
                        </div>

                        <!-- Status Pengumpulan -->
                        <?php $stats = getSubmissionStats($taskSubmissions ?? []); ?>
                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-900">Status Pengumpulan</p>
                            
                            <div class="bg-white rounded-2xl p-4 flex items-center justify-between border border-gray-100 hover:shadow-md transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Selesai</p>
                                        <p class="text-xs text-gray-500"><?php echo $stats['selesai']; ?> Siswa</p>
                                    </div>
                                </div>
                                <span class="text-gray-400">›</span>
                            </div>

                            <div class="bg-white rounded-2xl p-4 flex items-center justify-between border border-gray-100 hover:shadow-md transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00-.293.707l-.707.707a1 1 0 101.414 1.414L9 9.414V6z"/></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Sedang Mengerjakan</p>
                                        <p class="text-xs text-gray-500"><?php echo $stats['sedang_mengerjakan']; ?> Siswa</p>
                                    </div>
                                </div>
                                <span class="text-gray-400">›</span>
                            </div>

                            <div class="bg-white rounded-2xl p-4 flex items-center justify-between border border-gray-100 hover:shadow-md transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Telat</p>
                                        <p class="text-xs text-gray-500"><?php echo $stats['telat']; ?> Siswa</p>
                                    </div>
                                </div>
                                <span class="text-gray-400">›</span>
                            </div>
                        </div>

                        <!-- Bottom Action Button -->
                        <a href="dashboard.php?page=submission_list&id=<?php echo $task['id']; ?>" class="block w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-4 rounded-2xl text-center mt-6 active:scale-95 transition-all">
                            Lihat Pengumpulan
                        </a>
                    </div>

                <?php elseif ($page === 'submission_list' && $task): ?>
                    <!-- SUBMISSION LIST VIEW -->
                    <div class="space-y-4">
                        <!-- App Bar -->
                        <div class="flex items-center gap-3 mb-4">
                            <a href="dashboard.php?page=detail_task&id=<?php echo $task['id']; ?>" class="text-blue-500 text-2xl">←</a>
                            <div>
                                <h1 class="text-lg font-bold text-gray-900">Daftar Pengumpulan</h1>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($task['class_name']); ?></p>
                            </div>
                        </div>

                        <!-- Status Tabs -->
                        <?php 
                            $stats = getSubmissionStats($taskSubmissions ?? []);
                            $selectedStatus = $_GET['status'] ?? 'semua';
                        ?>
                        <div class="flex gap-2 overflow-x-auto pb-2">
                            <a href="dashboard.php?page=submission_list&id=<?php echo $task['id']; ?>&status=semua" class="px-4 py-2 rounded-full whitespace-nowrap font-medium text-sm <?php echo $selectedStatus === 'semua' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700'; ?>">
                                Semua (<?php echo count($taskSubmissions ?? []); ?>)
                            </a>
                            <a href="dashboard.php?page=submission_list&id=<?php echo $task['id']; ?>&status=selesai" class="px-4 py-2 rounded-full whitespace-nowrap font-medium text-sm <?php echo $selectedStatus === 'selesai' ? 'bg-green-500 text-white' : 'bg-green-100 text-green-700'; ?>">
                                Selesai (<?php echo $stats['selesai']; ?>)
                            </a>
                            <a href="dashboard.php?page=submission_list&id=<?php echo $task['id']; ?>&status=sedang_mengerjakan" class="px-4 py-2 rounded-full whitespace-nowrap font-medium text-sm <?php echo $selectedStatus === 'sedang_mengerjakan' ? 'bg-yellow-500 text-white' : 'bg-yellow-100 text-yellow-700'; ?>">
                                Mengerjakan (<?php echo $stats['sedang_mengerjakan']; ?>)
                            </a>
                            <a href="dashboard.php?page=submission_list&id=<?php echo $task['id']; ?>&status=telat" class="px-4 py-2 rounded-full whitespace-nowrap font-medium text-sm <?php echo $selectedStatus === 'telat' ? 'bg-red-500 text-white' : 'bg-red-100 text-red-700'; ?>">
                                Telat (<?php echo $stats['telat']; ?>)
                            </a>
                        </div>

                        <!-- Student List -->
                        <div class="space-y-2">
                            <?php foreach ($taskSubmissions as $sub):
                                if ($selectedStatus !== 'semua' && $sub['status'] !== $selectedStatus) continue;
                                
                                $statusClass = match($sub['status']) {
                                    'selesai' => ['bg-green-100', 'text-green-700', '✓'],
                                    'sedang_mengerjakan' => ['bg-yellow-100', 'text-yellow-700', '⏱'],
                                    'telat' => ['bg-red-100', 'text-red-700', '⚠'],
                                    default => ['bg-gray-100', 'text-gray-700', '○']
                                };
                            ?>
                            <div class="bg-white rounded-2xl p-4 flex items-center justify-between border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="<?php echo $statusClass[0]; ?> rounded-full w-10 h-10 flex items-center justify-center">
                                        <span class="text-lg"><?php echo $statusClass[2]; ?></span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($sub['full_name']); ?></p>
                                        <p class="text-xs text-gray-500">
                                            <?php 
                                                if ($sub['status'] === 'selesai') {
                                                    echo 'Dikumpulkan: ' . date('d M, H:i', strtotime($sub['updated_at']));
                                                } elseif ($sub['status'] === 'sedang_mengerjakan') {
                                                    echo 'Terakhir aktif: 2 jam yang lalu';
                                                } else {
                                                    echo 'Belum mengumpulkan (Melewati deadline)';
                                                }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                <button class="text-gray-400 hover:text-gray-600 text-xl transition">
                                    🔔
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                <?php elseif ($page === 'create_task'): ?>
                    <!-- CREATE TASK VIEW -->
                    <div class="space-y-4">
                        <!-- App Bar -->
                        <div class="flex items-center justify-between mb-4">
                            <a href="dashboard.php?page=task" class="text-blue-500 text-2xl">←</a>
                            <h1 class="text-lg font-bold text-gray-900">Buat Tugas</h1>
                            <button type="submit" form="createTaskForm" class="text-blue-500 hover:text-blue-700 font-medium">Kirim</button>
                        </div>

                        <!-- Form -->
                        <form id="createTaskForm" method="POST" class="space-y-4">
                            <!-- Title Input -->
                            <div>
                                <label class="text-xs font-semibold text-gray-700 mb-2 block">Judul Tugas</label>
                                <input 
                                    type="text" 
                                    name="title" 
                                    placeholder="Contoh: Tugas UI Dashboard"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                                    required
                                >
                            </div>

                            <!-- Description Input -->
                            <div>
                                <label class="text-xs font-semibold text-gray-700 mb-2 block">Deskripsi Tugas</label>
                                <textarea 
                                    name="description" 
                                    placeholder="Jelaskan instruksi tugas disini"
                                    rows="4"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"
                                    required
                                ></textarea>
                            </div>

                            <!-- Deadline Input -->
                            <div>
                                <label class="text-xs font-semibold text-gray-700 mb-2 block">Deadline</label>
                                <input 
                                    type="text" 
                                    name="deadline_text" 
                                    placeholder="Contoh: Besok, 11:00"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                                    required
                                >
                            </div>

                            <!-- Class & Hour Grid -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-gray-700 mb-2 block">Kelas</label>
                                    <select name="class_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                                        <option value="">Pilih Kelas</option>
                                        <?php foreach ($classes as $c): ?>
                                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['class_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-700 mb-2 block">Jam Ke-</label>
                                    <select name="lesson_hour" class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                                        <option value="">Pilih Jam</option>
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Urgent Checkbox -->
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="is_urgent" id="isUrgent" class="w-4 h-4 text-blue-500 rounded">
                                <label for="isUrgent" class="text-sm text-gray-700">Tandai sebagai Urgent/Penting</label>
                            </div>

                            <!-- Submit Button -->
                            <button 
                                type="submit"
                                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-4 rounded-2xl mt-6 active:scale-95 transition-all"
                            >
                                Kirim Tugas
                            </button>
                        </form>
                    </div>
                <?php elseif ($page === 'statistik'): ?>
                    <!-- STATISTIK VIEW -->
                    <div class="space-y-4">
                        <!-- Header -->
                        <h1 class="text-xl font-bold text-gray-900 mb-4">Statistik Siswa</h1>
                        <p class="text-sm text-gray-600 mb-4">Ringkasan Performa Siswa</p>

                        <!-- Tab Selector -->
                        <div class="flex gap-2 bg-gray-100 rounded-2xl p-1">
                            <button class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-white transition">Daily</button>
                            <button class="px-4 py-2 rounded-xl text-sm font-medium bg-blue-500 text-white">Weekly</button>
                            <button class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-white transition">Monthly</button>
                        </div>

                        <!-- Rata-Rata Card -->
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-4 border border-blue-200">
                            <p class="text-sm font-semibold text-gray-700 mb-2">📚 Produktive - <?php echo htmlspecialchars($user['full_name']); ?></p>
                            <p class="text-xs text-gray-600">Produktive - SMKN 1 GARUT</p>
                            <div class="mt-3 pt-3 border-t border-blue-200">
                                <p class="text-xs font-semibold text-gray-700 mb-2">Rata - Rata</p>
                                <p class="text-lg font-bold text-blue-600">60 dari 114 siswa</p>
                            </div>
                        </div>

                        <!-- Per Class Statistics -->
                        <div class="space-y-3">
                            <p class="text-sm font-semibold text-gray-900">Performa per Kelas</p>
                            
                            <?php 
                                // Get statistics per class
                                foreach ($classes as $cls):
                                    $stmt = $pdo->prepare('
                                        SELECT 
                                            COUNT(DISTINCT s.student_id) as total_students,
                                            SUM(CASE WHEN sub.status = "selesai" THEN 1 ELSE 0 END) as selesai,
                                            SUM(CASE WHEN sub.status = "sedang_mengerjakan" THEN 1 ELSE 0 END) as mengerjakan,
                                            SUM(CASE WHEN sub.status IN ("telat", "belum_mengerjakan") THEN 1 ELSE 0 END) as gagal
                                        FROM users s
                                        LEFT JOIN submissions sub ON s.id = sub.student_id
                                        LEFT JOIN tasks t ON sub.task_id = t.id
                                        WHERE s.class_id = ? AND s.role = "student"
                                    ');
                                    $stmt->execute([$cls['id']]);
                                    $classStats = $stmt->fetch();
                                    
                                    $totalStudents = $classStats['total_students'] ?? 0;
                                    $selesai = $classStats['selesai'] ?? 0;
                                    $mengerjakan = $classStats['mengerjakan'] ?? 0;
                                    $gagal = $classStats['gagal'] ?? 0;
                                    
                                    $percentage = $totalStudents > 0 ? round(($selesai / $totalStudents) * 100) : 0;
                            ?>
                            <div class="bg-white rounded-2xl p-4 border border-gray-200 hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($cls['class_name']); ?></h3>
                                    <span class="text-lg font-bold text-blue-600"><?php echo $percentage; ?>%</span>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden mb-3">
                                    <div class="bg-gradient-to-r from-green-400 to-blue-500 h-full rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                
                                <!-- Stats -->
                                <div class="flex gap-3 text-xs font-semibold">
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full">✓ <?php echo $selesai; ?> Selesai</span>
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">🟡 <?php echo $mengerjakan; ?> Mengerjakan</span>
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full">⚠️ <?php echo $gagal; ?> Gagal</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Bottom Navigation -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t-2 border-purple-100 max-w-md mx-auto flex justify-around shadow-2xl rounded-t-3xl">
                <a href="dashboard.php?page=home" class="flex-1 flex flex-col items-center justify-center py-4 <?php echo $page === 'home' ? 'text-purple-600 bg-purple-50' : 'text-gray-500 hover:text-purple-600'; ?> hover:bg-purple-50 transition font-semibold text-xs">
                    <svg class="w-6 h-6 mb-1" fill="<?php echo $page === 'home' ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4l4 2m-6-2l-4-2"/></svg>
                    <span>Home</span>
                </a>
                <a href="dashboard.php?page=task" class="flex-1 flex flex-col items-center justify-center py-4 <?php echo $page === 'task' ? 'text-purple-600 bg-purple-50' : 'text-gray-500 hover:text-purple-600'; ?> hover:bg-purple-50 transition font-semibold text-xs">
                    <svg class="w-6 h-6 mb-1" fill="<?php echo $page === 'task' ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Tugas</span>
                </a>
                <a href="dashboard.php?page=statistik" class="flex-1 flex flex-col items-center justify-center py-4 <?php echo $page === 'statistik' ? 'text-purple-600 bg-purple-50' : 'text-gray-500 hover:text-purple-600'; ?> hover:bg-purple-50 transition font-semibold text-xs">
                    <svg class="w-6 h-6 mb-1" fill="<?php echo $page === 'statistik' ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Statistik</span>
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
