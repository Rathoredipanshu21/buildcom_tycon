<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['franchise_id'])) {
    header("Location: Login.php");
    exit();
}

$f_id = $_SESSION['franchise_id'];

// --- 1. HANDLE REPAYMENT SUBMISSION ---
if (isset($_POST['process_repayment'])) {
    $bill_id = $_POST['bill_id'];
    $amount = floatval($_POST['repay_amount']);
    $mode = $_POST['pay_mode'];
    $txn = mysqli_real_escape_string($conn, $_POST['transaction_id'] ?? '');

    // BACKEND VALIDATION: Check remaining amount again before saving
    $check_q = mysqli_query($conn, "SELECT remaining_amount FROM bills WHERE id = '$bill_id'");
    $check_data = mysqli_fetch_assoc($check_q);
    
    if ($amount > $check_data['remaining_amount']) {
        die("Error: Payment exceeds remaining balance.");
    }

    $ins = "INSERT INTO bill_repayments (bill_id, amount_paid, payment_mode, transaction_id) VALUES ('$bill_id', '$amount', '$mode', '$txn')";
    if (mysqli_query($conn, $ins)) {
        $repay_id = mysqli_insert_id($conn);
        mysqli_query($conn, "UPDATE bills SET paid_amount = paid_amount + $amount, remaining_amount = remaining_amount - $amount WHERE id = '$bill_id'");
        // Redirect to a specific Repayment Receipt
        header("Location: repayment_receipt.php?id=$repay_id");
        exit();
    }
}

// AJAX FETCH HISTORY
if (isset($_GET['fetch_history'])) {
    $b_id = $_GET['fetch_history'];
    $res = mysqli_query($conn, "SELECT * FROM bill_repayments WHERE bill_id = '$b_id' ORDER BY payment_date DESC");
    $data = [];
    while($r = mysqli_fetch_assoc($res)) { $data[] = $r; }
    echo json_encode($data);
    exit;
}

// DASHBOARD STATS
$stats_res = mysqli_query($conn, "SELECT COUNT(id) as total_cust, SUM(grand_total) as total_sales, SUM(remaining_amount) as total_due FROM bills WHERE franchise_id = '$f_id'");
$stats = mysqli_fetch_assoc($stats_res);

