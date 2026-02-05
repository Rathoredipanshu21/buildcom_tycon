<?php
session_start();
// Security Gatekeeper: Ensure Admin is authorized
if (!isset($_SESSION['admin'])) {
    header("Location: Login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Terminal | Buildcom ERP</title>
    <link rel="icon" type="image/x-icon" href="../Assets/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --primary-blue: #2563EB; /* Specific blue requested */
            --industrial-dark: #0f172a; /* Deep Slate */
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            /* Blue and White Gradient Combination */
            background: linear-gradient(135deg, #2563EB 0%, #ffffff 100%);
            color: #0f172a;
            overflow: hidden;
            height: 100vh;
        }

        /* --- SIDEBAR DESIGN --- */
        .sidebar {
            width: var(--sidebar-width);
            /* Gradient Sidebar matching the theme */
            background: linear-gradient(180deg, #2563EB 0%, #0f172a 100%);
            height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            z-index: 100;
            border-right: 2px solid #ffffff;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.collapsed .nav-text, 
        .sidebar.collapsed .nav-label,
        .sidebar.collapsed .logo-text {
            display: none;
        }

        .logo-area {
            height: 85px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-label {
            font-size: 10px;
            font-weight: 900;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.2em;
            padding: 25px 24px 10px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 2px 14px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
            text-decoration: none;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .sidebar-link i {
            width: 24px;
            margin-right: 12px;
            font-size: 1rem;
            text-align: center;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }

        .sidebar-link.active {
            background-color: #ffffff;
            color: #2563EB;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* --- CONTENT INTERFACE --- */
        #main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .navbar {
            height: 70px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #2563EB;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }

        .content-frame-container {
            flex: 1;
            padding: 12px;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: 2px solid #0f172a;
            background: white;
            border-radius: 4px;
            box-shadow: 12px 12px 0px rgba(37, 99, 235, 0.2);
        }

        .online-status {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #2563EB;
            padding: 6px 14px;
            border-radius: 4px;
        }

        .online-dot {
            width: 8px;
            height: 8px;
            background-color: #4ade80;
            border-radius: 50%;
            box-shadow: 0 0 8px #4ade80;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }

        .btn-action {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563EB;
            border: 1px solid #2563EB;
            background: white;
            transition: 0.2s;
            border-radius: 4px;
        }

        .btn-action:hover {
            background: #2563EB;
            color: white;
        }
    </style>
</head>
<body class="flex h-screen">

    <aside id="sidebar" class="sidebar">
        <div class="logo-area">
            <div class="bg-white p-2 border-2 border-blue-600 rounded-sm mr-3">
                <img src="../Assets/logo.png" alt="Logo" class="w-6 h-6">
            </div>
            <div class="logo-text">
                <span class="block font-black text-white uppercase tracking-tighter text-xl italic leading-none">Buildcom</span>
                <span class="text-[9px] text-blue-100 font-bold uppercase tracking-[0.3em]">ERP Terminal</span>
            </div>
        </div>

        <div class="flex-grow overflow-y-auto">
            <div class="nav-label">Main Operations</div>
            <a href="dashboard.php" class="sidebar-link active" target="content-frame">
                <i class="fa-solid fa-gauge-high"></i> <span class="nav-text">Executive Hub</span>
            </a>
            <a href="performa.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-chart-line"></i> <span class="nav-text">Sales Performa</span>
            </a>

            <div class="nav-label">Finance & Ledger</div>
            <a href="Bill_create.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-plus-square"></i> <span class="nav-text">Create Bill</span>
            </a>
            <a href="all_invoices.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-file-invoice"></i> <span class="nav-text">Invoices</span>
            </a>
            <a href="admin_ledger_report.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-book-bookmark"></i> <span class="nav-text">Credit Ledger</span>
            </a>
            <a href="profit_loss.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-scale-balanced"></i> <span class="nav-text">Account P&L</span>
            </a>
            <a href="manage_expenses.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-wallet"></i> <span class="nav-text">Manage Expenses</span>
            </a>

            <div class="nav-label">Inventory & Units</div>
            <a href="manage_products.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-boxes-stacked"></i> <span class="nav-text">Product Registry</span>
            </a>
            <a href="stock_maintenance.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-truck-ramp-box"></i> <span class="nav-text">Supply Control</span>
            </a>
            <a href="manage_franchise.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-building-circle-check"></i> <span class="nav-text">Branch Units</span>
            </a>

            <div class="nav-label">Personnel & Analytics</div>
            <a href="manage_staff.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-user-gear"></i> <span class="nav-text">Staff Records</span>
            </a>
            <a href="franchise_reports.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-chart-pie"></i> <span class="nav-text">Unit Analytics</span>
            </a>
        </div>

        <div class="p-4 bg-black/40">
            <a href="logout.php" class="sidebar-link !text-red-400 !mx-0 hover:!bg-red-600 hover:!text-white">
                <i class="fa-solid fa-power-off"></i> <span class="nav-text">Exit Terminal</span>
            </a>
        </div>
    </aside>

    <div id="main-wrapper">
        <header class="navbar">
            <div class="flex items-center gap-4">
                <button id="toggleBtn" class="btn-action">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>
                <h2 id="page-title" class="font-black text-blue-800 uppercase tracking-tight text-lg">Executive Hub</h2>
            </div>

            <div class="flex items-center gap-4">
                <div class="online-status">
                    <span class="online-dot"></span>
                    <span class="text-[10px] font-black text-white uppercase tracking-widest">Secure Online</span>
                </div>

                <div class="hidden lg:flex items-center gap-2 text-[11px] font-black text-blue-700 bg-white border border-blue-600 px-4 py-2">
                    <i class="fa-solid fa-clock text-blue-600"></i>
                    <span id="live-time">00:00:00</span>
                </div>

                <div class="h-8 w-[1px] bg-blue-200 mx-1"></div>

                <button onclick="toggleFullScreen()" class="btn-action" title="Full Screen">
                    <i id="fs-icon" class="fa-solid fa-expand"></i>
                </button>
                
                <a href="profile.php" target="content-frame" class="border-2 border-blue-600 p-0.5 ml-2">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=2563EB&color=fff&bold=true" class="w-10 h-10 hover:opacity-80 transition-opacity">
                </a>
            </div>
        </header>

        <main class="content-frame-container">
            <iframe id="content-frame" name="content-frame" src="dashboard.php"></iframe>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const pageTitle = document.getElementById('page-title');
        const links = document.querySelectorAll('.sidebar-link');

        // Sidebar Toggle
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        // Navigation Sync
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                if(this.getAttribute('href') === 'logout.php') return;
                
                links.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                const text = this.querySelector('.nav-text') ? this.querySelector('.nav-text').innerText : "Menu";
                pageTitle.innerText = text;

                if(window.innerWidth < 1024) sidebar.classList.add('collapsed');
            });
        });

        // Fullscreen Logic
        function toggleFullScreen() {
            const icon = document.getElementById('fs-icon');
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                icon.classList.replace('fa-expand', 'fa-compress');
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    icon.classList.replace('fa-compress', 'fa-expand');
                }
            }
        }

        // Live Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('live-time').textContent = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit', hour12: true});
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>