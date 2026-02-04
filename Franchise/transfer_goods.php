<?php
include '../config/db.php';
session_start();

/** * SESSION GATEKEEPER: Ensure only an authorized Franchise Partner accesses this terminal.
 * 
 */
if (!isset($_SESSION['franchise_id'])) {
    header("Location: Login.php");
    exit();
}

$sender_f_id = $_SESSION['franchise_id'];
$store_name = $_SESSION['franchise_name'];

/**
 * CORE TRANSFER LOGIC
 * 1. Reduce stock from Sender.
 * 2. Check if product exists in Receiver's inventory.
 * 3. Update existing stock OR Insert new product record for Receiver.
 */
if (isset($_POST['execute_transfer'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $receiver_f_id = mysqli_real_escape_string($conn, $_POST['receiver_franchise_id']);
    $transfer_qty = (float)$_POST['quantity'];

    // Prevent transferring to self
    if ($sender_f_id == $receiver_f_id) {
        echo "<script>alert('Error: You cannot transfer goods to your own store.'); window.location='transfer_goods.php';</script>";
        exit();
    }

    // Step A: Fetch Sender's Product Details
    $p_query = mysqli_query($conn, "SELECT * FROM products WHERE id = '$product_id' AND franchise_id = '$sender_f_id'");
    $p_data = mysqli_fetch_assoc($p_query);

    if ($p_data && $p_data['stock'] >= $transfer_qty) {
        mysqli_begin_transaction($conn);

        try {
            // Step B: Deduct from Sender
            mysqli_query($conn, "UPDATE products SET stock = stock - $transfer_qty WHERE id = '$product_id'");

            // Step C: Check if Receiver already has this product (By Code)
            $p_code = $p_data['product_code'];
            $check_receiver = mysqli_query($conn, "SELECT id FROM products WHERE product_code = '$p_code' AND franchise_id = '$receiver_f_id'");

            if (mysqli_num_rows($check_receiver) > 0) {
                // Update existing stock for receiver
                mysqli_query($conn, "UPDATE products SET stock = stock + $transfer_qty WHERE product_code = '$p_code' AND franchise_id = '$receiver_f_id'");
            } else {
                // Create new entry for receiver
                $p_name = mysqli_real_escape_string($conn, $p_data['product_name']);
                $cat = mysqli_real_escape_string($conn, $p_data['category']);
                $price = $p_data['price'];
                $unit = mysqli_real_escape_string($conn, $p_data['unit']);

                mysqli_query($conn, "INSERT INTO products (product_code, franchise_id, created_by_franchise, product_name, category, price, stock, unit) 
                                     VALUES ('$p_code', '$receiver_f_id', '$sender_f_id', '$p_name', '$cat', '$price', '$transfer_qty', '$unit')");
            }

            mysqli_commit($conn);
            echo "<script>alert('Transfer Successful! Goods shifted to partner store.'); window.location='transfer_goods.php';</script>";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "<script>alert('Transfer Failed: Database Error.'); window.location='transfer_goods.php';</script>";
        }
    } else {
        echo "<script>alert('Error: Insufficient stock available for transfer.'); window.location='transfer_goods.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Goods Transfer | <?php echo $store_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f7ff; color: #1e3a8a; }
        .transfer-card { 
            background: white; border-radius: 24px; padding: 40px; 
            box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.15); 
            border: 1px solid #e0e7ff;
        }
        .input-field { 
            width: 100%; padding: 14px 18px; border-radius: 12px; 
            border: 1px solid #e2e8f0; outline: none; transition: 0.3s;
            font-size: 14px; font-weight: 600;
        }
        .input-field:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.05); }
    </style>
</head>
<body class="p-8 md:p-12">

    <div class="max-w-4xl mx-auto">
        <div class="mb-10 flex justify-between items-center" data-aos="fade-down">
            <div>
                <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight italic">Stock <span class="text-blue-600">Distribution</span></h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-1">Inter-Franchise Goods Transfer Protocol</p>
            </div>
            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-lg border border-slate-100">
                <i class="fa-solid fa-truck-fast text-blue-600"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1" data-aos="fade-right">
                <div class="bg-blue-600 rounded-3xl p-8 text-white shadow-xl shadow-blue-200">
                    <h3 class="font-extrabold text-lg leading-tight mb-4">Transfer Guidelines</h3>
                    <ul class="space-y-4 text-[11px] font-semibold opacity-90 uppercase tracking-wider">
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle-check mt-1"></i> Stock is deducted instantly from your ledger.</li>
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle-check mt-1"></i> Receiving store gets updated via product code match.</li>
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle-check mt-1"></i> New entries created if product is new to receiver.</li>
                    </ul>
                    <div class="mt-10 pt-6 border-t border-white/10">
                        <p class="text-[10px] opacity-60">SENDER STORE</p>
                        <p class="font-black text-sm uppercase italic"><?php echo $store_name; ?></p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2" data-aos="fade-left">
                <form method="POST" class="transfer-card">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Select Fabric/Goods from Your Stock</label>
                            <select name="product_id" required class="input-field bg-slate-50">
                                <option value="">-- Browse Your Inventory --</option>
                                <?php
                                $my_stock = mysqli_query($conn, "SELECT id, product_name, stock, unit FROM products WHERE franchise_id = '$sender_f_id' AND stock > 0");
                                while($row = mysqli_fetch_assoc($my_stock)) {
                                    echo "<option value='".$row['id']."'>".$row['product_name']." (Avail: ".$row['stock']." ".$row['unit'].")</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Quantity to Transfer</label>
                                <input type="number" step="0.01" name="quantity" required placeholder="0.00" class="input-field">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Target Franchise</label>
                                <select name="receiver_franchise_id" required class="input-field">
                                    <option value="">-- Select Receiving Store --</option>
                                    <?php
                                    $others = mysqli_query($conn, "SELECT id, store_name, city FROM franchises WHERE id != '$sender_f_id' AND status='Active'");
                                    while($row = mysqli_fetch_assoc($others)) {
                                        echo "<option value='".$row['id']."'>".$row['store_name']." (".$row['city'].")</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" name="execute_transfer" class="w-full bg-slate-900 text-white font-black py-4 rounded-xl text-[10px] uppercase tracking-[0.2em] shadow-xl hover:bg-blue-600 transition-all active:scale-[0.98]">
                                <i class="fa-solid fa-paper-plane mr-2"></i> Authorize Transfer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>
</body>
</html>1