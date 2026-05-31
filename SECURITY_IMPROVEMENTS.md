# 🔒 SECURITY IMPROVEMENTS - Web Phim

## Ngày: 31/05/2026
## Version: 1.0

---

## 📋 TÓM TẮT CÁC CẢI TIẾN

### ✅ Đã Sửa

#### 1. **CSRF Token Initialization (ketnoi.php)**
- **Vấn đề**: CSRF token chỉ được tạo khi đăng nhập, các file khác không thể sử dụng
- **Giải pháp**: Initialize CSRF token cho TẤT CẢ sessions ngay trong `ketnoi.php`
```php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

#### 2. **Account Status Check (dangnhap_user.php)**
- **Vấn đề**: User bị khóa vẫn có thể đăng nhập
- **Giải pháp**: Thêm kiểm tra `trang_thai` trước khi tạo session
```php
if ($user['trang_thai'] === 'bi_khoa') {
    $error = "❌ Tài khoản của bạn đã bị khóa. Vui lòng liên hệ admin.";
    logActivity('login_blocked_account', $user['id'], "Tài khoản bị khóa");
}
```

#### 3. **Rate Limiting (dangnhap.php, dangnhap_user.php)**
- **Vấn đề**: Không có giới hạn số lần đăng nhập thất bại
- **Giải pháp**: Implement rate limiting - tối đa 5 lần/5 phút + delay 2 giây
```php
$attempts_key = "login_attempts_$ip";
$attempts = array_filter($_SESSION[$attempts_key] ?? [], fn($t) => time() - $t < 300);
if (count($attempts) >= 5) {
    $error = "⚠️ Quá nhiều lần đăng nhập thất bại.";
}
// Delay 2 giây để chậm lại brute force
sleep(2);
```

#### 4. **Weak Password Detection (dangky.php)**
- **Vấn đề**: Chấp nhận mật khẩu yếu như "123456"
- **Giải pháp**: 
  - Kiểm tra blacklist mật khẩu yếu
  - Yêu cầu chữ hoa + số
  - Real-time password strength indicator
```php
$weak_passwords = ['123456', 'password', 'admin', 'qwerty'];
if (in_array(strtolower($pass), $weak_passwords)) {
    $error = "⚠️ Mật khẩu quá yếu!";
}
if (!preg_match('/[A-Z]/', $pass) || !preg_match('/[0-9]/', $pass)) {
    $error = "⚠️ Mật khẩu phải chứa chữ in hoa và số!";
}
```

#### 5. **Security Headers (ketnoi.php)**
- **Vấn đề**: Thiếu headers bảo vệ khỏi các kiểu tấn công
- **Giải pháp**: Thêm security headers
```php
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'; ...");
```

#### 6. **Activity Logging (ketnoi.php)**
- **Vấn đề**: Không có log hoạt động để audit
- **Giải pháp**: Implement logging function
```php
function logActivity($action, $user_id = null, $details = '') {
    // Log vào file logs/activity_YYYY-MM-DD.log
    $log_entry = "[$timestamp] IP:$ip User:$user_id Action:$action\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}
```

#### 7. **Input Validation Functions (ketnoi.php)**
- **Vấn đề**: Không có utility functions để validate input
- **Giải pháp**: Thêm các hàm helper
```php
function validate_int($value, $min = 0) { ... }
function validate_email($email) { ... }
function validate_url($url) { ... }
function sanitize_string($str, $max_length = 255) { ... }
```

#### 8. **Strong Username Validation (dangky.php)**
- **Vấn đề**: Chấp nhận username với ký tự đặc biệt
- **Giải pháp**: Validate với regex - chỉ cho chữ, số, _, -
```php
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
    $error = "Tên đăng nhập chỉ chứa chữ, số, dấu gạch dưới và dấu gạch ngang!";
}
```

---

## 🔴 VẤN ĐỀ VẪN CẦN THEO DÕI

### 1. **File Upload Validation**
- **Nếu có** chức năng upload file, cần validate:
  - Kiểm tra MIME type thực tế (không chỉ extension)
  - Giới hạn kích thước file
  - Scan virus nếu có thể
  - Lưu file ngoài web root

### 2. **SQL Injection Prevention**
- ✅ Đã dùng Prepared Statements mọi chỗ
- ⚠️ Kiểm tra thêm file `index.php` dòng 92-95 để chắc chắn validation

### 3. **Database Backup**
- Implement automated backup
- Test restore process

### 4. **HTTPS Enforcement**
- Thêm redirect HTTP → HTTPS
```php
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}
```

### 5. **Session Security**
- Thêm vào `php.ini` hoặc `.htaccess`:
```ini
session.cookie_secure = On      # Chỉ gửi cookie qua HTTPS
session.cookie_httponly = On    # Không cho JavaScript access
session.use_strict_mode = On    # Tăng cứng chế độ strict
session.cookie_samesite = "Lax" # CSRF protection
```

### 6. **API Rate Limiting** (Nếu có API)
- Implement global rate limiting
- Per-user rate limiting

---

## 📝 CÁC FILE ĐÃ CẬP NHẬT

1. ✅ `ketnoi.php` - Security headers, CSRF token, logging
2. ✅ `dangnhap_user.php` - Account status check, rate limiting
3. ✅ `dangnhap.php` - Rate limiting, logging
4. ✅ `dangky.php` - Weak password detection, strong username validation

---

## 🧪 TESTING CHECKLIST

- [ ] Test rate limiting với 6 lần failed login
- [ ] Kiểm tra khóa account không thể đăng nhập
- [ ] Test CSRF token trên tất cả form
- [ ] Kiểm tra logs được ghi lại đúng
- [ ] Test weak password rejection
- [ ] Verify security headers đã được set
- [ ] Test password strength indicator
- [ ] Kiểm tra username validation

---

## 🚀 CÁC CẢI TIẾN ĐỀ XUẤT TRONG TƯƠNG LAI

1. **Two-Factor Authentication (2FA)**
   - Thêm OTP qua email hoặc authenticator app

2. **Password Reset with Token**
   - Thay vì email password, dùng reset token

3. **Session Management**
   - Limit concurrent sessions per user
   - Session timeout
   - Device tracking

4. **Admin Panel Hardening**
   - IP whitelist cho admin
   - Require 2FA for admin
   - Admin activity audit log

5. **Database Security**
   - Encryption for sensitive fields
   - Regular security audits
   - Database backups

6. **API Security** (Nếu có)
   - API key authentication
   - Rate limiting per key
   - JWT tokens

7. **Dependency Updates**
   - Regular PHP updates
   - MySQL/MariaDB security patches
   - Library vulnerability scanning

---

## 📚 REFERENCES

- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Security_Cheat_Sheet.html)
- [PHP Manual: Security](https://www.php.net/manual/en/security.php)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [CWE: Common Weakness Enumeration](https://cwe.mitre.org/)

---

## 📞 CONTACT

Nếu phát hiện lỗ hổng bảo mật, vui lòng báo cáo ngay.

---

**Last Updated**: 31/05/2026  
**Status**: ✅ Active  
**Version**: 1.0
