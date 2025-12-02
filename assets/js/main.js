// Main JavaScript

// Close flash messages
document.addEventListener("DOMContentLoaded", function () {
  const closeFlash = document.querySelector(".close-flash");
  if (closeFlash) {
    closeFlash.addEventListener("click", function () {
      this.parentElement.style.display = "none";
    });

    // Auto close after 5 seconds
    setTimeout(() => {
      const flashMsg = document.querySelector(".flash-message");
      if (flashMsg) {
        flashMsg.style.display = "none";
      }
    }, 5000);
  }
});

// Mobile navigation toggle
const navToggle = document.querySelector(".nav-toggle");
const navLinks = document.querySelector(".nav-links");

if (navToggle) {
  navToggle.addEventListener("click", function () {
    navLinks.style.display =
      navLinks.style.display === "flex" ? "none" : "flex";
  });
}

// Confirm delete
function confirmDelete(
  message = "Apakah Anda yakin ingin menghapus data ini?"
) {
  return confirm(message);
}

// Add to cart function
function addToCart(bookId) {
  fetch("/Munif/pages/add_to_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "book_id=" + bookId,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Buku berhasil ditambahkan ke keranjang!");
        updateCartCount();
      } else {
        alert(data.message || "Gagal menambahkan ke keranjang");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Terjadi kesalahan");
    });
}

// Update cart count
function updateCartCount() {
  fetch("/Munif/pages/get_cart_count.php")
    .then((response) => response.json())
    .then((data) => {
      const cartCount = document.querySelector(".cart-count");
      if (cartCount) {
        cartCount.textContent = data.count;
      }
    });
}

// Update quantity in cart
function updateQuantity(cartId, quantity) {
  if (quantity < 1) {
    if (!confirm("Hapus item dari keranjang?")) {
      return;
    }
  }

  fetch("/Munif/pages/update_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "cart_id=" + cartId + "&quantity=" + quantity,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert(data.message || "Gagal mengupdate keranjang");
      }
    });
}

// Remove from cart
function removeFromCart(cartId) {
  if (!confirm("Hapus item dari keranjang?")) {
    return;
  }

  fetch("/Munif/pages/remove_from_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "cart_id=" + cartId,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert(data.message || "Gagal menghapus dari keranjang");
      }
    });
}

// Image preview for upload
function previewImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const preview = document.getElementById("image-preview");
      if (preview) {
        preview.src = e.target.result;
        preview.style.display = "block";
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}
