<?php
require_once '../config/db.php';
require_once '../config/functions.php';

if (!is_logged_in() || !is_admin()) {
    set_flash('Akses ditolak!', 'error');
    redirect('/Munif/pages/login.php');
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('/Munif/admin/manage_orders.php');
}

$order_id = (int)$_GET['id'];

// Get order details with user info
$query = "SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = $order_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    set_flash('Pesanan tidak ditemukan!', 'error');
    redirect('/Munif/admin/manage_orders.php');
}

$order = mysqli_fetch_assoc($result);
$page_title = 'Detail Pesanan #' . $order_id;

// Get order items
$items_query = "SELECT oi.*, b.title, b.author, b.image FROM order_items oi JOIN books b ON oi.book_id = b.id WHERE oi.order_id = $order_id";
$items = mysqli_query($conn, $items_query);

include '../includes/header.php';
include '../includes/navbar.php';

$status_colors = [
    'pending' => '#f39c12',
    'processing' => '#3498db',
    'shipped' => '#9b59b6',
    'delivered' => '#27ae60',
    'cancelled' => '#e74c3c'
];
$status_labels = [
    'pending' => 'Menunggu',
    'processing' => 'Diproses',
    'shipped' => 'Dikirim',
    'delivered' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];
?>

<div class="container mt-2">
    <h1 class="text-center mb-2">Detail Pesanan #<?php echo $order_id; ?></h1>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Order Items -->
        <div>
            <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h3 style="margin-bottom: 1rem;">Item Pesanan</h3>
                <?php while ($item = mysqli_fetch_assoc($items)): ?>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                        <img src="../assets/images/books/<?php echo $item['image'] ?: 'default.jpg'; ?>"
                            alt="<?php echo $item['title']; ?>"
                            onerror="this.src='../assets/images/books/default.jpg'"
                            style="width: 80px; height: 110px; object-fit: cover; border-radius: 5px;">
                        <div style="flex: 1;">
                            <h4><?php echo $item['title']; ?></h4>
                            <p style="color: #666;">oleh <?php echo $item['author']; ?></p>
                            <p><?php echo $item['quantity']; ?> x <?php echo format_rupiah($item['price']); ?></p>
                            <strong><?php echo format_rupiah($item['quantity'] * $item['price']); ?></strong>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Order Info -->
        <div>
            <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 1.5rem;">
                <h3 style="margin-bottom: 1rem;">Informasi Pesanan</h3>

                <div style="margin-bottom: 1rem;">
                    <strong>Pemesan:</strong><br>
                    <?php echo htmlspecialchars($order['full_name']); ?>
                    <div style="color:#666; font-size:0.9rem;">Email: <?php echo htmlspecialchars($order['email']); ?></div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <strong>Status:</strong><br>
                    <span style="background: <?php echo $status_colors[$order['status']]; ?>; color: white; padding: 0.5rem 1rem; border-radius: 20px; display: inline-block; margin-top: 0.5rem;">
                        <?php echo $status_labels[$order['status']]; ?>
                    </span>
                </div>

                <div style="margin-bottom: 1rem;">
                    <strong>Tanggal Pesanan:</strong><br>
                    <?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?>
                </div>

                <div style="margin-bottom: 1rem;">
                    <strong>Metode Pembayaran:</strong><br>
                    <?php
                    $payment_methods = [
                        'bank_transfer' => 'Transfer Bank',
                        'cod' => 'Cash on Delivery',
                        'e_wallet' => 'E-Wallet'
                    ];
                    echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                    ?>
                </div>

                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                    <strong style="font-size: 1.1rem;">Total Pembayaran:</strong><br>
                    <span style="font-size: 1.5rem; color: var(--secondary-color); font-weight: bold;">
                        <?php echo format_rupiah($order['total_amount']); ?>
                    </span>
                </div>
            </div>

            <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h3 style="margin-bottom: 1rem;">Alamat Pengiriman</h3>
                <p style="line-height: 1.6;"><?php echo nl2br($order['shipping_address']); ?></p>
            </div>
        </div>
    </div>

    <div class="text-center mt-2">
        <a href="manage_orders.php" class="btn">
            <i class="fas fa-arrow-left"></i> Kembali ke Kelola Pesanan
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>