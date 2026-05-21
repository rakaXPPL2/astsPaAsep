# 🚀 Quick Setup Guide

## Step 1: Database Setup (Most Important!)

1. Open **phpMyAdmin** at `http://localhost/phpmyadmin`
2. Open **SQL Tab**
3. Copy all content from `database.sql` file
4. Paste into the SQL Query window
5. Click **Execute** button

✅ Database is now ready with tables and sample data!

---

## Step 2: Access the Application

Open your browser and go to:
```
http://localhost/New folder/
```

Or create a simpler URL by accessing directly:
```
http://localhost/New folder/login.php
```

---

## Step 3: Test Login

### For Guru (Teacher):
- Username: `guru001`
- Password: `password123`

### For Siswa (Student):
- Username: `siswa001` (or siswa002-007)
- Password: `password123`

---

## 📱 What You Get

### Guru Features:
- ✅ Create tasks with deadline, class, and lesson hour
- ✅ View all tasks with submission statistics
- ✅ See which students have submitted (Selesai/Mengerjakan/Telat)
- ✅ Professional Android-style interface
- ✅ Real-time statistics dashboard

### Siswa Features:
- ✅ View tasks assigned to their class
- ✅ Update their submission status
- ✅ See task instructions and deadlines
- ✅ Track their own progress
- ✅ Easy-to-use mobile interface

---

## 🎯 Quick Test Flow

### As Guru:
1. Login as guru001
2. Click "+" button to create a task
3. Fill in task details and submit
4. Go to "Tugas" tab to see the task
5. Click task to see detail
6. Click "Lihat Pengumpulan" to see student submissions

### As Siswa:
1. Login as siswa001
2. You'll see tasks assigned to your class
3. Click a task to open it
4. Select your status (Belum/Sedang/Selesai)
5. Click "Simpan Progres Tugas"
6. Go back and see the status updated!

---

## 💾 Sample Data Included

### Classes:
- X PPLG 1
- X PPLG 2
- XI PPLG 1

### Users:
- 1 Guru (guru001)
- 7 Siswa (siswa001-siswa007)
  - siswa001 & siswa005 → X PPLG 1
  - siswa003, siswa004, siswa006 → X PPLG 2
  - siswa002, siswa007 → X PPLG 1

### Tasks:
- 3 Sample tasks with different statuses
- Mix of urgent and non-urgent tasks

---

## 🔧 File Structure

```
New folder/
├── database.sql          ← Import this first!
├── config.php            ← Database config
├── login.php             ← Login page
├── index.php             ← Redirect page
├── dashboard.php         ← Guru dashboard (HOME, TASK, DETAIL, CREATE, SUBMISSION LIST)
├── student_view.php      ← Siswa dashboard (HOME, DETAIL, PROFILE)
├── logout.php            ← Logout handler
└── README.md             ← Full documentation
```

---

## ⚠️ Common Issues & Solutions

**Issue: "Koneksi Database Gagal"**
- Make sure MySQL is running in XAMPP
- Import database.sql first!
- Check database name is `dashboard_guru`

**Issue: Login infinite loop**
- Clear browser cache
- Check session_start() is called

**Issue: Tasks not showing for student**
- Make sure student is in the correct class
- Check table submissions has correct task_id & student_id

---

## 📞 Need Help?

Refer to README.md for:
- Full feature documentation
- Database schema details
- API/Backend flow
- Troubleshooting guide
- Future enhancements ideas

---

**✅ Setup Complete! Enjoy your Dashboard App!** 🎉
