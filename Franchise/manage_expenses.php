<?php
include '../config/db.php';
session_start();

// SESSION GATEKEEPER
if (!isset($_SESSION['franchise_id'])) {
    header("Location: Login.php");
    exit();
}

$f_id = $_SESSION['franchise_id'];
$store_name = $_SESSION['franchise_name'];

// HANDLE EXPENSE ENTRY
if (isset($_POST['add_expense'])) {
    $cat = mysqli_real_escape_string($conn, $_POST['category']);
    $amt = mysqli_real_escape_string($conn, $_POST['amount']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['expense_date']);

    $sql = "INSERT INTO expenses (franchise_id, category, amount, description, expense_date) 
            VALUES ('$f_id', '$cat', '$amt', '$desc', '$date')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Expense Logged Successfully!'); window.location='manage_expenses.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Ledger | <?php echo $store_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f7ff; color: #1e3a8a; }
        .card-shadow { box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.15); border: 1px solid #e0e7ff; }
        .form-input { width: 100%; padding: 14px 18px; border-radius: 12px; border: 1px solid #e2e8f0; outline: none; transition: 0.3s; font-size: 14px; font-weight: 600; }
        .form-input:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.05); }
    </style>
</head>
<body class="p-8">

    <div class="max-w-6xl mx-auto">
        <header class="mb-10 flex justify-between items-center" data-aos="fade-down">
            <div>
                <h1 class="text-2xl font-black text-slate-900 uppercase italic">Expense <span class="text-blue-600">Tracking</span></h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Operational Cost Management: <?php echo $store_name; ?></p>
            </div>
            <button onclick="document.getElementById('expenseModal').classList.toggle('hidden')" class="bg-blue-600 text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg">
                <i class="fa-solid fa-plus-circle mr-2"></i> Log New Expense
            </button>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <?php
            $total_exp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses WHERE franchise_id = '$f_id'"))['total'] ?? 0;
            $month_exp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses WHERE franchise_id = '$f_id' AND MONTH(expense_date) = MONTH(CURRENT_DATE())"))['total'] ?? 0;
            ?>
            <div class="bg-white p-6 rounded-3xl card-shadow" data-aos="zoom-in" data-aos-delay="100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Monthly Cost</p>
                <h2 class="text-2xl font-black text-red-600 mt-1">₹<?php echo number_format($month_exp, 2); ?></h2>
            </div>
            <div class="bg-white p-6 rounded-3xl card-shadow" data-aos="zoom-in" data-aos-delay="200">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cumulative Spend</p>
                <h2 class="text-2xl font-black text-slate-900 mt-1">₹<?php echo number_format($total_exp, 2); ?></h2>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] card-shadow overflow-hidden" data-aos="fade-up">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase">Category</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase">Description</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase">Date</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php
                    $res = mysqli_query($conn, "SELECT * FROM expenses WHERE franchise_id = '$f_id' ORDER BY expense_date DESC LIMIT 15");
                    while($row = mysqli_fetch_assoc($res)) {
                    ?>
                    <tr class="hover:bg-blue-50/20 transition-all">
                        <td class="px-8 py-6"><span class="text-[10px] font-black bg-blue-50 text-blue-600 px-3 py-1 rounded-lg uppercase"><?php echo $row['category']; ?></span></td>
                        <td class="px-8 py-6 text-xs font-bold text-slate-600"><?php echo $row['description']; ?></td>
                        <td class="px-8 py-6 text-xs font-bold text-slate-400"><?php echo date("d M, Y", strtotime($row['expense_date'])); ?></td>
                        <td class="px-8 py-6 text-right text-sm font-black text-red-600">₹<?php echo number_format($row['amount'], 2); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="expenseModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-6">
        <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden" data-aos="zoom-in">
            <div class="px-8 py-6 bg-slate-50 border-b flex justify-between items-center">
                <h2 class="text-xs font-black uppercase italic tracking-widest">Log Expenditure</h2>
                <button onclick="document.getElementById('expenseModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500"><i class="fa-solid fa-circle-xmark text-xl"></i></button>
            </div>
            <form method="POST" class="p-8 space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Expense Category</label>
                    <select name="category" required class="form-input bg-slate-50">
                        <option>Shop Rent</option>
                        <option>Staff Salary</option>
                        <option>Electricity/Utilities</option>
                        <option>Stock Procurement</option>
                        <option>Marketing</option>
                        <option>Miscellaneous</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" required class="form-input">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Expense Date</label>
                    <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required class="form-input">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Notes / Description</label>
                    <textarea name="description" class="form-input h-24" placeholder="Brief details..."></textarea>
                </div>
                <button type="submit" name="add_expense" class="w-full bg-slate-900 text-white font-black py-4 rounded-xl text-[10px] uppercase tracking-[0.2em] shadow-xl hover:bg-blue-600 transition-all">Authorize Expenditure</button>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init();</script>
</body>
</html>