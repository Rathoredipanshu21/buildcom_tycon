<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gateway | Buildcom Tycoon Ventures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a; /* Deep Black/Blue Base */
            overflow-x: hidden;
        }

        .bg-gradient-mesh {
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, rgba(30, 64, 175, 0.5) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.3) 0px, transparent 50%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
        }

        .btn-premium {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-premium:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .floating-icon {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .systaio-link {
            color: #60a5fa;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.3s;
            border-bottom: 2px solid transparent;
        }

        .systaio-link:hover {
            color: #ffffff;
            border-bottom: 2px solid #3b82f6;
        }
    </style>
</head>
<body class="bg-gradient-mesh min-h-screen flex flex-col items-center justify-center p-6 text-white">

    <i class="fa-solid fa-city absolute top-20 left-20 text-blue-500/10 text-9xl floating-icon"></i>
    <i class="fa-solid fa-chart-line absolute bottom-20 right-20 text-blue-400/10 text-9xl floating-icon" style="animation-delay: 2s;"></i>

    <main class="relative z-10 w-full max-w-4xl text-center">
        <div class="mb-8 flex flex-col items-center">
            <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center shadow-2xl mb-6">
                <img src="Assets/logo.png" alt="Buildcom Logo" class="w-12 h-12 object-contain">
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-2">
                BUILDCOM <span class="text-blue-500">TYCOON</span>
            </h1>
            <p class="text-slate-400 font-medium tracking-widest uppercase text-sm">Enterprise Resource Management</p>
        </div>

        <div class="glass-card p-8 md:p-12 shadow-2xl">
            <h2 class="text-xl font-semibold mb-10 text-slate-200">Select Your Access Portal</h2>
            
            <div class="grid md:grid-cols-2 gap-8">
                <a href="admin/index.php" class="btn-premium group">
                    <div class="bg-white text-slate-900 p-8 rounded-2xl flex flex-col items-center gap-4 h-full border-2 border-transparent group-hover:border-blue-500 transition-all">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-3xl mb-2 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <h3 class="text-2xl font-bold">Admin Portal</h3>
                        <p class="text-slate-500 text-sm">Complete command center for global operations and management.</p>
                        <div class="mt-4 flex items-center gap-2 font-bold text-blue-600 text-xs uppercase tracking-widest">
                            Secure Entry <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </div>
                </a>

                <a href="Franchise/index.php" class="btn-premium group">
                    <div class="bg-slate-800 text-white p-8 rounded-2xl flex flex-col items-center gap-4 h-full border-2 border-slate-700 group-hover:border-blue-400 transition-all">
                        <div class="w-16 h-16 bg-slate-700 rounded-full flex items-center justify-center text-blue-400 text-3xl mb-2 group-hover:bg-blue-400 group-hover:text-white transition-all">
                            <i class="fa-solid fa-store"></i>
                        </div>
                        <h3 class="text-2xl font-bold">Franchise Login</h3>
                        <p class="text-slate-400 text-sm">Store-specific dashboard for billing, inventory, and local reporting.</p>
                        <div class="mt-4 flex items-center gap-2 font-bold text-blue-400 text-xs uppercase tracking-widest">
                            Partner Access <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <footer class="mt-16 text-slate-400 text-sm">
            <div class="flex flex-wrap justify-center items-center gap-4 mb-6">
                <div class="flex items-center gap-2 px-4 py-2 bg-white/5 rounded-full border border-white/10">
                    <i class="fa-solid fa-microchip text-blue-500"></i>
                    <span>Secure Architecture</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-white/5 rounded-full border border-white/10">
                    <i class="fa-solid fa-database text-blue-500"></i>
                    <span>Cloud Synced</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-white/5 rounded-full border border-white/10">
                    <i class="fa-solid fa-shield-halved text-blue-500"></i>
                    <span>SSL Encrypted</span>
                </div>
            </div>
            
            <p class="tracking-wide">
                Project is building under the guidance of 
                <a href="https://www.systaio.com" target="_blank" class="systaio-link">
                    SystAIO Technologies
                </a>
            </p>
        </footer>
    </main>

    <div class="fixed top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-blue-500 to-transparent opacity-50"></div>
</body>
</html>