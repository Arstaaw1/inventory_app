<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';

$error = '';
$success = '';

$id = (int)($_GET['id'] ?? 0);

// Ambil data produk yang akan diedit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='alert alert-danger'>Produk tidak ditemukan! <a href='index.php'>Kembali</a></div>";
    exit;
}

// Ambil data kategori
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId   = (int)($_POST['category_id'] ?? 0);
    $sku          = trim($_POST['sku'] ?? '');
    $name         = trim($_POST['name'] ?? '');
    $price        = (float)($_POST['price'] ?? 0);
    $stock        = (int)($_POST['stock'] ?? 0);
    $minimumStock = (int)($_POST['minimum_stock'] ?? 5);

    if ($categoryId > 0 && !empty($sku) && !empty($name)) {
        try {
            $stmtUpdate = $pdo->prepare("
                UPDATE products 
                SET category_id = :category_id, 
                    sku = :sku, 
                    name = :name, 
                    price = :price, 
                    stock = :stock, 
                    minimum_stock = :minimum_stock 
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                'category_id'   => $categoryId,
                'sku'           => $sku,
                'name'          => $name,
                'price'         => $price,
                'stock'         => $stock,
                'minimum_stock' => $minimumStock,
                'id'            => $id
            ]);
            $success = "Data produk berhasil diperbarui!";
            
            // Refresh data produk terbaru
            $stmt->execute(['id' => $id]);
            $product = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Gagal memperbarui produk: SKU mungkin sudah digunakan.";
        }
    } else {
        $error = "Harap isi semua kolom wajib!";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Produk</h3>
        <p class="text-muted small mb-0">Ubah informasi produk, harga, maupun stok.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger py-2"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success py-2"><?= e($success) ?> - <a href="index.php">Kembali ke Daftar Produk</a></div>
<?php endif; ?>

<div class="card border-0 shadow-sm p-4" style="max-width: 600px; border-radius: 12px;">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label font-medium">Kategori</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($c['id'] == $product['category_id']) ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label font-medium">SKU Produk</label>
            <input type="text" name="sku" class="form-control" value="<?= e($product['sku']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label font-medium">Nama Produk</label>
            <input type="text" name="name" class="form-control" value="<?= e($product['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label font-medium">Harga (Rp)</label>
            <input type="number" name="price" class="form-control" min="0" value="<?= $product['price'] ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label font-medium">Stok</label>
            <input type="number" name="stock" class="form-control" min="0" value="<?= $product['stock'] ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label font-medium">Minimum Stok</label>
            <input type="number" name="minimum_stock" class="form-control" min="1" value="<?= $product['minimum_stock'] ?>" required>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary px-4" style="border-radius: 8px;">Batal</a>
        </div>
    </form>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>