$search = "";
if (isset($_GET['q'])) {
    $q = mysqli_real_escape_string($conn, $_GET['q']);
    $search = " AND (customer_name LIKE '%$q%' OR customer_phone LIKE '%$q%' OR bill_no LIKE '%$q%')";
}
$customers = mysqli_query($conn, "SELECT * FROM bills WHERE franchise_id = '$f_id' $search ORDER BY remaining_amount DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Credit Ledger | <?php echo $f_id; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        body { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); font-family: 'Segoe UI', sans-serif; }
        .industrial-card { background: white; border: 1px solid #000; border-radius: 0; }
        .btn-industrial { background: #000; color: #fff; padding: 10px 20px; font-weight: 900; text-transform: uppercase; font-size: 12px; border-radius: 0; }
        .input-industrial { border: 1px solid #000; border-radius: 0; padding: 12px; font-weight: bold; width: 100%; outline: none; }
        .modal-industrial { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:1000; align-items:center; justify-content:center; }
        th { background: #0f172a; color: white; font-size: 11px; text-transform: uppercase; padding: 15px; border: 1px solid #334155; }
        td { border: 1px solid #e2e8f0; padding: 12px; }
    </style>
</head>
<body class="p-6">

<div class="max-w-7xl mx-auto space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" data-aos="fade-down">
        <div class="industrial-card p-6 border-l-8 border-l-black">
            <p class="text-[10px] font-black uppercase text-slate-400">Total Customers</p>
            <h3 class="text-3xl font-black"><?php echo $stats['total_cust']; ?></h3>
        </div>
        <div class="industrial-card p-6 border-l-8 border-l-blue-600">
            <p class="text-[10px] font-black uppercase text-slate-400">Total Sales</p>
            <h3 class="text-3xl font-black text-blue-600">₹<?php echo number_format($stats['total_sales'], 2); ?></h3>
        </div>
        <div class="industrial-card p-6 border-l-8 border-l-red-600">
            <p class="text-[10px] font-black uppercase text-slate-400">Total Remaining</p>
            <h3 class="text-3xl font-black text-red-600">₹<?php echo number_format($stats['total_due'], 2); ?></h3>
        </div>
    </div>

    <div class="industrial-card p-6" data-aos="fade-up">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-black uppercase tracking-tighter"><i class="fa-solid fa-list-check mr-2"></i> Debt Ledger</h2>
            <form class="flex gap-2 w-1/3">
                <input type="text" name="q" value="<?php echo $_GET['q'] ?? ''; ?>" placeholder="Search Name/Phone/Bill..." class="input-industrial py-2">
                <button class="btn-industrial px-4"><i class="fa-solid fa-search"></i></button>
            </form>
        </div>

        <table class="w-full border-collapse">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th class="text-right">Grand Total</th>
                    <th class="text-right">Remaining</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($customers)): ?>
                <tr class="hover:bg-slate-50">
                    <td class="text-xs font-bold"><?php echo date("d/m/Y", strtotime($row['billing_date'])); ?></td>
                    <td class="text-xs font-black text-blue-700 uppercase"><?php echo $row['bill_no']; ?></td>
                    <td>
                        <div class="text-xs font-black uppercase"><?php echo $row['customer_name']; ?></div>
                        <div class="text-[10px] text-slate-400 font-bold"><?php echo $row['customer_phone']; ?></div>
                    </td>
                    <td class="text-right font-bold">₹<?php echo number_format($row['grand_total'], 2); ?></td>
                    <td class="text-right font-black text-red-600">₹<?php echo number_format($row['remaining_amount'], 2); ?></td>
                    <td class="text-center space-x-3">
                        <button onclick="viewHistory(<?php echo $row['id']; ?>, '<?php echo $row['bill_no']; ?>')" class="text-slate-400 hover:text-black"><i class="fa-solid fa-history"></i></button>
                        <?php if($row['remaining_amount'] > 0): ?>
                        <button onclick="openPayModal(<?php echo $row['id']; ?>, '<?php echo $row['customer_name']; ?>', <?php echo $row['remaining_amount']; ?>)" class="bg-green-700 text-white px-3 py-1 text-[10px] font-black uppercase">Pay</button>
                        <?php else: ?>
                            <span class="text-[10px] font-black text-green-600 uppercase">Settled</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="payModal" class="modal-industrial">
    <div class="bg-white border-2 border-black w-[450px]">
        <div class="bg-black p-4 text-white font-black text-xs uppercase flex justify-between">
            <span>Process Repayment</span>
            <button onclick="$('#payModal').hide()"><i class="fa-solid fa-times"></i></button>
        </div>
        <form method="POST" class="p-8 space-y-4" onsubmit="return validatePayment()">
            <input type="hidden" name="bill_id" id="pay_bill_id">
            <div class="p-3 bg-slate-100 border border-black mb-4">
                <p id="pay_name" class="text-xs font-black uppercase"></p>
                <p class="text-xs font-bold text-red-600">Max Payable: ₹<span id="max_label"></span></p>
            </div>
            
            <div>
                <label class="text-[10px] font-black uppercase">Paying Amount (₹)</label>
                <input type="number" step="0.01" name="repay_amount" id="repay_input" class="input-industrial text-2xl" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black uppercase">Mode</label>
                    <select name="pay_mode" id="m_mode" class="input-industrial" onchange="toggleTxn()">
                        <option value="Cash">Cash</option>
                        <option value="UPI">UPI</option>
                    </select>
                </div>
                <div id="txn_box" class="hidden">
                    <label class="text-[10px] font-black uppercase">Txn ID</label>
                    <input type="text" name="transaction_id" class="input-industrial">
                </div>
            </div>

            <button type="submit" name="process_repayment" class="w-full bg-blue-700 text-white py-4 font-black uppercase text-xs border-b-4 border-blue-900">Confirm Payment</button>
        </form>
    </div>
</div>

<div id="historyModal" class="modal-industrial">
    <div class="bg-white border-2 border-black w-[500px]">
        <div class="bg-slate-900 p-4 text-white font-black text-xs uppercase flex justify-between">
            <span>Payment History</span>
            <button onclick="$('#historyModal').hide()"><i class="fa-solid fa-times"></i></button>
        </div>
        <div id="hist_body" class="p-6 max-h-[400px] overflow-y-auto space-y-2"></div>
    </div>
</div>

<script>
    AOS.init();
    let currentMax = 0;

    function toggleTxn() {
        if($('#m_mode').val() === 'UPI') $('#txn_box').show();
        else $('#txn_box').hide();
    }

    function openPayModal(id, name, rem) {
        currentMax = rem;
        $('#pay_bill_id').val(id);
        $('#pay_name').text(name);
        $('#max_label').text(rem.toFixed(2));
        $('#repay_input').val(rem).attr('max', rem);
        $('#payModal').css('display','flex');
    }

    function validatePayment() {
        let val = parseFloat($('#repay_input').val());
        if(val > currentMax) {
            alert("Error: You cannot pay more than the remaining balance of ₹" + currentMax);
            return false;
        }
        return true;
    }

    function viewHistory(id, bno) {
        $('#hist_body').html('<p class="text-center font-bold uppercase text-xs">Loading...</p>');
        $('#historyModal').css('display','flex');
        $.getJSON('repayments.php', { fetch_history: id }, function(data) {
            let html = `<p class="text-[10px] font-black text-slate-400 mb-4 uppercase">Ref Bill: ${bno}</p>`;
            data.forEach(h => {
                html += `<div class="p-3 border border-black bg-slate-50 flex justify-between">
                    <div>
                        <div class="text-[10px] font-black uppercase">${h.payment_date}</div>
                        <div class="text-[9px] font-bold text-slate-500">${h.payment_mode} ${h.transaction_id || ''}</div>
                    </div>
                    <div class="font-black text-green-700">₹${parseFloat(h.amount_paid).toFixed(2)}</div>
                </div>`;
            });
            $('#hist_body').html(html);
        });
    }
</script>
</body>
</html>