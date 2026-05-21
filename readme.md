# Dashboard Guru & Siswa - SMKN 1 Garut
**Android Native Mobile Web App untuk Manajemen Tugas Sekolah**

---

## 🎯 Deskripsi Aplikasi

Aplikasi web responsif yang dirancang khusus untuk guru dan siswa SMKN 1 Garut dengan interface Android Material Design 3. Aplikasi ini memungkinkan guru untuk membuat dan mengelola tugas, serta siswa untuk melihat tugas dan memperbarui status pengerjaan mereka.

---

## 📋 Fitur Utama

### Dashboard Guru
- ✅ **Home View** - Statistik tugas aktif, kelas, dan deadline
- ✅ **Task List** - Daftar semua tugas dengan filter per kelas
- ✅ **Task Detail** - Lihat detail tugas dan status pengumpulan siswa
- ✅ **Create Task** - Membuat tugas baru dengan deadline dan kelas
- ✅ **Submission List** - Daftar siswa beserta status pengumpulan (Selesai/Mengerjakan/Telat)
- ✅ **Notifikasi** - Badge notifikasi untuk tugas urgent
- ✅ **Bottom Navigation** - Navigasi antar halaman (Home, Task, Statistik)

### Dashboard Siswa
- ✅ **Home View** - Statistik tugas belum selesai dan sudah dikumpul
- ✅ **Task List** - Daftar tugas yang ditugaskan ke kelasnya
- ✅ **Task Detail** - Lihat detail tugas dengan instruksi
- ✅ **Status Updater** - Update status pengerjaan (Belum/Sedang/Selesai)
- ✅ **Profile View** - Lihat profil dan logout
- ✅ **Bottom Navigation** - Navigasi antar halaman (Tugas, Profil)

---

## 🔧 Instalasi & Setup

### Prasyarat
- XAMPP atau server lokal dengan PHP 7.4+ dan MySQL
- Database management tool (phpMyAdmin)

### Langkah-Langkah Instalasi

1. **Extract File ke Folder Web Root**
   ```
   c:\xampp\htdocs\New folder\
   ```

2. **Buat Database**
   - Buka phpMyAdmin: `http://localhost/phpmyadmin`
   - Copy & paste isi file `database.sql` ke SQL Query window
   - Execute untuk membuat tabel dan data sample

3. **Akses Aplikasi**
   ```
   http://localhost/New folder/
   ```

---

## 🔐 Test Credentials

### Akun Guru
- **Username:** `guru001`
- **Password:** `password123`
- **Role:** Teacher
- **Akses:** Dashboard Guru dengan fitur manajemen tugas lengkap

### Akun Siswa
- **Username:** `siswa001` - `siswa007`
- **Password:** `password123` (semua sama)
- **Role:** Student
- **Akses:** Dashboard Siswa dengan fitur update status

---

## 📁 Struktur File

```
New folder/
├── index.php              # Redirect ke halaman sesuai login
├── login.php              # Halaman login dengan 2 role (guru/siswa)
├── config.php             # Konfigurasi database PDO
├── dashboard.php          # Dashboard guru (home, task, detail, create, submission)
├── student_view.php       # Dashboard siswa (home, detail, profile)
├── logout.php             # Logout & destroy session
├── database.sql           # SQL schema & sample data
└── README.md              # File ini
```

---

## 🎨 Design System

### Color Palette
- **Primary Blue:** `#4A90E2` - Main brand color
- **Success Green:** `#4CD964` - Status selesai
- **Warning Orange:** `#FFCC00` - Status sedang mengerjakan
- **Danger Red:** `#FF3B30` - Status telat/urgent
- **Background:** `#F3F7FA` - Soft UI background

### Layout & Responsive
- **Mobile-First:** Viewport 360-480px (Android standard)
- **Max Width:** `max-w-md` Tailwind (384px)
- **Border Radius:** `rounded-2xl` to `rounded-3xl` (Deep corner)
- **Framework:** Tailwind CSS 3.x

### Components
- Status Bar Simulation - Native Android status bar
- Bottom Navigation - Persistent navigation bar
- Material Design Cards - Rounded, shadowed containers
- Radio Buttons & Selects - Android material inputs
- FAB (Floating Action Button) - Circular action button

