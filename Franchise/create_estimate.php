<?php
include '../config/db.php';
session_start();

// SECURITY GATEKEEPER: Ensure authorized Franchise access
if (!isset($_SESSION['franchise_id'])) {
    header("Location: Login.php");
    exit();
}

$f_id = $_SESSION['franchise_id']; 
$f_res = mysqli_query($conn, "SELECT * FROM franchises WHERE id = '$f_id'");
$f_info = mysqli_fetch_assoc($f_res);

// DRAFT REFERENCE GENERATION
$est_no = "EST-" . strtoupper(substr($f_info['store_name'], 0, 3)) . "-" . date("Ymd") . "-" . rand(100, 999);

/**
 * AJAX INVENTORY SEARCH
 * Matches the logic from create_bill.php exactly.
 */
if (isset($_GET['search_term'])) {
    $term = "%" . mysqli_real_escape_string($conn, $_GET['search_term']) . "%";
    $query = "SELECT * FROM products WHERE franchise_id = '$f_id' AND (product_name LIKE '$term' OR product_code LIKE '$term') LIMIT 8";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) { 
        $data[] = $row; 
    }
    echo json_encode($data);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Estimate Terminal | <?php echo $f_info['store_name']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', Arial, sans-serif; color: #000; }
        .header-box { background: #0f172a; border-bottom: 4px solid #f59e0b; }
        .card-industrial { background: #fff; border: 1px solid #000; border-radius: 0px; }
        .input-industrial { border: 1px solid #000; border-radius: 0px; padding: 10px; outline: none; font-size: 14px; font-weight: 600; }
        th { background: #0f172a; color: #fff; font-size: 11px; text-transform: uppercase; padding: 12px; border: 1px solid #000; }
        td { border: 1px solid #000; padding: 12px; }
        
        /* PRINT LOGIC: Hide UI elements and show clean invoice */
        @media print {
            body { background: white; padding: 0; }
            .no-print, .search-area, .sidebar, .warning-bar, .delete-col { display: none !important; }
            .header-box { background: white !important; color: black !important; border-bottom: 2px solid black !important; padding: 0 !important; margin-bottom: 20px; }
            .header-box h1, .header-box p { color: black !important; }
            .header-box img { filter: grayscale(1); }
            .card-industrial { border: none !important; box-shadow: none !important; }
            .input-industrial { border: none !important; padding: 0 !important; }
            .main-content { width: 100% !important; }
            table { width: 100% !important; border-collapse: collapse; }
            th { background: #f1f5f9 !important; color: black !important; }
            .print-only { display: block !important; }
        }
        .print-only { display: none; }
    </style>
</head>
<body class="p-4">

<div class="max-w-full mx-auto space-y-4 main-content">
    <header class="header-box p-6 flex justify-between items-center text-white shadow-md">
        <div class="flex items-center gap-4">
            <div class="bg-white p-2 border border-black no-print">
                <img src="../Assets/logo.png" class="h-8">
            </div>
            <div>
                <h1 class="text-2xl font-black uppercase tracking-tight"><?php echo $f_info['store_name']; ?></h1>
                <p class="text-[10px] text-amber-400 font-bold uppercase tracking-widest no-print">Authorized Estimate Terminal</p>
                <div class="print-only text-[10px] font-bold">
                    <?php echo $f_info['address']; ?>, <?php echo $f_info['city']; ?><br>
                    Phone: <?php echo $f_info['phone']; ?>
                </div>
            </div>
        </div>
        <div class="text-right">
            <h2 class="print-only text-2xl font-black uppercase">ESTIMATE / QUOTATION</h2>
            <p class="text-[10px] font-bold text-slate-400 uppercase">Ref Number</p>
            <p class="font-mono text-xl font-bold text-white header-text-black" id="print-est-no"><?php echo $est_no; ?></p>
            <p class="print-only text-[10px] font-bold uppercase mt-1">Date: <?php echo date("d-m-Y"); ?></p>
        </div>
    </header>

    <div class="warning-bar p-3 bg-amber-50 border-l-4 border-amber-500 text-amber-800 text-[10px] font-black uppercase tracking-widest">
        <i class="fa-solid fa-circle-info mr-2"></i> Pro-Forma Mode: No stock deduction or ledger entry will be recorded.
    </div>

    <form method="POST" id="estimateForm" class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="lg:col-span-3 space-y-4">
            <div class="card-industrial p-6 grid grid-cols-2 gap-6 border-t-4 border-black">
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 block mb-1">Customer Name</label>
                    <input type="text" id="cust_name" placeholder="Name" class="input-industrial w-full font-bold">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 block mb-1">Contact Number</label>
                    <input type="text" id="cust_phone" placeholder="Phone" class="input-industrial w-full font-bold">
                </div>
            </div>

            <div class="card-industrial p-6 min-h-[500px]">
                <div class="search-area relative mb-6 no-print">
                    <label class="text-[10px] font-black text-slate-400 uppercase block mb-1">Add Items to Quote</label>
                    <div class="flex">
                        <span class="bg-black text-white p-3 border border-black"><i class="fa-solid fa-search"></i></span>
                        <input type="text" id="pSearch" placeholder="Search product name or barcode..." class="w-full border border-black p-3 outline-none font-bold">
                    </div>
                    <div id="results" class="absolute w-full mt-1 bg-white border-2 border-black z-50 hidden shadow-2xl"></div>
                </div>

                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="text-left">Description of Goods</th>
                            <th class="text-center">Rate (₹)</th>
                            <th class="text-center w-32">Quantity</th>
                            <th class="text-right">Total (₹)</th>
                            <th class="no-print w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="billBody">
                        </tbody>
                </table>
                
                <div class="print-only mt-10 border-t border-black pt-4">
                    <p class="text-[10px] font-bold uppercase italic">Terms: This estimate is valid for 7 days. Price may vary based on market fluctuations.</p>
                </div>
            </div>
        </div>

        <div class="sidebar space-y-4 no-print">
            <div class="card-industrial p-6 border-t-4 border-black sticky top-4">
                <h2 class="text-sm font-black uppercase mb-4 border-b border-black pb-2">Calculations</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between text-xs font-bold">
                        <span>Base Amount</span>
                        <span id="subTotalDisplay">₹0.00</span>
                    </div>

                    <div class="p-3 bg-slate-50 border border-slate-200">
                        <label class="text-[9px] font-black uppercase text-slate-400 block mb-2">Estimated Taxes (18%)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-white border border-black p-1 text-center text-[9px] font-black">CGST 9%</div>
                            <div class="bg-white border border-black p-1 text-center text-[9px] font-black">SGST 9%</div>
                        </div>
                    </div>

                    <div class="bg-black text-white p-4">
                        <div class="flex justify-between items-center text-xs font-bold">
                            <span>TAX AMOUNT</span>
                            <span id="taxDisplay">₹0.00</span>
                        </div>
                        <div class="flex justify-between items-center mt-2 pt-2 border-t border-slate-700">
                            <span class="text-xs font-black uppercase">GRAND TOTAL</span>
                            <span id="grandTotalDisplay" class="text-2xl font-black text-amber-400">₹0.00</span>
                        </div>
                    </div>

                    <button type="button" onclick="window.print()" class="w-full bg-blue-700 text-white font-black py-4 uppercase text-xs tracking-widest border-b-4 border-blue-900 active:translate-y-1">
                        <i class="fa-solid fa-print mr-2"></i> PRINT ESTIMATE
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    let estimateItems = {};

    // --- SEARCH LOGIC FROM CREATE_BILL.PHP ---
    $('#pSearch').on('input', function() {
        let val = $(this).val();
        if (val.length > 1) {
            $.getJSON('create_estimate.php', { search_term: val }, function(data) {
                let html = '';
                if(data.length > 0) {
                    data.forEach(p => {
                        html += `<div class="p-3 cursor-pointer flex justify-between border-b border-black hover:bg-slate-50" onclick='addItem(${JSON.stringify(p)})'>
                            <div>
                                <div class="text-xs font-black uppercase">${p.product_name}</div>
                                <div class="text-[9px] text-slate-500 font-bold uppercase">Code: ${p.product_code}</div>
                            </div>
                            <span class="font-bold">₹${p.price}</span>
                        </div>`;
                    });
                } else {
                    html = `<div class="p-3 text-xs font-black text-red-500 uppercase text-center">No inventory match</div>`;
                }
                $('#results').html(html).removeClass('hidden');
            });
        } else { $('#results').addClass('hidden'); }
    });

    function addItem(p) {
        if (estimateItems[p.id]) { estimateItems[p.id].qty += 1; } 
        else { estimateItems[p.id] = { id: p.id, name: p.product_name, rate: parseFloat(p.price), unit: p.unit, qty: 1 }; }
        renderItems();
        $('#results').addClass('hidden'); $('#pSearch').val('');
    }

    function renderItems() {
        let html = '';
        let total = 0;
        Object.values(estimateItems).forEach(item => {
            let rowTotal = item.rate * item.qty;
            total += rowTotal;
            html += `<tr>
                <td class="font-black text-xs uppercase">${item.name}<br><span class="text-[9px] text-slate-400 font-bold">${item.unit}</span></td>
                <td class="text-center font-bold">₹${item.rate}</td>
                <td class="text-center">
                    <span class="print-only font-bold">${item.qty}</span>
                    <input type="number" value="${item.qty}" oninput="updateQty(${item.id}, this.value)" class="no-print w-16 p-1 border border-black font-black text-xs text-center outline-none">
                </td>
                <td class="text-right font-black">₹${rowTotal.toFixed(2)}</td>
                <td class="no-print text-center"><button onclick="removeItem(${item.id})" class="text-red-600"><i class="fa-solid fa-trash"></i></button></td>
            </tr>`;
        });

        // Add Tax and Grand Total rows for print view specifically
        if (Object.keys(estimateItems).length > 0) {
            let tax = total * 0.18;
            html += `<tr class="print-only">
                <td colspan="3" class="text-right font-black uppercase text-xs">Tax (18%)</td>
                <td class="text-right font-black">₹${tax.toFixed(2)}</td>
            </tr>`;
            html += `<tr class="print-only">
                <td colspan="3" class="text-right font-black uppercase text-xs">Net Amount</td>
                <td class="text-right font-black text-lg">₹${(total + tax).toFixed(2)}</td>
            </tr>`;
        }

        $('#billBody').html(html);
        let tax = total * 0.18;
        $('#subTotalDisplay').text('₹' + total.toFixed(2));
        $('#taxDisplay').text('₹' + tax.toFixed(2));
        $('#grandTotalDisplay').text('₹' + (total + tax).toFixed(2));
    }

    function updateQty(id, val) { estimateItems[id].qty = parseFloat(val) || 0; renderItems(); }
    function removeItem(id) { delete estimateItems[id]; renderItems(); }
</script>
</body>
</html>