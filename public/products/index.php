<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';

// Fitur Hapus Produk
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmtDel = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmtDel->execute(['id' => $id]);
            echo "<script>alert('Produk berhasil dihapus!'); window.location.href='index.php';</script>";
            exit;
        } catch (PDOException $e) {
            echo "<script>alert('Gagal menghapus produk: Produk mungkin masih terkait dengan transaksi stok.'); window.location.href='index.php';</script>";
            exit;
        }
    }
}

// Pagination
$limit = 4;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$totalStmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    LEFT JOIN categories c ON c.id = p.category_id 
    ORDER BY p.id DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1"><i class="bi bi-boxes me-2 text-primary"></i>Daftar Produk</h3>
        <p class="text-muted small mb-0">Kelola inventaris dan stok barang secara real-time.</p>
    </div>
    <a href="/inventory-app/public/products/create.php" class="btn btn-primary px-3 shadow-sm" style="border-radius: 8px;">
        <i class="bi bi-plus-lg me-1"></i> Tambah Produk
    </a>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary">
                <tr class="text-uppercase fs-7">
                    <th class="ps-4 py-3">SKU</th>
                    <th class="py-3">Nama Produk</th>
                    <th class="py-3">Kategori</th>
                    <th class="py-3">Harga</th>
                    <th class="py-3">Stok</th>
                    <th class="py-3">Min. Stok</th>
                    <th class="py-3 pe-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block text-secondary mb-2"></i>
                            Belum ada data produk tersedia.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark font-monospace border px-2 py-1"><?= e($p['sku']) ?></span>
                            </td>
                            <td class="fw-semibold text-dark"><?= e($p['name']) ?></td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary fw-medium px-2 py-1">
                                    <?= e($p['category_name'] ?? 'Uncategorized') ?>
                                </span>
                            </td>
                            <td class="fw-medium text-dark">Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                            <td>
                                <?php if ($p['stock'] <= $p['minimum_stock']): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-exclamation-circle me-1"></i><?= $p['stock'] ?> (Rendah)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success px-2 py-1"><?= $p['stock'] ?> unit</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted"><?= $p['minimum_stock'] ?></td>
                            <td class="pe-4 text-center">
                                <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning me-1">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <a href="index.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus produk ini?');">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white border-0 py-3">
            <nav>
                <ul class="pagination pagination-sm justify-content-end mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>