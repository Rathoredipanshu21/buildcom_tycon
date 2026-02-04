<?php
include '../config/db.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header("Location: Login.php");
    exit();
}

// AUTO-GENERATE BILL NUMBER
$bill_no = "BTV-" . date("Ymd") . "-" . strtoupper(substr(uniqid(), -4));

// AJAX Search for Products
if (isset($_GET['search_term'])) {
    $term = "%" . mysqli_real_escape_string($conn, $_GET['search_term']) . "%";
    $query = "SELECT * FROM products WHERE product_name LIKE '$term' OR product_code LIKE '$term' OR category LIKE '$term' LIMIT 8";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// Handle Form Submission
if (isset($_POST['finalize_bill'])) {
    $c_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $c_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $c_addr = mysqli_real_escape_string($conn, $_POST['customer_address']);
    $sub_total = $_POST['sub_total'];
    $tax_amt = $_POST['tax_total'];
    $grand_total = $_POST['grand_total'];

    $ins_bill = "INSERT INTO bills (bill_no, customer_name, customer_phone, customer_address, sub_total, tax_amount, grand_total) 
                 VALUES ('$bill_no', '$c_name', '$c_phone', '$c_addr', '$sub_total', '$tax_amt', '$grand_total')";
    
    if (mysqli_query($conn, $ins_bill)) {
        $bill_id = mysqli_insert_id($conn);
        foreach ($_POST['items'] as $item) {
            $p_id = $item['id'];
            $p_name = $item['name'];
            $qty = $item['qty'];
            $unit = $item['unit'];
            $rate = $item['rate'];
            $amt = $item['amt'];

            mysqli_query($conn, "INSERT INTO bill_items (bill_id, product_id, product_name, quantity, unit, rate, amount) VALUES ('$bill_id', '$p_id', '$p_name', '$qty', '$unit', '$rate', '$amt')");
            // Deduct from Stock
            mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $p_id");
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
    <title>Billing Terminal | BUILDCOM TYCOON</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .form-input { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; font-size: 14px; transition: all 0.2s; }
        .form-input:focus { border-color: #2563eb; outline: none; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .search-results { position: absolute; width: 100%; background: white; z-index: 100; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); max-height: 300px; overflow-y: auto; margin-top: 4px; }
        .search-item { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .search-item:last-child { border-bottom: none; }
        .search-item:hover { background: #f8fafc; color: #2563eb; }
        .table-head { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
    </style>
</head>
<body class="p-6">

    <div class="max-w-6xl mx-auto bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        
        <div class="bg-slate-900 px-8 py-6 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="bg-white p-2 rounded">
                    <img src="../Assets/logo.png" class="h-10">
                </div>
                <div>
                    <h1 class="text-white font-bold text-lg tracking-tight uppercase">BUILDCOM TYCOON VENTURES</h1>
                    <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Enterprise Distribution Billing</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-slate-500 text-[10px] font-bold uppercase block mb-1">Invoice Number</span>
                <span class="text-white font-semibold font-mono bg-white/10 px-3 py-1 rounded border border-white/10"><?php echo $bill_no; ?></span>
            </div>
        </div>

        <form method="POST" id="billingForm" class="p-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 pb-8 border-b border-slate-100">
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-slate-500 uppercase">Customer Name</label>
                    <input type="text" name="customer_name" required placeholder="Full Name" class="form-input">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-slate-500 uppercase">Contact Number</label>
                    <input type="text" name="customer_phone" required placeholder="+91 00000 00000" class="form-input">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-slate-500 uppercase">Shipping Address</label>
                    <input type="text" name="customer_address" placeholder="Destination Details" class="form-input">
                </div>
            </div>

            <div class="relative mb-8 bg-slate-50 p-6 rounded-lg border border-slate-100">
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-3"><i class="fa-solid fa-search mr-2"></i> Inventory Item Lookup</label>
                <div class="relative">
                    <input type="text" id="pSearch" placeholder="Search by Product Name, Code, or Fabric Category..." class="w-full form-input bg-white py-3">
                    <div id="results" class="search-results hidden"></div>
                </div>
            </div>

            <div class="overflow-x-auto mb-10">
                <table class="w-full text-sm text-left border border-slate-200">
                    <thead class="table-head">
                        <tr>
                            <th class="px-6 py-4 font-bold text-slate-600 uppercase text-[11px]">Product Description</th>
                            <th class="px-6 py-4 font-bold text-slate-600 uppercase text-[11px] text-center w-24">Rate</th>
                            <th class="px-6 py-4 font-bold text-slate-600 uppercase text-[11px] text-center w-32">Qty/Meter</th>
                            <th class="px-6 py-4 font-bold text-slate-600 uppercase text-[11px] text-center w-24">Unit</th>
                            <th class="px-6 py-4 font-bold text-slate-600 uppercase text-[11px] text-right w-32">Total</th>
                            <th class="px-6 py-4 w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="billBody" class="divide-y divide-slate-100">
                        </tbody>
                </table>
                <div id="empty-state" class="text-center py-10 border border-t-0 border-slate-200 border-dashed">
                    <p class="text-slate-400 text-sm font-medium">No items added to current session.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <h3 class="text-xs font-bold text-slate-600 uppercase mb-4">Tax Configuration</h3>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded border border-slate-200 cursor-pointer hover:bg-white hover:border-blue-600 transition-all">
                            <input type="checkbox" class="tax-check w-4 h-4 text-blue-600" data-rate="0.09" name="cgst">
                            <span class="text-xs font-bold text-slate-700">CGST @9%</span>
                        </label>
                        <label class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded border border-slate-200 cursor-pointer hover:bg-white hover:border-blue-600 transition-all">
                            <input type="checkbox" class="tax-check w-4 h-4 text-blue-600" data-rate="0.09" name="sgst">
                            <span class="text-xs font-bold text-slate-700">SGST @9%</span>
                        </label>
                        <label class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded border border-slate-200 cursor-pointer hover:bg-white hover:border-blue-600 transition-all">
                            <input type="checkbox" class="tax-check w-4 h-4 text-blue-600" data-rate="0.18" name="igst">
                            <span class="text-xs font-bold text-slate-700">IGST @18%</span>
                        </label>
                    </div>
                </div>

                <div class="bg-slate-50 p-6 rounded-lg border border-slate-200">
                    <div class="flex justify-between mb-2">
                        <span class="text-xs font-semibold text-slate-500 uppercase">Sub Total</span>
                        <span class="text-sm font-bold">₹ <span id="subTotalDisplay">0.00</span></span>
                    </div>
                    <div class="flex justify-between mb-4 pb-4 border-b border-slate-200">
                        <span class="text-xs font-semibold text-slate-500 uppercase">Applicable Taxes</span>
                        <span class="text-sm font-bold text-blue-600">₹ <span id="taxDisplay">0.00</span></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-900 uppercase">Payable Amount</span>
                        <span class="text-2xl font-black text-slate-900 tracking-tight">₹ <span id="grandTotalDisplay">0.00</span></span>
                    </div>
                    
                    <input type="hidden" name="sub_total" id="sub_val" value="0">
                    <input type="hidden" name="tax_total" id="tax_val" value="0">
                    <input type="hidden" name="grand_total" id="grand_val" value="0">

                    <button type="submit" name="finalize_bill" class="w-full mt-6 bg-blue-700 hover:bg-blue-800 text-white font-bold py-4 rounded shadow-md transition-all uppercase text-xs tracking-widest">
                        Submit & Print Invoice
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let itemIdx = 0;

        $('#pSearch').on('input', function() {
            let val = $(this).val();
            if (val.length > 1) {
                $.getJSON('Bill_create.php', { search_term: val }, function(data) {
                    let html = '';
                    if(data.length > 0) {
                        data.forEach(p => {
                            html += `<div class="search-item" onclick="addItem(${p.id}, '${p.product_name}', ${p.price}, '${p.unit}', ${p.stock})">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-slate-900">${p.product_name}</span>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">${p.category}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-bold text-blue-600">₹${p.price}/${p.unit}</span>
                                            <span class="block text-[10px] text-slate-400 font-bold">In Stock: ${p.stock}</span>
                                        </div>
                                     </div>`;
                        });
                    } else {
                        html = '<div class="p-4 text-xs text-slate-400 font-medium text-center">No matching products found</div>';
                    }
                    $('#results').html(html).removeClass('hidden');
                });
            } else { $('#results').addClass('hidden'); }
        });

        function addItem(id, name, rate, unit, stock) {
            $('#empty-state').hide();
            itemIdx++;
            let row = `<tr class="item-row hover:bg-slate-50 transition-colors" id="row-${itemIdx}">
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-slate-800 uppercase">${name}</span>
                            <input type="hidden" name="items[${itemIdx}][id]" value="${id}">
                            <input type="hidden" name="items[${itemIdx}][name]" value="${name}">
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-semibold text-slate-600">₹${rate}</td>
                        <td class="px-6 py-4">
                            <input type="number" name="items[${itemIdx}][qty]" value="1" min="1" max="${stock}" oninput="calc()" class="qty-input w-full p-2 bg-white rounded border border-slate-200 font-bold text-xs text-center focus:border-blue-500 outline-none">
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-bold text-slate-400 uppercase tracking-tighter">${unit}</td>
                        <input type="hidden" name="items[${itemIdx}][unit]" value="${unit}">
                        <input type="hidden" name="items[${itemIdx}][rate]" value="${rate}">
                        <td class="px-6 py-4 text-right font-bold text-slate-900 text-xs">₹ <span class="row-amt">${rate}</span></td>
                        <input type="hidden" name="items[${itemIdx}][amt]" class="amt-input" value="${rate}">
                        <td class="px-6 py-4 text-right">
                            <button type="button" onclick="$('#row-${itemIdx}').remove(); calc(); checkEmpty();" class="text-slate-300 hover:text-red-500 transition-colors"><i class="fa-solid fa-circle-xmark"></i></button>
                        </td>
                    </tr>`;
            $('#billBody').append(row);
            $('#results').addClass('hidden');
            $('#pSearch').val('');
            calc();
        }

        function checkEmpty() {
            if ($('.item-row').length === 0) $('#empty-state').show();
        }

        function calc() {
            let sub = 0;
            $('.item-row').each(function() {
                let r = parseFloat($(this).find('.qty-input').parent().prev().text().replace('₹', ''));
                let q = parseFloat($(this).find('.qty-input').val());
                if(isNaN(q)) q = 0;
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