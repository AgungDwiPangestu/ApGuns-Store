<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$page_title = 'Login';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('/Munif/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } else {
        $query = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                set_flash('Login berhasil! Selamat datang ' . $user['full_name'], 'success');

                if ($user['role'] == 'admin') {
                    redirect('/Munif/admin/dashboard.php');
                } else {
                    redirect('/Munif/index.php');
                }
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Username atau email tidak ditemukan!';
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="form-container">
    <h2 class="text-center">Login</h2>

    <?php if (!empty($error)): ?>
        <div class="flash-message error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Username atau Email</label>
            <input type="text" name="username" required
                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn" style="width: 100%;">Login</button>
    </form>

    <p class="text-center mt-1">
        Belum punya akun? <a href="register.php">Daftar di sini</a>
    </p>
</div>

<?php include '../includes/footer.php'; ?>