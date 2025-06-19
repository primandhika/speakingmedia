# 🚀 Panduan Instalasi Bicaranta

## 📋 Prerequisites

- **XAMPP** atau server lokal dengan PHP 7.4+ dan MySQL
- **Web Browser** modern (Chrome, Firefox, Safari, Edge)
- File **bicaranta_db.sql** yang telah disediakan

## 🔧 Langkah Instalasi

### 1. Setup Server Lokal

1. **Download dan Install XAMPP**
   - Download dari: https://www.apachefriends.org/
   - Install dengan mengikuti wizard

2. **Start Services**
   - Buka XAMPP Control Panel
   - Start **Apache** dan **MySQL**

### 2. Setup Database

1. **Buka phpMyAdmin**
   - Akses: http://localhost/phpmyadmin
   - Login dengan username: `root`, password: (kosong)

2. **Import Database**
   - Klik tab **"Import"**
   - Klik **"Choose File"** dan pilih `bicaranta_db.sql`
   - Klik **"Go"** untuk mengimport
   - Database `bicaranta_db` akan terbuat secara otomatis

### 3. Setup Files

1. **Copy Project Files**
   ```
   C:\xampp\htdocs\speakingmedia\
   ├── index.php                 ← File utama (updated)
   ├── config/
   │   ├── app.php              ← Konfigurasi aplikasi
   │   ├── database.php         ← Konfigurasi database (NEW)
   │   └── init_database.php    ← Script test database (NEW)
   ├── includes/
   │   ├── functions.php        ← Functions dengan database (UPDATED)
   │   ├── header.php           ← Header template
   │   └── footer.php           ← Footer template
   ├── pages/                   ← Halaman materi
   ├── assets/                  ← CSS, JS, images
   └── bicaranta_db.sql         ← File database
   ```

2. **Update File Functions**
   - Ganti `includes/functions.php` dengan versi database-enabled
   - Pastikan `config/database.php` ada dengan konfigurasi yang benar
   - **HAPUS** `config/materials.php` - tidak diperlukan lagi!

### 4. Konfigurasi Database

Pastikan file `config/database.php` berisi:

```php
<?php
return [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',           // Kosong untuk XAMPP default
    'database' => 'bicaranta_db',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
?>
```

### 5. Test Installation

1. **Akses Website**
   - Buka browser dan akses: http://localhost/speakingmedia

2. **Test Database Connection**
   - Jika ada error database, akses: http://localhost/speakingmedia/config/init_database.php
   - Follow petunjuk yang muncul

3. **Test Login**
   - Klik tombol "Masuk" atau "Demo ID"
   - Gunakan salah satu demo account:
     - `00029` - Demo User (Admin)
     - `INST001` - Dr. Budi Dharma (Instructor) 
     - `STU001` - Maya Sari (Student)
     - `12345` - Ahmad Rizki (Instructor)
     - `67890` - Siti Nurhaliza (Student)

## 👤 Demo Accounts

| User ID | Name | Role | Email |
|---------|------|------|-------|
| `00029` | Demo User | Admin | demo@bicaranta.com |
| `INST001` | Dr. Budi Dharma | Instructor | budi.dharma@bicaranta.com |
| `STU001` | Maya Sari | Student | maya.sari@example.com |
| `12345` | Ahmad Rizki | Instructor | ahmad@example.com |
| `67890` | Siti Nurhaliza | Student | siti@example.com |

**Password untuk semua account:** `password` atau kosongkan

## ✨ Fitur yang Tersedia

### 🔐 Authentication System
- [x] Login dengan ID dan password
- [x] Session management
- [x] Role-based access (Admin, Instructor, Student)
- [x] Activity logging

### 📚 Material Management
- [x] Material dari database
- [x] Progress tracking per user
- [x] Click counting dan progress percentage
- [x] Material status (not_started, in_progress, completed)
- [x] Difficulty levels dan duration info

### 🔍 Search & Filter
- [x] Real-time search materials
- [x] Search by name, description, atau key
- [x] Search result logging

### 📊 Statistics & Analytics
- [x] User progress tracking
- [x] Total interactions
- [x] Completed materials count
- [x] Average progress percentage
- [x] Activity logging (login, logout, material clicks, searches)

### 🎨 UI/UX Features
- [x] Responsive design
- [x] Bootstrap 5 integration
- [x] AOS animations
- [x] Status badges pada materials
- [x] Progress bars visual
- [x] Role-based navigation

## 🐛 Troubleshooting

### Database Connection Issues

**Error:** "Database connection failed"
**Solution:**
1. Pastikan MySQL service running di XAMPP
2. Check konfigurasi di `config/database.php`
3. Import ulang `bicaranta_db.sql`

### Missing Functions Error

**Error:** "Call to undefined function"
**Solution:**
1. Pastikan `includes/functions.php` sudah diupdate dengan versi database
2. Check semua include paths

### Material Pages Not Found

**Error:** 404 pada halaman material
**Solution:**
1. Pastikan folder `pages/` ada
2. Buat file HTML untuk setiap material key
3. Atau update redirect logic di `index.php`

## 📈 Database Schema

### Core Tables
- **users** - Data pengguna dan authentication
- **materials** - Master data materi pembelajaran
- **user_progress** - Progress tracking per user per material
- **activity_log** - Log semua aktivitas user
- **sessions** - Session tracking
- **permissions** - Role-based permissions
- **instructor_materials** - Assignment materi ke instructor

### Key Relationships
- `user_progress.user_id` → `users.user_id`
- `user_progress.material_key` → `materials.material_key`
- `activity_log.user_id` → `users.user_id`
- `instructor_materials.instructor_id` → `users.user_id`

## 🔄 Migration from Session-based

Jika sebelumnya menggunakan session-based tracking:

1. Data session tidak akan otomatis migrate
2. Progress akan mulai dari 0 untuk semua user
3. Untuk preserve data lama, bisa create custom migration script

## 🛠️ Development Notes

### Adding New Materials
1. Insert ke table `materials` dengan query:
   ```sql
   INSERT INTO materials (material_key, name, description, icon, difficulty, duration) 
   VALUES ('new-material', 'Material Baru', 'Deskripsi material', 'bi-icon', 'Pemula', '2-3 jam');
   ```
2. Buat file HTML di folder `pages/new-material.html`
3. Material akan otomatis muncul di interface

### Custom Permissions
1. Add permission di table `permissions`
2. Use `hasPermission($permission)` function
3. Implement di UI logic

### Analytics Enhancement
1. Data tersimpan di `activity_log`
2. Bisa create custom reports dari data ini
3. Session tracking available di table `sessions`

---

🎉 **Instalasi Selesai!** Sekarang Anda dapat menggunakan Bicaranta dengan full database integration.