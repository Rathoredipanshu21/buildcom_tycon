<?php
include '../config/db.php';
$id = $_GET['id'];
$bill_res = mysqli_query($conn, "SELECT * FROM bills WHERE id = $id");
$bill = mysqli_fetch_assoc($bill_res);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice | BUILDCOM TYCOON</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print { .no-print { display: none; } body { background: white; padding: 0; } }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; padding: 40px; }
    </style>
</head>
<body>

    <div class="max-w-4xl mx-auto bg-white p-12 shadow-2xl border-t-[10px] border-blue-700">
        <div class="flex justify-between mb-12">
            <div>
                <img src="../Assets/logo.png" class="h-16 mb-4">
                <h1 class="text-xl font-black text-slate-800 uppercase italic">BUILDCOM TYCOON VENTURES</h1>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">Quality Fabric & Bulk Distribution</p>
                <p class="text-xs text-slate-500 mt-2 font-medium">Main Market Road, Industrial Zone<br>Contact: +91 9204413695 | buildcom@gmail.com</p>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-black text-blue-700 italic uppercase">Invoice</h2>
                <div class="mt-4 space-y-1">
                    <p class="text-xs font-bold text-slate-400 uppercase">Bill No: <span class="text-slate-900 font-black">#<?php echo $bill['bill_no']; ?></span></p>
                    <p class="text-xs font-bold text-slate-400 uppercase">Date: <span class="text-slate-900 font-black"><?php echo date("d M, Y", strtotime($bill['billing_date'])); ?></span></p>
                </div>
            </div>
        </div>

        <div class="bg-slate-50 p-6 rounded-2xl mb-10 flex justify-between">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase mb-2 italic">Billed To:</p>
                <h3 class="font-black text-slate-800 uppercase"><?php echo $bill['customer_name']; ?></h3>
                <p class="text-xs text-slate-600 font-medium"><?php echo $bill['customer_phone']; ?></p>
                <p class="text-xs text-slate-500"><?php echo $bill['customer_address']; ?></p>
            </div>
        </div>

        <table class="w-full mb-10">
            <thead>
                <tr class="text-[10px] font-black text-slate-400 uppercase text-left border-b-2 border-slate-100">
                    <th class="pb-4">Description</th>
                    <th class="pb-4 text-center">Qty/Weight</th>
                    <th class="pb-4 text-right">Rate</th>
                    <th class="pb-4 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $items = mysqli_query($conn, "SELECT * FROM bill_items WHERE bill_id = $id");
                while ($it = mysqli_fetch_assoc($items)) {
                ?>
                <tr class="border-b border-slate-50">
                    <td class="py-4">
                        <span class="text-xs font-black text-slate-800 uppercase"><?php echo $it['product_name']; ?></span>
                    </td>
                    <td class="py-4 text-center text-xs font-bold text-slate-600"><?php echo $it['quantity'] . " " . $it['unit']; ?></td>
                    <td class="py-4 text-right text-xs font-bold text-slate-600">₹<?php echo number_format($it['rate'], 2); ?></td>
                    <td class="py-4 text-right text-xs font-black text-slate-900">₹<?php echo number_format($it['amount'], 2); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="flex justify-end">
            <div class="w-64 space-y-3">
                <div class="flex justify-between text-xs font-bold text-slate-400 uppercase">
                    <span>Sub-Total</span>
                    <span>₹<?php echo number_format($bill['sub_total'], 2); ?></span>
                </div>
                <div class="flex justify-between text-xs font-bold text-blue-600 uppercase">
                    <span>GST (Selected)</span>
                    <span>₹<?php echo number_format($bill['tax_amount'], 2); ?></span>
                </div>
                <div class="flex justify-between border-t-2 border-slate-100 pt-3 text-lg font-black text-slate-900 uppercase italic">
                    <span>Grand Total</span>
                    <span class="text-blue-700">₹<?php echo number_format($bill['grand_total'], 2); ?></span>
                </div>
            </div>
        </div>

        <div class="mt-20 border-t border-slate-100 pt-8 flex justify-between items-end">
            <div>
                <p class="text-[10px] font-black text-slate-300 uppercase italic mb-10">Customer Signature</p>
                <div class="w-40 border-b border-slate-200"></div>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black text-slate-800 uppercase italic">Buildcom Tycoon Ventures</p>
                <p class="text-[8px] text-slate-400 font-bold uppercase mb-8">Authorized Signatory</p>
                <div class="w-40 border-b border-slate-900 ml-auto"></div>
            </div>
        </div>

        <div class="mt-12 no-print flex gap-4">
            <button onclick="window.print()" class="bg-slate-900 text-white px-8 py-3 rounded-xl font-bold text-sm uppercase"><i class="fa-solid fa-print mr-2"></i> Print Invoice</button>
            <a href="Bill_create.php" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold text-sm uppercase">New Bill</a>
        </div>
    </div>

</body>
</html>