<?php
include '../config/db.php';
session_start();

// Admin Security Check could go here

// GLOBAL STATS
$global_stats_q = mysqli_query($conn, "SELECT 
    COUNT(DISTINCT franchise_id) as active_stores, 
    SUM(grand_total) as total_revenue, 
    SUM(remaining_amount) as total_debt 
    FROM bills");
$global_stats = mysqli_fetch_assoc($global_stats_q);

$where_clause = "WHERE 1";
if (isset($_GET['f_id']) && !empty($_GET['f_id'])) {
    $f_id = mysqli_real_escape_string($conn, $_GET['f_id']);
    $where_clause = "WHERE f.id = '$f_id'";
}

$report_query = "SELECT 
    f.id, f.store_name, f.owner_name, f.city, f.phone,
    COUNT(b.id) as total_bills,
    SUM(b.grand_total) as gross_sales,
    SUM(b.paid_amount) as collected_amount,
    SUM(b.remaining_amount) as outstanding_debt
    FROM franchises f
    LEFT JOIN bills b ON f.id = b.franchise_id
    $where_clause
    GROUP BY f.id
    ORDER BY outstanding_debt DESC";
$reports = mysqli_query($conn, $report_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Terminal | Franchise Ledger</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
        .stat-card { background: white; border: 1px solid #000; border-radius: 0; position: relative; }
        .stat-card::after { content: ""; position: absolute; bottom: 0; left: 0; width: 100%; height: 4px; background: #000; }
        .table-admin th { background: #0f172a; color: white; text-transform: uppercase; font-size: 10px; padding: 15px; border: 1px solid #334155; }
        .table-admin td { border: 1px solid #e2e8f0; padding: 12px; font-size: 13px; font-weight: 600; }
        .input-admin { border: 1px solid #000; padding: 10px; font-weight: bold; outline: none; }
    </style>
</head>
<body class="p-8">

<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex justify-between items-center bg-white p-6 border-2 border-black shadow-[8px_8px_0px_rgba(0,0,0,1)]" data-aos="fade-down">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tighter">Centralized Franchise Ledger</h1>
            <p class="text-xs font-bold text-slate-500 uppercase">Master Admin Panel</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="stat-card p-6" data-aos="zoom-in" data-aos-delay="100">
            <p class="text-[10px] font-black text-slate-400 uppercase">Active Branches</p>
            <h2 class="text-3xl font-black"><?php echo $global_stats['active_stores']; ?></h2>
        </div>
        <div class="stat-card p-6 !after:bg-blue-600" data-aos="zoom-in" data-aos-delay="200">
            <p class="text-[10px] font-black text-slate-400 uppercase">Total Sales</p>
            <h2 class="text-3xl font-black text-blue-600">₹<?php echo number_format($global_stats['total_revenue'], 2); ?></h2>
        </div>
        <div class="stat-card p-6 !after:bg-red-600" data-aos="zoom-in" data-aos-delay="300">
            <p class="text-[10px] font-black text-slate-400 uppercase">Total Debt</p>
            <h2 class="text-3xl font-black text-red-600">₹<?php echo number_format($global_stats['total_debt'], 2); ?></h2>
        </div>
    </div>

    <div class="bg-white border-2 border-black p-8 shadow-xl" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <h2 class="text-xl font-black uppercase tracking-tighter border-b-4 border-black">Franchise Performance</h2>
            <form action="" method="GET" class="flex gap-2">
                <select name="f_id" class="input-admin min-w-[200px]">
                    <option value="">All Franchises</option>
                    <?php 
                    $f_list = mysqli_query($conn, "SELECT id, store_name FROM franchises");
                    while($fl = mysqli_fetch_assoc($f_list)) {
                        $sel = (isset($_GET['f_id']) && $_GET['f_id'] == $fl['id']) ? 'selected' : '';
                        echo "<option value='".$fl['id']."' $sel>".$fl['store_name']."</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="bg-black text-white px-6 font-black uppercase text-xs">Filter</button>
            </form>
        </div>

        <table class="w-full table-admin border-collapse">
            <thead>
                <tr>
                    <th>Store Detail</th>
                    <th class="text-center">Invoices</th>
                    <th class="text-right">Sales</th>
                    <th class="text-right">Collected</th>
                    <th class="text-right text-red-400">Debt</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($reports)): ?>
                <tr>
                    <td>
                        <div class="text-sm font-black uppercase"><?php echo $row['store_name']; ?></div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase"><?php echo $row['owner_name']; ?></div>
                    </td>
                    <td class="text-center"><?php echo $row['total_bills'] ?? 0; ?></td>
                    <td class="text-right">₹<?php echo number_format($row['gross_sales'] ?? 0, 2); ?></td>
                    <td class="text-right text-green-700">₹<?php echo number_format($row['collected_amount'] ?? 0, 2); ?></td>
                    <td class="text-right text-red-600 bg-red-50">₹<?php echo number_format($row['outstanding_debt'] ?? 0, 2); ?></td>
                    <td class="text-center">
                        <a href="admin_franchise_drilldown.php?id=<?php echo $row['id']; ?>" class="bg-black text-white px-4 py-2 text-[10px] font-black uppercase">
                            Detailed Audit
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script>AOS.init();</script>
</body>
</html>