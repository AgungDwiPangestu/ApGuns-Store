<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$page_title = 'Import Buku dari API';

if (!is_logged_in() || !is_admin()) {
    set_flash('Akses ditolak!', 'error');
    redirect('/Munif/pages/login.php');
}

// Get categories for mapping
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-2">
    <h1 class="text-center mb-2">📚 Import Buku dari API</h1>

    <div class="card mb-2">
        <h3>Cara Menggunakan:</h3>
        <ol>
            <li>Masukkan kata kunci pencarian (contoh: "programming", "novel indonesia", "business")</li>
            <li>Pilih kategori untuk buku yang akan diimport</li>
            <li>Klik "Cari Buku" untuk melihat hasil dari Google Books API</li>
            <li>Pilih buku yang ingin diimport dengan centang checkbox</li>
            <li>Klik "Import Buku Terpilih" untuk menambahkan ke database</li>
        </ol>
        <p><strong>Note:</strong> Gambar cover akan otomatis didownload dari API</p>
    </div>

    <!-- Search Form -->
    <div class="card mb-2">
        <h3>Pencarian Buku</h3>
        <form id="searchForm" method="POST">
            <div class="form-group">
                <label>Kata Kunci Pencarian *</label>
                <input type="text" name="search_query" id="search_query" class="form-control"
                    placeholder="Contoh: programming, novel, business, science" required>
                <small>Tips: Gunakan bahasa Inggris untuk hasil lebih banyak</small>
            </div>

            <div class="form-group">
                <label>Kategori Default *</label>
                <select name="default_category" id="default_category" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php mysqli_data_seek($categories, 0); ?>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Jumlah Buku</label>
                <select name="max_results" id="max_results" class="form-control">
                    <option value="10">10 buku</option>
                    <option value="20" selected>20 buku</option>
                    <option value="30">30 buku</option>
                    <option value="40">40 buku</option>
                </select>
            </div>

            <button type="button" onclick="searchBooks()" class="btn btn-primary">
                <i class="fas fa-search"></i> Cari Buku
            </button>
        </form>
    </div>

    <!-- Search Results -->
    <div id="searchResults" style="display: none;">
        <div class="card">
            <h3>Hasil Pencarian (<span id="resultCount">0</span> buku ditemukan)</h3>
            <div id="loadingMessage" style="text-align: center; padding: 20px; display: none;">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Mencari buku...</p>
            </div>
            <form id="importForm" method="POST" action="process_import_books.php">
                <input type="hidden" name="category_id" id="import_category">
                <div id="booksList"></div>
                <div id="importButton" style="display: none; text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-download"></i> Import Buku Terpilih
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .book-item {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 5px;
        display: flex;
        gap: 15px;
        background: white;
    }

    .book-item:hover {
        background: #f8f9fa;
        border-color: #2c3e50;
    }

    .book-item img {
        width: 100px;
        height: 140px;
        object-fit: cover;
        border-radius: 3px;
    }

    .book-item-content {
        flex: 1;
    }

    .book-item-title {
        font-size: 18px;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .book-item-author {
        color: #666;
        margin-bottom: 10px;
    }

    .book-item-info {
        font-size: 14px;
        color: #888;
        margin-bottom: 10px;
    }

    .book-item-description {
        font-size: 14px;
        color: #555;
        line-height: 1.5;
        max-height: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .book-item-checkbox {
        display: flex;
        align-items: center;
        padding: 10px;
    }

    .book-item-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .no-image {
        width: 100px;
        height: 140px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        text-align: center;
        border-radius: 3px;
    }
</style>

<script>
    function searchBooks() {
        const query = document.getElementById('search_query').value;
        const category = document.getElementById('default_category').value;
        const maxResults = document.getElementById('max_results').value;

        if (!query || !category) {
            alert('Harap isi kata kunci dan pilih kategori!');
            return;
        }

        // Show loading
        document.getElementById('searchResults').style.display = 'block';
        document.getElementById('loadingMessage').style.display = 'block';
        document.getElementById('booksList').innerHTML = '';
        document.getElementById('importButton').style.display = 'none';
        document.getElementById('import_category').value = category;

        // Call Google Books API
        const apiUrl = `https://www.googleapis.com/books/v1/volumes?q=${encodeURIComponent(query)}&maxResults=${maxResults}&langRestrict=en&printType=books`;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingMessage').style.display = 'none';

                if (data.items && data.items.length > 0) {
                    displayBooks(data.items);
                    document.getElementById('resultCount').textContent = data.items.length;
                    document.getElementById('importButton').style.display = 'block';
                } else {
                    document.getElementById('booksList').innerHTML = '<p style="text-align: center; padding: 20px;">Tidak ada buku ditemukan. Coba kata kunci lain.</p>';
                    document.getElementById('resultCount').textContent = '0';
                }
            })
            .catch(error => {
                document.getElementById('loadingMessage').style.display = 'none';
                document.getElementById('booksList').innerHTML = '<p style="text-align: center; padding: 20px; color: red;">Error: ' + error.message + '</p>';
            });
    }

    function displayBooks(books) {
        const booksList = document.getElementById('booksList');
        let html = '';

        books.forEach((item, index) => {
            const book = item.volumeInfo;
            const title = book.title || 'No Title';
            const authors = book.authors ? book.authors.join(', ') : 'Unknown Author';
            const publisher = book.publisher || 'Unknown';
            const publishedDate = book.publishedDate || 'N/A';
            const description = book.description || 'No description available';
            const isbn = book.industryIdentifiers ? book.industryIdentifiers.find(id => id.type === 'ISBN_13')?.identifier || book.industryIdentifiers[0]?.identifier || '' : '';
            const thumbnail = book.imageLinks?.thumbnail || book.imageLinks?.smallThumbnail || '';
            const pageCount = book.pageCount || 0;

            // Generate a reasonable price (you can adjust this logic)
            const basePrice = 50000 + (pageCount * 100);
            const price = Math.round(basePrice / 1000) * 1000; // Round to nearest thousand

            html += `
            <div class="book-item">
                <div class="book-item-checkbox">
                    <input type="checkbox" name="books[]" value='${JSON.stringify({
                        title: title,
                        author: authors,
                        isbn: isbn,
                        publisher: publisher,
                        publication_year: publishedDate.substring(0, 4),
                        description: description.substring(0, 500),
                        price: price,
                        stock: 10,
                        image_url: thumbnail
                    })}' checked>
                </div>
                ${thumbnail ? 
                    `<img src="${thumbnail}" alt="${title}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                     <div class="no-image" style="display: none;">No Cover</div>` : 
                    `<div class="no-image">No Cover</div>`
                }
                <div class="book-item-content">
                    <div class="book-item-title">${title}</div>
                    <div class="book-item-author">oleh ${authors}</div>
                    <div class="book-item-info">
                        📚 ${publisher} | 📅 ${publishedDate} | 📖 ${pageCount} halaman | 
                        💰 ${formatRupiah(price)} | 📦 Stok: 10
                    </div>
                    <div class="book-item-description">${description.substring(0, 200)}...</div>
                </div>
            </div>
        `;
        });

        booksList.innerHTML = html;
    }

    function formatRupiah(amount) {
        return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Handle form submission
    document.getElementById('importForm').addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('input[name="books[]"]:checked');
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu buku untuk diimport!');
        } else {
            if (!confirm(`Import ${checkedBoxes.length} buku ke database?`)) {
                e.preventDefault();
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>