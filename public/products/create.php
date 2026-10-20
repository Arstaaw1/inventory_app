<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';

$error = '';
$success = '';

// Auto-insert kategori default jika tabel categories masih kosong
$checkCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
if ($checkCategories == 0) {
    $pdo->exec("INSERT INTO categories (name) VALUES ('Elektronik'), ('Aksesori'), ('Peralatan Kantor')");
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
            $stmt = $pdo->prepare("
                INSERT INTO products (category_id, sku, name, price, stock, minimum_stock) 
                VALUES (:category_id, :sku, :name, :price, :stock, :minimum_stock)
            ");
            $stmt->execute([
                'category_id'   => $categoryId,
                'sku'           => $sku,
                'name'          => $name,
                'price'         => $price,
                'stock'         => $stock,
                'minimum_stock' => $minimumStock
            ]);
            $success = "Produk berhasil ditambahkan!";
        } catch (PDOException $e) {
            $error = "Gagal menambah produk: SKU mungkin sudah digunakan.";
        }
    } else {
        $error = "Harap isi semua kolom!";
    }
}
?>

<h3 class="mb-4 fw-bold">Tambah Produk Baru</h3>

<?php if ($error): ?>
    <div class="alert alert-danger py-2"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success py-2"><?= e($success) ?> - <a href="index.php">Lihat Daftar Produk</a></div>
<?php endif; ?>

<div class="card p-4 shadow-sm" style="max-width: 600px;">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">SKU Produk</label>
            <input type="text" name="sku" class="form-control" placeholder="KBD-001" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="name" class="form-control" placeholder="Keyboard Mechanical" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Harga (Rp)</label>
            <input type="number" name="price" class="form-control" min="0" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Stok Awal</label>
            <input type="number" name="stock" class="form-control" min="0" value="0" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Minimum Stok</label>
            <input type="number" name="minimum_stock" class="form-control" min="1" value="5" required>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

</div>
</body>
</html>