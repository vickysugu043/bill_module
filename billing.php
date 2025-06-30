<?php
date_default_timezone_set('Asia/Kolkata'); // Set to Indian time
// echo date("Y-m-d H:i:s");
$today = date('Y-m-d');  // current date
$year = date('Y');       // current year
$month = date('m');      // current month

if ($month >= 4) {
    // April to December
    $fromDate = $year . '-04-01';
    $toDate = ($year + 1) . '-03-31';
    $finYearCode = substr($year, 2, 2) . substr($year + 1, 2, 2); // like 2425
} else {
    // January to March
    $fromDate = ($year - 1) . '-04-01';
    $toDate = $year . '-03-31';
    $finYearCode = substr($year - 1, 2, 2) . substr($year, 2, 2); // like 2425
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>KTM || Billing System</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<style>
body {
    touch-action: pan-x pan-y;
    /* Only scrolling allowed, no zoom */
}
</style>

<body class="bg-dark">
    <div class="container mt-4">
        <!-- 🔴 Logout Button -->
        <div class="d-flex mb-2">
            <!-- <button class="btn btn-danger btn-sm" onclick="logout()">Logout</button> -->
            <nav class="navbar navbar-light bg-light">
                <div class="container-fluid justify-content-end">
                    <div class="user-dropdown position-relative">
                        <button class="btn" id="userDropdownBtn">
                            <i class="fa fa-user fs-4"></i>
                        </button>
                        <div class="dropdown-content position-absolute">
                            <button class="btn btn-danger btn-sm w-100" onclick="logout()">Logout</button>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <!-- <h2 class="mb-4 text-center"><img class="sktmlogo" src="images/sktmlogo.ico">The KTM Jewellery Ltd.</h2> -->
        <div class="header">
            <a href="dash3.php"><img class="sktmlogo" src="images/sktmlogo.ico" alt="KTM Logo"></a>
            <h2 class="company-name">The KTM Jewellery Ltd.</h2>
        </div>

        <form action="" method="post">
            <div class="row mb-3">
                <div class="col-md-1"></div>
                <div class="col-md-2">
                    <label for="TrnNo" class="form-label">TrnNo</label>
                    <div class="custom-input-wrapper">
                        <input type="text" class="form-control" id="TrnNo" name="TrnNo" placeholder="TrnNo">
                        <button type="button" class="end-box-button" onclick="getTrnNo();"></button>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="TrnDate" class="form-label">TrnDate</label>
                    <input type="text" class="form-control" id="TrnDate" name="TrnDate" value="<?php echo $today; ?>"
                        disabled>
                </div>
                <div class="col-md-2">
                    <label for="customer" class="form-label">Customer</label>
                    <div class="custom-input-wrapper">
                        <input type="text" class="form-control" id="cus_mob" name="cus_mob"
                            placeholder="Customer Mobile">
                        <button type="button" class="end-box-button" onclick="getCustomer()"></button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="cus_name" class="form-label">Customer Name</label>
                    <input type="text" class="form-control" id="cus_name" name="cus_name" placeholder="Customer Name"
                        readonly>
                </div>
            </div>

            <div class="row mb-3 align-items-end">
                <div class="col-md-1"></div>
                <div class="col-md-2 estno">
                    <label for="est_no" class="form-label">Estimation No</label>
                    <div class="custom-input-wrapper">
                        <input type="text" class="form-control" id="est_no" name="est_no" placeholder="Estimation No">
                        <button type="button" class="end-box-button" onclick="estimateNo();"></button>
                    </div>
                </div>

                <div class="col-md-2 estno">
                    <label for="bill_by" class="form-label">Billed By</label>
                    <div class="custom-input-wrapper">
                        <input type="text" class="form-control" id="bill_by" name="bill_by" placeholder="Billed By">
                        <button type="button" class="end-box-button" onclick="showPopup('bill_by')"></button>
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="emp_name" class="form-label">Biller Name</label>
                    <input type="text" class="form-control" id="emp_name" name="emp_name" placeholder="Name" readonly>
                </div>

                <div class="col-md-1 d-grid">
                    <button class="btn btn-dark" type="button">Show</button>
                </div>

                <div class="col-md-1 d-grid">
                    <button type="button" class="btn btn-secondary">Print</button>
                </div>
            </div>

            <div class="row mb-3 align-items-end">
                <div class="col-md-1"></div>
                <div class="col-md-2">
                    <label for="ord_no">Customer Order No</label>
                    <div class="custom-input-wrapper">
                        <input type="text" class="form-control" id="ord_no" name="ord_no"
                            placeholder="Customer Order No">
                        <button type="button" class="end-box-button" onclick="showPopup('ord_no')"></button>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="adv_no">Advance No</label>
                    <div class="custom-input-wrapper">
                        <input type="text" class="form-control" id="adv_no" name="adv_no" placeholder="Advance No">
                        <button type="button" class="end-box-button" onclick="showPopup('adv_no')"></button>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="sold_by">Sold By</label>
                    <div class="custom-input-wrapper">
                        <input type="text" class="form-control" id="sold_by" name="sold_by" placeholder="Sold By">
                        <button type="button" class="end-box-button" onclick="showPopup('sold_by')"></button>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs" id="billTabs">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab_bill">Bill</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_tax">Tax Details</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_payment">Payment Details</a>
                </li>
            </ul>

            <div class="tab-content form-section">
                <div class="tab-pane fade show active" id="tab_bill">
                    <div class="table-responsive" style="max-height: 400px; overflow-x: auto;">
                        <table class="table table-bordered table-sm text-nowrap text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>S.No.</th>
                                    <th>Stock Bar Code</th>
                                    <th>Sub Section</th>
                                    <th>Sub Product</th>
                                    <th>HSN Code</th>
                                    <th>Metal Type</th>
                                    <th>Purity</th>
                                    <th>No. of Pieces</th>
                                    <th>Gross Wt (gms)</th>
                                    <th>Stone Wt</th>
                                    <th>Dia Wt</th>
                                    <th>Net Wt</th>
                                    <th>Discnt Wt</th>
                                    <th>Stone Ct</th>
                                    <th>Product Rte</th>
                                    <th>Metal Amt</th>
                                    <th>MC</th>
                                    <th>Waste %</th>
                                    <th>Waste Charge</th>
                                    <th>Waste Disc %</th>
                                    <th>Waste DiscAmt</th>
                                    <th>Stone Val</th>
                                    <th>Stone Disc</th>
                                    <th>Stone FinVal</th>
                                    <th>HalMark Charge</th>
                                    <th>Offer Disc</th>
                                    <th>Gross Amt</th>
                                    <th>Tax Cde</th>
                                    <th>Tax Val</th>
                                    <th>Other Val</th>
                                    <th>Fin Val</th>
                                    <th>Sold By</th>
                                    <th>Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>SB123456</td>
                                    <td>Necklace</td>
                                    <td>Gold Chain</td>
                                    <td>7113</td>
                                    <td>Gold</td>
                                    <td>22K</td>
                                    <td>1</td>
                                    <td>15.55</td>
                                    <td>0.50</td>
                                    <td>0.20</td>
                                    <td>14.85</td>
                                    <td>0.10</td>
                                    <td>1.2</td>
                                    <td>5200</td>
                                    <td>77220</td>
                                    <td>1000</td>
                                    <td>5%</td>
                                    <td>3861</td>
                                    <td>2%</td>
                                    <td>77.22</td>
                                    <td>3000</td>
                                    <td>5%</td>
                                    <td>2850</td>
                                    <td>45</td>
                                    <td>500</td>
                                    <td>84456</td>
                                    <td>GST18</td>
                                    <td>15202</td>
                                    <td>100</td>
                                    <td>99758</td>
                                    <td>1150</td>
                                    <td>Mr.Bheem</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 🟡 Navbar with Burger Menu -->
            <!-- <nav class="navbar navbar-dark bg-dark">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
                        aria-controls="sidebarMenu" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <span class="navbar-brand mb-0 h1">KTM Billing System</span>
                </div>
            </nav> -->

            <!-- 🟢 Offcanvas Burger Sidebar -->
            <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="sidebarMenu"
                aria-labelledby="sidebarMenuLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="sidebarMenuLabel">Menu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <button class="btn btn-danger w-100" onclick="logout()">Logout</button>
                        </li>
                        <!-- 🔁 You can add more menu items here -->
                        <li class="nav-item mb-2">
                            <a href="#" class="nav-link text-white">Dashboard</a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" class="nav-link text-white">Reports</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bootstrap Modal -->
            <div class="modal fade" id="customModal" tabindex="-1" aria-labelledby="customModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-sm modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header py-2">
                            <h5 class="modal-title" id="customModalLabel">Transaction List</h5>
                            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-2">
                            <table class="table table-bordered table-sm mb-2">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>TrnNo</th>
                                    </tr>
                                </thead>
                                <tbody id="transactionTableBody">
                                    <!-- Rows will be dynamically added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Modal -->
            <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-sm modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header py-2">
                            <h5 class="modal-title" id="customerModalLabel">Customer List</h5>
                            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-2">
                            <table class="table table-bordered table-sm mb-2">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Customer Name</th>
                                        <th>Mobile</th>
                                    </tr>
                                </thead>
                                <tbody id="customerTableBody">
                                    <!-- Rows will be dynamically added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/script.js"></script>
    <script>
    function getTrnNo() {
        var finYearCode = "<?php echo $finYearCode; ?>"; // Get the financial year code from PHP

        // Validate financial year code
        if (!finYearCode) {
            alert("Financial Year Code is missing!");
            return;
        }

        // Fetch the transaction numbers using AJAX
        $.ajax({
            url: 'api_keys/getTrnNo.php', // Your API endpoint
            type: 'GET',
            data: {
                finYearCode: finYearCode // Send the financial year code as a parameter
            },
            dataType: 'json',
            beforeSend: function() {
                const tableBody = $("#transactionTableBody");
                tableBody.html('<tr><td colspan="2">Loading...</td></tr>'); // Show a loader
            },
            success: function(response) {
                if (response.status === "success") {
                    const transactions = response.data;
                    const tableBody = $("#transactionTableBody");
                    tableBody.empty(); // Clear existing rows

                    // Populate table dynamically
                    transactions.forEach(transaction => {
                        const row = `<tr onclick="fillTrnNo('${transaction.bstrtrnno}')">
                            <td>${transaction.btrndate}</td>
                            <td>${transaction.bstrtrnno}</td>
                        </tr>`;
                        tableBody.append(row);
                    });

                    // Show the modal
                    $("#customModal").modal("show");
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert("An error occurred while fetching the data. Please try again!");
                console.error("AJAX Error:", error);
            }
        });
    }

    function getCustomer() {
        var brnId = 15; // Replace with the actual branch ID if needed

        $.ajax({
            url: 'api_keys/getCustomer.php',
            type: 'GET',
            data: {
                brn_id: brnId
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === "success") {
                    const customers = response.data;
                    const tableBody = $("#customerTableBody");
                    tableBody.empty(); // Clear existing rows

                    // Populate table dynamically
                    customers.forEach(customer => {
                        const row = `<tr onclick="fillCustomer('${customer.cus_name}','${customer.cus_mob}')">
                            <td>${customer.cus_name}</td>
                            <td>${customer.cus_mob}</td>
                        </tr>`;
                        tableBody.append(row);
                    });

                    // Show the modal
                    $("#customerModal").modal("show");
                } else {
                    alert("Error fetching customers: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    }


    function fillTrnNo(trnNo) {
        $("#TrnNo").val(trnNo);
        $("#customModal").modal("hide");
    }

    function estimateNo() {
        $.ajax({
            url: 'api_keys/getEstimateNo.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === "success") {
                    $('#est_no').val(response.data);
                } else {
                    alert("Error fetching estimate number: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    }

    function logout() {
        window.location.href = 'logout.php';
    }
    </script>
</body>

</html>