<?php
include '../config/db.php';

// AUTO-GENERATE STAFF CODE (BTV + Random 4 Digits)
$auto_code = "BTV" . rand(1000, 9999);

// HANDLE FORM SUBMISSION
if (isset($_POST['add_staff'])) {
    $staff_code = $_POST['staff_code'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = $_POST['phone'];
    $designation = $_POST['designation'];
    $store = $_POST['store'];

    // Image Upload Logic
    $target_dir = "../uploads/employee/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $image_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO staff (staff_code, name, email, phone, designation, assigned_store, image) 
                VALUES ('$staff_code', '$name', '$email', '$phone', '$designation', '$store', '$image_name')";
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Staff Member Onboarded Successfully!'); window.location='manage_staff.php';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corporate Directory | Buildcom Tycoon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-card { background: white; border: 1px solid #e2e8f0; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .glass-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.05); border-color: #3b82f6; }
        .tycoon-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); }
        
        /* Modal Overlay & Scroll Logic */
        .modal-overlay { 
            background: rgba(15, 23, 42, 0.6); 
            backdrop-filter: blur(4px);
            display: none; 
            position: fixed;
            inset: 0;
            z-index: 100;
            padding: 2rem;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }
        /* Custom Scrollbar for Chrome/Safari */
        .modal-content::-webkit-scrollbar { width: 6px; }
        .modal-content::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#f1f5f9] text-slate-900 antialiased h-screen flex flex-col">

    <header class="w-full px-10 py-8 flex justify-between items-center bg-[#f1f5f9]/80 backdrop-blur-md sticky top-0 z-40">
        <div data-aos="fade-right">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
                 <span class="text-blue-600"></span>
            </h1>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.2em]">Buildcom Tycoon Management</p>
        </div>
        <button onclick="toggleModal()" class="bg-slate-900 text-white px-6 py-3 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg active:scale-95">
            <i class="fa-solid fa-plus-circle"></i> Add Personnel
        </button>
    </header>

    <main class="flex-1 px-10 pb-12 overflow-y-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" data-aos="fade-up">
            <?php
            $res = mysqli_query($conn, "SELECT * FROM staff ORDER BY id DESC");
            while($row = mysqli_fetch_assoc($res)) {
            ?>
            <div class="glass-card rounded-2xl p-5 relative group overflow-hidden">
                <div class="flex items-start justify-between mb-4">
                    <div class="relative">
                        <img src="../uploads/employee/<?php echo $row['image']; ?>" class="w-14 h-14 rounded-xl object-cover ring-2 ring-slate-100">
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                    </div>
                    <span class="text-[10px] font-bold bg-slate-100 px-2 py-1 rounded text-slate-500">#<?php echo $row['staff_code']; ?></span>
                </div>

                <div class="space-y-1">
                    <h3 class="font-bold text-slate-800 text-sm leading-tight uppercase"><?php echo $row['name']; ?></h3>
                    <p class="text-[11px] font-medium text-blue-600"><?php echo $row['designation']; ?></p>
                </div>
                
                <div class="mt-5 pt-4 border-t border-slate-50 space-y-2">
                    <div class="flex items-center gap-3 text-slate-500 text-[12px]">
                        <i class="fa-solid fa-envelope w-4 text-slate-400"></i>
                        <span class="truncate"><?php echo $row['email']; ?></span>
                    </div>
                    <div class="flex items-center gap-3 text-slate-500 text-[12px]">
                        <i class="fa-solid fa-phone w-4 text-slate-400"></i>
                        <?php echo $row['phone']; ?>
                    </div>
                    <div class="flex items-center gap-3 text-slate-700 text-[12px] font-semibold">
                        <i class="fa-solid fa-location-dot w-4 text-blue-500"></i>
                        <?php echo $row['assigned_store']; ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </main>

    <div id="staffModal" class="modal-overlay">
        <div class="bg-white w-full max-w-xl rounded-[2rem] shadow-2xl overflow-hidden" data-aos="zoom-in" data-aos-duration="300">
            <div class="bg-slate-900 p-6 flex justify-between items-center text-white">
                <div>
                    <h2 class="text-lg font-bold">Add New Staff</h2>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest">Personnel Information System</p>
                </div>
                <button onclick="toggleModal()" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-white/10 transition-colors">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <div class="modal-content p-8">
                <form method="POST" enctype="multipart/form-data" class="grid grid-cols-2 gap-5">
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2 ml-1">Generated ID</label>
                        <input type="text" name="staff_code" value="<?php echo $auto_code; ?>" readonly class="w-full px-4 py-3 bg-slate-50 rounded-xl font-mono text-sm text-blue-600 border border-slate-100 outline-none">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2 ml-1">Full Name</label>
                        <input type="text" name="name" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-50 outline-none transition-all">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2 ml-1">Email Address</label>
                        <input type="email" name="email" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:border-blue-500 outline-none">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2 ml-1">Phone Number</label>
                        <input type="text" name="phone" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:border-blue-500 outline-none">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2 ml-1">Store Location</label>
                        <input type="text" name="store" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:border-blue-500 outline-none">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2 ml-1">Designation</label>
                        <select name="designation" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:border-blue-500 outline-none bg-white">
                            <option>Store Manager</option>
                            <option>Inventory In-charge</option>
                            <option>Sales Associate</option>
                            <option>Logistics Lead</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2 ml-1">Profile Photo</label>
                        <div class="relative w-full">
                            <input type="file" name="image" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>
                    </div>
                    
                    <div class="col-span-2 pt-4">
                        <button type="submit" name="add_staff" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all text-sm uppercase tracking-wider">
                            Confirm Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        function toggleModal() {
            const modal = document.getElementById('staffModal');
            modal.classList.toggle('active');
            // Prevent background scrolling when modal is open
            document.body.style.overflow = modal.classList.contains('active') ? 'hidden' : 'auto';
        }

        // Close modal when clicking outside content
        window.onclick = function(event) {
            const modal = document.getElementById('staffModal');
            if (event.target == modal) {
                toggleModal();
            }
        }
    </script>
</body>
</html>