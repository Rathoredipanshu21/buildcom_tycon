<?php
include '../config/db.php';
session_start();

// 1. MASTER GATEKEEPER: Ensure only logged-in Admin can access
if (!isset($_SESSION['admin'])) {
    header("Location: Login.php");
    exit();
}

// 2. REAL-TIME DATA ENGINE (AGGREGATED FOR ADMIN)
// Fiscal Data
$all_time = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(grand_total) as total FROM bills"))['total'] ?? 0;
$today = date('Y-m-d');
$today_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(grand_total) as total FROM bills WHERE billing_date LIKE '$today%'"))['total'] ?? 0;
$week_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(grand_total) as total FROM bills WHERE YEARWEEK(billing_date, 1) = YEARWEEK(CURDATE(), 1)"))['total'] ?? 0;

// Network & Resource Data
$total_franchises = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM franchises"))['total'] ?? 0;
$total_bills = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bills"))['total'] ?? 0;
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'] ?? 0;
$total_staff = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM staff"))['total'] ?? 0;
$low_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE stock <= 10"))['total'] ?? 0;

// 3. CHART DATA: LAST 7 DAYS REVENUE
$labels = []; $data = [];
for($i=6; $i>=0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('D', strtotime($date));
    $rev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(grand_total) as total FROM bills WHERE billing_date LIKE '$date%'"))['total'] ?? 0;
    $data[] = $rev;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Executive Terminal | Buildcom Tycoon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #334155; }
        
        .stat-card { 
            background: white;
            border-radius: 24px; 
            padding: 24px; 
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 160px;
            cursor: pointer;
        }
        .stat-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
            border-color: #3b82f6;
        }

        .icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: #eff6ff;
            color: #2563eb;
        }

        .chart-box {
            background: white;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            padding: 24px;
        }
    </style>
