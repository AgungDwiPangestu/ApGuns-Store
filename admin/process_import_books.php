<?php
require_once '../config/db.php';
require_once '../config/functions.php';

if (!is_logged_in() || !is_admin()) {
    set_flash('Akses ditolak!', 'error');
    redirect('/Munif/pages/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Munif/admin/import_books.php');
}

$category_id = (int)$_POST['category_id'];
$books = $_POST['books'] ?? [];

if (empty($books)) {
    set_flash('Tidak ada buku yang dipilih untuk diimport!', 'error');
    redirect('/Munif/admin/import_books.php');
}

$success_count = 0;
$error_count = 0;
$errors = [];

foreach ($books as $book_json) {
    $book = json_decode($book_json, true);

    // Clean and escape data with STRICT length limits to prevent errors
    $title = mysqli_real_escape_string($conn, substr(trim($book['title'] ?? ''), 0, 255));
    $author = mysqli_real_escape_string($conn, substr(trim($book['author'] ?? ''), 0, 199)); // 199 to be extra safe
    $isbn = mysqli_real_escape_string($conn, substr(trim($book['isbn'] ?? ''), 0, 49)); // 49 to be extra safe
    $publisher = mysqli_real_escape_string($conn, substr(trim($book['publisher'] ?? ''), 0, 199)); // 199 to be extra safe

    // Validasi publication_year: MySQL YEAR hanya menerima 1901-2155
    $year = (int)($book['publication_year'] ?? 0);
    if ($year < 1901 || $year > 2155) {
        $publication_year = date('Y'); // Default ke tahun sekarang jika invalid
    } else {
        $publication_year = $year;
    }

    $description = mysqli_real_escape_string($conn, substr(trim($book['description'] ?? ''), 0, 1000));
    $price = (float)($book['price'] ?? 0);
    $stock = (int)($book['stock'] ?? 0);
    $image_url = $book['image_url'] ?? '';

    // Download and save image
    $image_name = '';
    if (!empty($image_url)) {
        $image_name = download_book_image($image_url, $isbn);
    }

    // Validasi data critical - skip jika title kosong
    if (empty($title)) {
        $error_count++;
        $errors[] = "Buku dilewati: Judul kosong";
        continue;
    }

    // Generate ISBN jika kosong
    if (empty($isbn)) {
        $isbn = 'ISBN-' . time() . '-' . rand(1000, 9999);
    }

    // Check if book already exists by ISBN or title
    $check_query = "SELECT id FROM books WHERE (isbn != '' AND isbn = '$isbn') OR title = '$title' LIMIT 1";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $error_count++;
        $errors[] = "Buku '$title' sudah ada di database";
        continue;
    }

    // Insert book
    $insert_query = "INSERT INTO books (title, author, isbn, category_id, publisher, publication_year, description, price, stock, image) 
                     VALUES ('$title', '$author', '$isbn', $category_id, '$publisher', $publication_year, '$description', $price, $stock, '$image_name')";

    if (mysqli_query($conn, $insert_query)) {
        $success_count++;
    } else {
        $error_count++;
        $errors[] = "Gagal import '$title': " . mysqli_error($conn);
    }
}

// Set flash message
if ($success_count > 0) {
    $message = "Berhasil import $success_count buku!";
    if ($error_count > 0) {
        $message .= " ($error_count gagal/duplikat)";
    }
    set_flash($message, 'success');
} else {
    set_flash("Gagal import buku. Semua buku mungkin sudah ada di database.", 'error');
}

redirect('/Munif/admin/manage_books.php');

/**
 * Download book cover image from URL
 */
function download_book_image($url, $isbn)
{
    // Replace http with https for better security
    $url = str_replace('http://', 'https://', $url);

    // Create filename
    $filename = 'imported_' . preg_replace('/[^a-zA-Z0-9]/', '_', $isbn) . '_' . time() . '.jpg';
    $upload_path = __DIR__ . '/../assets/images/books/' . $filename;

    // Try to download image
    try {
        $image_data = @file_get_contents($url);

        if ($image_data !== false) {
            file_put_contents($upload_path, $image_data);
            return $filename;
        }
    } catch (Exception $e) {
        // If download fails, return empty string (will use default image)
    }

    return '';
}
