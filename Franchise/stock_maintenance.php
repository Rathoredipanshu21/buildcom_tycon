<?php
include '../config/db.php';
session_start();

// SESSION GATEKEEPER
if (!isset($_SESSION['franchise_id'])) {
    header("Location: Login.php");
    exit();
}

$logged_f_id = $_SESSION['franchise_id'];
$store_name = $_SESSION['franchise_name'];

// AJAX: Fetch Stock Sale History
if (isset($_GET['fetch_history'])) {
    $p_id = mysqli_real_escape_string($conn, $_GET['p_id']);
    
    // Joint query to get customer details and sale quantities
    $query = "SELECT bi.quantity, bi.unit, b.bill_no, b.customer_name, b.billing_date 
              FROM bill_items bi 
              JOIN bills b ON bi.bill_id = b.id 
              WHERE bi.product_id = '$p_id' AND b.franchise_id = '$logged_f_id' 
              ORDER BY b.billing_date DESC";
    
    $res = mysqli_query($conn, $query);
    $history = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $row['formatted_date'] = date("d M Y, h:i A", strtotime($row['billing_date']));
        $history[] = $row;
    }
    echo json_encode($history);
    exit;
}

// HANDLE MANUAL STOCK UPDATES
if (isset($_POST['update_stock'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $adjustment_type = $_POST['adjustment_type']; 
    $quantity = (float)$_POST['quantity'];
    
    $check_sql = "SELECT stock FROM products WHERE id = '$product_id' AND franchise_id = '$logged_f_id'";
    $current_query = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($current_query) > 0) {
        $row = mysqli_fetch_assoc($current_query);
        $new_stock = ($adjustment_type == 'add') ? ($row['stock'] + $quantity) : ($row['stock'] - $quantity);

        if ($new_stock >= 0) {
            mysqli_query($conn, "UPDATE products SET stock = '$new_stock' WHERE id = '$product_id' AND franchise_id = '$logged_f_id'");
            echo "<script>alert('Stock Ledger Updated!'); window.location='stock_maintenance.php';</script>";
        } else {
            echo "<script>alert('Error: Negative stock not allowed.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Audit | <?php echo $store_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f7ff; }
        .ledger-card { background: white; border-radius: 24px; box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.1); border: 1px solid #e0e7ff; }
        .modal-wrapper { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); display: none; position: fixed; inset: 0; z-index: 1000; align-items: center; justify-content: center; padding: 40px; }
        .modal-wrapper.active { display: flex; }
        .history-item { border-left: 3px solid #2563eb; transition: all 0.2s; }
        .history-item:hover { background: #f8faff; transform: translateX(5px); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="p-8">

    <div class="max-w-6xl mx-auto">
        <header class="mb-10 flex justify-between items-center" data-aos="fade-down">
            <div>
                <h1 class="text-2xl font-black text-slate-900 uppercase italic">Stock <span class="text-blue-600">Audit</span></h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1"><?php echo $store_name; ?> Inventory Terminal</p>
            </div>
            <div class="flex gap-4">
                <div class="px-5 py-2.5 bg-white border border-blue-100 rounded-xl text-[10px] font-black text-blue-600 uppercase tracking-widest shadow-sm">
                   <i class="fa-solid fa-boxes-stacked mr-2"></i> Active Inventory
                </div>
            </div>
        </header>

        <div class="ledger-card overflow-hidden" data-aos="fade-up">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase">Product Details</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase text-center">Category</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase text-center">Stock Level</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase text-right">Terminal Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php
                    $res = mysqli_query($conn, "SELECT * FROM products WHERE franchise_id = '$logged_f_id' ORDER BY stock ASC");
                    while($row = mysqli_fetch_assoc($res)) {
                        $low = $row['stock'] <= 10;
                    ?>
                    <tr class="hover:bg-blue-50/20 transition-all">
                        <td class="px-8 py-6">
                            <span class="text-xs font-black text-slate-800 uppercase italic"><?php echo $row['product_name']; ?></span>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-tighter">Code: <?php echo $row['product_code']; ?></span>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <span class="text-[9px] font-black bg-blue-50 text-blue-600 px-3 py-1 rounded-lg uppercase italic"><?php echo $row['category']; ?></span>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <span class="text-sm font-black <?php echo $low ? 'text-red-600 animate-pulse' : 'text-slate-900'; ?>">
                                <?php echo $row['stock']; ?> <span class="text-[10px] text-slate-400"><?php echo $row['unit']; ?></span>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right space-x-2">
                            <button onclick="openHistory('<?php echo $row['id']; ?>', '<?php echo addslashes($row['product_name']); ?>', '<?php echo $row['stock']; ?>', '<?php echo $row['unit']; ?>')" 
                                    class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </button>
                            <button onclick="openAdjust('<?php echo $row['id']; ?>', '<?php echo addslashes($row['product_name']); ?>', '<?php echo $row['stock']; ?>', '<?php echo $row['unit']; ?>')" 
                                    class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase hover:bg-blue-700 transition-all shadow-md">
                                Adjust
                            </button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="historyModal" class="modal-wrapper">
        <div class="bg-white w-full max-w-2xl rounded-[2.5rem] overflow-hidden shadow-2xl flex flex-col max-h-[80vh]">
            <div class="p-10 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest italic">Stock Transaction History</h2>
                    <p id="h_meta" class="text-[10px] font-bold text-blue-600 mt-1 uppercase"></p>
                </div>
                <button onclick="closeModal('historyModal')" class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-red-500 transition-all flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <div id="historyList" class="flex-1 overflow-y-auto p-10 space-y-4 bg-white">
                </div>

            <div class="p-8 bg-slate-50 border-t border-slate-100 text-center">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em]">Audit Trail Secured by Buildcom Tycoon</p>
            </div>
        </div>
    </div>

    <div id="adjustModal" class="modal-wrapper">
        <div class="bg-white w-full max-w-md rounded-[2rem] overflow-hidden shadow-2xl">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest italic">Ledger Adjustment</h2>
                <button onclick="closeModal('adjustModal')" class="text-slate-400 hover:text-red-500 transition-colors"><i class="fa-solid fa-circle-xmark text-xl"></i></button>
            </div>
            <form method="POST" class="p-8">
                <input type="hidden" name="product_id" id="m_id">
                <div class="mb-6 p-5 bg-blue-50 rounded-2xl border border-blue-100">
                    <h3 id="m_name" class="text-sm font-black text-blue-900 uppercase italic"></h3>
                    <p class="text-[9px] font-black text-blue-400 mt-2 uppercase tracking-widest">Current: <span id="m_stock" class="text-blue-900"></span></p>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <label class="cursor-pointer">
                        <input type="radio" name="adjustment_type" value="add" checked class="hidden peer">
                        <div class="text-center py-4 border border-slate-200 rounded-xl text-[10px] font-black uppercase peer-checked:bg-blue-600 peer-checked:text-white transition-all shadow-sm">Add Stock</div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="adjustment_type" value="reduce" class="hidden peer">
                        <div class="text-center py-4 border border-slate-200 rounded-xl text-[10px] font-black uppercase peer-checked:bg-red-600 peer-checked:text-white transition-all shadow-sm">Reduce Stock</div>
                    </label>
                </div>
                <input type="number" step="0.01" name="quantity" required placeholder="Quantity (0.00)" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-black focus:border-blue-600 outline-none mb-6">
                <button type="submit" name="update_stock" class="w-full bg-slate-900 text-white font-black py-4 rounded-xl text-[10px] uppercase tracking-[0.2em] shadow-xl hover:bg-blue-600 transition-all">Submit Update</button>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();

        function openHistory(id, name, remaining, unit) {
            $('#h_meta').html(`<i class="fa-solid fa-tag mr-1"></i> ${name} | <i class="fa-solid fa-warehouse ml-3 mr-1"></i> Remaining: ${remaining} ${unit}`);
            $('#historyList').html('<div class="py-10 text-center text-slate-400 font-bold uppercase text-[10px] tracking-widest"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Accessing Data Logs...</div>');
            $('#historyModal').addClass('active');
            
            $.getJSON('stock_maintenance.php', { fetch_history: 1, p_id: id }, function(data) {
                let html = '';
                if(data.length > 0) {
                    data.forEach(item => {
                        html += `<div class="history-item p-5 bg-slate-50 rounded-2xl flex justify-between items-center shadow-sm">
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter italic">Sold to: ${item.customer_name}</p>
                                        <h4 class="text-xs font-black text-slate-800 uppercase mt-1">Invoice: #${item.bill_no}</h4>
                                        <p class="text-[9px] font-bold text-blue-500 mt-1"><i class="fa-solid fa-clock mr-1"></i> ${item.formatted_date}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-black text-red-600 italic">-${item.quantity}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase ml-1">${item.unit}</span>
                                    </div>
                                 </div>`;
                    });
                } else {
                    html = '<div class="py-20 text-center"><i class="fa-solid fa-folder-open text-3xl text-slate-200 mb-4"></i><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No Sales Recorded for this Item</p></div>';
                }
                $('#historyList').html(html);
            });
        }

        function openAdjust(id, name, stock, unit) {
            $('#m_id').val(id); $('#m_name').text(name); $('#m_stock').text(stock + ' ' + unit);
            $('#adjustModal').addClass('active');
        }

        function closeModal(id) { $('#' + id).removeClass('active'); }
    </script>
</body>
</html>