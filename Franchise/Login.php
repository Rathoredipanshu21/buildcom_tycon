<?php
session_start();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../config/db.php'; 
    $login_id = mysqli_real_escape_string($conn, $_POST['login_id']);
    $password = $_POST['password'];

    // Check by Email OR Phone
    $sql = "SELECT id, store_name FROM franchises WHERE (email = ? OR phone = ?) AND password = ? AND status = 'Active'";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sss", $login_id, $login_id, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $_SESSION['franchise_id'] = $row['id'];
            $_SESSION['franchise_name'] = $row['store_name'];
            $_SESSION['admin'] = 'franchise'; // Set session key for index.php access
            header("Location: index.php");
            exit();
        } else {
            $error = "Access Denied: Invalid credentials or inactive store.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Franchise Login | BUILDCOM TYCOON VENTURES</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-effect { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-[#f0f4f8] min-h-screen flex items-center justify-center p-4" style="background-image: radial-gradient(circle at 0% 0%, #1e3a8a 0%, transparent 40%), radial-gradient(circle at 100% 100%, #1e40af 0%, transparent 40%);">
    
    <div class="w-full max-w-md glass-effect rounded-[2rem] shadow-2xl border border-white p-10">
        <div class="text-center mb-10">
            <img src="../Assets/logo.png" alt="BUILDCOM Logo" class="h-20 mx-auto mb-4 drop-shadow-md">
            <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight ">Franchise <span class="text-blue-700 ">Login</span></h1>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.3em] mt-1">Franchise Terminal Access</p>
        </div>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Email or Phone</label>
                <div class="relative">
                    <i class="fas fa-store absolute left-4 top-4 text-slate-400"></i>
                    <input type="text" name="login_id" required class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-600 outline-none transition-all text-sm font-semibold" placeholder="Enter credentials">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Access Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-4 top-4 text-slate-400"></i>
                    <input type="password" name="password" required class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-600 outline-none transition-all text-sm font-semibold" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="w-full bg-slate-900 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest text-sm">
                Enter Terminal
            </button>

            <?php if($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl text-xs font-bold flex items-center gap-3 border border-red-100">
                    <i class="fas fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
        </form>


        
<div class="mt-8 p-4 bg-blue-50/50 rounded-2xl border border-blue-100">
            <p class="text-[9px] text-blue-400 font-bold uppercase tracking-widest mb-2 text-center">Franchise Credentials</p>
            <div class="flex justify-around text-[10px] text-slate-600 font-bold  tracking-tighter">
                <span>User: <span class="text-blue-700">Shyam@gmail.com</span></span>
                <span class="h-3 w-px bg-slate-300"></span>
                <span>Pass: <span class="text-blue-700">12345</span></span>
            </div>
</div>

        <div class="mt-8 text-center text-[11px] text-slate-400 font-medium italic">
            &copy; 2026 Buildcom Tycoon Ventures Pvt. Ltd.
        </div>
    </div>
</body>
</html>