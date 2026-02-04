<?php
include '../config/db.php';
session_start();

/**
 * SESSION GATEKEEPER
 * Strictly ensures only an authorized Franchise Partner can access this terminal.
 * Validates 'franchise_id' from the session established during login.
 */
if (!isset($_SESSION['franchise_id'])) {
    header("Location: Login.php");
    exit();
}

// Fetch the unique ID and store identity for the logged-in user
$f_id = $_SESSION['franchise_id']; 
$f_res = mysqli_query($conn, "SELECT * FROM franchises WHERE id = '$f_id'");
$f_info = mysqli_fetch_assoc($f_res);

/**
 * AUTO-GENERATED BILLING REFERENCE
 * Combines Store Identifier + Date + Serial for professional tracking.
 */
$bill_no = "BTV-" . strtoupper(substr($f_info['store_name'], 0, 3)) . "-" . date("Ymd") . "-" . rand(100, 999);

/**
 * AJAX INVENTORY LOOKUP (RESTRICTED)
 * Performs a real-time search that strictly matches the logged-in 'franchise_id'.
 * This ensures Store A cannot see or sell Store B's allocated stock.
 */
if (isset($_GET['search_term'])) {
    $term = "%" . mysqli_real_escape_string($conn, $_GET['search_term']) . "%";
    
    // MANDATORY FILTER: franchise_id must match the session ID
    $query = "SELECT * FROM products 
              WHERE franchise_id = '$f_id' 
              AND (product_name LIKE '$term' OR product_code LIKE '$term' OR category LIKE '$term') 
              AND stock > 0 LIMIT 8";
    
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

/**
 * TRANSACTION FINALIZATION
 * Records the sale and subtracts the exact quantity from THIS franchise's stock.
 */
if (isset($_POST['finalize_bill'])) {
    $c_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $c_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $c_addr = mysqli_real_escape_string($conn, $_POST['customer_address']);
    $sub_total = $_POST['sub_total'];
    $tax_amt = $_POST['tax_total'];
    $grand_total = $_POST['grand_total'];

    // Link the bill to the specific franchise_id
    $ins_bill = "INSERT INTO bills (franchise_id, bill_no, customer_name, customer_phone, customer_address, sub_total, tax_amount, grand_total) 
                 VALUES ('$f_id', '$bill_no', '$c_name', '$c_phone', '$c_addr', '$sub_total', '$tax_amt', '$grand_total')";
    
    if (mysqli_query($conn, $ins_bill)) {
        $bill_id = mysqli_insert_id($conn);
        
        foreach ($_POST['items'] as $item) {
            $p_id = $item['id'];
            $p_name = $item['name'];
            $qty = $item['qty'];
            $unit = $item['unit'];
            $rate = $item['rate'];
            $amt = $item['amt'];

            mysqli_query($conn, "INSERT INTO bill_items (bill_id, product_id, product_name, quantity, unit, rate, amount) 
                                 VALUES ('$bill_id', '$p_id', '$p_name', '$qty', '$unit', '$rate', '$amt')");
            
            // SECURITY CHECK: Only update stock belonging to THIS franchise
            mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $p_id AND franchise_id = '$f_id'");
        }
        header("Location: Invoice.php?id=$bill_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Terminal | <?php echo $f_info['store_name']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .form-input { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; font-size: 14px; transition: all 0.2s; outline: none; }
        .form-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .search-results { position: absolute; width: 100%; background: white; z-index: 100; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); max-height: 300px; overflow-y: auto; margin-top: 4px; }
        .search-item { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; }
        .search-item:hover { background: #f8fafc; color: #2563eb; }
    </style>
</head>
<body class="p-6">

    <div class="max-w-6xl mx-auto bg-white border border-slate-200 rounded-lg shadow-sm">
        
        <div class="bg-slate-900 px-8 py-6 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="bg-white p-2 rounded">
                    <img src="../Assets/logo.png" class="h-10">
                </div>
                <div>
                    <h1 class="text-white font-bold text-lg uppercase"><?php echo $f_info['store_name']; ?></h1>
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                        GST: <?php echo !empty($f_info['gst_number']) ? $f_info['gst_number'] : 'NOT PROVIDED'; ?> | TEL: <?php echo $f_info['phone']; ?>
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-slate-500 text-[10px] font-bold uppercase block mb-1">Invoice ID</span>
                <span class="text-white font-mono bg-white/10 px-3 py-1 rounded border border-white/10"><?php echo $bill_no; ?></span>
            </div>
        </div>

        <form method="POST" id="billingForm" class="p-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 pb-8 border-b border-slate-100">
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold text-slate-500 uppercase">Customer Name</label>
                    <input type="text" name="customer_name" required placeholder="Full Name" class="form-input">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold text-slate-500 uppercase">Contact Number</label>
                    <input type="text" name="customer_phone" required placeholder="+91" class="form-input">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-bold text-slate-500 uppercase">Address</label>
                    <input type="text" name="customer_address" placeholder="Optional" class="form-input">
                </div>
            </div>

            <div class="relative mb-8 bg-slate-50 p-6 rounded-lg border border-slate-100">
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-3"><i class="fa-solid fa-search mr-2"></i> Assigned Stock Search</label>
                <div class="relative">
                    <input type="text" id="pSearch" placeholder="Search by name or product code assigned to this store..." class="w-full form-input bg-white py-3">
                    <div id="results" class="search-results hidden"></div>
                </div>
            </div>

            <div class="overflow-x-auto mb-10">
                <table class="w-full text-sm text-left border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 font-bold text-slate-600 uppercase text-[10px]">Fabric Description</th>
                            <th class="px-6 py-4 font-bold text-slate-600 uppercase text-[10px] text-center">Rate</th>
                            <th class="px-6 py-4 font-bold text-slate-600 uppercase text-[10px] text-center w-32">Qty/Meter</th>
                            <th class="px-6 py-4 font-bold text-slate-600 uppercase text-[10px] text-center">Unit</th>
                            <th class="px-6 py-4 font-bold text-slate-600 uppercase text-[10px] text-right">Amount</th>
                            <th class="px-6 py-4 w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="billBody" class="divide-y divide-slate-100">
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <h3 class="text-[11px] font-bold text-slate-600 uppercase mb-4 tracking-widest">Taxation Selection</h3>
                    <div class="flex flex-wrap gap-3">
                        <label class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded border border-slate-200 cursor-pointer hover:bg-white transition-all">
                            <input type="checkbox" class="tax-check w-4 h-4" data-rate="0.09" name="cgst">
                            <span class="text-[11px] font-bold text-slate-700">CGST @9%</span>
                        </label>
                        <label class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded border border-slate-200 cursor-pointer hover:bg-white transition-all">
                            <input type="checkbox" class="tax-check w-4 h-4" data-rate="0.09" name="sgst">
                            <span class="text-[11px] font-bold text-slate-700">SGST @9%</span>
                        </label>
                        <label class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded border border-slate-200 cursor-pointer hover:bg-white transition-all">
                            <input type="checkbox" class="tax-check w-4 h-4" data-rate="0.18" name="igst">
                            <span class="text-[11px] font-bold text-slate-700">IGST @18%</span>
                        </label>
                    </div>
                </div>

                <div class="bg-slate-50 p-6 rounded-lg border border-slate-200">
                    <div class="flex justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-500 uppercase">Sub Total</span>
                        <span class="text-sm font-bold">₹ <span id="subTotalDisplay">0.00</span></span>
                    </div>
                    <div class="flex justify-between mb-4 pb-4 border-b border-slate-200">
                        <span class="text-[11px] font-bold text-slate-500 uppercase">Tax Amount</span>
                        <span class="text-sm font-bold text-blue-600">₹ <span id="taxDisplay">0.00</span></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-black text-slate-900 uppercase">Payable Total</span>
                        <span class="text-2xl font-black text-slate-900 tracking-tighter">₹ <span id="grandTotalDisplay">0.00</span></span>
                    </div>
                    
                    <input type="hidden" name="sub_total" id="sub_val" value="0">
                    <input type="hidden" name="tax_total" id="tax_val" value="0">
                    <input type="hidden" name="grand_total" id="grand_val" value="0">

                    <button type="submit" name="finalize_bill" class="w-full mt-6 bg-blue-700 hover:bg-slate-900 text-white font-bold py-4 rounded transition-all uppercase text-[11px] tracking-[0.2em]">
                        Submit and Issue Invoice
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let itemIdx = 0;

        /**
         * REAL-TIME SEARCH SCRIPT
         * Only returns items matching the logged-in Store ID.
         */
        $('#pSearch').on('input', function() {
            let val = $(this).val();
            if (val.length > 1) {
                $.getJSON('create_bill.php', { search_term: val }, function(data) {
                    let html = '';
                    if(data.length > 0) {
                        data.forEach(p => {
                            html += `<div class="search-item" onclick="addItem(${p.id}, '${p.product_name}', ${p.price}, '${p.unit}', ${p.stock})">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-slate-900 uppercase">${p.product_name}</span>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">CODE: ${p.product_code} | ${p.category}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-bold text-blue-600">₹${p.price}/${p.unit}</span>
                                            <span class="block text-[9px] text-slate-400 font-bold uppercase">Store Stock: ${p.stock}</span>
                                        </div>
                                     </div>`;
                        });
                    } else {
                        html = '<div class="p-4 text-xs text-slate-400 text-center font-bold uppercase">Product not found or not assigned to this store.</div>';
                    }
                    $('#results').html(html).removeClass('hidden');
                });
            } else { $('#results').addClass('hidden'); }
        });

        function addItem(id, name, rate, unit, stock) {
            itemIdx++;
            let row = `<tr class="item-row" id="row-${itemIdx}">
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-slate-800 uppercase">${name}</span>
                            <input type="hidden" name="items[${itemIdx}][id]" value="${id}">
                            <input type="hidden" name="items[${itemIdx}][name]" value="${name}">
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-semibold text-slate-600">₹${rate}</td>
                        <td class="px-6 py-4">
                            <input type="number" name="items[${itemIdx}][qty]" value="1" min="1" max="${stock}" oninput="calc()" class="qty-input w-full p-2 bg-white rounded border border-slate-200 font-bold text-xs text-center focus:border-blue-500 outline-none">
                        </td>
                        <td class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase">${unit}</td>
                        <input type="hidden" name="items[${itemIdx}][unit]" value="${unit}">
                        <input type="hidden" name="items[${itemIdx}][rate]" value="${rate}">
                        <td class="px-6 py-4 text-right font-bold text-slate-900 text-xs">₹ <span class="row-amt">${rate}</span></td>
                        <input type="hidden" name="items[${itemIdx}][amt]" class="amt-input" value="${rate}">
                        <td class="px-6 py-4 text-right">
                            <button type="button" onclick="$('#row-${itemIdx}').remove(); calc();" class="text-slate-300 hover:text-red-500"><i class="fa-solid fa-xmark"></i></button>
                        </td>
                    </tr>`;
            $('#billBody').append(row);
            $('#results').addClass('hidden');
            $('#pSearch').val('');
            calc();
        }

        function calc() {
            let sub = 0;
            $('.item-row').each(function() {
                let r = parseFloat($(this).find('.qty-input').parent().prev().text().replace('₹', ''));
                let q = parseFloat($(this).find('.qty-input').val()) || 0;
                let a = r * q;
                $(this).find('.row-amt').text(a.toFixed(2));
                $(this).find('.amt-input').val(a.toFixed(2));
                sub += a;
            });
            let taxRate = 0;
            $('.tax-check:checked').each(function() { taxRate += parseFloat($(this).data('rate')); });
            let tax = sub * taxRate;
            let grand = sub + tax;
            $('#subTotalDisplay').text(sub.toLocaleString('en-IN', {minimumFractionDigits: 2}));
            $('#taxDisplay').text(tax.toLocaleString('en-IN', {minimumFractionDigits: 2}));
            $('#grandTotalDisplay').text(grand.toLocaleString('en-IN', {minimumFractionDigits: 2}));
            $('#sub_val').val(sub.toFixed(2));
            $('#tax_val').val(tax.toFixed(2));
            $('#grand_val').val(grand.toFixed(2));
        }
        $('.tax-check').change(calc);
    </script>
</body>
</html>