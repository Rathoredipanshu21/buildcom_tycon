<?php
session_start();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../config/db.php'; 
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT password FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if ($password === $row['password']) {
                $_SESSION['admin'] = $username;
                header("Location: index");
                exit();
            } else {
                $error = "Access Denied: Invalid credentials.";
            }
        } else {
            $error = "Access Denied: User not found.";
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
    <title>Admin Login | BUILDCOM TYCOON VENTURES</title>
    <link rel="icon" type="image/x-icon" href="../Assets/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: #0f172a;
            height: 100vh;
            overflow: hidden;
        }
        .bg-gradient-mesh {
            position: fixed;
            inset: 0;
            background-image: 
                radial-gradient(circle at 0% 0%, #1e3a8a 0%, transparent 40%), 
                radial-gradient(circle at 100% 100%, #1e40af 0%, transparent 40%);
            z-index: -1;
        }
        .glass-card { 
            background: rgba(255, 255, 255, 0.98); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="bg-gradient-mesh"></div>
    
    <div class="w-full max-w-md glass-card rounded-[2.5rem] shadow-2xl p-10 relative">
        <div class="text-center mb-8">
            <div class="inline-block p-4 bg-slate-50 rounded-2xl shadow-sm mb-4">
                <img src="../Assets/logo.png" alt="Logo" class="h-12 object-contain">
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 uppercase tracking-tight">Buildcom Tycoon</h1>
            <p class="text-[10px] text-blue-600 font-bold uppercase tracking-[0.3em] mt-1">Administrative Gateway</p>
        </div>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Username</label>
                <div class="relative">
                    <i class="fas fa-user-shield absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="username" required 
                        class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-600 outline-none transition-all text-sm font-semibold text-slate-700" 
                        placeholder="Admin ID">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Secure Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="password" name="password" required 
                        class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-600 outline-none transition-all text-sm font-semibold text-slate-700" 
                        placeholder="••••••••">
                </div>
            </div>

            <button type="submit" 
                class="w-full bg-blue-700 hover:bg-slate-900 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-100 transition-all transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest text-xs">
                Authorize Entry
            </button>






<div class="mt-8 p-4 bg-blue-50/50 rounded-2xl border border-blue-100">
            <p class="text-[9px] text-blue-400 font-bold uppercase tracking-widest mb-2 text-center">Development Credentials</p>
            <div class="flex justify-around text-[10px] text-slate-600 font-bold  tracking-tighter">
                <span>User: <span class="text-blue-700">admin</span></span>
                <span class="h-3 w-px bg-slate-300"></span>
                <span>Pass: <span class="text-blue-700">admin</span></span>
            </div>
</div>
            <?php if($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl text-[11px] font-bold flex items-center gap-3 border border-red-100 animate-pulse">
                    <i class="fas fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
        </form>

        

        <div class="mt-8 text-center">
            <p class="text-[10px] text-slate-400 font-medium">
                &copy; 2026 Buildcom Tycoon Ventures
            </p>
        </div>
    </div>
</body>
</html>












