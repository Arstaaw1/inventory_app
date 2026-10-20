<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../views/header.php';

$totalProducts = $pdo->query("SELECT COUNT(id) FROM products")->fetchColumn();
$inventoryValue = $pdo->query("SELECT SUM(stock * price) FROM products")->fetchColumn() ?? 0;
$lowStock = $pdo->query("SELECT COUNT(id) FROM products WHERE stock <= minimum_stock")->fetchColumn();

$totalIn = $pdo->query("SELECT SUM(quantity) FROM stock_movements WHERE type = 'IN'")->fetchColumn() ?? 0;
$totalOut = $pdo->query("SELECT SUM(quantity) FROM stock_movements WHERE type = 'OUT'")->fetchColumn() ?? 0;

// Ambil 5 transaksi terakhir untuk widget aktivitas
$recentMovements = $pdo->query("
    SELECT sm.*, p.name AS product_name, p.sku 
    FROM stock_movements sm
    LEFT JOIN products p ON p.id = sm.product_id
    ORDER BY sm.created_at DESC LIMIT 5
")->fetchAll();
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Dashboard Overview</h3>
        <p class="text-muted small mb-0">Pantau pergerakan stok dan statistik inventaris secara terpusat.</p>
    </div>
    <div class="text-end text-muted small">
        <i class="bi bi-calendar3 me-1"></i> Real-time System
    </div>
</div>

<!-- Grid Top Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-stat bg-grad-blue text-white p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Total Produk</span>
                    <h1 class="fw-bold mb-0 mt-2"><?= number_format($totalProducts) ?></h1>
                </div>
                <div class="icon-box">
                    <i class="bi bi-box-seam fs-2 text-white"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat bg-grad-emerald text-white p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Nilai Total Inventory</span>
                    <h2 class="fw-bold mb-0 mt-2">Rp <?= number_format($inventoryValue, 0, ',', '.') ?></h2>
                </div>
                <div class="icon-box">
                    <i class="bi bi-wallet2 fs-2 text-white"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat bg-grad-amber text-white p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Stok Rendah</span>
                    <h1 class="fw-bold mb-0 mt-2"><?= number_format($lowStock) ?></h1>
                </div>
                <div class="icon-box">
                    <i class="bi bi-exclamation-triangle fs-2 text-white"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats & Recent Activity -->
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
            <h5 class="fw-bold mb-4 text-dark">Ringkasan Alur Stok</h5>
            
            <div class="d-flex align-items-center p-3 rounded-3 mb-3" style="background-color: #f0fdf4; border: 1px solid #dcfce7;">
                <div class="bg-success text-white rounded-3 p-3 me-3">
                    <i class="bi bi-arrow-down-left fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Barang Masuk (IN)</span>
                    <h4 class="fw-bold text-success mb-0"><?= number_format($totalIn) ?> <small class="fs-6 text-muted font-normal">unit</small></h4>
                </div>
            </div>

            <div class="d-flex align-items-center p-3 rounded-3" style="background-color: #fef2f2; border: 1px solid #fee2e2;">
                <div class="bg-danger text-white rounded-3 p-3 me-3">
                    <i class="bi bi-arrow-up-right fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Barang Keluar (OUT)</span>
                    <h4 class="fw-bold text-danger mb-0"><?= number_format($totalOut) ?> <small class="fs-6 text-muted font-normal">unit</small></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Log Table Widget -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark">Aktivitas Stok Terkini</h5>
                <a href="/inventory-app/public/stock/index.php" class="btn btn-sm btn-light rounded-pill px-3">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-muted small text-uppercase" style="background-color: #f8fafc;">
                        <tr>
                            <th class="border-0">SKU</th>
                            <th class="border-0">Produk</th>
                            <th class="border-0">Tipe</th>
                            <th class="border-0">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentMovements)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentMovements as $m): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark font-monospace border"><?= e($m['sku'] ?? '-') ?></span></td>
                                    <td class="fw-semibold text-dark"><?= e($m['product_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($m['type'] === 'IN'): ?>
                                            <span class="badge bg-success-subtle text-success px-2 py-1">IN</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1">OUT</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold"><?= $m['quantity'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>