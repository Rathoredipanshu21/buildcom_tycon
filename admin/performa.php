<?php
include '../config/db.php';
session_start();

// --- FETCH SYSTEM-WIDE ANALYTICS ---
$stats_q = mysqli_query($conn, "SELECT 
    (SELECT COUNT(id) FROM franchises) as total_f,
    (SELECT COUNT(id) FROM bills) as total_bills,
    (SELECT SUM(grand_total) FROM bills) as total_rev,
    (SELECT SUM(remaining_amount) FROM bills) as total_debt
");
$sys = mysqli_fetch_assoc($stats_q);

// Recent Activities (Last 5 repayments across all franchises)
$recent_q = mysqli_query($conn, "SELECT r.*, b.bill_no, f.store_name 
    FROM bill_repayments r 
    JOIN bills b ON r.bill_id = b.id 
    JOIN franchises f ON b.franchise_id = f.id 
    ORDER BY r.payment_date DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Performa | Master Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', sans-serif; color: #000; }
        .bento-card { background: white; border: 2px solid #000; padding: 25px; transition: 0.3s; }
        .bento-card:hover { transform: translateY(-5px); box-shadow: 10px 10px 0px #000; }
        .btn-nav { background: #000; color: #fff; padding: 15px; text-align: center; font-weight: 900; text-transform: uppercase; display: block; font-size: 12px; }
        .btn-nav:hover { background: #2563eb; }
        .status-tag { font-size: 10px; font-weight: 900; padding: 2px 8px; border: 1px solid #000; text-transform: uppercase; }
    </style>
</head>
<body class="p-6 lg:p-12">

<div class="max-w-7xl mx-auto space-y-8">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b-4 border-black pb-8" data-aos="fade-down">
        <div>
            <h1 class="text-5xl font-black uppercase italic tracking-tighter">Admin <span class="text-blue-600">Performa</span></h1>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-[0.3em] mt-2">Enterprise Resource Management & Credit Ledger</p>
        </div>
        <div class="mt-4 md:mt-0 text-right">
            <p class="text-xs font-black uppercase">Server Time: <?php echo date("d M Y | H:i"); ?></p>
            <div class="flex gap-2 mt-2 justify-end">
                <span class="status-tag bg-green-500 text-white">System Live</span>
                <span class="status-tag bg-black text-white">Admin Secure</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        
        <div class="bento-card md:col-span-1" data-aos="fade-up" data-aos-delay="100">
            <p class="text-[10px] font-black uppercase text-slate-400 mb-2">Network Size</p>
            <h2 class="text-4xl font-black"><?php echo $sys['total_f']; ?></h2>
            <p class="text-xs font-bold mt-1 text-slate-600 uppercase tracking-widest">Active Franchises</p>
            <i class="fa-solid fa-sitemap mt-6 text-3xl opacity-20"></i>
        </div>

        <div class="bento-card md:col-span-1" data-aos="fade-up" data-aos-delay="200">
            <p class="text-[10px] font-black uppercase text-slate-400 mb-2">Gross Revenue</p>
            <h2 class="text-4xl font-black text-blue-600">₹<?php echo number_format($sys['total_rev'], 0); ?></h2>
            <p class="text-xs font-bold mt-1 text-slate-600 uppercase tracking-widest">Across All Units</p>
            <i class="fa-solid fa-wallet mt-6 text-3xl opacity-20"></i>
        </div>

        <div class="bento-card md:col-span-1" data-aos="fade-up" data-aos-delay="300">
            <p class="text-[10px] font-black uppercase text-slate-400 mb-2">Market Debt</p>
            <h2 class="text-4xl font-black text-red-600">₹<?php echo number_format($sys['total_debt'], 0); ?></h2>
            <p class="text-xs font-bold mt-1 text-slate-600 uppercase tracking-widest">Total Outstanding</p>
            <i class="fa-solid fa-handshake-slash mt-6 text-3xl opacity-20"></i>
        </div>

        <div class="bento-card md:col-span-1" data-aos="fade-up" data-aos-delay="400">
            <p class="text-[10px] font-black uppercase text-slate-400 mb-2">Volume</p>
            <h2 class="text-4xl font-black"><?php echo $sys['total_bills']; ?></h2>
            <p class="text-xs font-bold mt-1 text-slate-600 uppercase tracking-widest">Total Invoices</p>
            <i class="fa-solid fa-receipt mt-6 text-3xl opacity-20"></i>
        </div>

        <div class="md:col-span-2 space-y-4" data-aos="fade-right">
            <h3 class="text-sm font-black uppercase tracking-widest border-l-4 border-black pl-4">Management Modules</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="admin_ledger_report.php" class="btn-nav"><i class="fa-solid fa-file-invoice-dollar mb-2 block text-2xl"></i> Ledger Reports</a>
                <a href="manage_franchises.php" class="btn-nav"><i class="fa-solid fa-store mb-2 block text-2xl"></i> Manage Branches</a>
                <a href="#" class="btn-nav opacity-50"><i class="fa-solid fa-boxes-stacked mb-2 block text-2xl"></i> Global Stock</a>
                <a href="#" class="btn-nav opacity-50"><i class="fa-solid fa-users-gear mb-2 block text-2xl"></i> System Logs</a>
            </div>
        </div>

        <div class="md:col-span-2 bento-card" data-aos="fade-left">
            <h3 class="text-sm font-black uppercase tracking-widest mb-6"><i class="fa-solid fa-clock-rotate-left mr-2"></i> Live Repayment Feed</h3>
            <div class="space-y-3">
                <?php while($r = mysqli_fetch_assoc($recent_q)): ?>
                <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                    <div>
                        <p class="text-[11px] font-black uppercase"><?php echo $r['store_name']; ?></p>
                        <p class="text-[9px] font-bold text-slate-400 uppercase">INV: <?php echo $r['bill_no']; ?> | <?php echo $r['payment_mode']; ?></p>
                    </div>
                    <span class="text-sm font-black text-green-600">+ ₹<?php echo number_format($r['amount_paid'], 2); ?></span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

    </div>

   

</div>

<script>AOS.init({ duration: 1000, once: true });</script>
</body>
</html>