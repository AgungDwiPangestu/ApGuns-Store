<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$page_title = 'Checkout';

// Check if user is logged in
if (!is_logged_in()) {
    set_flash('Silakan login terlebih dahulu!', 'error');
    redirect('/Munif/pages/login.php');
}

$user_id = $_SESSION['user_id'];

// Get cart items
$query = "SELECT c.*, b.title, b.price, b.stock 
          FROM cart c 
          JOIN books b ON c.book_id = b.id 
          WHERE c.user_id = $user_id";
$cart_items = mysqli_query($conn, $query);

if (mysqli_num_rows($cart_items) == 0) {
    set_flash('Keranjang belanja kosong!', 'error');
    redirect('/Munif/pages/cart.php');
}

// Calculate total
$total = 0;
$items_array = [];
while ($item = mysqli_fetch_assoc($cart_items)) {
    $total += $item['price'] * $item['quantity'];
    $items_array[] = $item;
}

// Get user info
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = clean_input($_POST['shipping_address']);
    $payment_method = clean_input($_POST['payment_method']);

    if (empty($shipping_address) || empty($payment_method)) {
        $error = 'Semua field harus diisi!';
    } else {
        // Start transaction
        mysqli_begin_transaction($conn);

        try {
            // Create order
            $insert_order = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, status) 
                           VALUES ($user_id, $total, '$shipping_address', '$payment_method', 'pending')";
            mysqli_query($conn, $insert_order);
            $order_id = mysqli_insert_id($conn);

            // Insert order items and update stock
            foreach ($items_array as $item) {
                // Check stock
                if ($item['stock'] < $item['quantity']) {
                    throw new Exception('Stok buku "' . $item['title'] . '" tidak mencukupi');
                }

                // Insert order item
                $insert_item = "INSERT INTO order_items (order_id, book_id, quantity, price) 
                              VALUES ($order_id, {$item['book_id']}, {$item['quantity']}, {$item['price']})";
                mysqli_query($conn, $insert_item);

                // Update stock
                $update_stock = "UPDATE books SET stock = stock - {$item['quantity']} WHERE id = {$item['book_id']}";
                mysqli_query($conn, $update_stock);
            }

            // Clear cart
            $clear_cart = "DELETE FROM cart WHERE user_id = $user_id";
            mysqli_query($conn, $clear_cart);

            // Commit transaction
            mysqli_commit($conn);

            set_flash('Pesanan berhasil dibuat! Nomor pesanan: #' . $order_id, 'success');
            redirect('/Munif/pages/order_detail.php?id=' . $order_id);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-2">
    <h1 class="text-center mb-2">Checkout</h1>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Checkout Form -->
        <div class="form-container" style="margin: 0;">
            <h3>Informasi Pengiriman</h3>

            <?php if (!empty($error)): ?>
                <div class="flash-message error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Nama Penerima</label>
                    <input type="text" value="<?php echo $user['full_name']; ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?php echo $user['email']; ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Telepon</label>
                    <input type="tel" value="<?php echo $user['phone']; ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Alamat Pengiriman *</label>
                    <textarea name="shipping_address" rows="4" required><?php echo $user['address']; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Metode Pembayaran *</label>
                    <select name="payment_method" required>
                        <option value="">Pilih metode pembayaran</option>
                        <option value="bank_transfer">Transfer Bank</option>
                        <option value="cod">Cash on Delivery (COD)</option>
                        <option value="e_wallet">E-Wallet</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success" style="width: 100%;">
                    <i class="fas fa-check"></i> Buat Pesanan
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div>
            <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h3 style="margin-bottom: 1rem;">Ringkasan Pesanan</h3>

                <?php foreach ($items_array as $item): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                        <div>
                            <strong><?php echo $item['title']; ?></strong><br>
                            <small><?php echo $item['quantity']; ?> x <?php echo format_rupiah($item['price']); ?></small>
                        </div>
                        <div>
                            <strong><?php echo format_rupiah($item['price'] * $item['quantity']); ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; font-size: 1.3rem;">
                        <strong>Total:</strong>
                        <strong style="color: var(--secondary-color);"><?php echo format_rupiah($total); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>