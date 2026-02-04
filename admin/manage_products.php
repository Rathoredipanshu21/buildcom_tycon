<?php
include '../config/db.php';
session_start();

// AUTO-GENERATE PRODUCT CODE
$auto_p_code = "BTP" . rand(1000, 9999);

// GET LOGGED IN ADMIN ID
$logged_admin_id = $_SESSION['admin_id'] ?? 1; 

// 1. HANDLE QUICK ADD ACTIONS
if (isset($_POST['quick_category'])) {
    $new_cat = mysqli_real_escape_string($conn, $_POST['new_category_name']);
    mysqli_query($conn, "INSERT IGNORE INTO categories (category_name) VALUES ('$new_cat')");
    echo "<script>window.location='manage_products.php';</script>";
    exit();
}

if (isset($_POST['quick_unit'])) {
    $new_unit = strtoupper(mysqli_real_escape_string($conn, $_POST['new_unit_name']));
    mysqli_query($conn, "INSERT IGNORE INTO units (unit_name) VALUES ('$new_unit')");
    echo "<script>window.location='manage_products.php';</script>";
    exit();
}

// 2. HANDLE PRODUCT SUBMISSION
if (isset($_POST['add_product'])) {
    $p_code = $_POST['p_code'];
    $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $unit = $_POST['unit'];
    $target_franchise = $_POST['franchise_id'];

    // LOGIC: Save Admin ID as the creator [cite: 70, 71]
    $sql = "INSERT INTO products (product_code, franchise_id, created_by_admin, product_name, category, price, stock, unit) 
            VALUES ('$p_code', '$target_franchise', '$logged_admin_id', '$p_name', '$category', '$price', '$stock', '$unit')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Bulk Cloth Entry Authorized!'); window.location='manage_products.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Inventory | Buildcom Tycoon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .product-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.3s; }
        .product-card:hover { border-color: #1e40af; transform: translateY(-3px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05); }
        .modal-wrapper { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); display: none; position: fixed; inset: 0; z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
        .modal-wrapper.active { display: flex; }
        .sharp-input { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; font-size: 13px; outline: none; transition: all 0.2s; }
        .sharp-input:focus { border-color: #1e40af; box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.05); }
    </style>
</head>
<body class="text-slate-900">

    <header class="w-full px-10 py-8 flex justify-between items-center sticky top-0 bg-white/80 backdrop-blur-md z-40 border-b border-slate-100">
        <div data-aos="fade-right">
            <h1 class="text-xl font-black text-slate-800 uppercase italic tracking-tight">Bulk <span class="text-blue-700">Inventory</span></h1>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.3em]">Corporate Distribution Center</p>
        </div>
        <div class="flex gap-4">
            <button onclick="toggleModal('catModal')" class="bg-white text-slate-900 border border-slate-200 px-5 py-3 rounded-lg text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fa-solid fa-tags"></i> Categories
            </button>
            <button onclick="toggleModal('productModal')" class="bg-slate-900 text-white px-6 py-3 rounded-lg text-xs font-black uppercase tracking-widest hover:bg-blue-700 transition-all flex items-center gap-3 shadow-lg active:scale-95">
                <i class="fa-solid fa-plus-circle"></i> Add Cloth Thaan
            </button>
        </div>
    </header>

    <main class="p-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" data-aos="fade-up">
            <?php
            $res = mysqli_query($conn, "SELECT p.*, f.store_name FROM products p LEFT JOIN franchises f ON p.franchise_id = f.id ORDER BY p.id DESC");
            while($row = mysqli_fetch_assoc($res)) {
                // ALERT AT 10 UNITS OR BELOW [cite: 44]
                $is_low = $row['stock'] <= 10;
                $status_class = $is_low ? 'bg-red-50 text-red-600 border-red-100' : 'bg-green-50 text-green-700 border-green-100';
            ?>
            <div class="product-card p-6 flex flex-col justify-between border-b-4 <?php echo $is_low ? 'border-red-500' : 'border-blue-700'; ?>">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-[9px] font-black text-slate-300 uppercase italic">#<?php echo $row['product_code']; ?></span>
                        <span class="px-3 py-1 border <?php echo $status_class; ?> text-[9px] font-black rounded-full uppercase tracking-tighter">
                            <?php echo $is_low ? 'Stock Alert' : 'Bulk Active'; ?>
                        </span>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase truncate mb-1"><?php echo $row['product_name']; ?></h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4 italic italic">
                        <i class="fa-solid fa-user-shield mr-1 text-blue-600"></i> Added By: <?php echo $row['created_by_admin'] ? 'Admin' : 'Shop'; ?>
                    </p>
                    <div class="flex justify-between items-end border-t border-slate-50 pt-4">
                        <div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Rate/Unit</p>
                            <h2 class="text-md font-black text-slate-900">₹<?php echo number_format($row['price'], 2); ?></h2>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Weight/Qty</p>
                            <h2 class="text-md font-black <?php echo $is_low ? 'text-red-600' : 'text-slate-900'; ?>">
                                <?php echo $row['stock']; ?> <span class="text-[9px] font-medium"><?php echo $row['unit']; ?></span>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </main>

    <div id="productModal" class="modal-wrapper">
        <div class="bg-white w-full max-w-2xl rounded-xl overflow-hidden shadow-2xl" data-aos="zoom-in" data-aos-duration="200">
            <div class="px-10 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest italic">New Fabric Entry</h2>
                <button onclick="toggleModal('productModal')" class="text-slate-400 hover:text-slate-900"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-10">
                <form method="POST" class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Franchise Assignment</label>
                        <select name="franchise_id" class="w-full sharp-input bg-white">
                            <option value="0">Master Warehouse</option>
                            <?php
                            $f_res = mysqli_query($conn, "SELECT id, store_name FROM franchises WHERE status='Active'");
                            while($f_row = mysqli_fetch_assoc($f_res)) {
                                echo "<option value='".$f_row['id']."'>".$f_row['store_name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Cloth Category</label>
                        <div class="flex gap-2">
                            <select name="category" required class="flex-1 sharp-input bg-white">
                                <?php
                                $c_res = mysqli_query($conn, "SELECT category_name FROM categories");
                                while($c_row = mysqli_fetch_assoc($c_res)) {
                                    echo "<option value='".$c_row['category_name']."'>".$c_row['category_name']."</option>";
                                }
                                ?>
                            </select>
                            <button type="button" onclick="toggleModal('catModal')" class="w-12 bg-blue-700 text-white rounded-lg flex items-center justify-center shadow-lg shadow-blue-100">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Cloth Name</label>
                        <input type="text" name="p_name" required placeholder="e.g. Silk Thaan Premium" class="w-full sharp-input">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Rate (per Meter/KG)</label>
                        <input type="number" step="0.01" name="price" required placeholder="₹ 0.00" class="w-full sharp-input">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Total Weight/Meter</label>
                        <input type="number" name="stock" required placeholder="e.g. 100" class="w-full sharp-input">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Select Unit</label>
                        <div class="flex gap-2">
                            <select name="unit" class="flex-1 sharp-input bg-white">
                                <?php
                                $u_res = mysqli_query($conn, "SELECT unit_name FROM units");
                                while($u_row = mysqli_fetch_assoc($u_res)) {
                                    echo "<option value='".$u_row['unit_name']."'>".$u_row['unit_name']."</option>";
                                }
                                ?>
                            </select>
                            <button type="button" onclick="toggleModal('unitModal')" class="w-12 bg-blue-700 text-white rounded-lg flex items-center justify-center shadow-lg shadow-blue-100">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="p_code" value="<?php echo $auto_p_code; ?>">
                    <div class="col-span-2 pt-4">
                        <button type="submit" name="add_product" class="w-full bg-slate-900 text-white font-black py-4 rounded-lg text-[10px] uppercase tracking-[0.2em] shadow-xl hover:bg-blue-700 transition-all">
                            Confirm Stock Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="catModal" class="modal-wrapper" style="z-index: 1100;">
        <div class="bg-white w-full max-w-sm rounded-xl overflow-hidden shadow-2xl" data-aos="zoom-in">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest">Register Category</h2>
                <button onclick="toggleModal('catModal')" class="text-slate-400"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" class="p-8">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2 ml-1">New Category Name</label>
                <input type="text" name="new_category_name" required placeholder="e.g. Silk Thaan" class="w-full sharp-input mb-6">
                <button type="submit" name="quick_category" class="w-full bg-slate-900 text-white font-black py-3 rounded-lg text-[10px] uppercase tracking-widest">
                    Add Category
                </button>
            </form>
        </div>
    </div>

    <div id="unitModal" class="modal-wrapper" style="z-index: 1100;">
        <div class="bg-white w-full max-w-sm rounded-xl overflow-hidden shadow-2xl" data-aos="zoom-in">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest">Register Unit</h2>
                <button onclick="toggleModal('unitModal')" class="text-slate-400"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" class="p-8">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2 ml-1">Unit Name</label>
                <input type="text" name="new_unit_name" required placeholder="e.g. METERS" class="w-full sharp-input mb-6">
                <button type="submit" name="quick_unit" class="w-full bg-blue-700 text-white font-black py-3 rounded-lg text-[10px] uppercase tracking-widest">
                    Add Unit
                </button>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 600, once: true });
        function toggleModal(id) {
            const modal = document.getElementById(id);
            modal.classList.toggle('active');
            document.body.style.overflow = modal.classList.contains('active') ? 'hidden' : 'auto';
        }
    </script>
</body>
</html>