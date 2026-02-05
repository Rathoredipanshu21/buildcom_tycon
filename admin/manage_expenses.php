<?php
include '../config/db.php';
session_start();

// MASTER GATEKEEPER: Ensure only Admin can access
if (!isset($_SESSION['admin']) || $_SESSION['admin'] === 'franchise') {
    header("Location: Login.php"); exit();
}

$admin_id = $_SESSION['admin_id'] ?? 1;

// HANDLE ADMIN EXPENSE SUBMISSION
if (isset($_POST['add_admin_expense'])) {
    $cat = mysqli_real_escape_string($conn, $_POST['category']);
    $amt = mysqli_real_escape_string($conn, $_POST['amount']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['expense_date']);

    // franchise_id is NULL for Admin expenses
    $sql = "INSERT INTO expenses (admin_id, franchise_id, category, amount, description, expense_date) 
            VALUES ('$admin_id', NULL, '$cat', '$amt', '$desc', '$date')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Corporate Expense Registered!'); window.location='manage_expenses.php';</script>";
    }
}

// AGGREGATE STATS
$corporate_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses WHERE admin_id IS NOT NULL"))['total'] ?? 0;
$franchise_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses WHERE franchise_id IS NOT NULL"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Corporate Expense Hub | Buildcom Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; }
        .industrial-header { background: linear-gradient(90deg, #1e3a8a 0%, #3b82f6 100%); border-bottom: 4px solid #0f172a; }
        .erp-card { background: white; border-radius: 4px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .modal-bg { display:none; position:fixed; inset:0; background:rgba(15, 23, 42, 0.7); z-index:100; align-items:center; justify-content:center; }
        .modal-sharp { background:white; padding:2px; border-radius:4px; width:450px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); border: 1px solid #e2e8f0; }
    </style>
</head>
<body class="p-6">

<div class="max-w-7xl mx-auto space-y-6">
    <header class="industrial-header p-8 flex justify-between items-center text-white shadow-lg" data-aos="fade-down">
        <div class="flex items-center gap-6">
            <div class="bg-white p-2 rounded-sm"><img src="../Assets/logo.png" class="h-10"></div>
            <div>
                <h1 class="text-2xl font-black uppercase tracking-tight leading-none">Global <span class="text-blue-100 italic">Expense Audit</span></h1>
                <p class="text-[10px] font-bold text-blue-100 uppercase tracking-widest mt-1">Corporate & Franchise Ledger Control</p>
            </div>
        </div>
        <button onclick="$('#expenseModal').css('display','flex')" class="bg-black text-white px-8 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-white hover:text-black transition-all shadow-xl">
            <i class="fa-solid fa-plus-circle mr-2"></i> Log Admin Expense
        </button>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6" data-aos="fade-up">
        <div class="erp-card p-8 border-l-4 border-blue-600">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Corporate Overhead</p>
            <h2 class="text-2xl font-black text-slate-900 mt-2">₹<?php echo number_format($corporate_total, 2); ?></h2>
        </div>
        <div class="erp-card p-8 border-l-4 border-orange-500">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Franchise Expenditures</p>
            <h2 class="text-2xl font-black text-slate-900 mt-2">₹<?php echo number_format($franchise_total, 2); ?></h2>
        </div>
        <div class="erp-card p-8 border-l-4 border-slate-900 bg-slate-900 text-white">
            <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Total Global Spend</p>
            <h2 class="text-2xl font-black mt-2">₹<?php echo number_format($corporate_total + $franchise_total, 2); ?></h2>
        </div>
    </div>

    <div class="erp-card overflow-hidden" data-aos="fade-up">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="p-6 text-[10px] font-black text-slate-500 uppercase tracking-widest">Ownership</th>
                    <th class="p-6 text-[10px] font-black text-slate-500 uppercase tracking-widest">Category</th>
                    <th class="p-6 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">Date</th>
                    <th class="p-6 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = "SELECT e.*, f.store_name 
                      FROM expenses e 
                      LEFT JOIN franchises f ON e.franchise_id = f.id 
                      ORDER BY e.expense_date DESC LIMIT 50";
                $res = mysqli_query($conn, $q);
                while($row = mysqli_fetch_assoc($res)) {
                    $is_admin = !empty($row['admin_id']);
                ?>
                <tr class="hover:bg-slate-50 transition-all border-b border-slate-50">
                    <td class="p-6">
                        <span class="text-[10px] font-black px-3 py-1 rounded-sm uppercase <?php echo $is_admin ? 'bg-blue-600 text-white' : 'bg-orange-100 text-orange-700'; ?>">
                            <?php echo $is_admin ? 'Master Admin' : $row['store_name']; ?>
                        </span>
                    </td>
                    <td class="p-6">
                        <p class="text-xs font-bold text-slate-800 uppercase"><?php echo $row['category']; ?></p>
                        <p class="text-[9px] text-slate-400 font-bold uppercase mt-1 italic"><?php echo $row['description']; ?></p>
                    </td>
                    <td class="p-6 text-center text-[10px] font-bold text-slate-400">
                        <?php echo date("d M Y", strtotime($row['expense_date'])); ?>
                    </td>
                    <td class="p-6 text-right font-black text-red-600">₹<?php echo number_format($row['amount'], 2); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div id="expenseModal" class="modal-bg">
    <div class="modal-sharp">
        <div class="p-8 border border-slate-200">
            <div class="flex justify-between items-center mb-8 border-b pb-4">
                <h3 class="text-xs font-black uppercase tracking-widest">Log Corporate Expense</h3>
                <button onclick="$('#expenseModal').hide()" class="text-slate-400 hover:text-red-600"><i class="fa-solid fa-square-xmark text-2xl"></i></button>
            </div>
            <form method="POST" class="space-y-6">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase block mb-2">Category</label>
                    <select name="category" required class="w-full border-2 border-slate-100 p-4 font-bold text-xs outline-none focus:border-blue-600">
                        <option>Office Rent</option><option>Admin Salary</option>
                        <option>Marketing</option><option>Taxes / Legal</option>
                        <option>General Procurement</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase block mb-2">Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" required class="w-full border-2 border-slate-100 p-4 font-black text-xl outline-none focus:border-blue-600">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase block mb-2">Expense Date</label>
                    <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full border-2 border-slate-100 p-4 font-bold text-xs outline-none">
                </div>
                <button type="submit" name="add_admin_expense" class="w-full bg-slate-900 text-white font-black py-4 uppercase text-[10px] tracking-widest hover:bg-blue-600 transition-all">Record Expenditure</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>
</body>
</html>