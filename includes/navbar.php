<nav class="navbar">
    <div class="container">
        <div class="nav-brand">
            <a href="/Munif/index.php">
                <i class="fas fa-book"></i>
                <span>Munif Store</span>
            </a>
        </div>

        <div class="nav-search">
            <form action="/Munif/pages/books.php" method="GET">
                <input type="text" name="q" placeholder="Cari buku, penulis, atau penerbit..." required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="nav-links">
            <a href="/Munif/index.php">Beranda</a>
            <a href="/Munif/pages/books.php">Katalog</a>

            <?php if (is_logged_in()): ?>
                <?php if (is_admin()): ?>
                    <a href="/Munif/admin/dashboard.php">Admin Panel</a>
                <?php endif; ?>
                <a href="/Munif/pages/cart.php" class="cart-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo get_cart_count(); ?></span>
                </a>
                <div class="dropdown">
                    <a href="#" class="dropbtn">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                    </a>
                    <div class="dropdown-content">
                        <a href="/Munif/pages/profile.php">Profil</a>
                        <a href="/Munif/pages/orders.php">Pesanan Saya</a>
                        <a href="/Munif/pages/logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/Munif/pages/login.php" class="btn-login">Login</a>
                <a href="/Munif/pages/register.php" class="btn-register">Daftar</a>
            <?php endif; ?>
        </div>

        <div class="nav-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>