<?php
include '../config/db.php';
session_start();

// Ensure only Master Admin accesses this
if (!isset($_SESSION['admin'])) {
    header("Location: Login.php");
    exit();
}

// Handle Search Query
$search = "";
$where_clause = "";
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $search = mysqli_real_escape_string($conn, $_GET['query']);
    $where_clause = " WHERE bill_no LIKE '%$search%' OR customer_name LIKE '%$search%' OR customer_phone LIKE '%$search%'";
}

// Fetch Stats
$stats_query = mysqli_query($conn, "SELECT 
    COUNT(*) as total_count, 
    SUM(grand_total) as total_revenue,
    SUM(tax_amount) as total_tax
    FROM bills");
$stats = mysqli_fetch_assoc($stats_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Ledger | BUILDCOM TYCOON</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .stats-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; transition: transform 0.3s; }
        .stats-card:hover { transform: translateY(-5px); }
        .data-table { width: 100%; background: white; border-radius: 12px; border-collapse: separate; border-spacing: 0; overflow: hidden; border: 1px solid #e2e8f0; }
        .data-table thead { background: #f8fafc; }
        .data-table th { padding: 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0; }
        .data-table td { padding: 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .search-input { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; font-size: 14px; width: 350px; outline: none; transition: all 0.2s; }
        .search-input:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.05); }
    </style>
</head>
<body class="p-8">

    <div class="mb-10" data-aos="fade-down">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="text-2xl font-black text-slate-800 uppercase italic tracking-tight">Financial <span class="text-blue-700">Ledger</span></h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Master Sales & Invoice Audit</p>
            </div>
            <form method="GET" class="relative">
                <i class="fa-solid fa-magnifying-glass absolute right-4 top-4 text-slate-400"></i>
                <input type="text" name="query" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Invoice, Name, or Phone..." class="search-input pr-12">
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="stats-card flex items-center gap-5">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Invoices</p>
                    <h2 class="text-xl font-black text-slate-800"><?php echo number_format($stats['total_count']); ?></h2>
                </div>
            </div>
            <div class="stats-card flex items-center gap-5 border-l-4 border-l-blue-700">
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Gross Revenue</p>
                    <h2 class="text-xl font-black text-slate-800">₹<?php echo number_format($stats['total_revenue'], 2); ?></h2>
                </div>
            </div>
            <div class="stats-card flex items-center gap-5">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Collected Taxes</p>
                    <h2 class="text-xl font-black text-slate-800">₹<?php echo number_format($stats['total_tax'], 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-slate-200" data-aos="fade-up">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">Invoice Detail</th>
                    <th class="text-left">Customer Information</th>
                    <th class="text-center">Billing Date</th>
                    <th class="text-right">Grand Total</th>
                    <th class="text-right w-40">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php
                $invoice_sql = "SELECT * FROM bills $where_clause ORDER BY billing_date DESC";
                $res = mysqli_query($conn, $invoice_sql);
                if (mysqli_num_rows($res) > 0) {
                    while($row = mysqli_fetch_assoc($res)) {
                ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td>
                        <div class="flex flex-col">
                            <span class="text-xs font-black text-blue-700 uppercase tracking-tight">#<?php echo $row['bill_no']; ?></span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">System ID: <?php echo $row['id']; ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-slate-800 uppercase"><?php echo $row['customer_name']; ?></span>
                            <span class="text-[11px] font-medium text-slate-500"><i class="fa-solid fa-phone text-[9px] mr-1"></i> <?php echo $row['customer_phone']; ?></span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="text-xs font-semibold text-slate-600"><?php echo date("d M, Y", strtotime($row['billing_date'])); ?></span>
                        <span class="block text-[9px] font-bold text-slate-400"><?php echo date("h:i A", strtotime($row['billing_date'])); ?></span>
                    </td>
                    <td class="text-right">
                        <span class="text-sm font-black text-slate-900">₹<?php echo number_format($row['grand_total'], 2); ?></span>
                    </td>
                    <td class="text-right space-x-2">
                        <a href="Invoice.php?id=<?php echo $row['id']; ?>" class="inline-flex items-center justify-center w-9 h-9 bg-slate-100 text-slate-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="View Details">
                            <i class="fa-solid fa-eye text-xs"></i>
                        </a>
                        <a href="Invoice.php?id=<?php echo $row['id']; ?>" target="_blank" class="inline-flex items-center justify-center w-9 h-9 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-900 hover:text-white transition-all shadow-sm" title="Download/Print">
                            <i class="fa-solid fa-download text-xs"></i>
                        </a>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo '<tr><td colspan="5" class="py-20 text-center text-slate-400 font-medium italic">No transactions matching your criteria found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>
</body>
</html>