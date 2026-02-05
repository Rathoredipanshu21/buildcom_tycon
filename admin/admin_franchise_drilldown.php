<?php
include '../config/db.php';
session_start();

if (!isset($_GET['id'])) { die("Franchise ID Missing"); }
$fid = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch Franchise Info
$f_info_q = mysqli_query($conn, "SELECT * FROM franchises WHERE id = '$fid'");
$f_info = mysqli_fetch_assoc($f_info_q);

// Fetch Bills for this Franchise
$bills_q = mysqli_query($conn, "SELECT * FROM bills WHERE franchise_id = '$fid' ORDER BY remaining_amount DESC");

// Stats for this Franchise
$f_stats_q = mysqli_query($conn, "SELECT SUM(grand_total) as s, SUM(remaining_amount) as d FROM bills WHERE franchise_id = '$fid'");
$f_stats = mysqli_fetch_assoc($f_stats_q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit: <?php echo $f_info['store_name']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        body { background: #fff; font-family: 'Segoe UI', sans-serif; }
        .audit-header { border-bottom: 5px solid #000; padding: 40px 0; background: #fafafa; }
        .grid-table th { background: #000; color: #fff; text-transform: uppercase; font-size: 11px; padding: 12px; text-align: left; }
        .grid-table td { border: 1px solid #000; padding: 12px; font-size: 13px; font-weight: 700; }
    </style>
</head>
<body class="p-8">

<div class="max-w-6xl mx-auto">
    <a href="admin_ledger_report.php" class="text-xs font-black uppercase mb-6 inline-block bg-slate-100 px-4 py-2 border border-black hover:bg-black hover:text-white transition-all">
        <i class="fa-solid fa-arrow-left mr-2"></i> Back to Main Ledger
    </a>

    <div class="audit-header mb-10" data-aos="fade-right">
        <h1 class="text-4xl font-black uppercase tracking-tighter">Audit Report: <?php echo $f_info['store_name']; ?></h1>
        <div class="flex gap-10 mt-4 text-xs font-bold uppercase text-slate-500">
            <p><i class="fa-solid fa-user mr-2"></i> Owner: <?php echo $f_info['owner_name']; ?></p>
            <p><i class="fa-solid fa-location-dot mr-2"></i> Location: <?php echo $f_info['city']; ?>, <?php echo $f_info['state']; ?></p>
            <p><i class="fa-solid fa-phone mr-2"></i> Contact: <?php echo $f_info['phone']; ?></p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-10" data-aos="fade-up">
        <div class="border-2 border-black p-6 bg-blue-50">
            <p class="text-[10px] font-black uppercase">Branch Total Revenue</p>
            <h3 class="text-3xl font-black">₹<?php echo number_format($f_stats['s'] ?? 0, 2); ?></h3>
        </div>
        <div class="border-2 border-black p-6 bg-red-50">
            <p class="text-[10px] font-black uppercase">Branch Total Outstanding</p>
            <h3 class="text-3xl font-black text-red-600">₹<?php echo number_format($f_stats['d'] ?? 0, 2); ?></h3>
        </div>
    </div>

    <div class="bg-white" data-aos="fade-up">
        <h2 class="text-sm font-black uppercase mb-4 border-b-2 border-black pb-2">Customer Debt Breakdown</h2>
        <table class="w-full grid-table border-collapse">
            <thead>
                <tr>
                    <th>Bill Date</th>
                    <th>Invoice No</th>
                    <th>Customer Name</th>
                    <th class="text-right">Total Amount</th>
                    <th class="text-right text-red-500">Remaining Due</th>
                </tr>
            </thead>
            <tbody>
                <?php while($b = mysqli_fetch_assoc($bills_q)): ?>
                <tr class="<?php echo $b['remaining_amount'] > 0 ? 'bg-white' : 'bg-green-50 opacity-60'; ?>">
                    <td><?php echo date("d-m-Y", strtotime($b['billing_date'])); ?></td>
                    <td class="text-blue-700 uppercase font-black"><?php echo $b['bill_no']; ?></td>
                    <td class="uppercase"><?php echo $b['customer_name']; ?> <br> <span class="text-[10px] text-slate-400"><?php echo $b['customer_phone']; ?></span></td>
                    <td class="text-right">₹<?php echo number_format($b['grand_total'], 2); ?></td>
                    <td class="text-right font-black <?php echo $b['remaining_amount'] > 0 ? 'text-red-600' : 'text-green-600'; ?>">
                        ₹<?php echo number_format($b['remaining_amount'], 2); ?>
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