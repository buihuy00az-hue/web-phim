<?php
session_start();
include 'ketnoi.php';

// ... existing code ...

// 🔒 SECURITY: Kiểm tra session trước khi hiển thị logout button
$is_logged_in = isset($_SESSION['user']) && !empty($_SESSION['user']);
$is_admin = isset($_SESSION['admin']) && !empty($_SESSION['admin']);

?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHIM CỦA TÔI</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="header-container">
        <a href="index.php" class="logo">PHIM CỦA TÔI</a>

        <nav class="main-menu">
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="index.php?loai=le">Phim lẻ</a></li>
                <li><a href="index.php?loai=bo">Phim bộ</a></li>
                <li class="dropdown">
                    <a href="javascript:void(0)" onclick="toggleGenreMenu(event)">Thể loại ▼</a>
                    <ul class="dropdown-content" id="genreMenu">
                        <?php
                        $res = $conn->query("SELECT * FROM theloai");
                        while ($row_tl = $res->fetch_assoc()) {
                            echo "<li><a href='index.php?theloai_id=" . (int)$row_tl['id'] . "'>" . e($row_tl['tenTheLoai']) . "</a></li>";
                        }
                        ?>
                    </ul>
                </li>
            </ul>
        </nav>

        <div class="header-right">
            <form action="timkiem.php" method="GET" class="search-box">
                <input type="text" name="keyword" placeholder="Tìm kiếm nâng cao...">
            </form>

            <!-- Dark/Light mode toggle -->
            <button class="theme-toggle" onclick="toggleTheme()" title="Chuyển giao diện">🌙</button>

            <div class="user-info">
                <?php if ($is_admin): ?>
                    <span style="font-size:14px;">Chào, <b><?php echo e($_SESSION['admin']); ?></b></span>
                    <a href="dashboard.php" style="color:#f5c518; margin-left:10px; font-size:12px; text-decoration:none;">📊 Dashboard</a>
                    <a href="thongke.php" style="color:#f5c518; margin-left:8px; font-size:12px; text-decoration:none;">📈 Thống kê</a>
                    <a href="dangxuat.php" style="color:#aaa; margin-left:8px; font-size:12px;" onclick="return confirm('Bạn có chắc muốn đăng xuất?')">Thoát</a>
                <?php elseif ($is_logged_in): ?>
                    <a href="taikhoan.php" style="color:#f5c518; text-decoration:none; font-size:14px;">👤 <?php echo e($_SESSION['user']); ?></a>
                    <a href="doimatkhau_user.php" style="color:#aaa; margin-left:10px; font-size:12px;">Đổi MK</a>
                    <a href="dangxuat_user.php" style="color:#aaa; margin-left:8px; font-size:12px;" onclick="return confirm('Bạn có chắc muốn đăng xuất?')">Thoát</a>
                <?php else: ?>
                    <a href="dangnhap_user.php" style="color:white; text-decoration:none; font-size:14px;">Đăng nhập</a>
                    <a href="dangky.php" style="color:#e50914; text-decoration:none; font-size:14px; margin-left:10px; border:1px solid #e50914; padding:4px 10px; border-radius:4px;">Đăng ký</a>
                    <a href="dangnhap.php" style="color:#555; text-decoration:none; font-size:11px; margin-left:10px;">Admin</a>
                <?php endif; ?>
            </div>

            <!-- Hamburger mobile -->
            <button class="hamburger" onclick="toggleMobileMenu()" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="mobile-menu" id="mobileMenu">
        <ul>
            <li><a href="index.php" onclick="closeMobileMenu()">🏠 Trang chủ</a></li>
            <li><a href="index.php?loai=le" onclick="closeMobileMenu()">🎥 Phim lẻ</a></li>
            <li><a href="index.php?loai=bo" onclick="closeMobileMenu()">📺 Phim bộ</a></li>
            <?php
            $res2 = $conn->query("SELECT * FROM theloai");
            while ($tl = $res2->fetch_assoc()) {
                echo "<li><a href='index.php?theloai_id=" . (int)$tl['id'] . "' onclick='closeMobileMenu()'>🎬 " . e($tl['tenTheLoai']) . "</a></li>";
            }
            ?>
            <li><a href="timkiem.php" onclick="closeMobileMenu()">🔍 Tìm kiếm</a></li>
            <?php if ($is_logged_in): ?>
                <li><a href="taikhoan.php" onclick="closeMobileMenu()">👤 Tài khoản</a></li>
                <li><a href="dangxuat_user.php" onclick="return confirm('Bạn có chắc muốn đăng xuất?')">🚪 Đăng xuất</a></li>
            <?php elseif (!$is_admin): ?>
                <li><a href="dangnhap_user.php" onclick="closeMobileMenu()">🔑 Đăng nhập</a></li>
                <li><a href="dangky.php" onclick="closeMobileMenu()">📝 Đăng ký</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

<main style="padding-top:80px; padding-left:4%; padding-right:4%;">
    <!-- REST OF THE CODE... -->
</main>

<script>
function toggleGenreMenu(event) {
    event.stopPropagation();
    document.getElementById("genreMenu").classList.toggle("show");
}
window.onclick = function(event) {
    if (!event.target.matches('.dropdown > a')) {
        var menu = document.getElementById("genreMenu");
        if (menu && menu.classList.contains('show')) menu.classList.remove('show');
    }
}

// Mobile menu
function toggleMobileMenu() {
    document.getElementById('mobileMenu').classList.toggle('open');
    document.querySelector('.hamburger').classList.toggle('active');
}
function closeMobileMenu() {
    document.getElementById('mobileMenu').classList.remove('open');
    document.querySelector('.hamburger').classList.remove('active');
}

// Dark/Light mode
function toggleTheme() {
    const html = document.documentElement;
    const btn  = document.querySelector('.theme-toggle');
    const isDark = html.getAttribute('data-theme') === 'dark';
    html.setAttribute('data-theme', isDark ? 'light' : 'dark');
    btn.textContent = isDark ? '☀️' : '🌙';
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
}
(function() {
    const saved = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', saved);
    const btn = document.querySelector('.theme-toggle');
    if (btn) btn.textContent = saved === 'dark' ? '🌙' : '☀️';
})();

// Cuộn hàng đề cử
function scrollRow(dir) {
    const el = document.getElementById('recommendScroll');
    if (el) el.scrollBy({ left: dir * 600, behavior: 'smooth' });
}
</script>
</body>
</html>
