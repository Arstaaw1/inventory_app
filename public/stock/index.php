<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';

// Pengaturan Pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Hitung total data riwayat stok
$totalStmt = $pdo->query("SELECT COUNT(*) FROM stock_movements");
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Query data riwayat stok dengan LIMIT dan OFFSET
$stmt = $pdo->prepare("
    SELECT sm.*, p.name AS product_name, p.sku, u.name AS user_name 
    FROM stock_movements sm
    LEFT JOIN products p ON p.id = sm.product_id
    LEFT JOIN users u ON u.id = sm.user_id
    ORDER BY sm.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$movements = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1"><i class="bi bi-arrow-left-right me-2 text-success"></i>Riwayat Transaksi Stok</h3>
        <p class="text-muted small mb-0">Catatan pergerakan barang masuk (IN) dan keluar (OUT).</p>
    </div>
    <a href="/inventory-app/public/stock/create.php" class="btn btn-success px-3 shadow-sm" style="border-radius: 8px;">
        <i class="bi bi-plus-lg me-1"></i> Catat Transaksi
    </a>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary">
                <tr class="text-uppercase fs-7">
                    <th class="ps-4 py-3">Waktu</th>
                    <th class="py-3">SKU</th>
                    <th class="py-3">Produk</th>
                    <th class="py-3">Tipe</th>
                    <th class="py-3">Jumlah</th>
                    <th class="py-3 pe-4">Petugas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movements)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-clock-history fs-1 d-block text-secondary mb-2"></i>
                            Belum ada riwayat transaksi stok.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movements as $m): ?>
                        <tr>
                            <td class="ps-4 text-muted small"><?= date('d M Y, H:i', strtotime($m['created_at'])) ?></td>
                            <td><span class="badge bg-light text-dark font-monospace border"><?= e($m['sku'] ?? '-') ?></span></td>
                            <td class="fw-semibold text-dark"><?= e($m['product_name'] ?? '-') ?></td>
                            <td>
                                <?php if ($m['type'] === 'IN'): ?>
                                    <span class="badge bg-success-subtle text-success px-2 py-1">
                                        <i class="bi bi-arrow-down-left me-1"></i>Masuk (IN)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1">
                                        <i class="bi bi-arrow-up-right me-1"></i>Keluar (OUT)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-dark"><?= $m['quantity'] ?></td>
                            <td class="pe-4 text-muted"><i class="bi bi-person me-1"></i><?= e($m['user_name'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Tombol Pagination -->
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