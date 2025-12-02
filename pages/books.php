<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$page_title = 'Katalog Buku';

// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['q']) ? clean_input($_GET['q']) : '';

// Build query
$query = "SELECT b.*, c.name as category_name 
          FROM books b 
          LEFT JOIN categories c ON b.category_id = c.id 
          WHERE 1=1";

if ($category_id > 0) {
    $query .= " AND b.category_id = $category_id";
}

if (!empty($search)) {
    $query .= " AND (b.title LIKE '%$search%' OR b.author LIKE '%$search%' OR b.publisher LIKE '%$search%')";
}

$query .= " ORDER BY b.title ASC";
$books = mysqli_query($conn, $query);

// Get all categories for filter
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories = mysqli_query($conn, $categories_query);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-2">
    <h1 class="text-center mb-2">Katalog Buku</h1>

    <!-- Filter Section -->
    <div class="filter-section" style="background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
        <form method="GET" action="">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                    <label>Kategori</label>
                    <select name="category" class="form-control">
                        <option value="0">Semua Kategori</option>
                        <?php
                        mysqli_data_seek($categories, 0);
                        while ($cat = mysqli_fetch_assoc($categories)):
                        ?>
                            <option value="<?php echo $cat['id']; ?>"
                                <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group" style="flex: 2; min-width: 300px; margin: 0;">
                    <label>Pencarian</label>
                    <input type="text" name="q" placeholder="Cari buku, penulis, atau penerbit..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="books.php" class="btn" style="margin-left: 0.5rem;">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Books Grid -->
    <?php if (mysqli_num_rows($books) > 0): ?>
        <div class="book-grid">
            <?php while ($book = mysqli_fetch_assoc($books)): ?>
                <div class="book-card">
                    <img src="../assets/images/books/<?php echo $book['image'] ?: 'default.jpg'; ?>"
                        alt="<?php echo $book['title']; ?>"
                        onerror="this.src='../assets/images/books/default.jpg'">
                    <div class="book-card-content">
                        <h3><?php echo $book['title']; ?></h3>
                        <p class="book-author">oleh <?php echo $book['author']; ?></p>
                        <span class="badge" style="background: var(--accent-color); color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.85rem;">
                            <?php echo $book['category_name']; ?>
                        </span>
                        <p class="book-price"><?php echo format_rupiah($book['price']); ?></p>
                        <p class="book-stock">Stok: <?php echo $book['stock']; ?></p>
                        <div class="book-actions">
                            <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="btn btn-detail">
                                <i class="fas fa-info-circle"></i> Detail
                            </a>
                            <?php if (is_logged_in() && $book['stock'] > 0): ?>
                                <button onclick="addToCart(<?php echo $book['id']; ?>)" class="btn btn-cart">
                                    <i class="fas fa-cart-plus"></i> Keranjang
                                </button>
                            <?php elseif ($book['stock'] > 0): ?>
                                <a href="login.php" class="btn btn-cart">
                                    <i class="fas fa-cart-plus"></i> Keranjang
                                </a>
                            <?php else: ?>
                                <button class="btn btn-cart" disabled style="opacity: 0.5; cursor: not-allowed;">
                                    <i class="fas fa-times"></i> Habis
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center" style="padding: 3rem;">
            <i class="fas fa-search" style="font-size: 4rem; color: #ccc;"></i>
            <h3 style="margin-top: 1rem;">Tidak ada buku ditemukan</h3>
            <p>Coba gunakan kata kunci lain atau ubah filter pencarian</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>