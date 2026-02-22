# 🚀 InfinityFree Upload Guide — opengenetics.infinityfreeapp.com

## ✅ สิ่งที่เตรียมไว้ให้แล้ว

1. ✅ `composer install` เสร็จแล้ว → มี `vendor/`
2. ✅ `.env.production` → ไฟล์ config สำหรับ production
3. ✅ `public/index.production.php` → index.php ที่แก้ path แล้ว
4. ✅ Database schema import เสร็จแล้ว (ตามที่คุณบอก)

---

## 📦 ขั้นตอนอัปโหลด (ทำตามนี้เลย)

### Step 1: เข้า InfinityFree File Manager
1. เข้า InfinityFree Control Panel
2. คลิก **File Manager**
3. เข้าโฟลเดอร์ `htdocs/`

### Step 2: อัปโหลดไฟล์/โฟลเดอร์ทั้งหมดนี้ไปใน `htdocs/`

อัปโหลดจากโฟลเดอร์โปรเจค `/Applications/XAMPP/xamppfiles/htdocs/open-genetics/`:

```
htdocs/
├── api/                    ← อัปทั้งโฟลเดอร์
├── src/                    ← อัปทั้งโฟลเดอร์
├── vendor/                 ← อัปทั้งโฟลเดอร์ (สำคัญมาก!)
├── locales/                ← อัปทั้งโฟลเดอร์
├── storage/                ← อัปทั้งโฟลเดอร์
│   └── rate-limit/
├── .htaccess               ← จาก public/.htaccess
├── index.php               ← ใช้ public/index.production.php แล้วเปลี่ยนชื่อเป็น index.php
└── .env                    ← ใช้ .env.production แล้วเปลี่ยนชื่อเป็น .env
```

### Step 3: เปลี่ยนชื่อไฟล์หลังอัปเสร็จ

ใน File Manager บน InfinityFree:
1. **Rename** `index.production.php` → `index.php`
2. **Rename** `.env.production` → `.env`

### Step 4: ตั้ง Permission
คลิกขวาโฟลเดอร์ `storage/` → **Change Permissions** → ตั้งเป็น `755`

---

## 🧪 ทดสอบ

### Test 1: Health Check
เปิดเบราว์เซอร์:
```
https://opengenetics.infinityfreeapp.com/api/health
```

ควรได้:
```json
{
  "status": "ok",
  "version": "1.0.0",
  "php": "8.x.x",
  "env": "production",
  "time": "...",
  "database": "connected"
}
```

### Test 2: Login
```bash
curl -X POST https://opengenetics.infinityfreeapp.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@opengenetics.io","password":"password"}'
```

ควรได้ JWT token กลับมา

---

## ⚠️ ถ้าเจอปัญหา

### ปัญหา: 500 Internal Server Error
- เช็ค `.htaccess` ว่าอัปแล้วหรือยัง
- เช็ค `vendor/` ว่าอัปครบหรือยัง

### ปัญหา: Database connection failed
- เช็ค `.env` ว่า `DB_NAME`, `DB_USER`, `DB_PASS` ถูกต้องหรือไม่
- ตรวจสอบว่า database มีตารางครบ 4 ตาราง (roles, users, password_resets, audit_logs)

### ปัญหา: Class not found
- แปลว่า `vendor/` ไม่ได้อัปหรืออัปไม่ครบ → อัปใหม่

---

## 📋 Checklist ก่อนอัป

- [ ] `composer install --no-dev` รันแล้ว
- [ ] มีโฟลเดอร์ `vendor/` ในเครื่อง
- [ ] `.env.production` มีค่า DB ถูกต้อง
- [ ] `public/index.production.php` พร้อมใช้
- [ ] Database import เสร็จแล้ว (4 ตาราง + seed data)

---

## 🎯 สรุป: อัปแค่ 8 รายการนี้

1. `api/`
2. `src/`
3. `vendor/`
4. `locales/`
5. `storage/`
6. `public/.htaccess` → อัปเป็น `.htaccess`
7. `public/index.production.php` → อัปเป็น `index.php`
8. `.env.production` → อัปเป็น `.env`

เสร็จแล้วเปิด `https://opengenetics.infinityfreeapp.com/api/health` ทดสอบ!
