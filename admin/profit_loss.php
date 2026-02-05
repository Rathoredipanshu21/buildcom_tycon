<?php
include '../config/db.php';
session_start();

// MASTER GATEKEEPER
if (!isset($_SESSION['admin']) || $_SESSION['admin'] === 'franchise') {
    header("Location: Login.php");
    exit();
}

// 1. DYNAMIC FILTERING ENGINE
$f_id = $_GET['f_id'] ?? '';
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // Default to start of month
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$bill_where = "WHERE billing_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
$exp_where = "WHERE expense_date BETWEEN '$start_date' AND '$end_date'";

if (!empty($f_id)) {
    $bill_where .= " AND franchise_id = '$f_id'";
    $exp_where .= " AND franchise_id = '$f_id'";
}

// 2. FISCAL CALCULATIONS
// Total Revenue (Sales)
$rev_query = mysqli_query($conn, "SELECT SUM(grand_total) as total, SUM(tax_amount) as tax FROM bills $bill_where");
$rev_data = mysqli_fetch_assoc($rev_query);
$total_revenue = $rev_data['total'] ?? 0;
$total_tax = $rev_data['tax'] ?? 0;
$net_sales = $total_revenue - $total_tax;

// Total Expenses
$exp_query = mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses $exp_where");
$total_expenses = mysqli_fetch_assoc($exp_query)['total'] ?? 0;

// Final Profit/Loss
$net_profit = $net_sales - $total_expenses;
$profit_margin = ($net_sales > 0) ? ($net_profit / $net_sales) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profit & Loss Statement | Buildcom Tycoon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f7ff; color: #1e3a8a; }
        .report-card { background: white; border-radius: 32px; padding: 40px; border: 1px solid #e0e7ff; box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.1); }
        .row-item { display: flex; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 14px; }
        .total-box { background: #1e3a8a; color: white; border-radius: 20px; padding: 25px; margin-top: 20px; }
    </style>
</head>
<body class="p-10">

    <div class="max-w-4xl mx-auto">
        <div class="mb-10 flex justify-between items-end" data-aos="fade-down">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase italic">Fiscal <span class="text-blue-600">Reporting</span></h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-1">Income & Expenditure Statement</p>
            </div>
            <button onclick="window.print()" class="bg-white border border-slate-200 p-3 rounded-xl shadow-sm hover:bg-slate-50 transition-all">
                <i class="fa-solid fa-print"></i>
            </button>
        </div>

        <div class="bg-white p-8 rounded-3xl mb-10 border border-slate-200 shadow-sm" data-aos="fade-up">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-1">
                    <label class="text-[9px] font-black text-slate-400 uppercase ml-1">Entity Selection</label>
                    <select name="f_id" class="w-full mt-2 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none">
                        <option value="">All Global Nodes</option>
                        <?php
                        $f_res = mysqli_query($conn, "SELECT id, store_name FROM franchises");
                        while($f = mysqli_fetch_assoc($f_res)) {
                            $sel = ($f_id == $f['id']) ? 'selected' : '';
                            echo "<option value='".$f['id']."' $sel>".$f['store_name']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase ml-1">From Date</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="w-full mt-2 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none">
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase ml-1">To Date</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="w-full mt-2 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white font-black py-3.5 rounded-xl text-[10px] uppercase tracking-widest shadow-lg hover:bg-slate-900 transition-all">Generate Report</button>
                </div>
            </form>
        </div>

        <div class="report-card" data-aos="zoom-in">
            <div class="text-center mb-12">
                <h2 class="text-sm font-black uppercase tracking-[0.4em] text-slate-400">Statement of Profit & Loss</h2>
                <p class="text-xs font-bold text-slate-900 mt-2"><?php echo date('d M Y', strtotime($start_date)); ?> — <?php echo date('d M Y', strtotime($end_date)); ?></p>
            </div>

            <div class="mb-10">
                <h3 class="text-[10px] font-black text-blue-600 uppercase tracking-widest border-b border-blue-100 pb-2 mb-4">I. Operational Revenue</h3>
                <div class="row-item"><span>Gross Sales (Invoices)</span> <span>₹<?php echo number_format($total_revenue, 2); ?></span></div>
                <div class="row-item text-slate-400 font-medium"><span>Less: Tax Collected (GST)</span> <span>- ₹<?php echo number_format($total_tax, 2); ?></span></div>
                <div class="row-item bg-slate-50 px-4 rounded-xl mt-2 font-black text-slate-900"><span>NET REVENUE</span> <span>₹<?php echo number_format($net_sales, 2); ?></span></div>
            </div>

            <div class="mb-10">
                <h3 class="text-[10px] font-black text-red-500 uppercase tracking-widest border-b border-red-100 pb-2 mb-4">II. Operating Expenditures</h3>
                <?php
                $cat_query = mysqli_query($conn, "SELECT category, SUM(amount) as total FROM expenses $exp_where GROUP BY category");
                if(mysqli_num_rows($cat_query) > 0) {
                    while($c = mysqli_fetch_assoc($cat_query)) {
                        echo "<div class='row-item'><span>".$c['category']."</span> <span>₹".number_format($c['total'], 2)."</span></div>";
                    }
                } else {
                    echo "<p class='text-[10px] text-slate-300 font-bold py-4 text-center uppercase tracking-widest italic'>No expenditures recorded for this period</p>";
                }
                ?>
                <div class="row-item bg-red-50 px-4 rounded-xl mt-2 font-black text-red-600"><span>TOTAL EXPENDITURE</span> <span>₹<?php echo number_format($total_expenses, 2); ?></span></div>
            </div>

            <div class="total-box shadow-2xl shadow-blue-200">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] opacity-70 italic">Net Earnings Post-Expense</span>
                    <span class="text-[10px] font-black bg-white/10 px-3 py-1 rounded-full uppercase">Efficiency: <?php echo number_format($profit_margin, 1); ?>%</span>
                </div>
                <div class="flex justify-between items-end">
                    <h2 class="text-3xl font-black italic tracking-tighter uppercase">Net Statement</h2>
                    <h2 class="text-4xl font-black italic tracking-tighter">₹<?php echo number_format($net_profit, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 800 });</script>
</body>
</html>