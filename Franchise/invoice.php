<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['franchise_id'])) {
    header("Location: Login.php");
    exit();
}

$logged_f_id = $_SESSION['franchise_id']; 

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $invoice_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT b.*, f.store_name, f.phone as f_phone, f.address as f_addr, f.city, f.state, f.gst_number 
                            FROM bills b 
                            JOIN franchises f ON b.franchise_id = f.id 
                            WHERE b.id = ? AND b.franchise_id = ?");
    $stmt->bind_param("ii", $invoice_id, $logged_f_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Invalid Access.");
    }
    $bill = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $bill['bill_no']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print { .no-print { display: none; } body { padding: 0; background: #fff; } .invoice-container { border: none; box-shadow: none; width: 100%; max-width: 100%; } }
        body { font-family: 'Courier New', Courier, monospace; background: #e2e8f0; padding: 40px; color: #000; }
        .invoice-container { background: #fff; padding: 40px; border: 1px solid #000; max-width: 800px; margin: auto; position: relative; }
        .grid-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .grid-table th, .grid-table td { border: 1px solid #000; padding: 10px; text-align: left; }
        .grid-table th { background: #f1f5f9; text-transform: uppercase; font-size: 12px; }
        .summary-box { border: 1px solid #000; padding: 10px; margin-top: -1px; width: 250px; float: right; }
        .line-item { font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="invoice-container shadow-2xl">
        <div class="flex justify-between items-start border-b-2 border-black pb-6 mb-6">
            <div>
                <h1 class="text-2xl font-black uppercase"><?php echo htmlspecialchars($bill['store_name']); ?></h1>
                <p class="text-xs font-bold"><?php echo htmlspecialchars($bill['f_addr']); ?>, <?php echo htmlspecialchars($bill['city']); ?></p>
                <p class="text-xs font-bold">GSTIN: <?php echo !empty($bill['gst_number']) ? htmlspecialchars($bill['gst_number']) : 'N/A'; ?></p>
                <p class="text-xs font-bold">Contact: <?php echo htmlspecialchars($bill['f_phone']); ?></p>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-black uppercase">Tax Invoice</h2>
                <p class="text-sm font-bold">Bill No: <span class="bg-black text-white px-2">#<?php echo $bill['bill_no']; ?></span></p>
                <p class="text-sm font-bold">Date: <?php echo date("d/m/Y", strtotime($bill['billing_date'])); ?></p>
            </div>
        </div>

        <div class="mb-6 p-4 border border-black bg-slate-50">
            <p class="text-[10px] font-black uppercase text-slate-500 mb-1">Customer Details / Consignee:</p>
            <h3 class="text-lg font-black uppercase"><?php echo htmlspecialchars($bill['customer_name']); ?></h3>
            <p class="text-sm font-bold">Phone: <?php echo htmlspecialchars($bill['customer_phone']); ?></p>
        </div>

        <table class="grid-table">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Product Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $items = $conn->prepare("SELECT * FROM bill_items WHERE bill_id = ?");
                $items->bind_param("i", $invoice_id);
                $items->execute();
                $item_res = $items->get_result();
                $sn = 1;
                while ($it = $item_res->fetch_assoc()) {
                ?>
                <tr class="line-item">
                    <td><?php echo $sn++; ?></td>
                    <td class="uppercase"><?php echo htmlspecialchars($it['product_name']); ?></td>
                    <td class="text-center"><?php echo $it['quantity'] . " " . $it['unit']; ?></td>
                    <td class="text-right">₹<?php echo number_format($it['rate'], 2); ?></td>
                    <td class="text-right">₹<?php echo number_format($it['amount'], 2); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="overflow-hidden">
            <div class="summary-box space-y-2">
                <div class="flex justify-between text-xs font-bold">
                    <span>Sub Total:</span>
                    <span>₹<?php echo number_format($bill['sub_total'], 2); ?></span>
                </div>
                <div class="flex justify-between text-xs font-bold text-red-600">
                    <span>Discount (<?php echo $bill['discount_percent']; ?>%):</span>
                    <span>- ₹<?php echo number_format($bill['sub_total'] * ($bill['discount_percent']/100), 2); ?></span>
                </div>
                <div class="flex justify-between text-xs font-bold">
                    <span>Tax Amount:</span>
                    <span>₹<?php echo number_format($bill['tax_amount'], 2); ?></span>
                </div>
                <div class="flex justify-between border-t border-black pt-2 text-lg font-black uppercase">
                    <span>Total:</span>
                    <span>₹<?php echo number_format($bill['grand_total'], 2); ?></span>
                </div>
            </div>
            <div class="mt-4 text-[10px] font-bold">
                <p>Payment Mode: <?php echo $bill['payment_mode']; ?></p>
                <?php if(!empty($bill['transaction_id'])): ?>
                <p>Txn ID: <?php echo $bill['transaction_id']; ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-dashed border-slate-400 text-center">
            <p class="text-xs font-black uppercase">This is a computer generated invoice. No signature required.</p>
            <p class="text-[10px] mt-1 font-bold">Thank you for your business!</p>
        </div>

        <div class="mt-8 no-print flex gap-2">
            <button onclick="window.print()" class="bg-black text-white px-6 py-2 font-black uppercase text-xs">Print Bill</button>
            <a href="invoice.php" class="bg-slate-200 text-black px-6 py-2 font-black uppercase text-xs">History</a>
        </div>
    </div>
</body>
</html>
<?php
    exit(); 
} 

// LEDGER VIEW
$query = "SELECT * FROM bills WHERE franchise_id = ? ORDER BY billing_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $logged_f_id);
$stmt->execute();
$invoices = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; }
        .table-row:hover { background: #f1f5f9; }
    </style>
</head>
<body class="p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-black uppercase border-b-4 border-black pb-2">Sales Ledger</h1>
            <a href="create_bill.php" class="bg-black text-white px-6 py-3 font-bold text-xs uppercase tracking-widest">
                + New Transaction
            </a>
        </div>

        <div class="bg-white border border-black">
            <table class="w-full text-left">
                <thead class="bg-black text-white text-[10px] uppercase font-black">
                    <tr>
                        <th class="p-4">Billing Date</th>
                        <th class="p-4">Bill No.</th>
                        <th class="p-4">Client Name</th>
                        <th class="p-4 text-right">Invoice Value</th>
                        <th class="p-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php while ($row = $invoices->fetch_assoc()): ?>
                    <tr class="table-row">
                        <td class="p-4 text-xs font-bold"><?php echo date("d-m-Y", strtotime($row['billing_date'])); ?></td>
                        <td class="p-4 text-xs font-black uppercase text-blue-800"><?php echo htmlspecialchars($row['bill_no']); ?></td>
                        <td class="p-4 text-xs font-bold uppercase"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td class="p-4 text-right text-xs font-black">₹<?php echo number_format($row['grand_total'], 2); ?></td>
                        <td class="p-4 text-center">
                            <a href="invoice.php?id=<?php echo $row['id']; ?>" class="bg-slate-100 border border-black px-3 py-1 text-[10px] font-black uppercase">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>