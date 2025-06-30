<?php @session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>KTM || Billing System</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>

<body class="bg-dark">
    <div class="container mt-4">
        <!-- 🔴 Logout Button -->
        <div class="d-flex mb-2">
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

        <div class="header">
            <img class="sktmlogo" src="images/image.png" alt="KTM Logo">
            <h2 class="company-name">The KTM Jewellery Ltd.</h2>
        </div>

        <form action="POST">
            <!-- #region -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="entry_by" class="form-label">Enter By</label>
                    <div class="custom-input-wrapper">
                        <input type="text" class="form-control custom_width"
                            value="<?php echo $_SESSION["empid"] . '-' . $_SESSION['empname']; ?>" id="entry_by"
                            name="entry_by" placeholder="Enter By" readonly>
                        <input type="hidden" name="empid" id="empid" value="<?php echo $_SESSION["empid"]; ?>">
                        <!-- <button type="button" class="end-box-button" onclick="getEmployee();"></button> -->
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="barcode_scan" class="form-label">Barcode</label>
                    <input type="text" class="form-control custom_width" id="barcode_scan" name="barcode_scan"
                        placeholder="Barcode" onchange="productDetails(this.value);">
                </div>
            </div>

            <!-- #endregion onclick="toggleScanner();" -->
            <div class="tab-content form-section">
                <div class="tab-pane fade show active" id="tab_bill">
                    <div class="table-responsive" style="max-height: 400px; overflow-x: auto;">
                        <table class="table table-bordered table-sm text-nowrap text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Barcode</th>
                                    <th>Product</th>
                                    <th>Nett. Wt.</th>
                                    <th>Rate</th>
                                    <th>WstPer%</th>
                                    <th>Offer Dis</th>
                                    <th>Tax Val</th>
                                    <th>Oth Chrg</th>
                                    <th>Gross Amt</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <!-- Rows will be dynamically added here -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="text-end">Final Amount:</td>
                                    <td colspan="2" class="text-start">
                                        <input type="text" class="form-control" id="final_amount" name="final_amount"
                                            disabled>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="text-end">Discount:</td>
                                    <td colspan="2" class="text-start">
                                        <input type="text" class="form-control" id="discount" name="discount" disabled>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="text-end">Round Off:</td>
                                    <td colspan="2" class="text-start">
                                        <input type="text" class="form-control" id="round_off" name="round_off"
                                            disabled>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="text-end">Nett Amount:</td>
                                    <td colspan="2" class="text-start">
                                        <input type="text" class="form-control" id="nett_amount" name="nett_amount"
                                            disabled>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 text-center">
                    <button type="button" class="btn btn-primary" style="width:7%;" onclick="validate();">Save</button>
                    <button type="button" class="btn btn-warning" style="width:7%;"
                        onclick="previewData();">Preview</button>
                    <button type="button" class="btn btn-success" style="width:7%;"
                        onclick="generateBill();">Bill</button>
                </div>
            </div>

            <!-- QR Scanner Modal -->
            <div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="qrScannerModalLabel">Scan Barcode</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div id="qr-reader" style="width: 100%; height: auto; display: block; position: relative;">
                                <button type="button" onclick="stopScanner()"
                                    style="position: absolute; top: 5px; right: 5px; background: red; color: white; border: none; padding: 5px 10px; border-radius: 3px;">
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Modal -->
            <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-sm modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header py-2">
                            <h5 class="modal-title" id="employeeModalLabel">Select Employee</h5>
                            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-2">
                            <table class="table table-bordered table-sm mb-2">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Emp. ID</th>
                                        <th>Name</th>
                                    </tr>
                                </thead>
                                <tbody id="EmployeeTableBody">
                                    <!-- Rows will be dynamically added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true" style="height: 100% !important;">
                <div class="modal-dialog modal-xl">
                    <!-- Use modal-xl for large content like estimates -->
                    <div class="modal-content" style="width: 70%;">
                        <div class="modal-header" style="display: none;">
                            <h5 class="modal-title" id="printModalLabel">Estimate Preview</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="printModalBody">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- End of Modal -->
        </form>
    </div>
    <style>
    .modal-content {
        width: 115% !important;
        left: -21px !important;
        margin: auto;
    }
