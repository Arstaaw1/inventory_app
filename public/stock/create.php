<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';

$error = '';
$success = '';

// Ambil data produk untuk pilihan dropdown
$products = $pdo->query("SELECT id, sku, name, stock FROM products ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $type      = $_POST['type'] ?? '';
    $quantity  = (int)($_POST['quantity'] ?? 0);
    $userId    = $_SESSION['user']['id'];

    if ($productId > 0 && in_array($type, ['IN', 'OUT']) && $quantity > 0) {
        try {
            // 1. Mulai Database Transaction (Slide 27)
            $pdo->beginTransaction();

            // Cek stok produk saat ini dengan Lock (FOR UPDATE)
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $productId]);
            $currentStock = $stmt->fetchColumn();

            if ($currentStock === false) {
                throw new Exception("Produk tidak ditemukan.");
            }

            // Validasi Aturan Bisnis: Barang Keluar tidak boleh melebihi stok (Slide 26)
            if ($type === 'OUT' && $quantity > $currentStock) {
                throw new Exception("Stok tidak mencukupi! Stok saat ini: $currentStock");
            }

            // Update Stok Produk
            $newStock = ($type === 'IN') ? $currentStock + $quantity : $currentStock - $quantity;
            $updateStmt = $pdo->prepare("UPDATE products SET stock = :stock WHERE id = :id");
            $updateStmt->execute(['stock' => $newStock, 'id' => $productId]);

            // Catat Riwayat Pergerakan Stok
            $logStmt = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity) VALUES (:p_id, :u_id, :type, :qty)");
            $logStmt->execute([
                'p_id' => $productId,
                'u_id' => $userId,
                'type' => $type,
                'qty'  => $quantity
            ]);

            // 2. Commit Transaksi jika semua sukses
            $pdo->commit();
            $success = "Transaksi stok berhasil disimpan!";
        } catch (Exception $e) {
            // 3. Rollback jika terjadi kesalahan/stok kurang
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    } else {
        $error = "Harap isi seluruh input dengan benar!";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm p-4">
            <h4 class="fw-bold mb-3"><i class="bi bi-box-arrow-in-right me-2"></i>Catat Barang Masuk / Keluar</h4>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= e($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success py-2"><?= e($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Produk</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                [<?= e($p['sku']) ?>] <?= e($p['name']) ?> (Stok: <?= $p['stock'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tipe Transaksi</label>
                    <select name="type" class="form-select" required>
                        <option value="IN">Barang Masuk (IN)</option>
                        <option value="OUT">Barang Keluar (OUT)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jumlah (Quantity)</label>
                    <input type="number" name="quantity" class="form-control" min="1" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>