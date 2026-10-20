<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/escape.php';
check_login();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory App</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { 
            background-color: #f8fafc; 
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #334155;
        }
        .navbar { 
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
        }
        .nav-link { font-weight: 500; opacity: 0.8; transition: all 0.2s; }
        .nav-link:hover, .nav-link.active { opacity: 1; color: #fff !important; }
        
        .card-stat {
            border: none;
            border-radius: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }
        .card-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
        }
        
        .bg-grad-blue { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
        .bg-grad-emerald { background: linear-gradient(135deg, #059669 0%, #047857 100%); }
        .bg-grad-amber { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); }
        
        .icon-box {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark py-3 mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold fs-4 d-flex align-items-center" href="/inventory-app/public/dashboard.php">
      <div class="bg-primary bg-gradient text-white rounded-3 p-2 me-2 d-inline-flex">
        <i class="bi bi-box-seam-fill"></i>
      </div>
      Inventory<span class="text-primary ms-1">App</span>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto ms-lg-4">
        <li class="nav-item">
          <a class="nav-link px-3 active" href="/inventory-app/public/dashboard.php">
            <i class="bi bi-grid-1x2-fill me-2"></i>Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3" href="/inventory-app/public/products/index.php">
            <i class="bi bi-boxes me-2"></i>Produk
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3" href="/inventory-app/public/stock/index.php">
            <i class="bi bi-arrow-left-right me-2"></i>Transaksi Stok
          </a>
        </li>
      </ul>
      <div class="d-flex align-items-center text-white gap-3">
        <div class="d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-1 border border-white border-opacity-10">
            <i class="bi bi-person-circle me-2 text-info"></i>
            <span class="small font-medium me-2"><?= e($_SESSION['user']['name'] ?? $_SESSION['user']['username']) ?></span>
            <span class="badge bg-primary rounded-pill uppercase" style="font-size: 10px;"><?= e($_SESSION['user']['role']) ?></span>
        </div>
        <a href="/inventory-app/public/logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1 font-medium">
          <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
      </div>
    </div>
  </div>
</nav>
<div class="container pb-5"></div>