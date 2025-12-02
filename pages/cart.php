<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$page_title = 'Keranjang Belanja';

// Check if user is logged in
if (!is_logged_in()) {
    set_flash('Silakan login terlebih dahulu!', 'error');
    redirect('/Munif/pages/login.php');
}

$user_id = $_SESSION['user_id'];

// Get cart items
$query = "SELECT c.*, b.title, b.author, b.price, b.stock, b.image 
          FROM cart c 
          JOIN books b ON c.book_id = b.id 
          WHERE c.user_id = $user_id 
          ORDER BY c.added_at DESC";
$cart_items = mysqli_query($conn, $query);

// Calculate total
$total = 0;

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-2">
    <h1 class="text-center mb-2">Keranjang Belanja</h1>

    <?php if (mysqli_num_rows($cart_items) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($cart_items)):
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="../assets/images/books/<?php echo $item['image'] ?: 'default.jpg'; ?>"
                                        alt="<?php echo $item['title']; ?>"
                                        onerror="this.src='../assets/images/books/default.jpg'"
                                        style="width: 60px; height: 80px; object-fit: cover; border-radius: 5px;">
                                    <div>
                                        <strong><?php echo $item['title']; ?></strong><br>
                                        <small>oleh <?php echo $item['author']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo format_rupiah($item['price']); ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <button onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)"
                                        class="btn" style="padding: 0.25rem 0.75rem;">-</button>
                                    <input type="number" value="<?php echo $item['quantity']; ?>"
                                        min="1" max="<?php echo $item['stock']; ?>"
                                        style="width: 60px; text-align: center; padding: 0.25rem;"
                                        onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                                    <button onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)"
                                        class="btn" style="padding: 0.25rem 0.75rem;"
                                        <?php echo $item['quantity'] >= $item['stock'] ? 'disabled' : ''; ?>>+</button>
                                </div>
                                <small style="color: #666;">Stok: <?php echo $item['stock']; ?></small>
                            </td>
                            <td><strong><?php echo format_rupiah($subtotal); ?></strong></td>
                            <td>
                                <button onclick="removeFromCart(<?php echo $item['id']; ?>)"
                                    class="btn btn-danger" style="background: var(--secondary-color);">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                        <td colspan="2"><strong style="font-size: 1.3rem; color: var(--secondary-color);">
                                <?php echo format_rupiah($total); ?>
                            </strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="display: flex; justify-content: space-between; margin-top: 2rem;">
            <a href="books.php" class="btn">
                <i class="fas fa-arrow-left"></i> Lanjut Belanja
            </a>
            <a href="checkout.php" class="btn btn-success">
                Checkout <i class="fas fa-arrow-right"></i>
            </a>
        </div>

    <?php else: ?>
        <div class="text-center" style="padding: 3rem; background: white; border-radius: 10px;">
            <i class="fas fa-shopping-cart" style="font-size: 4rem; color: #ccc;"></i>
            <h3 style="margin-top: 1rem;">Keranjang Belanja Kosong</h3>
            <p>Belum ada buku dalam keranjang Anda</p>
            <a href="books.php" class="btn mt-1">Mulai Belanja</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>