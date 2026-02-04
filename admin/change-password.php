<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

$msg = "";
$msg_type = "";

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    $admin_user = $_SESSION['admin'];

    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $msg = "All fields are required.";
        $msg_type = "error";
    } elseif ($new_pass !== $confirm_pass) {
        $msg = "New passwords do not match.";
        $msg_type = "error";
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $admin_user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashed_password = $row['password'];

            if (password_verify($current_pass, $hashed_password)) {
                $new_hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $new_hashed_pass, $row['id']);
                
                if ($updateStmt->execute()) {
                    $msg = "Security credentials updated successfully!";
                    $msg_type = "success";
                } else {
                    $msg = "Error updating database.";
                    $msg_type = "error";
                }
            } else {
                $msg = "Incorrect current password.";
                $msg_type = "error";
            }
        } else {
            $msg = "Session error: Admin user not found.";
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings | Srishti Polytech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; }
        .bg-royal-gradient { background: linear-gradient(135deg, #2563EB 0%, #1E40AF 100%); }
        .form-input:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        .strength-bar {
            height: 6px;
            border-radius: 10px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 0%;
        }
    </style>
</head>
<body class="overflow-x-hidden">

    <nav class="bg-[#0f172a] text-white shadow-xl border-b-4 border-[#2563EB]">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-[#2563EB] rounded-xl flex items-center justify-center font-bold text-xl shadow-lg transform rotate-3">
                    <i class="fa-solid fa-shield-halved text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">Access Control</h1>
                    <p class="text-[10px] text-blue-300 uppercase tracking-widest font-black">Secure System Management</p>
                </div>
            </div>
            <a href="../index.php" class="bg-white/10 hover:bg-white/20 px-5 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2 border border-white/10">
                <i class="fa-solid fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 mt-12 pb-20">
        
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-4" data-aos="fade-down">
            <div>
                <h2 class="text-4xl font-black text-slate-800 tracking-tight">Update <span class="text-[#2563EB]">Credentials</span></h2>
                <div class="w-20 h-1.5 bg-[#3B72ED] mt-2 rounded-full"></div>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="mb-8 p-5 rounded-2xl text-white font-bold shadow-xl flex items-center gap-4 <?php echo $msg_type == 'success' ? 'bg-emerald-500' : 'bg-rose-500'; ?>" data-aos="zoom-in">
                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center text-xl">
                    <i class="fa-solid <?php echo $msg_type == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                </div>
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl border border-slate-100 h-full" data-aos="fade-right">
                <form method="POST" class="space-y-8">
                    
                    <div>
                        <label class="block text-slate-500 font-black mb-3 text-[11px] uppercase tracking-[0.2em]">Verify Identity</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-blue-400">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <input type="password" name="current_password" required class="form-input w-full bg-slate-50 border border-slate-200 rounded-2xl pl-12 pr-4 py-4 outline-none transition font-medium" placeholder="Current Password">
                        </div>
                    </div>

                    <div class="border-t border-slate-100 my-6"></div>

                    <div>
                        <label class="block text-slate-500 font-black mb-3 text-[11px] uppercase tracking-[0.2em]">New Access Key</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-blue-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" id="new_pass" name="new_password" required class="form-input w-full bg-slate-50 border border-slate-200 rounded-2xl pl-12 pr-4 py-4 outline-none transition font-medium" placeholder="Create New Password">
                        </div>
                        <div class="w-full bg-slate-200 h-1.5 mt-3 rounded-full overflow-hidden">
                            <div class="strength-bar bg-rose-500" id="strength-bar"></div>
                        </div>
                        <p id="strength-text" class="text-[10px] font-bold text-slate-400 mt-2 uppercase tracking-widest">Strength: Weak</p>
                    </div>

                    <div>
                        <label class="block text-slate-500 font-black mb-3 text-[11px] uppercase tracking-[0.2em]">Re-Enter New Key</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-blue-400">
                                <i class="fa-solid fa-check-double"></i>
                            </span>
                            <input type="password" name="confirm_password" required class="form-input w-full bg-slate-50 border border-slate-200 rounded-2xl pl-12 pr-4 py-4 outline-none transition font-medium" placeholder="Confirm New Password">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full bg-royal-gradient text-white font-black py-5 rounded-2xl shadow-xl shadow-blue-200 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3 uppercase tracking-widest text-xs">
                            <span>Update Security Key</span>
                            <i class="fa-solid fa-paper-plane text-[10px]"></i>
                        </button>
                    </div>

                </form>
            </div>

            <div class="bg-[#0f172a] p-10 rounded-[2.5rem] shadow-2xl flex flex-col justify-center items-center text-center relative overflow-hidden" data-aos="fade-left">
                
                <div class="absolute inset-0 opacity-10">
                    <i class="fa-solid fa-fingerprint text-[350px] absolute -right-20 -bottom-20 text-white"></i>
                </div>

                <div class="relative z-10">
                    <div class="w-24 h-24 bg-[#2563EB] rounded-[2rem] flex items-center justify-center text-white text-4xl mx-auto mb-8 shadow-2xl shadow-blue-500/40 transform rotate-3">
                        <i class="fa-solid fa-user-lock"></i>
                    </div>
                    
                    <h3 class="text-3xl font-black text-white mb-6 tracking-tight">Protocols <span class="text-blue-500">&</span> Security</h3>
                    <p class="text-slate-400 mb-10 max-w-sm mx-auto leading-relaxed font-medium">
                        To maintain system integrity, ensure your password contains a combination of complex alphanumeric characters.
                    </p>

                    <div class="bg-white/5 border border-white/10 rounded-3xl p-8 text-left max-w-sm mx-auto backdrop-blur-md">
                        <h4 class="text-blue-400 font-black mb-5 flex items-center gap-3 text-xs uppercase tracking-[0.2em]"><i class="fa-solid fa-shield-virus"></i> Security Matrix</h4>
                        <ul class="text-slate-300 text-[11px] space-y-4 font-bold uppercase tracking-widest">
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-emerald-500"></i> Min 8 Characters</li>
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-emerald-500"></i> Alphanumeric Mix</li>
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-emerald-500"></i> Symbols Allowed</li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });

        const passInput = document.getElementById('new_pass');
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');

        passInput.addEventListener('input', function() {
            const val = this.value;
            let strength = 0;
            if (val.length > 5) strength += 20;
            if (val.length > 8) strength += 20;
            if (/[A-Z]/.test(val)) strength += 20;
            if (/[0-9]/.test(val)) strength += 20;
            if (/[^A-Za-z0-9]/.test(val)) strength += 20;

            strengthBar.style.width = strength + '%';
            
            if(strength < 40) {
                strengthBar.style.backgroundColor = '#f43f5e'; // Rose
                strengthText.innerText = "Strength: Weak";
                strengthText.style.color = '#f43f5e';
            } else if(strength < 80) {
                strengthBar.style.backgroundColor = '#fbbf24'; // Amber
                strengthText.innerText = "Strength: Moderate";
                strengthText.style.color = '#fbbf24';
            } else {
                strengthBar.style.backgroundColor = '#10b981'; // Emerald
                strengthText.innerText = "Strength: Strong / Professional";
                strengthText.style.color = '#10b981';
            }
        });
    </script>
</body>
</html>