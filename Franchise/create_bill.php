<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['franchise_id'])) {
    header("Location: Login.php");
    exit();
}

$f_id = $_SESSION['franchise_id']; 
$f_res = mysqli_query($conn, "SELECT * FROM franchises WHERE id = '$f_id'");
$f_info = mysqli_fetch_assoc($f_res);
$bill_no = "BTV-" . strtoupper(substr($f_info['store_name'], 0, 3)) . "-" . date("Ymd") . "-" . rand(100, 999);

// AJAX Search
if (isset($_GET['search_term'])) {
    $term = "%" . mysqli_real_escape_string($conn, $_GET['search_term']) . "%";
    $query = "SELECT * FROM products WHERE franchise_id = '$f_id' AND (product_name LIKE '$term' OR product_code LIKE '$term') AND stock > 0 LIMIT 8";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) { $data[] = $row; }
    echo json_encode($data);
    exit;
}

// Finalize Bill Logic
if (isset($_POST['finalize_bill'])) {
    $c_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $c_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $sub_total = $_POST['sub_total'];
    $discount_p = $_POST['discount_percent'];
    $tax_amt = $_POST['tax_total'];
    $grand_total = $_POST['grand_total'];
    $paid_amt = $_POST['paid_amount'];
    $rem_amt = $grand_total - $paid_amt;
    $pay_mode = $_POST['payment_mode'];
    $txn_id = mysqli_real_escape_string($conn, $_POST['transaction_id'] ?? '');

    $ins_bill = "INSERT INTO bills (franchise_id, bill_no, customer_name, customer_phone, sub_total, discount_percent, tax_amount, grand_total, paid_amount, remaining_amount, payment_mode, transaction_id) 
                 VALUES ('$f_id', '$bill_no', '$c_name', '$c_phone', '$sub_total', '$discount_p', '$tax_amt', '$grand_total', '$paid_amt', '$rem_amt', '$pay_mode', '$txn_id')";
    
    if (mysqli_query($conn, $ins_bill)) {
        $bill_id = mysqli_insert_id($conn);
        mysqli_query($conn, "INSERT INTO bill_repayments (bill_id, amount_paid, payment_mode, transaction_id) VALUES ('$bill_id', '$paid_amt', '$pay_mode', '$txn_id')");
        
        foreach ($_POST['items'] as $item) {
            mysqli_query($conn, "INSERT INTO bill_items (bill_id, product_id, product_name, quantity, unit, rate, amount) 
                                 VALUES ('$bill_id', '".$item['id']."', '".$item['name']."', '".$item['qty']."', '".$item['unit']."', '".$item['rate']."', '".$item['amt']."')");
            mysqli_query($conn, "UPDATE products SET stock = stock - ".$item['qty']." WHERE id = ".$item['id']);
        }
        header("Location: invoice.php?id=$bill_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enterprise Billing | <?php echo $f_info['store_name']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { background: linear-gradient(to bottom, #ffffff, #eef2ff); font-family: 'Segoe UI', Arial, sans-serif; color: #000; min-height: 100vh; }
        .header-box { background: #0f172a; border-bottom: 4px solid #f59e0b; }
        .card-industrial { background: #fff; border: 1px solid #000; border-radius: 0px; }
        .input-industrial { border: 1px solid #000; border-radius: 0px; padding: 10px; outline: none; font-size: 14px; font-weight: 600; }
        .label-industrial { font-size: 11px; font-weight: 900; color: #334155; text-transform: uppercase; display: block; margin-bottom: 4px; }
        .gst-btn { border: 1px solid #000; background: #fff; padding: 8px; text-align: center; font-size: 10px; font-weight: 900; cursor: pointer; }
        .gst-btn.active { background: #0f172a; color: #fff; }
        .glass-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:100; align-items:center; justify-content:center; }
        .modal-content { background:white; border: 2px solid #000; width: 400px; }
        table th { background: #0f172a; color: #fff; font-size: 11px; text-transform: uppercase; padding: 12px; }
        table td { border-bottom: 1px solid #e2e8f0; padding: 12px; }
    </style>
</head>
<body class="p-4">

<div class="max-w-full mx-auto space-y-4">
    <div class="header-box p-6 flex justify-between items-center text-white shadow-md">
        <div class="flex items-center gap-4">
            <div class="bg-white p-2 border border-black"><img src="../Assets/logo.png" class="h-8"></div>
            <div>
                <h1 class="text-2xl font-black uppercase tracking-tight"><?php echo $f_info['store_name']; ?></h1>
                <p class="text-[10px] text-amber-400 font-bold uppercase tracking-widest">Authorized Billing Terminal</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-bold text-slate-400 uppercase">System Bill No.</p>
            <p class="font-mono text-xl font-bold text-white"><?php echo $bill_no; ?></p>
        </div>
    </div>

    <form method="POST" id="billingForm" class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="lg:col-span-3 space-y-4">
            <div class="card-industrial p-6 grid grid-cols-2 gap-6 border-t-4 border-black">
                <div>
                    <label class="label-industrial">Customer Full Name</label>
                    <input type="text" name="customer_name" required placeholder="Ex: Rahul Sharma" class="input-industrial w-full">
                </div>
                <div>
                    <label class="label-industrial">Customer Contact</label>
                    <input type="text" name="customer_phone" required placeholder="+91" class="input-industrial w-full">
                </div>
            </div>

            <div class="card-industrial p-6 min-h-[600px]">
                <div class="relative mb-6">
                    <label class="label-industrial">Search Products (Code or Name)</label>
                    <div class="flex">
                        <span class="bg-black text-white p-3 border border-black"><i class="fa-solid fa-barcode"></i></span>
                        <input type="text" id="pSearch" placeholder="Type to search..." class="w-full border border-black p-3 outline-none font-bold">
                    </div>
                    <div id="results" class="absolute w-full mt-1 bg-white border-2 border-black z-50 hidden shadow-2xl"></div>
                </div>

                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="text-left">Product / Description</th>
                            <th class="text-center">Unit Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Total Price</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="billBody"></tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card-industrial p-6 border-t-4 border-black">
                <h2 class="text-sm font-black uppercase mb-4 border-b border-black pb-2">Billing Calculations</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between text-sm font-bold">
                        <span>Base Amount</span>
                        <span id="subTotalDisplay">₹0.00</span>
                    </div>

                    <div class="p-3 bg-slate-50 border border-slate-200">
                        <label class="label-industrial mb-2">Tax Logic</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer">
                                <input type="checkbox" class="tax-check hidden" data-rate="0.09" id="cgst_check">
                                <div class="gst-btn" id="cgst_label">CGST 9%</div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="checkbox" class="tax-check hidden" data-rate="0.09" id="sgst_check">
                                <div class="gst-btn" id="sgst_label">SGST 9%</div>
                            </label>
                        </div>
                        <label class="cursor-pointer mt-2 block">
                            <input type="checkbox" class="tax-check hidden" data-rate="0.18" id="igst_check">
                            <div class="gst-btn" id="igst_label">IGST 18% (Interstate)</div>
                        </label>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b">
                        <button type="button" onclick="openDiscountModal()" class="text-[10px] font-black bg-red-700 text-white px-2 py-1">APPLY DISCOUNT</button>
                        <div class="text-right">
                            <span id="discountDisplay" class="text-red-700 font-bold">- ₹0.00</span>
                            <p class="text-[9px] font-bold text-slate-400" id="disc_perc_label">DISC: 0%</p>
                        </div>
                    </div>

                    <div class="bg-black text-white p-3">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold uppercase">Total Tax</span>
                            <span id="taxDisplay" class="font-bold">₹0.00</span>
                        </div>
                        <div class="flex justify-between items-center mt-2 pt-2 border-t border-slate-700">
                            <span class="text-xs font-black uppercase">Net Payable</span>
                            <span id="grandTotalDisplay" class="text-2xl font-black text-amber-400">₹0.00</span>
                        </div>
                    </div>

                    <div class="space-y-3 pt-4">
                        <div>
                            <label class="label-industrial">Payment Mode</label>
                            <select name="payment_mode" id="payMode" onchange="toggleTxn()" class="w-full p-2 border border-black font-bold text-sm">
                                <option value="Cash">Cash Payment</option>
                                <option value="UPI">UPI / QR Scan</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div id="txnContainer" class="hidden">
                            <label class="label-industrial">Ref No. / Transaction ID</label>
                            <input type="text" name="transaction_id" class="w-full p-2 border border-black text-sm">
                        </div>

                        <div>
                            <label class="label-industrial text-green-700">Paid Amount (₹)</label>
                            <input type="number" step="0.01" name="paid_amount" id="paidAmount" oninput="calc()" class="w-full p-3 border-2 border-black text-lg font-black bg-green-50">
                        </div>

                        <div class="flex justify-between bg-white border border-black p-2">
                            <span class="label-industrial m-0">Balance Due</span>
                            <span id="remDisplay" class="font-black text-red-600">₹0.00</span>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="sub_total" id="sub_val" value="0">
                <input type="hidden" name="discount_percent" id="disc_val" value="0">
                <input type="hidden" name="tax_total" id="tax_val" value="0">
                <input type="hidden" name="grand_total" id="grand_val" value="0">

                <button type="submit" name="finalize_bill" class="w-full bg-blue-700 text-white font-black py-4 mt-6 border-b-4 border-blue-900 hover:bg-blue-800 uppercase text-xs tracking-widest active:translate-y-1 transition-all">
                    Finalize & Generate Invoice
                </button>
            </div>
        </div>
    </form>
</div>

<div id="rateModal" class="glass-modal">
    <div class="modal-content">
        <div class="bg-black p-3 text-white font-bold text-xs uppercase">Edit Unit Price</div>
        <div class="p-6">
            <p id="rateModalName" class="text-xs font-bold text-slate-500 uppercase mb-4"></p>
            <input type="number" id="newRateInput" class="w-full input-industrial text-xl font-bold mb-6">
            <input type="hidden" id="targetRowId">
            <div class="grid grid-cols-2 gap-2">
                <button onclick="$('#rateModal').hide()" class="p-3 text-xs font-bold bg-slate-200">CANCEL</button>
                <button onclick="applyNewRate()" class="p-3 text-xs font-bold bg-black text-white">UPDATE</button>
            </div>
        </div>
    </div>
</div>

<div id="discountModal" class="glass-modal">
    <div class="modal-content">
        <div class="bg-red-700 p-3 text-white font-bold text-xs uppercase">Set Discount Percentage</div>
        <div class="p-6">
            <input type="number" id="discInput" placeholder="Enter %" class="w-full input-industrial text-xl font-bold mb-6">
            <div class="grid grid-cols-2 gap-2">
                <button onclick="$('#discountModal').hide()" class="p-3 text-xs font-bold bg-slate-200">CANCEL</button>
                <button onclick="applyDiscount()" class="p-3 text-xs font-bold bg-red-700 text-white">APPLY</button>
            </div>
        </div>
    </div>
</div>

<script>
    let billItems = {};

    function toggleTxn() {
        if ($('#payMode').val() === 'Cash') $('#txnContainer').addClass('hidden');
        else $('#txnContainer').removeClass('hidden');
    }

    $('.tax-check').change(function() {
        const id = $(this).attr('id');
        if($(this).is(':checked')) $(`#${id.replace('check', 'label')}`).addClass('active');
        else $(`#${id.replace('check', 'label')}`).removeClass('active');
        calc();
    });

    $('#pSearch').on('input', function() {
        let val = $(this).val();
        if (val.length > 1) {
            $.getJSON('create_bill.php', { search_term: val }, function(data) {
                let html = '';
                data.forEach(p => {
                    html += `<div class="p-3 cursor-pointer flex justify-between border-b border-black hover:bg-slate-100" onclick='addItem(${JSON.stringify(p)})'>
                        <div class="text-xs font-black uppercase">${p.product_name} <span class="text-slate-500">[STK: ${p.stock}]</span></div>
                        <span class="font-bold">₹${p.price}</span>
                    </div>`;
                });
                $('#results').html(html).removeClass('hidden');
            });
        } else $('#results').addClass('hidden');
    });

    function addItem(p) {
        if (billItems[p.id]) billItems[p.id].qty += 1;
        else billItems[p.id] = { id: p.id, name: p.product_name, rate: parseFloat(p.price), unit: p.unit, qty: 1, stock: p.stock };
        renderItems();
        $('#results').addClass('hidden');
        $('#pSearch').val('');
    }

    function renderItems() {
        let html = '';
        Object.values(billItems).forEach(item => {
            html += `<tr>
                <td class="font-bold uppercase text-xs">${item.name}<input type="hidden" name="items[${item.id}][id]" value="${item.id}"><input type="hidden" name="items[${item.id}][name]" value="${item.name}"><input type="hidden" name="items[${item.id}][unit]" value="${item.unit}"></td>
                <td class="text-center"><button type="button" onclick="changeRate(${item.id}, '${item.name}')" class="border border-black px-2 py-1 font-bold">₹${item.rate}</button><input type="hidden" name="items[${item.id}][rate]" value="${item.rate}"></td>
                <td class="text-center"><input type="number" name="items[${item.id}][qty]" value="${item.qty}" oninput="updateQty(${item.id}, this.value)" class="w-16 border border-black p-1 text-center font-bold"></td>
                <td class="text-right font-bold">₹${(item.rate * item.qty).toFixed(2)}<input type="hidden" name="items[${item.id}][amt]" value="${(item.rate * item.qty).toFixed(2)}"></td>
                <td class="text-center"><button onclick="removeItem(${item.id})" class="text-red-600"><i class="fa-solid fa-trash"></i></button></td>
            </tr>`;
        });
        $('#billBody').html(html);
        calc();
    }

    function changeRate(id, name) {
        $('#targetRowId').val(id);
        $('#rateModalName').text(name);
        $('#newRateInput').val(billItems[id].rate);
        $('#rateModal').css('display','flex');
    }

    function applyNewRate() {
        let id = $('#targetRowId').val();
        billItems[id].rate = parseFloat($('#newRateInput').val());
        $('#rateModal').hide();
        renderItems();
    }

    function openDiscountModal() { $('#discountModal').css('display','flex'); }
    function applyDiscount() { $('#disc_val').val($('#discInput').val()); $('#discountModal').hide(); calc(); }
    function updateQty(id, val) { billItems[id].qty = parseFloat(val); renderItems(); }
    function removeItem(id) { delete billItems[id]; renderItems(); }

    function calc() {
        let sub = 0;
        Object.values(billItems).forEach(item => { sub += item.rate * item.qty; });
        let discP = parseFloat($('#disc_val').val()) || 0;
        let discAmt = sub * (discP / 100);
        let afterDisc = sub - discAmt;
        let taxRate = 0;
        $('.tax-check:checked').each(function() { taxRate += parseFloat($(this).data('rate')); });
        let tax = afterDisc * taxRate;
        let grand = afterDisc + tax;
        let paid = parseFloat($('#paidAmount').val()) || 0;

        $('#subTotalDisplay').text('₹' + sub.toFixed(2));
        $('#discountDisplay').text('- ₹' + discAmt.toFixed(2));
        $('#disc_perc_label').text('DISC: ' + discP + '%');
        $('#taxDisplay').text('₹' + tax.toFixed(2));
        $('#grandTotalDisplay').text('₹' + grand.toFixed(2));
        $('#remDisplay').text('₹' + (grand - paid).toFixed(2));

        $('#sub_val').val(sub.toFixed(2));
        $('#tax_val').val(tax.toFixed(2));
        $('#grand_val').val(grand.toFixed(2));
    }
</script>
</body>
</html>