</head>
<body class="p-6">

    <div class="mb-10 flex justify-between items-center" data-aos="fade-down">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Executive Dashboard</h1>
            <p class="text-blue-600 font-bold uppercase text-[10px] tracking-[0.3em] mt-1">Global System Control Center</p>
        </div>
        <div class="px-4 py-2 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-200">
            System Online
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        
        <div class="stat-card" data-aos="zoom-in" data-aos-delay="100" onclick="window.parent.document.querySelector('[href=\'manage_franchise.php\']').click()">
            <div class="flex justify-between items-start">
                <div class="icon-circle"><i class="fa-solid fa-shop"></i></div>
                <span class="text-[9px] font-black bg-blue-50 text-blue-600 px-2 py-1 rounded-md uppercase tracking-tighter">Verified</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Franchises</p>
                <h2 class="text-2xl font-black text-slate-900 mt-1"><?php echo $total_franchises; ?></h2>
                <p class="text-[10px] font-bold text-blue-500 mt-1">Global Network</p>
            </div>
        </div>

        <div class="stat-card" data-aos="zoom-in" data-aos-delay="200" onclick="window.parent.document.querySelector('[href=\'manage_products.php\']').click()">
            <div class="flex justify-between items-start">
                <div class="icon-circle"><i class="fa-solid fa-layer-group"></i></div>
                <span class="text-[9px] font-black bg-blue-50 text-blue-600 px-2 py-1 rounded-md uppercase tracking-tighter">Global</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Products</p>
                <h2 class="text-2xl font-black text-slate-900 mt-1"><?php echo $total_products; ?></h2>
                <p class="text-[10px] font-bold text-blue-500 mt-1">Master Inventory</p>
            </div>
        </div>

        <div class="stat-card" data-aos="zoom-in" data-aos-delay="300" onclick="window.parent.document.querySelector('[href=\'all_invoices.php\']').click()">
            <div class="flex justify-between items-start">
                <div class="icon-circle"><i class="fa-solid fa-receipt"></i></div>
                <span class="text-[9px] font-black bg-blue-50 text-blue-600 px-2 py-1 rounded-md uppercase tracking-tighter">Ledger</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Invoices</p>
                <h2 class="text-2xl font-black text-slate-900 mt-1"><?php echo number_format($total_bills); ?></h2>
                <p class="text-[10px] font-bold text-blue-500 mt-1">System Audit</p>
            </div>
        </div>

        <div class="stat-card" data-aos="zoom-in" data-aos-delay="400" onclick="window.parent.document.querySelector('[href=\'stock_maintenance.php\']').click()">
            <div class="flex justify-between items-start">
                <div class="icon-circle text-red-600 bg-red-50"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <span class="text-[9px] font-black bg-red-50 text-red-600 px-2 py-1 rounded-md uppercase tracking-tighter">Alert</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Stock Warnings</p>
                <h2 class="text-2xl font-black text-slate-900 mt-1"><?php echo $low_stock; ?></h2>
                <p class="text-[10px] font-bold text-red-500 mt-1 uppercase">Restock Required</p>
            </div>
        </div>

        <div class="stat-card" data-aos="zoom-in" data-aos-delay="500" onclick="window.parent.document.querySelector('[href=\'manage_staff.php\']').click()">
            <div class="flex justify-between items-start">
                <div class="icon-circle"><i class="fa-solid fa-user-tie"></i></div>
                <span class="text-[9px] font-black bg-blue-50 text-blue-600 px-2 py-1 rounded-md uppercase tracking-tighter">Staff</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Workforce</p>
                <h2 class="text-2xl font-black text-slate-900 mt-1"><?php echo $total_staff; ?></h2>
                <p class="text-[10px] font-bold text-blue-500 mt-1">Active Personnel</p>
            </div>
        </div>

        <div class="stat-card" data-aos="zoom-in" data-aos-delay="600">
            <div class="flex justify-between items-start">
                <div class="icon-circle text-green-600 bg-green-50"><i class="fa-solid fa-calendar-day"></i></div>
                <span class="text-[9px] font-black bg-green-50 text-green-600 px-2 py-1 rounded-md uppercase tracking-tighter">Live</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Today's Revenue</p>
                <h2 class="text-2xl font-black text-slate-900 mt-1">₹<?php echo number_format($today_total, 2); ?></h2>
                <p class="text-[10px] font-bold text-green-600 mt-1">Real-time Progress</p>
            </div>
        </div>

        <div class="stat-card" data-aos="zoom-in" data-aos-delay="700">
            <div class="flex justify-between items-start">
                <div class="icon-circle"><i class="fa-solid fa-chart-line"></i></div>
                <span class="text-[9px] font-black bg-blue-50 text-blue-600 px-2 py-1 rounded-md uppercase tracking-tighter">Weekly</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Week Collection</p>
                <h2 class="text-2xl font-black text-slate-900 mt-1">₹<?php echo number_format($week_total, 2); ?></h2>
                <p class="text-[10px] font-bold text-blue-600 mt-1">7-Day Performance</p>
            </div>
        </div>

        <div class="stat-card bg-slate-900 border-none" data-aos="zoom-in" data-aos-delay="800">
            <div class="flex justify-between items-start">
                <div class="icon-circle bg-white/10 text-white"><i class="fa-solid fa-vault"></i></div>
                <span class="text-[9px] font-black bg-blue-900 text-blue-200 px-2 py-1 rounded-md uppercase tracking-tighter">Total</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Earnings</p>
                <h2 class="text-2xl font-black text-white mt-1">₹<?php echo number_format($all_time, 2); ?></h2>
                <p class="text-[10px] font-bold text-blue-300 mt-1">Aggregate Revenue</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" data-aos="fade-up">
        <div class="chart-box">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-6">Revenue Trend (Last 7 Days)</h3>
            <canvas id="revenueChart" height="150"></canvas>
        </div>
        <div class="chart-box">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-6">Resource Allocation</h3>
            <canvas id="resourceChart" height="150"></canvas>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 600, once: true });

        const ctxRev = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($data); ?>,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { weight: '600', size: 10 } } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { weight: '600', size: 10 } } }
                }
            }
        });

        const ctxRes = document.getElementById('resourceChart').getContext('2d');
        new Chart(ctxRes, {
            type: 'doughnut',
            data: {
                labels: ['Franchises', 'Staff', 'Products'],
                datasets: [{
                    data: [<?php echo $total_franchises; ?>, <?php echo $total_staff; ?>, <?php echo $total_products; ?>],
                    backgroundColor: ['#1e3a8a', '#2563eb', '#60a5fa'],
                    borderWidth: 0
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { font: { weight: '700', size: 10 } } } },
                cutout: '75%'
            }
        });
    </script>
</body>
</html>