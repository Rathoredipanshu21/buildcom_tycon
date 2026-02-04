<?php
include '../config/db.php';
session_start();

/**
 * SECURITY CHECK: Ensure user is logged in
 */
if (!isset($_SESSION['franchise_id'])) {
    header("Location: Login.php");
    exit();
}

$logged_f_id = $_SESSION['franchise_id']; 

// --- LOGIC FOR SINGLE INVOICE VIEW ---
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $invoice_id = $_GET['id'];

    /**
     * SECURE QUERY: Strictly match URL 'id' with Logged-in 'franchise_id'
     */
    $stmt = $conn->prepare("SELECT b.*, f.store_name, f.phone as f_phone, f.address as f_addr, f.city, f.state, f.gst_number 
                            FROM bills b 
                            JOIN franchises f ON b.franchise_id = f.id 
                            WHERE b.id = ? AND b.franchise_id = ?");
    $stmt->bind_param("ii", $invoice_id, $logged_f_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("<div style='font-family:Inter, sans-serif; padding:100px; text-align:center; background:#f8fafc; height:100vh;'>
                <div style='background:white; display:inline-block; padding:40px; border-radius:16px; border:1px solid #e2e8f0; box-shadow:0 10px 15px -3px rgba(0,0,0,0.05);'>
                    <h2 style='color:#e11d48; margin-bottom:10px; font-weight:800; text-transform:uppercase;'>Access Denied</h2>
                    <p style='color:#64748b; font-weight:500;'>This invoice belongs to another terminal or does not exist.</p>
                    <a href='invoice.php' style='color:#2563eb; font-weight:700; text-decoration:none; margin-top:20px; display:block;'>Return to Ledger</a>
                </div>
             </div>");
    }
    $bill = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $bill['bill_no']; ?> | <?php echo htmlspecialchars($bill['store_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @media print { .no-print { display: none; } body { background: white; padding: 0; } }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; padding: 40px; color: #1e293b; }
    </style>
</head>
<body>
    <div class="max-w-4xl mx-auto bg-white p-12 shadow-xl border-t-[8px] border-slate-900 rounded-b-xl">
        <div class="flex justify-between mb-12">
            <div>
                <img src="../Assets/logo.png" class="h-12 mb-4" onerror="this.style.display='none'">
                <h1 class="text-base font-bold text-slate-800 uppercase tracking-tight"><?php echo htmlspecialchars($bill['store_name']); ?></h1>
                <div class="text-[11px] text-slate-500 font-medium leading-relaxed">
                    <?php echo htmlspecialchars($bill['f_addr']); ?>, <?php echo htmlspecialchars($bill['city']); ?>, <?php echo htmlspecialchars($bill['state']); ?><br>
                    <span class="font-bold">GSTIN:</span> <?php echo !empty($bill['gst_number']) ? htmlspecialchars($bill['gst_number']) : 'N/A'; ?>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tighter mb-4">Tax Invoice</h2>
                <p class="font-bold text-slate-400 uppercase text-[11px] tracking-wider">Ref: <span class="text-slate-900">#<?php echo htmlspecialchars($bill['bill_no']); ?></span></p>
                <p class="font-bold text-slate-400 uppercase text-[11px] tracking-wider">Date: <span class="text-slate-900"><?php echo date("d M, Y", strtotime($bill['billing_date'])); ?></span></p>
            </div>
        </div>

        <div class="bg-slate-50 p-6 rounded-xl mb-10 border border-slate-100">
            <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Billed To:</p>
            <h3 class="text-sm font-bold text-slate-800 uppercase"><?php echo htmlspecialchars($bill['customer_name']); ?></h3>
            <p class="text-[11px] text-slate-600 font-medium">PH: <?php echo htmlspecialchars($bill['customer_phone']); ?></p>
        </div>

        <table class="w-full mb-10 text-sm">
            <thead class="border-b-2 border-slate-100 text-[10px] font-bold text-slate-400 uppercase">
                <tr>
                    <th class="pb-3 text-left">Description</th>
                    <th class="pb-3 text-center">Qty</th>
                    <th class="pb-3 text-right">Rate</th>
                    <th class="pb-3 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php
                $items = $conn->prepare("SELECT * FROM bill_items WHERE bill_id = ?");
                $items->bind_param("i", $invoice_id);
                $items->execute();
                $item_res = $items->get_result();
                while ($it = $item_res->fetch_assoc()) {
                ?>
                <tr>
                    <td class="py-4 font-bold text-slate-800 uppercase"><?php echo htmlspecialchars($it['product_name']); ?></td>
                    <td class="py-4 text-center font-semibold text-slate-600"><?php echo $it['quantity'] . " " . htmlspecialchars($it['unit']); ?></td>
                    <td class="py-4 text-right">₹<?php echo number_format($it['rate'], 2); ?></td>
                    <td class="py-4 text-right font-bold text-slate-900">₹<?php echo number_format($it['amount'], 2); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="flex justify-end border-t border-slate-100 pt-6">
            <div class="w-64 space-y-2">
                <div class="flex justify-between text-[11px] font-medium text-slate-500 uppercase">
                    <span>Sub-Total</span>
                    <span>₹<?php echo number_format($bill['sub_total'], 2); ?></span>
                </div>
                <div class="flex justify-between bg-slate-900 text-white p-3 rounded-lg mt-4 text-sm font-bold uppercase">
                    <span>Grand Total</span>
                    <span>₹<?php echo number_format($bill['grand_total'], 2); ?></span>
                </div>
            </div>
        </div>

        <div class="mt-12 no-print flex gap-4">
            <button onclick="window.print()" class="bg-slate-900 text-white px-6 py-3 rounded-lg font-bold text-xs uppercase hover:bg-slate-700 transition-all">
                <i class="fa-solid fa-print mr-2"></i> Print
            </button>
            <a href="invoice.php" class="bg-slate-200 text-slate-700 px-6 py-3 rounded-lg font-bold text-xs uppercase hover:bg-slate-300 transition-all">
                Back to History
            </a>
        </div>
    </div>
</body>
</html>
<?php
    exit(); 
} 

// --- LOGIC FOR ALL INVOICES LIST (LEDGER) ---
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
    <title>All Invoices</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background: #f8fafc; }</style>
</head>
<body class="p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-black text-slate-900 uppercase">Billing History</h1>
            <a href="create_bill.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-bold text-xs uppercase tracking-widest shadow-lg">
                <i class="fa-solid fa-plus mr-2"></i> Create New Bill
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-900 text-white uppercase text-[10px] tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Bill No</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4 text-right">Amount</th>
                        <th class="px-6 py-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php while ($row = $invoices->fetch_assoc()): ?>
                    <tr class="hover:bg-slate-50 transition-all">
                        <td class="px-6 py-4 text-xs text-slate-500"><?php echo date("d M, Y", strtotime($row['billing_date'])); ?></td>
                        <td class="px-6 py-4 text-xs font-bold text-slate-900"><?php echo htmlspecialchars($row['bill_no']); ?></td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-bold text-slate-800 uppercase"><?php echo htmlspecialchars($row['customer_name']); ?></div>
                        </td>
                        <td class="px-6 py-4 text-right text-xs font-black">₹<?php echo number_format($row['grand_total'], 2); ?></td>
                        <td class="px-6 py-4 text-center">
                            <a href="invoice.php?id=<?php echo $row['id']; ?>" class="bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 px-4 py-2 rounded font-bold text-[10px] uppercase transition-all">
                                <i class="fa-solid fa-eye mr-1"></i> View Invoice
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>