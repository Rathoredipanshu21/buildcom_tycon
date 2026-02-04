<?php
include '../config/db.php';

// AUTO-GENERATE FRANCHISE CODE
$auto_f_code = "BTF" . rand(1000, 9999);

if (isset($_POST['add_franchise'])) {
    $f_code = $_POST['f_code'];
    $store_name = mysqli_real_escape_string($conn, $_POST['store_name']);
    $owner_name = mysqli_real_escape_string($conn, $_POST['owner_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = $_POST['phone'];
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = $_POST['city'];
    $state = $_POST['state'];
    $gst = $_POST['gst'];
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "INSERT INTO franchises (franchise_code, store_name, owner_name, email, phone, address, city, state, gst_number, password) 
            VALUES ('$f_code', '$store_name', '$owner_name', '$email', '$phone', '$address', '$city', '$state', '$gst', '$password')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Franchise Onboarded Successfully!'); window.location='manage_franchise.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Franchise Network | BUILDCOM TYCOON</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sharp-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.3s; }
        .sharp-card:hover { border-color: #2563eb; transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
        
        /* High-End Scrollable Modal */
        .modal-wrapper { 
            background: rgba(15, 23, 42, 0.7); 
            backdrop-filter: blur(8px);
            display: none; 
            position: fixed;
            inset: 0;
            z-index: 1000;
            padding-top: 80px; /* Top Padding for placement */
            padding-bottom: 40px;
            overflow-y: auto; /* Modal background scrolls */
        }
        .modal-wrapper.active { display: flex; justify-content: center; }
        .modal-box {
            background: white;
            width: 100%;
            max-width: 700px;
            border-radius: 16px;
            height: fit-content;
            margin-bottom: 40px;
        }
    </style>
</head>
<body class="text-slate-900">

    <header class="w-full px-10 py-8 flex justify-between items-center sticky top-0 bg-white/80 backdrop-blur-md z-40 border-b border-slate-100">
        <div data-aos="fade-right">
            <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">Franchise <span class="text-blue-700">Network</span></h1>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Enterprise Distribution Hubs</p>
        </div>
        <button onclick="toggleModal()" class="bg-blue-700 text-white px-6 py-3 rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg active:scale-95">
            <i class="fa-solid fa-plus-circle mr-2"></i> Register New Store
        </button>
    </header>

    <main class="p-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" data-aos="fade-up">
            <?php
            $res = mysqli_query($conn, "SELECT * FROM franchises ORDER BY id DESC");
            while($row = mysqli_fetch_assoc($res)) {
            ?>
            <div class="sharp-card p-6 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded">#<?php echo $row['franchise_code']; ?></span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase <?php echo $row['status'] == 'Active' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'; ?>"><?php echo $row['status']; ?></span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase mb-1"><?php echo $row['store_name']; ?></h3>
                    <p class="text-[11px] font-medium text-slate-500 mb-4">Principal: <?php echo $row['owner_name']; ?></p>
                    <div class="space-y-2 border-t border-slate-50 pt-4 text-[11px] font-medium text-slate-500">
                        <div class="flex items-center gap-2"><i class="fa-solid fa-envelope text-slate-300 w-4"></i> <?php echo $row['email']; ?></div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-phone text-slate-300 w-4"></i> <?php echo $row['phone']; ?></div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </main>

    <div id="fModal" class="modal-wrapper">
        <div class="modal-box shadow-2xl overflow-hidden" data-aos="zoom-in" data-aos-duration="200">
            <div class="bg-slate-900 p-6 flex justify-between items-center text-white">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-widest">Store Onboarding</h2>
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Submit Franchise Credentials</p>
                </div>
                <button onclick="toggleModal()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" class="p-10 grid grid-cols-2 gap-x-6 gap-y-5">
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Legal Store Name</label>
                    <input type="text" name="store_name" required class="w-full px-4 py-3 border border-slate-200 rounded-lg text-xs font-medium focus:border-blue-600 outline-none transition-all">
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Login Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 border border-slate-200 rounded-lg text-xs font-medium focus:border-blue-600 outline-none">
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Full Name (Owner)</label>
                    <input type="text" name="owner_name" required class="w-full px-4 py-3 border border-slate-200 rounded-lg text-xs font-medium outline-none">
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">GST Identification</label>
                    <input type="text" name="gst" class="w-full px-4 py-3 border border-slate-200 rounded-lg text-xs font-medium outline-none uppercase font-mono">
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Email (Username)</label>
                    <input type="email" name="email" required class="w-full px-4 py-3 border border-slate-200 rounded-lg text-xs font-medium outline-none">
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Contact Phone</label>
                    <input type="text" name="phone" required class="w-full px-4 py-3 border border-slate-200 rounded-lg text-xs font-medium outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Business Address</label>
                    <textarea name="address" required rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-lg text-xs font-medium outline-none resize-none"></textarea>
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">City</label>
                    <input type="text" name="city" required class="w-full px-4 py-3 border border-slate-200 rounded-lg text-xs">
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">State</label>
                    <input type="text" name="state" required class="w-full px-4 py-3 border border-slate-200 rounded-lg text-xs">
                </div>
                
                <input type="hidden" name="f_code" value="<?php echo $auto_f_code; ?>">
                
                <div class="col-span-2 pt-6">
                    <button type="submit" name="add_franchise" class="w-full bg-slate-900 text-white font-bold py-4 rounded-lg text-[11px] uppercase tracking-[0.2em] shadow-xl hover:bg-blue-700 transition-all active:scale-[0.98]">
                        Confirm Franchise Entry
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 600, once: true });
        function toggleModal() {
            const modal = document.getElementById('fModal');
            modal.classList.toggle('active');
            document.body.style.overflow = modal.classList.contains('active') ? 'hidden' : 'auto';
        }
    </script>
</body>
</html>