<?php
include '../config/db.php';
session_start();

// 1. SESSION GATEKEEPER: Ensure only an authorized Franchise Partner accesses this.
if (!isset($_SESSION['franchise_id'])) {
    header("Location: Login.php");
    exit();
}

$f_id = $_SESSION['franchise_id'];
$store_name = $_SESSION['franchise_name'];

// 2. FETCH REAL-TIME STATS FROM DB
// Total Sales (Count of Bills)
$sales_count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM bills WHERE franchise_id = '$f_id'");
$total_sales = mysqli_fetch_assoc($sales_count_query)['total'];

// Total Collection (Sum of Grand Total)
$collection_query = mysqli_query($conn, "SELECT SUM(grand_total) as total FROM bills WHERE franchise_id = '$f_id'");
$total_collection = mysqli_fetch_assoc($collection_query)['total'] ?? 0;

// Total Products Available (Count of unique products)
$products_count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE franchise_id = '$f_id'");
$total_products = mysqli_fetch_assoc($products_count_query)['total'];

// Low Stock Alert (Products with stock <= 10)
$low_stock_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE franchise_id = '$f_id' AND stock <= 10");
$low_stock_count = mysqli_fetch_assoc($low_stock_query)['total'];

// Monthly Sales Data for Chart (Last 6 Months)
$chart_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M', strtotime("-$i months"));
    $m_query = mysqli_query($conn, "SELECT SUM(grand_total) as total FROM bills WHERE franchise_id = '$f_id' AND billing_date LIKE '$month%'");
    $m_total = mysqli_fetch_assoc($m_query)['total'] ?? 0;
    $chart_labels[] = $month_name;
    $chart_values[] = $m_total;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo $store_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .stats-card { 
            background: white; 
            border-radius: 20px; 
            padding: 24px; 
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .stats-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border-color: #3b82f6; }
        .chart-container { background: white; border-radius: 20px; padding: 30px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body class="p-8">

    <div class="flex justify-between items-center mb-10" data-aos="fade-down">
        <div>
            <h1 class="text-2xl font-black text-slate-800 uppercase italic">Store <span class="text-blue-700">Analytics</span></h1>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em]">Real-time Performance Metrics for <?php echo $store_name; ?></p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.location.reload()" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-all">
                <i class="fa-solid fa-rotate-right text-slate-400 text-sm"></i>
            </button>
            <div class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg">
                Terminal ID: #<?php echo str_pad($f_id, 4, '0', STR_PAD_LEFT); ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        
        <div class="stats-card" data-aos="fade-up" data-aos-delay="100">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <span class="text-[10px] font-bold text-green-500 bg-green-50 px-2 py-1 rounded-lg">+ Live</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Invoices</p>
            <h2 class="text-2xl font-black text-slate-800 mt-1"><?php echo number_format($total_sales); ?></h2>
        </div>

        <div class="stats-card" data-aos="fade-up" data-aos-delay="200">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Collection</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Gross Revenue</p>
            <h2 class="text-2xl font-black text-slate-800 mt-1">₹<?php echo number_format($total_collection, 2); ?></h2>
        </div>

        <div class="stats-card" data-aos="fade-up" data-aos-delay="300">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <span class="text-[10px] font-bold text-blue-500 bg-blue-50 px-2 py-1 rounded-lg">Catalog</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Unique Products</p>
            <h2 class="text-2xl font-black text-slate-800 mt-1"><?php echo $total_products; ?> Items</h2>
        </div>

        <div class="stats-card" data-aos="fade-up" data-aos-delay="400">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <?php if($low_stock_count > 0): ?>
                    <span class="text-[10px] font-bold text-rose-500 bg-rose-50 px-2 py-1 rounded-lg animate-pulse">Action Required</span>
                <?php endif; ?>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Low Stock Alert</p>
            <h2 class="text-2xl font-black <?php echo $low_stock_count > 0 ? 'text-rose-600' : 'text-slate-800'; ?> mt-1"><?php echo $low_stock_count; ?> Warnings</h2>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="chart-container" data-aos="fade-right">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-6 flex items-center gap-2">
                <i class="fa-solid fa-chart-simple text-blue-600"></i> Revenue Trend (Last 6 Months)
            </h3>
            <canvas id="revenueChart" height="200"></canvas>
        </div>

        <div class="chart-container" data-aos="fade-left">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-6 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-indigo-600"></i> Operational Overview
            </h3>
            <canvas id="distributionChart" height="200"></canvas>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        // Revenue Bar Chart Configuration
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctxRevenue, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: <?php echo json_encode($chart_values); ?>,
                    backgroundColor: '#3b82f6',
                    borderRadius: 8,
                    barThickness: 25
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Distribution Pie Chart Configuration
        const ctxDist = document.getElementById('distributionChart').getContext('2d');
        new Chart(ctxDist, {
            type: 'doughnut',
            data: {
                labels: ['Total Invoices', 'Low Stock Items', 'Assigned Products'],
                datasets: [{
                    data: [<?php echo $total_sales; ?>, <?php echo $low_stock_count; ?>, <?php echo $total_products; ?>],
                    backgroundColor: ['#1e293b', '#f43f5e', '#3b82f6'],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 10, weight: 'bold' } } }
                }
            }
        });
    </script>
</body>
</html>