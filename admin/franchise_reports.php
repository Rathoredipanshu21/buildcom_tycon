<?php
include '../config/db.php';
session_start();

// 1. MASTER GATEKEEPER: Ensure only logged-in Admin can access
// Fixed: Removed the condition that was redirecting valid 'admin' users
if (!isset($_SESSION['admin'])) {
    header("Location: Login.php");
    exit();
}

/**
 * DYNAMIC FILTERING ENGINE
 */
$where_clauses = ["1=1"];
$f_filter = $_GET['f_id'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

if (!empty($f_filter)) { $where_clauses[] = "b.franchise_id = '" . mysqli_real_escape_string($conn, $f_filter) . "'"; }
if (!empty($start_date)) { $where_clauses[] = "b.billing_date >= '$start_date 00:00:00'"; }
if (!empty($end_date)) { $where_clauses[] = "b.billing_date <= '$end_date 23:59:59'"; }

$where_sql = implode(' AND ', $where_clauses);

// 2. FETCH AGGREGATED INTELLIGENCE
$stats_query = mysqli_query($conn, "SELECT 
    COUNT(b.id) as total_invoices,
    SUM(b.grand_total) as total_revenue,
    SUM(b.tax_amount) as total_tax,
    COUNT(DISTINCT b.franchise_id) as active_nodes
    FROM bills b WHERE $where_sql");
$stats = mysqli_fetch_assoc($stats_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Intelligence Hub | Buildcom Tycoon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .glass-filter { background: white; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .report-card { background: white; border-radius: 24px; padding: 25px; border: 1px solid #e2e8f0; transition: all 0.3s; }
        .report-card:hover { border-color: #2563eb; transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.1); }
        .data-table tr { transition: all 0.2s; }
        .data-table tr:hover { background-color: #f1f5f9; }
        /* Professional Non-Italic Titles */
        .title-main { font-weight: 800; text-transform: uppercase; letter-spacing: -0.02em; }
    </style>
</head>
<body class="p-8">

    <div class="flex justify-between items-center mb-10" data-aos="fade-down">
        <div>
            <h1 class="text-2xl title-main text-slate-900">Network <span class="text-blue-600">Intelligence</span></h1>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em]">Master Franchise Audit & Reporting</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-slate-900 text-white px-5 py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg">
                <i class="fa-solid fa-print mr-2"></i> Export Report
            </button>
        </div>
    </div>

    <div class="glass-filter p-8 mb-10" data-aos="fade-up">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Select Franchise</label>
                <select name="f_id" class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-blue-600 transition-all cursor-pointer">
                    <option value="">All Global Nodes</option>
                    <?php
                    $f_list = mysqli_query($conn, "SELECT id, store_name FROM franchises");
                    while($f = mysqli_fetch_assoc($f_list)) {
                        $sel = ($f_filter == $f['id']) ? 'selected' : '';
                        echo "<option value='".$f['id']."' $sel>".$f['store_name']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">From Date</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-blue-600 transition-all">
            </div>
            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">To Date</label>
                <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-blue-600 transition-all">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white py-3.5 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-900 transition-all shadow-md">
                    <i class="fa-solid fa-filter mr-2"></i> Apply Intelligence Filter
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div class="report-card" data-aos="zoom-in" data-aos-delay="100">
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm mb-4">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Revenue</p>
            <h2 class="text-2xl font-extrabold text-slate-900 mt-1">₹<?php echo number_format($stats['total_revenue'], 2); ?></h2>
        </div>
        <div class="report-card" data-aos="zoom-in" data-aos-delay="200">
            <div class="w-10 h-10 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center text-sm mb-4">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Invoices Issued</p>
            <h2 class="text-2xl font-extrabold text-slate-900 mt-1"><?php echo number_format($stats['total_invoices']); ?></h2>
        </div>
        <div class="report-card" data-aos="zoom-in" data-aos-delay="300">
            <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center text-sm mb-4 shadow-lg shadow-blue-200">
                <i class="fa-solid fa-building-circle-check"></i>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Active Nodes</p>
            <h2 class="text-2xl font-extrabold text-slate-900 mt-1"><?php echo $stats['active_nodes']; ?></h2>
        </div>
        <div class="report-card" data-aos="zoom-in" data-aos-delay="400">
            <div class="w-10 h-10 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-sm mb-4">
                <i class="fa-solid fa-vault"></i>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tax Collected</p>
            <h2 class="text-2xl font-extrabold text-slate-900 mt-1">₹<?php echo number_format($stats['total_tax'], 2); ?></h2>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden" data-aos="fade-up">
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest">Detailed Transaction Audit</h3>
            <span class="text-[9px] font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase tracking-widest">Live Result Set</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left data-table">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Store / Location</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Invoice Ref</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Customer</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Date/Time</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Grand Total</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    $report_sql = "SELECT b.*, f.store_name, f.city FROM bills b 
                                  JOIN franchises f ON b.franchise_id = f.id 
                                  WHERE $where_sql ORDER BY b.billing_date DESC";
                    $res = mysqli_query($conn, $report_sql);
                    if(mysqli_num_rows($res) > 0) {
                        while($row = mysqli_fetch_assoc($res)) {
                    ?>
                    <tr>
                        <td class="px-8 py-5">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-800 uppercase"><?php echo $row['store_name']; ?></span>
                                <span class="text-[9px] font-bold text-blue-600 uppercase tracking-widest flex items-center gap-1">
                                    <i class="fa-solid fa-location-dot text-[8px]"></i> <?php echo $row['city']; ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-xs font-bold text-slate-500 font-mono">#<?php echo $row['bill_no']; ?></td>
                        <td class="px-8 py-5">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 uppercase"><?php echo $row['customer_name']; ?></span>
                                <span class="text-[9px] font-bold text-slate-400"><?php echo $row['customer_phone']; ?></span>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <span class="text-[11px] font-bold text-slate-700 block"><?php echo date("d M, Y", strtotime($row['billing_date'])); ?></span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase"><?php echo date("h:i A", strtotime($row['billing_date'])); ?></span>
                        </td>
                        <td class="px-8 py-5 text-right font-bold text-slate-900 text-sm">₹<?php echo number_format($row['grand_total'], 2); ?></td>
                        <td class="px-8 py-5 text-center">
                            <a href="invoice.php?id=<?php echo $row['id']; ?>" target="_blank" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all mx-auto border border-slate-100">
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } } else { ?>
                        <tr><td colspan="6" class="py-20 text-center text-slate-400 font-bold uppercase text-[10px] tracking-widest">No Intelligence Data Found</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 600, once: true });
    </script>
</body>
</html>