</style>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/script.js"></script>

    <script>
    function logout() {
        // Redirect to logout.php, which handles session clearing
        window.location.href = 'logout.php';
    }

    let html5QrCode;
    let isScannerRunning = false;

    function toggleScanner() {
        const qrReader = document.getElementById("qr-reader");

        // Show Bootstrap modal
        $('#qrScannerModal').modal('show');

        // Prevent multiple instances
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qr-reader");
        }

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            document.getElementById("barcode_scan").value = decodedText;
            productDetails(decodedText);

            html5QrCode.stop().then(() => {
                $('#qrScannerModal').modal('hide');
                isScannerRunning = false;
            }).catch(err => {
                console.error("Failed to stop scanner.", err);
            });
        };

        const config = {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            }
        };

        html5QrCode.start({
                facingMode: "environment"
            }, config, qrCodeSuccessCallback)
            .then(() => {
                isScannerRunning = true;
            })
            .catch(err => {
                console.error("Unable to start scanner.", err);
                alert("Camera access denied or not supported.");
            });
    }

    function stopScanner() {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                $('#qrScannerModal').modal('hide');
                isScannerRunning = false;
            }).catch(err => {
                console.error("Failed to stop scanner.", err);
            });
        }
    }

    function productDetails(barcode) {
        // Simulate an API call to get product details
        console.log("Fetching product details for barcode:", barcode);

        // Check if barcode already exists in the table
        const existingRows = document.querySelectorAll("#productTableBody tr");
        for (let row of existingRows) {
            const cellText = row.cells[0]?.textContent.trim();
            if (cellText === barcode) {
                alert("Product already added.");
                $("#barcode_scan").val(""); // Clear the input
                exit; // Stop execution if duplicate found
            }
        }

        $.ajax({
            url: 'api_keys/getProductDetails.php',
            type: 'GET',
            data: {
                brn_id: "<?php echo $_SESSION['empwrkunit']; ?>",
                barcode: barcode
            },
            success: function(response) {
                console.log(response); // Log the full response

                if (response.status === 'success' && Array.isArray(response.data) && response.data.length >
                    0) {
                    const product = response.data[0];

                    // Add new row
                    const newRow = `<tr>
                        <td>${product.barcode}</td>
                        <td>${product.name}</td>
                        <td>${product.nettWt}</td>
                        <td>${product.rate}</td>
                        <td>${product.wstper}</td>
                        <td>${parseFloat(product.offerDis).toFixed(2)}</td>
                        <td>${product.taxVal.toFixed(2)}</td>
                        <td>${product.hmcval}</td>
                        <td>${product.grossamt.toFixed(2)}</td>
                        <td style="display:none;"><input type="hidden" value="${product.bsitmid}" /></td>
                        <td style="display:none;"><input type="hidden" value="${product.bsitmsubctgid}" /></td>
                        <td style="display:none;"><input type="hidden" value="${product.bsitmctgid}" /></td>
                        <td style="display:none;"><input type="hidden" value="${product.bsmetaltype}" /></td>
                        <td style="display:none;"><input type="hidden" value="${product.stoneWt}" /></td>
                        <td style="display:none;"><input type="hidden" value="${product.itgstper}" /></td>
                        <td style="display:none;"><input type="hidden" value="${product.stoneVal}" /></td>
                        <td style="display:none;"><input type="hidden" value="${product.bscarat}" /></td>
                        <td style="display:none;"><input type="hidden" value="${product.bsdiawt}" /></td>
                        <td style="display:none;"><input type="hidden" value="${product.mcval}" /></td>
                        <td style="display:none;"><input type="hidden" value="${product.metval}" /></td>
                        <td style="display:none;"><input type="hidden" value="${product.wstval}" /></td>
                    </tr>`;

                    document.getElementById("productTableBody").insertAdjacentHTML('beforeend', newRow);

                    // 1. Get current total
                    let currentFinalAmt = parseFloat($("#final_amount").val()) || 0;
                    let currentDiscount = parseFloat($("#discount").val()) || 0;

                    // 2. Add current product
                    const newFinalAmt = currentFinalAmt + parseFloat(product.finalAmt);
                    const newDiscount = currentDiscount + parseFloat(product.offerDis);

                    // 4. Round-off to nearest lower multiple of 5
                    const remainder = newFinalAmt % 5;
                    let roundedValue = newFinalAmt;
                    let roundOff = 0;

                    if (remainder !== 0) {
                        roundedValue = Math.floor(newFinalAmt / 5) * 5;
                        roundOff = (roundedValue - newFinalAmt).toFixed(2); // negative
                    }

                    // 5. Update values
                    $("#final_amount").val(newFinalAmt.toFixed(2));
                    $("#discount").val(newDiscount.toFixed(2));
                    $("#nett_amount").val(roundedValue.toFixed(2));
                    $("#round_off").val(roundOff);

                    $("#barcode_scan").val(""); // Clear input
                } else {
                    alert("Error: No product found or invalid response format.");
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", status, error);
                alert("Failed to fetch product details. Please try again.");
            }
        });
    }

    function getEmployee() {
        // Fetch employee list via AJAX
        $.ajax({
            url: 'api_keys/getEmployee.php',
            type: 'GET',
            dataType: 'json',
            beforeSend: function() {
                const tableBody = $("#EmployeeTableBody");
                tableBody.html('<tr><td colspan="3">Loading...</td></tr>'); // Loader row
            },
            success: function(response) {
                if (response.status === "success") {
                    const employees = response.data;
                    const tableBody = $("#EmployeeTableBody");
                    tableBody.empty(); // Clear old rows

                    // Fill employee rows
                    employees.forEach(emp => {
                        const row = `
                        <tr onclick="fillEmployee('${emp.empid}', '${emp.empname}')">
                            <td>${emp.empid}</td>
                            <td>${emp.empname}</td>
                        </tr>`;
                        tableBody.append(row);
                    });

                    // Show the employee modal
                    $("#employeeModal").modal("show");
                } else {
                    alert("Error fetching employee list: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert("An error occurred while fetching employee data.");
                console.error("AJAX Error:", error);
            }
        });
    }

    function fillEmployee(empid, empname) {
        // Fill employee values into form
        $("#entry_by").val(empid + ' - ' + empname);
        $("#entry_by_name").val(empname); // optional input for name

        // Close the modal
        $("#employeeModal").modal("hide");
        $("#barcode_scan").focus(); // Focus back to barcode input
    }

    function validate() {
        // Perform validation here
        const entryBy = $("#entry_by").val();
        const barcode = $("#barcode_scan").val();
        const tableRows = $("#productTableBody tr");

        if (!entryBy) {
            alert("Please select an employee.");
            return false;
        } else if (tableRows.length === 0) {
            alert("Please add at least one product to the table.");
            return false;
        } else {
            manipulateData();
        }
    }

    function manipulateData() {
        const entryBy = $("#empid").val();
        const tableRows = $("#productTableBody tr");
        const rowData = [];
        let gross_Amt = 0; // Initialize the variable to accumulate the gross amounts
        let grossWt = 0; // Initialize the variable to accumulate the gross weight
        let fin_Amt = 0; // Initialize the variable to accumulate the final amount

        // Check if the nett_amount and round_off fields are valid
        const nettAmount = parseFloat($("#nett_amount").val()) || 0;
        const roundOff = parseFloat($("#round_off").val()) || 0;

        if (nettAmount === 0 || roundOff === 0) {
            alert("Please ensure Nett Amount and Round Off are filled correctly.");
            return; // Stop execution if these values are missing or incorrect
        }

        tableRows.each(function() {
            const cells = $(this).find("td");

            const nettWt = parseFloat(cells.eq(2).text()) || 0;
            const stoneWt = parseFloat(cells.eq(13).find("input").val()) || 0;
            const grossAmt = parseFloat(cells.eq(8).text()) || 0;
            const taxVal = parseFloat(cells.eq(6).text()) || 0;

            const row = {
                barcode: cells.eq(0).text(),
                productName: cells.eq(1).text(),
                nettWt: nettWt,
                rate: cells.eq(3).text(),
                wstPer: cells.eq(4).text(),
                offerDis: cells.eq(5).text(),
                taxVal: taxVal,
                hmcVal: cells.eq(7).text(),
                grossAmt: grossAmt,
                bsitmid: cells.eq(9).find("input").val(),
                bsitmsubctgid: cells.eq(10).find("input").val(),
                bsitmctgid: cells.eq(11).find("input").val(),
                bsmetaltype: cells.eq(12).find("input").val(),
                stoneWt: stoneWt,
                itgstper: cells.eq(14).find("input").val(),
                stoneVal: cells.eq(15).find("input").val(),
                bscarat: cells.eq(16).find("input").val(),
                bsdiawt: cells.eq(17).find("input").val()
            };

            gross_Amt += grossAmt;
            grossWt += nettWt + stoneWt;
            fin_Amt += grossAmt + taxVal;

            rowData.push(row);
        });

        // Prepare data to send in the AJAX request
        const postData = {
            entryBy: entryBy,
            nett_amount: parseFloat($("#nett_amount").val()) || 0,
            round_off: parseFloat($("#round_off").val()) || 0,
            gross_Amt: gross_Amt.toFixed(2),
            grossWt: grossWt.toFixed(3),
            fin_Amt: fin_Amt.toFixed(2),
            // bsmetaltype: rowData[0]?.bsmetaltype || '',
            rowData: rowData
        };

        // Send data to the server using AJAX
        $.ajax({
            url: 'model/estimatesave.php',
            type: 'POST',
            data: JSON.stringify(postData), // Convert data to JSON string
            contentType: 'application/json', // Important: Tell server it's JSON
            dataType: 'json', // Expect JSON back
            success: function(response) {
                if (response.status === "success") {
                    alert("Data saved successfully!");

                    // Load estimate_print.php content into the modal
                    // $("#printModalBody").load("estimate_print.php", function() {
                    //     // Show the modal after loading
                    //     $("#printModal").modal("show");
                    // });
                    // Close the modal after 2 seconds
                    window.open("estimate_print.php", "_blank");
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
                alert("Error saving data. Please try again.");
            }
        });
    }
    </script>

    <script>
    // 🔒 Prevent zoom using Ctrl +, Ctrl -, Ctrl = keys
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && ['+', '-', '=', '0'].includes(e.key)) {
            e.preventDefault();
        }
    });

    // 🔒 Prevent zoom using mouse wheel + Ctrl (desktop)
    window.addEventListener('wheel', function(e) {
        if (e.ctrlKey) {
            e.preventDefault();
        }
    }, {
        passive: false
    });

    // 🔒 Prevent pinch zoom on touch devices (extra layer)
    window.addEventListener('gesturestart', function(e) {
        e.preventDefault();
    });

    window.addEventListener('gesturechange', function(e) {
        e.preventDefault();
    });

    window.addEventListener('gestureend', function(e) {
        e.preventDefault();
    });
    </script>
</body>

</html>