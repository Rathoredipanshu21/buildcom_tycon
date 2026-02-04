<?php
session_start();
// Redirect if no session exists
if (!isset($_SESSION['admin'])) {
    header("Location: Login.php");
    exit();
}

/** * Identifying the logged-in user
 * For Master Admin: $_SESSION['admin'] contains the username.
 * For Franchises: $_SESSION['franchise_name'] contains the store_name from the database.
 */
$is_master = ($_SESSION['admin'] !== 'franchise');
$display_name = $is_master ? "Master Admin" : $_SESSION['franchise_name']; 
$display_role = $is_master ? "System Director" : "Franchise Partner";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $display_name; ?> | BUILDCOM TYCOON</title>
    <link rel="icon" type="image/x-icon" href="../Assets/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #1e293b; overflow: hidden; }
        
        /* Corporate Sidebar Styling */
        .sidebar { background: linear-gradient(180deg, #0f172a 0%, #1e3a8a 100%); height: 100vh; position: fixed; width: 280px; z-index: 50; }
        .sidebar-link { display: flex; align-items: center; padding: 12px 24px; color: rgba(255, 255, 255, 0.6); font-weight: 500; border-radius: 8px; margin: 4px 16px; transition: all 0.2s; cursor: pointer; }
        .sidebar-link:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }
        .sidebar-link.active { background: #fff; color: #1e3a8a; font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .sidebar-link i { width: 24px; margin-right: 12px; text-align: center; }

        /* Main Content Layout */
        .main-wrapper { margin-left: 280px; height: 100vh; display: flex; flex-direction: column; }
        .content-area { flex: 1; padding: 0; overflow: hidden; position: relative; }
        .top-header { background: white; border-bottom: 1px solid #e2e8f0; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; }
        
        iframe { width: 100%; height: 100%; border: none; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="p-8 flex items-center gap-3 border-b border-white/10 mb-6">
            <div class="bg-white p-1.5 rounded-lg shadow-lg">
                <img src="../Assets/logo.png" class="h-8">
            </div>
            <div>
                <h1 class="text-white font-black text-lg tracking-tighter uppercase leading-none">Buildcom</h1>
                <p class="text-blue-400 text-[9px] font-bold uppercase tracking-widest mt-1">Tycoon Suite</p>
            </div>
        </div>

        <nav id="nav-container">
            <p class="text-[10px] font-bold text-white/30 uppercase tracking-[0.2em] px-8 mb-4">Core Modules</p>
            
            <a href="dashboard.php" class="sidebar-link active" target="content-frame">
                <i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span>
            </a>

            <a href="create_bill.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-file-invoice-dollar"></i> <span>Billing</span>
            </a>

            <a href="invoice.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-receipt"></i> <span>Invoice History</span>
            </a>

             <a href="manage_products.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-layer-group"></i> <span>Manage Products</span>
            </a>
            
            <a href="stock_maintenance.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-boxes-stacked"></i> <span>Stock Management</span>
            </a>
            <a href="transfer_goods.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-truck"></i> <span>Transfer Goods</span>
            </a>

            <?php if($is_master): ?>
            <p class="text-[10px] font-bold text-white/30 uppercase tracking-[0.2em] px-8 mt-8 mb-4">Administration</p>

            <a href="manage_franchise.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-store"></i> <span>Franchises</span>
            </a>

            <a href="manage_staff.php" class="sidebar-link" target="content-frame">
                <i class="fa-solid fa-user-gear"></i> <span>Personnel</span>
            </a>
            <?php endif; ?>
        </nav>

        <div class="absolute bottom-0 w-full p-6 border-t border-white/10 bg-black/10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs font-black shrink-0 shadow-lg">
                        <?php echo strtoupper(substr($display_name, 0, 1)); ?>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-white text-[11px] font-black uppercase tracking-tight truncate"><?php echo $display_name; ?></p>
                        <p class="text-blue-400 text-[9px] font-bold uppercase tracking-tighter"><?php echo $display_role; ?></p>
                    </div>
                </div>
                <a href="logout.php" class="text-red-400 hover:text-red-300 transition-colors pl-2" title="Logout">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </div>
    </aside>

    <main class="main-wrapper">
        <header class="top-header">
            <div class="flex items-center gap-4">
                <div class="h-8 w-1 bg-blue-700 rounded-full"></div>
                <h2 id="page-title" class="text-lg font-black text-slate-800 uppercase tracking-tight italic">Dashboard</h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="px-4 py-1.5 bg-slate-50 border border-slate-200 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-calendar-day text-blue-600 text-xs"></i>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <?php echo date('D, M d, Y'); ?>
                    </span>
                </div>
            </div>
        </header>

        <div class="content-area">
            <iframe name="content-frame" id="content-frame" src="dashboard.php"></iframe>
        </div>
    </main>

    <script>
        const links = document.querySelectorAll('.sidebar-link');
        const title = document.getElementById('page-title');

        links.forEach(link => {
            link.addEventListener('click', function() {
                // Update Sidebar Active State
                links.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                // Update Header Title based on the text of the link
                const linkText = this.querySelector('span').innerText;
                title.innerText = linkText;
            });
        });
    </script>
</body>
</html>