---

## 🗄️ Database Schema

### Tabel: `users`
| Field | Type | Deskripsi |
|-------|------|-----------|
| id | INT | Primary Key |
| username | VARCHAR(100) | Unique username |
| password | VARCHAR(255) | Hashed password |
| full_name | VARCHAR(150) | Nama lengkap |
| role | ENUM | 'teacher' atau 'student' |
| class_id | INT | FK ke classes (untuk student) |
| created_at | TIMESTAMP | Timestamp pembuatan |

### Tabel: `classes`
| Field | Type | Deskripsi |
|-------|------|-----------|
| id | INT | Primary Key |
| class_name | VARCHAR(50) | Nama kelas (X PPLG 1, dll) |
| created_at | TIMESTAMP | Timestamp pembuatan |

### Tabel: `tasks`
| Field | Type | Deskripsi |
|-------|------|-----------|
| id | INT | Primary Key |
| title | VARCHAR(200) | Judul tugas |
| description | LONGTEXT | Deskripsi instruksi |
| class_id | INT | FK ke classes |
| deadline_text | VARCHAR(100) | Text deadline (Besok, 11:00) |
| lesson_hour | INT | Jam ke- (1-10) |
| attachment_path | VARCHAR(255) | Path file lampiran (optional) |
| is_urgent | BOOLEAN | Flag urgent |
| created_at | TIMESTAMP | Timestamp pembuatan |
| teacher_id | INT | FK ke users (guru) |

### Tabel: `submissions`
| Field | Type | Deskripsi |
|-------|------|-----------|
| id | INT | Primary Key |
| task_id | INT | FK ke tasks |
| student_id | INT | FK ke users (siswa) |
| status | ENUM | 'belum_mengerjakan', 'sedang_mengerjakan', 'selesai', 'telat' |
| updated_at | TIMESTAMP | Update terakhir |

---

## 🔐 Keamanan

✅ **Implementasi Keamanan:**
- PDO Prepared Statements untuk mencegah SQL Injection
- Password Hashing dengan `password_hash()` & `password_verify()`
- Session Management dengan `$_SESSION`
- Role-based Access Control (RBAC)
- CSRF Protection (untuk POST requests)

---

## 📱 UI/UX Features

### Guru Dashboard
```
STATUS BAR (Clock, WiFi, Battery)
├── HOME VIEW
│   ├── Header (Profile, Greeting, Notification Bell)
│   ├── Summary Cards (Tugas Aktif, Sisa Kelas, Deadline Besok)
│   ├── Recent Tasks (List dengan progress bar)
│   └── FAB (+ Buat Tugas Baru)
├── TASK LIST VIEW
│   ├── Filter Dropdown (Semua Kelas)
│   └── Task Cards Stack
├── TASK DETAIL VIEW
│   ├── App Bar (Back, Title, Menu)
│   ├── Priority Card (🔥 URGENT)
│   ├── Instructions Block
│   ├── Status Pengumpulan (Selesai/Mengerjakan/Telat)
│   └── CTA Button (Lihat Pengumpulan)
├── CREATE TASK VIEW
│   ├── Form Fields (Judul, Deskripsi, Deadline, Kelas, Jam Ke)
│   ├── Media Picker
│   └── Submit Button (Kirim Tugas)
└── SUBMISSION LIST VIEW
    ├── Segmented Tabs (Semua, Selesai, Mengerjakan, Telat)
    └── Student List Cards dengan status badges

BOTTOM NAVIGATION (Home, Tugas, Statistik)
```

### Siswa Dashboard
```
STATUS BAR
├── HOME VIEW
│   ├── Header (Profile, Greeting, Notification)
│   ├── Quick Stats (Belum Selesai, Sudah Dikumpul)
│   ├── Filter Dropdown
│   └── Task List dengan Status Badge
├── TASK DETAIL VIEW
│   ├── App Bar (Back, Title)
│   ├── Task Header Info
│   ├── Instructions
│   ├── Status Updater (Radio Buttons)
│   └── Save Button (Simpan Progres Tugas)
└── PROFILE VIEW
    ├── Profile Card
    └── Logout Button

BOTTOM NAVIGATION (Tugas, Profil)
```

