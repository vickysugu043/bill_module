<?php
// Start the session    
session_start();
// Check if the user is logged in
if (!isset($_SESSION['empid'])) {
    header("Location: index.php");
}

// Add these target values (replace with your actual data source)
$_SESSION['achieved_target'] = 90; // Example value - get from database
$_SESSION['remaining_target'] = 100 - $_SESSION['achieved_target']; // 100 - achieved
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <title>Employee Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<style>
@media screen and (max-width: 768px) {
    .landscape .grid {
        grid-template-columns: repeat(5, 1fr);
    }

    .flex-col {
        width: 100% !important;
    }
}

@media screen and (max-width: 478px) {
    .landscape .grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .flex-col {
        width: 100% !important;
    }
}

@media screen and (min-width: 48em) and (orientation: landscape) {
    .landscape .grid {
        grid-template-columns: repeat(8, 1fr);
    }
}

@media screen and (min-width: 49em) and (orientation: landscape) {
    .landscape .grid {
        grid-template-columns: repeat(7, 1fr);
    }

    .flex-col {
        width: 100% !important;
    }
}

html,
body {
    touch-action: pan-x pan-y;
}

.bg-dark-olive-600 {
    background-color: #3D5300;
}

.bg-tan-green {
    background-color: #ABBA7C;
}
</style>

<body class="bg-gray-100 text-gray-800">

    <!-- Header -->
    <div class="p-4 bg-white shadow flex justify-between items-center">
        <h1 class="text-xl font-bold">Employee Dashboard</h1>
        <div class="relative inline-block text-left">
            <div onclick="toggleLogout()" class="cursor-pointer">
                <i class="fas fa-user text-xl"></i>
            </div>
            <div id="logoutMenu" class="hidden absolute right-0 mt-2 w-28 bg-white border rounded shadow-lg z-50">
                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
            </div>
        </div>
    </div>

    <!-- Profile -->
    <div class="bg-white m-4 p-4 rounded-xl shadow flex items-center space-x-4">
        <img src="https://i.ibb.co/8rW5c1G/avatar.png" class="w-16 h-16 rounded-full" alt="profile">
        <div>
            <h2 class="text-lg font-semibold">
                <?php echo $_SESSION['empname']; ?>
            </h2>
            <p class="text-sm text-gray-500"><?php echo $_SESSION['empdesig_name']; ?> </p>
        </div>
    </div>

    <!-- Sales Target Pie Chart -->
    <div class="px-4 mt-4">
        <h2 class="text-lg font-semibold mb-4">Sales Target Progress</h2>
        <div class="bg-white p-4 rounded-xl shadow">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative">
                    <canvas id="salesChart" class="w-full h-64"></canvas>
                </div>
                <div class="flex flex-col justify-center">
                    <div class="flex items-center mb-3">
                        <div class="w-4 h-4 bg-blue-600 rounded-full mr-2"></div>
                        <span class="text-sm text-gray-700">
                            Achieved (<?php echo $_SESSION['achieved_target']; ?>%)
                        </span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-100 rounded-full mr-2"></div>
                        <span class="text-sm text-gray-700">
                            Remaining (<?php echo $_SESSION['remaining_target']; ?>%)
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="px-4 mt-6 landscape">
        <h2 class="text-lg font-semibold mb-2">Quick Actions</h2>
        <div class="grid grid-cols-3 md:grid-cols-3 gap-4">
            <!-- Estimate -->
            <div
                class="flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                <a href="estimate_list.php"
                    class="w-24 h-24 bg-white p-3 rounded-xl shadow flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                    <i class="fas fa-calculator text-xl mb-1 text-blue-600"></i>
                    <div class="text-sm font-medium text-gray-700">Estimate</div>
                </a>
            </div>

            <!-- Billing -->
            <div
                class="flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                <a href="billing.php"
                    class="w-24 h-24 bg-white p-3 rounded-xl shadow flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                    <i class="fas fa-receipt text-xl mb-1 text-blue-600"></i>
                    <div class="text-sm font-medium text-gray-700">Billing</div>
                </a>
            </div>

            <!-- Advance -->
            <div
                class="flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                <a href="estimate.php"
                    class="w-24 h-24 bg-white p-3 rounded-xl shadow flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                    <i class="fas fa-credit-card text-xl mb-1 text-blue-600"></i>
                    <div class="text-sm font-medium text-gray-700">Advance</div>
                </a>
            </div>

            <!-- Advance Bill Register -->
            <div
                class="flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                <a href="#"
                    class="w-24 h-24 bg-white p-3 rounded-xl shadow flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                    <i class="fas fa-file-invoice-dollar text-xl mb-1 text-blue-600"></i>
                    <div class="text-sm font-medium text-gray-700 text-center">Advance Bill</div>
                </a>
            </div>

            <!-- Exchange -->
            <div
                class="flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                <a href="#"
                    class="w-24 h-24 bg-white p-3 rounded-xl shadow flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                    <i class="fas fa-sync-alt text-xl mb-1 text-blue-600"></i>
                    <div class="text-sm font-medium text-gray-700">Exchange</div>
                </a>
            </div>

            <!-- Og/Os -->
            <div
                class="flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                <a href="#"
                    class="w-24 h-24 bg-white p-3 rounded-xl shadow flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                    <i class="fas fa-box text-xl mb-1 text-blue-600"></i>
                    <div class="text-sm font-medium text-gray-700">Og/Os</div>
                </a>
            </div>

            <!-- Chit -->
            <div
                class="flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                <a href="#"
                    class="w-24 h-24 bg-white p-3 rounded-xl shadow flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                    <i class="fas fa-money-bill-wave text-xl mb-1 text-blue-600"></i>
                    <div class="text-sm font-medium text-gray-700">Chit</div>
                </a>
            </div>

            <!-- Chit Closing -->
            <div
                class="flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                <a href="#"
                    class="w-24 h-24 bg-white p-3 rounded-xl shadow flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                    <i class="fas fa-file-alt text-xl mb-1 text-blue-600"></i>
                    <div class="text-sm font-medium text-gray-700 text-center">Chit Closing</div>
                </a>
            </div>

            <!-- CRM Panel -->
            <div
                class="flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                <a href="dashboard.php"
                    class="w-24 h-24 bg-white p-3 rounded-xl shadow flex flex-col items-center justify-center text-center hover:scale-105 transition-transform duration-200">
                    <i class="fas fa-file-alt text-xl mb-1 text-blue-600"></i>
                    <div class="text-sm font-medium text-gray-700 text-center">CRM Panel</div>
                </a>
            </div>

            <!-- Add more or leave empty -->
            <div class="bg-white p-3 rounded-xl shadow flex flex-col items-center justify-center text-center opacity-0">
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Sales Chart Implementation
    const achieved = <?php echo $_SESSION['achieved_target']; ?>;
    const remaining = <?php echo $_SESSION['remaining_target']; ?>;

    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Achieved', 'Remaining'],
            datasets: [{
                data: [achieved, remaining],
                backgroundColor: ['#2563eb', '#bfdbfe'],
                borderColor: ['#fff', '#fff'],
                borderWidth: 2,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.label}: ${context.raw}%`
                    }
                }
            }
        }
    });

    // Existing logout toggle function
    function toggleLogout() {
        const menu = document.getElementById("logoutMenu");
        menu.classList.toggle("hidden");
    }

    document.addEventListener('click', function(event) {
        const userIcon = event.target.closest('.relative');
        const menu = document.getElementById('logoutMenu');
        if (!userIcon) menu.classList.add('hidden');
    });
    </script>
</body>

</html>