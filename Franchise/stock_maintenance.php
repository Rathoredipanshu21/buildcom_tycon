<?php
include '../config/db.php';
session_start();

/**
 * SESSION GATEKEEPER: Ensure only an authorized Franchise Partner accesses this.
 * Prevents cross-terminal inventory tampering.
 */
if (!isset($_SESSION['franchise_id'])) {
    header("Location: Login.php");
    exit();
}

$logged_f_id = $_SESSION['franchise_id'];
$store_name = $_SESSION['franchise_name'];

/**
 * HANDLE STOCK UPDATES (Franchise-Locked)
 * Strictly updates products matching the logged-in franchise ID.
 */
if (isset($_POST['update_stock'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $adjustment_type = $_POST['adjustment_type']; 
    $quantity = (float)$_POST['quantity'];
    
    // SECURITY CHECK: Get current stock ONLY if it belongs to this franchise
    $check_sql = "SELECT stock FROM products WHERE id = '$product_id' AND franchise_id = '$logged_f_id'";
    $current_query = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($current_query) > 0) {
        $row = mysqli_fetch_assoc($current_query);
        $new_stock = ($adjustment_type == 'add') ? ($row['stock'] + $quantity) : ($row['stock'] - $quantity);

        if ($new_stock < 0) {
            echo "<script>alert('Error: Store stock cannot be negative.'); window.location='stock_maintenance.php';</script>";
        } else {
            // Secure update strictly for this franchise's entry
            mysqli_query($conn, "UPDATE products SET stock = '$new_stock' WHERE id = '$product_id' AND franchise_id = '$logged_f_id'");
            echo "<script>alert('Store Stock Ledger Updated!'); window.location='stock_maintenance.php';</script>";
        }
    } else {
        echo "<script>alert('Unauthorized: Product not found in your inventory.'); window.location='stock_maintenance.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Ledger | <?php echo $store_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .ledger-row { transition: all 0.2s; border-bottom: 1px solid #f1f5f9; }
        .ledger-row:hover { background-color: #fdfdfd; }
        .modal-wrapper { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); display: none; position: fixed; inset: 0; z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
        .modal-wrapper.active { display: flex; }
    </style>
</head>
<body class="text-slate-900">

    <header class="w-full px-10 py-8 flex justify-between items-center sticky top-0 bg-white/80 backdrop-blur-md z-40 border-b border-slate-100">
        <div data-aos="fade-right">
            <h1 class="text-xl font-black text-slate-800 uppercase italic tracking-tight italic">Store <span class="text-blue-700">Ledger</span></h1>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.3em]"><?php echo $store_name; ?> Inventory Control</p>
        </div>
        <div class="px-4 py-2 bg-slate-50 rounded-lg border border-slate-200 text-[11px] font-bold text-slate-500">
            <i class="fa-solid fa-boxes-stacked mr-2 text-blue-600"></i> Your Managed Items: 
            <?php 
                $total_items = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE franchise_id = '$logged_f_id'");
                echo mysqli_fetch_assoc($total_items)['total']; 
            ?>
        </div>
    </header>

    <main class="p-10">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" data-aos="fade-up">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Fabric Details</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Category</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Available Stock</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Ledger Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // RESTRICTED VIEW: Shows only products matching this franchise ID
                    $sql = "SELECT * FROM products WHERE franchise_id = '$logged_f_id' ORDER BY stock ASC";
                    $res = mysqli_query($conn, $sql);
                    while($row = mysqli_fetch_assoc($res)) {
                        $is_low = $row['stock'] <= 10; 
                    ?>
                    <tr class="ledger-row">
                        <td class="px-6 py-5">
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-slate-800 uppercase"><?php echo $row['product_name']; ?></span>
                                <span class="text-[9px] font-bold text-slate-400 tracking-tighter italic uppercase">Code: <?php echo $row['product_code']; ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                             <span class="text-[9px] font-black px-2 py-1 rounded bg-blue-50 text-blue-600 uppercase">
                                <?php echo $row['category']; ?>
                             </span>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="text-sm font-black <?php echo $is_low ? 'text-red-600 animate-pulse' : 'text-slate-900'; ?>">
                                <?php echo number_format($row['stock'], 2); ?> <span class="text-[10px] font-medium text-slate-400 uppercase"><?php echo $row['unit']; ?></span>
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <button onclick="openStockModal('<?php echo $row['id']; ?>', '<?php echo addslashes($row['product_name']); ?>', '<?php echo $row['stock']; ?>', '<?php echo $row['unit']; ?>')" 
                                    class="bg-blue-600 text-white px-5 py-2.5 rounded-lg text-[10px] font-black uppercase hover:bg-slate-900 transition-all shadow-md active:scale-95">
                                Update Inventory
                            </button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="stockModal" class="modal-wrapper">
        <div class="bg-white w-full max-w-md rounded-xl overflow-hidden shadow-2xl" data-aos="zoom-in">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest">Adjust Store Stock</h2>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-900 transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-8">
                <div class="mb-6 p-5 bg-blue-50 rounded-xl border border-blue-100">
                    <p class="text-[10px] font-bold text-blue-400 uppercase italic">Active Thaan Selection</p>
                    <h3 id="modal_product_name" class="text-sm font-black text-blue-900 uppercase italic"></h3>
                    <p class="text-[10px] font-bold text-blue-400 mt-2 uppercase tracking-widest">Current Stock: <span id="modal_current_stock" class="text-blue-900 font-black"></span></p>
                </div>

                <form method="POST">
                    <input type="hidden" name="product_id" id="modal_product_id">
                    
                    <div class="mb-5">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Inventory Mode</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="adjustment_type" value="add" checked class="hidden peer">
                                <div class="text-center py-4 border border-slate-200 rounded-xl text-[10px] font-black uppercase peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-all">
                                    <i class="fa-solid fa-plus-circle mr-1"></i> Add Stock
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="adjustment_type" value="reduce" class="hidden peer">
                                <div class="text-center py-4 border border-slate-200 rounded-xl text-[10px] font-black uppercase peer-checked:bg-red-600 peer-checked:text-white peer-checked:border-red-600 transition-all">
                                    <i class="fa-solid fa-minus-circle mr-1"></i> Reduce Stock
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2 ml-1">Quantity Adjustment</label>
                        <input type="number" step="0.01" name="quantity" required placeholder="0.00" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-blue-600 outline-none transition-all">
                    </div>

                    <button type="submit" name="update_stock" class="w-full bg-slate-900 text-white font-black py-4 rounded-xl text-[10px] uppercase tracking-[0.2em] shadow-xl hover:bg-blue-700 transition-all active:scale-[0.98]">
                        Confirm Ledger Update
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 600, once: true });

        function openStockModal(id, name, stock, unit) {
            document.getElementById('modal_product_id').value = id;
            document.getElementById('modal_product_name').innerText = name;
            document.getElementById('modal_current_stock').innerText = stock + ' ' + unit;
            document.getElementById('stockModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('stockModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    </script>
</body>
</html>