---

## 🚀 Menggunakan Aplikasi

### Sebagai Guru

1. **Login** dengan akun `guru001 / password123`
2. **Home Dashboard** - Lihat statistik & recent tasks
3. **Create Task** - Click FAB atau tombol "+ Buat"
   - Isi judul, deskripsi, deadline, kelas, jam ke-
   - Optional: Mark as Urgent
   - Click "Kirim Tugas"
4. **View Tasks** - Go ke "Tugas" tab
   - Click task untuk lihat detail
5. **Monitor Submissions** - Click "Lihat Pengumpulan"
   - Filter berdasarkan status (Selesai/Mengerjakan/Telat)
   - Lihat daftar siswa & waktu submit
   - Click bell icon untuk reminder (future feature)

### Sebagai Siswa

1. **Login** dengan akun `siswa001 / password123` (atau siswa002-007)
2. **Home Dashboard** - Lihat tugas & statistik
3. **View Task** - Click task card untuk detail
4. **Update Status** - Pilih status:
   - Belum Mengerjakan
   - Sedang Mengerjakan
   - Selesai
5. **Save** - Click "Simpan Progres Tugas"
   - Status akan muncul di dashboard guru

---

## 🔄 API/Backend Flow

### Authentication Flow
```php
1. User login di login.php
2. Query user dari database berdasarkan username
3. Verify password dengan password_verify()
4. Set $_SESSION['user_id'], $_SESSION['role'], $_SESSION['full_name']
5. Redirect ke dashboard sesuai role
```

### Create Task Flow (Guru)
```php
1. Form POST dari create_task.php
2. Validate input (judul, deskripsi, kelas, deadline, jam_ke)
3. INSERT ke table tasks dengan teacher_id
4. Auto-create submissions records untuk semua siswa di kelas tersebut
5. Redirect ke task list dengan success message
```

### Update Submission Status Flow (Siswa)
```php
1. Form POST dari student_view.php?page=detail_task
2. Check if submission record exists untuk task & student
3. If exists: UPDATE submissions SET status
4. If not exists: INSERT new submission record
5. Redirect dengan success message
```

---

## 🐛 Troubleshooting

### Database Connection Error
- **Problem:** "Koneksi Database Gagal"
- **Solution:** 
  - Check Apache & MySQL running di XAMPP
  - Verify database name in `config.php` (default: `dashboard_guru`)
  - Import `database.sql` di phpMyAdmin

### Session Not Persisting
- **Problem:** Login then redirect ke login page lagi
- **Solution:**
  - Check `session_start()` at top of all PHP files
  - Verify `$_SESSION` variables set correctly

### Task Not Showing for Student
- **Problem:** Siswa tidak melihat task yang dibuat guru
- **Solution:**
  - Verify student's class_id matches task's class_id
  - Check database query di student_view.php line ~50

### Submission Status Not Updating
- **Problem:** Status update button tidak berfungsi
- **Solution:**
  - Check POST method in form
  - Verify task_id & student_id passed correctly
  - Check database unique constraint on (task_id, student_id)

---

## 📝 Notes & Future Enhancements

### Current Limitations
- ❌ File attachment upload (placeholder only)
- ❌ Real-time notifications
- ❌ Email notifications
- ❌ Admin panel untuk manage users
- ❌ Dark mode
- ❌ Multi-language support

### Recommended Enhancements
1. Add file upload functionality for task attachments
2. Implement real-time notifications using WebSockets
3. Add email notifications to teachers & students
4. Create admin panel for user management
5. Add statistics & analytics dashboard
6. Implement deadline reminder auto-notifications
7. Add export submissions as PDF/Excel
8. Student collaboration features (comments)

---

## 📞 Support & Contact

Jika ada pertanyaan atau issue, silakan hubungi:
- **School:** SMKN 1 Garut
- **Department:** Produktif (Web Development)

---

## 📄 License

This project is created for educational purposes at SMKN 1 Garut.

---

**Last Updated:** May 2026  
**Version:** 1.0.0  
**Status:** Production Ready ✅
