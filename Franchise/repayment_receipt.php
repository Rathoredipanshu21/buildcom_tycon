<?php
include '../config/db.php';
session_start();

$rid = $_GET['id'];
$q = mysqli_query($conn, "SELECT r.*, b.bill_no, b.customer_name, b.customer_phone, b.grand_total, b.remaining_amount, f.store_name, f.address 
                          FROM bill_repayments r 
                          JOIN bills b ON r.bill_id = b.id 
                          JOIN franchises f ON b.franchise_id = f.id 
                          WHERE r.id = '$rid'");
$data = mysqli_fetch_assoc($q);
$bill_id = $data['bill_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Repayment Receipt #<?php echo $rid; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print { .no-print { display:none; } }
        body { font-family: 'Courier New', Courier, monospace; background: #fff; padding: 20px; }
        .receipt-box { border: 2px solid #000; padding: 30px; max-width: 600px; margin: auto; }
        .border-t-black { border-top: 2px solid #000; }
        .grid-line td { border: 1px solid #000; padding: 8px; }
    </style>
</head>
<body>

<div class="receipt-box">
    <div class="text-center mb-6">
        <h1 class="text-2xl font-black uppercase"><?php echo $data['store_name']; ?></h1>
        <p class="text-xs font-bold"><?php echo $data['address']; ?></p>
        <h2 class="text-lg font-black mt-4 border-y-2 border-black py-1 uppercase">Repayment Receipt</h2>
    </div>

    <div class="flex justify-between text-xs font-bold mb-6">
        <div>
            <p>Customer: <?php echo $data['customer_name']; ?></p>
            <p>Phone: <?php echo $data['customer_phone']; ?></p>
        </div>
        <div class="text-right">
            <p>Receipt #: RP-<?php echo $rid; ?></p>
            <p>Bill Ref: <?php echo $data['bill_no']; ?></p>
            <p>Date: <?php echo $data['payment_date']; ?></p>
        </div>
    </div>

    <div class="bg-slate-100 p-4 border border-black mb-6">
        <div class="flex justify-between font-black text-sm">
            <span>Amount Paid Now:</span>
            <span>₹<?php echo number_format($data['amount_paid'], 2); ?></span>
        </div>
        <p class="text-[10px] font-bold mt-1">Payment Mode: <?php echo $data['payment_mode']; ?> <?php echo $data['transaction_id']; ?></p>
    </div>

    <h3 class="text-xs font-black uppercase mb-2">Detailed Ledger History</h3>
    <table class="w-full text-[10px] border-collapse mb-6">
        <thead class="bg-black text-white">
            <tr>
                <th class="p-2 border border-black text-left">Date</th>
                <th class="p-2 border border-black text-left">Mode</th>
                <th class="p-2 border border-black text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $h_res = mysqli_query($conn, "SELECT * FROM bill_repayments WHERE bill_id = '$bill_id' ORDER BY payment_date ASC");
            $total_rec = 0;
            while($h = mysqli_fetch_assoc($h_res)){
                $total_rec += $h['amount_paid'];
            ?>
            <tr class="grid-line">
                <td><?php echo $h['payment_date']; ?></td>
                <td><?php echo $h['payment_mode']; ?></td>
                <td class="text-right">₹<?php echo number_format($h['amount_paid'], 2); ?></td>
            </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr class="font-black text-xs">
                <td colspan="2" class="p-2 border border-black text-right">Total Amount Received:</td>
                <td class="p-2 border border-black text-right">₹<?php echo number_format($total_rec, 2); ?></td>
            </tr>
            <tr class="font-black text-xs bg-red-50">
                <td colspan="2" class="p-2 border border-black text-right">Total Bill Value:</td>
                <td class="p-2 border border-black text-right">₹<?php echo number_format($data['grand_total'], 2); ?></td>
            </tr>
            <tr class="font-black text-sm bg-yellow-50">
                <td colspan="2" class="p-2 border border-black text-right">BALANCE DUE:</td>
                <td class="p-2 border border-black text-right">₹<?php echo number_format($data['remaining_amount'], 2); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="text-center text-[10px] font-bold mt-10">
        <p>This is a computer-generated repayment receipt.</p>
        <button onclick="window.print()" class="no-print mt-4 bg-black text-white px-6 py-2 uppercase">Print Receipt</button>
        <a href="repayments.php" class="no-print block mt-2 text-blue-600 underline">Back to Ledger</a>
    </div>
</div>

</body>
</html>