<?php
session_start();
// Redirect if not logged in
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
    <title>Admin Portal | Buildcom</title>
    <link rel="icon" type="image/x-icon" href="../Assets/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --primary-blue: #3b82f6;
            --sidebar-dark: #0f172a; /* Deep Slate */
            --sidebar-accent: #1e40af; /* Rich Blue */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        /* --- STYLISH DARK BLUE GRADIENT SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            /* Gradient from dark slate to deep blue */
            background: linear-gradient(180deg, var(--sidebar-dark) 0%, var(--sidebar-accent) 100%);
            height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.collapsed .nav-text, 
        .sidebar.collapsed .nav-label,
        .sidebar.collapsed .logo-text {
            display: none;
        }

        .sidebar.collapsed .sidebar-link {
            justify-content: center;
            padding: 12px 0;
            margin: 4px 10px;
        }

        .sidebar.collapsed .sidebar-link i {
            margin-right: 0;
            font-size: 1.25rem;
        }

        .logo-area {
            height: 80px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            background: rgba(0,0,0,0.1); /* Subtle separation */
        }

        .nav-label {
            font-size: 0.65rem;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 24px 24px 8px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 2px 16px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            font-size: 0.9rem;
            border-radius: 10px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .sidebar-link i {
            width: 24px;
            margin-right: 12px;
            font-size: 1.1rem;
            text-align: center;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .sidebar-link.active {
            background-color: #ffffff;
            color: var(--sidebar-accent);
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* --- NAVBAR & CONTENT --- */
        #main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .navbar {
            height: 70px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }

        .content-frame-container {
            flex: 1;
            padding: 15px;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        /* Nav Elements */
        .online-dot {
            width: 10px;
            height: 10px;
            background-color: #22c55e;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px #22c55e;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.7; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.7; }
        }

        .nav-icon-btn {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            color: #64748b;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .nav-icon-btn:hover {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: var(--primary-blue);
        }
    </style>
</head>
<body class="flex h-screen">

    <aside id="sidebar" class="sidebar">
        <div class="logo-area">
            <div class="bg-white p-1.5 rounded-lg mr-3 shadow-md">
                <img src="../Assets/logo.png" alt="Logo" class="w-6 h-6">
            </div>
            <div class="logo-text">
                <span class="block font-bold text-white tracking-tight text-lg">Buildcom</span>
                <span class="text-[9px] text-blue-300 font-bold uppercase tracking-[0.2em]">Tycoon Suite</span>
            </div>
        </div>

        <div class="flex-grow overflow-y-auto mt-4">
            <div class="nav-label">Main Hub</div>
            <a href="dashboard.php" class="sidebar-link active" target="content-frame">
                <i class="fa-solid fa-house"></i> <span class="nav-text">Dashboard</span>
            </a>

            <div class="nav-label">Management</div>
            <a href="Bill_create.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-receipt"></i> <span class="nav-text">Create Bill</span>
            </a>
            <a href="all_invoices.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-file-invoice-dollar"></i> <span class="nav-text">Invoices</span>
            </a>
            <a href="manage_franchise.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-store"></i> <span class="nav-text">Franchises</span>
            </a>
            <a href="manage_products.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-box-open"></i> <span class="nav-text">Inventory</span>
            </a>
            <a href="stock_maintenance.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-truck-moving"></i> <span class="nav-text">Stock Maintenance</span>
            </a>

            <div class="nav-label">Staffing</div>
            <a href="manage_staff.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-user-tie"></i> <span class="nav-text">Employees</span>
            </a>
            <a href="franchise_reports.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-chart-pie"></i> <span class="nav-text">Analytics</span>
            </a>
            <a href="profit_loss.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-chart-line"></i> <span class="nav-text">Profit & Loss</span>
            </a>
        </div>

        <div class="p-4 bg-black/10">
            <a href="logout.php" class="sidebar-link text-red-300 hover:text-white hover:bg-red-500/20 !mx-0">
                <i class="fa-solid fa-power-off"></i> <span class="nav-text">Logout</span>
            </a>
        </div>
    </aside>

    <div id="main-wrapper">
        <header class="navbar">
            <div class="flex items-center gap-4">
                <button id="toggleBtn" class="nav-icon-btn">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>
                <h2 id="page-title" class="font-bold text-slate-800 text-lg">Dashboard</h2>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-green-50 rounded-full border border-green-100 mr-2">
                    <span class="online-dot"></span>
                    <span class="text-[11px] font-bold text-green-600 uppercase">Online</span>
                </div>

                <div class="hidden lg:flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-100 px-4 py-2 rounded-xl">
                    <i class="fa-regular fa-clock text-blue-500"></i>
                    <span id="live-time">00:00:00</span>
                </div>

                <div class="h-6 w-[1px] bg-slate-200 mx-2"></div>

                <button onclick="toggleFullScreen()" class="nav-icon-btn" title="Expand View">
                    <i id="fs-icon" class="fa-solid fa-expand"></i>
                </button>
                
                <a href="notifications.php" target="content-frame" class="nav-icon-btn" title="Notifications">
                    <i class="fa-solid fa-bell"></i>
                </a>

                <a href="profile.php" target="content-frame" class="ml-2">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=1e40af&color=fff&bold=true" class="w-10 h-10 rounded-xl shadow-sm border-2 border-white hover:border-blue-200 transition-all">
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

        // Sidebar Collapse
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        // Navigation
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

        // Fullscreen
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