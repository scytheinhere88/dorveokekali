# 📧 PANDUAN SETUP EMAIL UNTUK DORVE.ID

## 🔴 MASALAH SEKARANG
- Email verifikasi **TIDAK TERKIRIM** ke user
- Email forgot password **TIDAK MASUK** ke Gmail
- Timer countdown **STUCK** di 60 detik (SUDAH FIXED! ✅)

---

## ✅ SOLUSI: Setup Gmail SMTP

### STEP 1: Install PHPMailer (WAJIB!)

**Via Composer (Recommended):**
```bash
cd /path/to/dorve.id
composer require phpmailer/phpmailer
```

**ATAU Download Manual:**
1. Download: https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip
2. Extract ke folder `vendor/phpmailer/phpmailer/`
3. Struktur folder:
   ```
   /dorve.id/
   ├── vendor/
   │   └── phpmailer/
   │       └── phpmailer/
   │           ├── src/
   │           │   ├── PHPMailer.php
   │           │   ├── SMTP.php
   │           │   └── Exception.php
   │           └── ...
   └── includes/
       └── email-helper.php
   ```

---

### STEP 2: Setup Gmail App Password

**⚠️ JANGAN PAKAI PASSWORD GMAIL BIASA! Harus pakai "App Password"**

#### A. Enable 2-Factor Authentication di Gmail:
1. Buka: https://myaccount.google.com/security
2. Cari **"2-Step Verification"**
3. Klik **"Get Started"**
4. Ikuti petunjuk untuk aktifkan 2FA

#### B. Generate App Password:
1. Setelah 2FA aktif, buka: https://myaccount.google.com/apppasswords
2. **App name:** Ketik `"Dorve.id Website"`
3. Klik **"Create"**
4. Gmail akan kasih **16 karakter password** seperti: `abcd efgh ijkl mnop`
5. **COPY PASSWORD INI!** (tanpa spasi)

---

### STEP 3: Update Email Configuration

Edit file: `/includes/email-helper.php`

**Line 22-23**, ganti dengan email & password kamu:

```php
define('SMTP_USERNAME', 'your-email@gmail.com'); // ⚠️ GANTI INI!
define('SMTP_PASSWORD', 'abcdefghijklmnop');     // ⚠️ GANTI INI! (App Password tanpa spasi)
```

**CONTOH:**
```php
define('SMTP_USERNAME', 'admin@dorve.id');
define('SMTP_PASSWORD', 'vkqp xmwy jklm nopq'); // App Password dari Gmail
```

---

### STEP 4: Test Email System

**Buat file test di root folder: `test-email.php`**

```php
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/email-helper.php';

// Test email ke alamat kamu sendiri
$test_email = 'your-email@gmail.com'; // GANTI INI!
$test_name = 'Test User';

echo "<h1>Testing Email System...</h1>";

// Test 1: Verification Email
echo "<h3>1. Testing Verification Email...</h3>";
$token = 'test-token-12345';
$link = 'https://dorve.id/auth/verify-email.php?token=' . $token;
$result1 = sendVerificationEmail($test_email, $test_name, $link);
echo $result1 ? "✅ BERHASIL!<br>" : "❌ GAGAL!<br>";

// Test 2: Password Reset Email
echo "<h3>2. Testing Password Reset Email...</h3>";
$link = 'https://dorve.id/auth/reset-password.php?token=' . $token;
$result2 = sendPasswordResetEmail($test_email, $test_name, $link);
echo $result2 ? "✅ BERHASIL!<br>" : "❌ GAGAL!<br>";

echo "<hr>";
echo "<p><strong>Cek inbox email kamu!</strong> Jika ada 2 email masuk, berarti setup berhasil! 🎉</p>";
?>
```

**Jalankan:**
```
http://dorve.id/test-email.php
```

---

## 🎯 HASIL SETELAH SETUP

### ✅ Yang AKAN BEKERJA:
1. **Email Verification** → Langsung masuk ke Gmail user
2. **Forgot Password** → Reset link dikirim ke email
3. **Order Confirmation** → Email otomatis saat order
4. **Shipping Update** → Email otomatis saat barang dikirim
5. **Timer Countdown** → Countdown 60 detik berjalan normal

### 🔄 Flow Email Verification:
```
User Register
  → Email verifikasi dikirim
  → User cek Gmail
  → Klik link verifikasi
  → Account aktif! ✅
```

---

## 🚨 TROUBLESHOOTING

### Problem 1: "SMTP Error: Could not authenticate"
**Solution:**
- Pastikan 2FA sudah aktif di Gmail
- Generate **App Password** baru
- Copy password tanpa spasi
- Pastikan SMTP_USERNAME & SMTP_PASSWORD sudah benar

### Problem 2: Email masih ga masuk
**Cek:**
1. **Spam/Junk folder** di Gmail
2. **Error log** di hosting: `/error_log` atau via cPanel
3. Pastikan **PHPMailer** sudah ter-install di `/vendor/`
4. Test dengan `test-email.php`

### Problem 3: "Class 'PHPMailer' not found"
**Solution:**
- PHPMailer belum ter-install
- Jalankan: `composer require phpmailer/phpmailer`
- ATAU download manual dari GitHub

### Problem 4: Gmail block login
**Solution:**
- Jangan pakai password Gmail biasa
- HARUS pakai **App Password** (16 karakter)
- Enable 2-Step Verification dulu

---

## 📝 ALTERNATIF SELAIN GMAIL

### Option 1: SendGrid (Recommended untuk Production)
- Free: 100 emails/day
- Signup: https://sendgrid.com/
- Setup: Ganti SMTP config di `email-helper.php`
  ```php
  define('SMTP_HOST', 'smtp.sendgrid.net');
  define('SMTP_PORT', 587);
  define('SMTP_USERNAME', 'apikey');
  define('SMTP_PASSWORD', 'your-sendgrid-api-key');
  ```

### Option 2: Mailgun
- Free: 5000 emails/month (first 3 months)
- Signup: https://www.mailgun.com/

### Option 3: SMTP Hosting Provider
- Tanya hosting provider kamu apakah ada SMTP service
- Biasanya ada di cPanel → Email Accounts

---

## ✨ AFTER SETUP CHECKLIST

- [ ] PHPMailer installed
- [ ] Gmail App Password generated
- [ ] Config updated di `email-helper.php`
- [ ] Test email sent successfully
- [ ] User registration working
- [ ] Forgot password working
- [ ] Countdown timer working

---

## 🎉 SELESAI!

Sekarang sistem email sudah siap production! User bisa:
- ✅ Register & dapat email verifikasi
- ✅ Reset password via email
- ✅ Dapat notifikasi order
- ✅ Tracking info via email

**Need help?** Check error logs atau contact developer! 💪
