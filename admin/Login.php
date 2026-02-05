<?php
session_start();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../config/db.php'; 
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Using the admin table structure provided
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
                header("Location: index.php");
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
    <title>Terminal Access | Buildcom ERP</title>
    <link rel="icon" type="image/x-icon" href="../Assets/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            /* Blue and White Gradient Combination */
            background: linear-gradient(135deg, #2563EB 0%, #ffffff 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .login-container {
            width: 1000px;
            height: 600px;
            background: white;
            display: flex;
            box-shadow: 20px 20px 0px rgba(15, 23, 42, 0.2);
            border: 2px solid #0f172a;
        }

        /* LEFT SIDE: INDUSTRIAL IMAGE SECTION */
        .login-image {
            width: 50%;
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');
            background-size: cover;
            background-position: center;
            position: relative;
            border-right: 2px solid #0f172a;
        }

        .login-image::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
        }

        .image-overlay-text {
            position: absolute;
            bottom: 40px;
            left: 40px;
            color: white;
            z-index: 10;
        }

        /* RIGHT SIDE: FORM SECTION */
        .login-form-area {
            width: 50%;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .input-industrial {
            border: 2px solid #e2e8f0;
            padding: 15px 45px;
            width: 100%;
            font-weight: 700;
            outline: none;
            transition: 0.2s;
            background: #f8fafc;
        }

        .input-industrial:focus {
            border-color: #2563EB;
            background: #ffffff;
        }

        .btn-authorize {
            background: #2563EB;
            color: white;
            padding: 18px;
            width: 100%;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-bottom: 5px solid #1e40af;
            transition: 0.2s;
        }

        .btn-authorize:active {
            transform: translateY(3px);
            border-bottom-width: 2px;
        }

        /* NO ITALICS RULE */
        * { font-style: normal !important; }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-image">
            <div class="image-overlay-text">
                <div class="bg-white p-3 inline-block mb-6 border-2 border-black">
                    <img src="../Assets/logo.png" alt="Logo" class="h-10">
                </div>
                <h2 class="text-4xl font-black uppercase tracking-tighter leading-none">Buildcom<br>Systems</h2>
                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-[0.4em] mt-3">Enterprise Resource Management</p>
            </div>
        </div>

        <div class="login-form-area">
            <div class="mb-10">
                <h3 class="text-2xl font-black uppercase text-slate-900 tracking-tight">Terminal Login</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Authorized Personnel Only</p>
            </div>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Username / Admin ID</label>
                    <div class="relative">
                        <i class="fas fa-user-shield absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="username" required class="input-industrial" placeholder="ENTER ID">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Master Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="password" name="password" required class="input-industrial" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn-authorize">
                    Authorize Entry <i class="fa-solid fa-chevron-right ml-2"></i>
                </button>

                <?php if($error): ?>
                <div class="bg-red-50 border-l-4 border-red-600 p-4 text-red-600 text-[11px] font-bold uppercase tracking-tight">
                    <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>
            </form>

            <div class="mt-12 p-4 bg-slate-50 border border-slate-200">
                <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest mb-2 text-center">Dev Access</p>
                <div class="flex justify-around text-[10px] text-slate-600 font-bold">
                    <span>ID: <span class="text-blue-600">admin</span></span>
                    <span class="h-3 w-px bg-slate-300"></span>
                    <span>PASS: <span class="text-blue-600">admin</span></span>
                </div>
            </div>
            
            <p class="mt-10 text-center text-[9px] font-black text-slate-300 uppercase tracking-widest">
                &copy; 2026 Buildcom Tycoon Ventures
            </p>
        </div>
    </div>

</body>
</html>