<?php require_once('header.php'); ?>

<script type="text/javascript">
    function callFailed() {
        new RetroNotify({
            style: 'red',
            animate: 'slideTopRight',
            contentHeader: '<i class="fa-regular fa-message"></i> Customer,',
            contentText: 'Update Failed.',
            closeDelay: 3000
        })
    }

    function callSuccess() {
        new RetroNotify({
            style: 'sky',
            animate: 'slideTopRight',
            contentHeader: '<i class="fa-regular fa-message"></i> Top Employee,',
            contentText: 'Successfully Updated',
            closeDelay: 3000
        });
    }

    function capitalizeFirstLetter(input) {
        let value = input.value;
        if (value.length > 0) {
            input.value = value.charAt(0).toUpperCase() + value.slice(1);
        }
    }

    function fetchInvoiceNo(MobileNo) {
        if (MobileNo) {
            $.ajax({
                type: 'POST',
                url: 'ajax/InvoiceNo_ajax.php',
                data: {
                    MobileNo: MobileNo
                },
                success: function(data) {
                    let invoiceDropdown = $('#bill_no');
                    invoiceDropdown.empty(); // Clear existing options
                    invoiceDropdown.append('<option value="0">Select</option>'); // Default option
    
                    try {
                        let invoices = JSON.parse(data);
    
                        // Always include 'Other Jewellery' once
                        invoiceDropdown.append('<option value="N">Other Jewellery</option>');
    
                        if (Array.isArray(invoices) && invoices.length > 0) {
                            invoices.forEach(function(invoice) {
                                invoiceDropdown.append('<option value="' + invoice.Id + '">' + invoice.InvoiceNo + '</option>');
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing JSON: ', e);
                        invoiceDropdown.append('<option value="0">Error loading invoices</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error: ' + status + ': ' + error);
                }
            });
        }
    
        $('#Exchange').empty(); // Clear Exchange section regardless
    }
</script>

<?php
// Initialize variables
$trn_no = '';
$trn_date = '';
$cus_mob = '';
$remarks = ''; // Initialize $remarks to an empty string

$statement = $pdo->prepare("SELECT DISTINCT cus.cus_mob AS MobileNo,cus.cus_name AS Name FROM mst_customer cus");
$statement->execute();
$tblcus = $statement->fetchAll(PDO::FETCH_ASSOC);

$id = $_SESSION['user']['user_id'];

// Get branch ID
$statement = $pdo->prepare("SELECT EMP.brn_id FROM mst_employee EMP INNER JOIN mst_user USR ON USR.emp_id = EMP.emp_id WHERE USR.user_id = ?");
$statement->execute([$id]);
$row = $statement->fetch(PDO::FETCH_ASSOC);
$trn_branch = $row['brn_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['emp_photo'])) {
    $uploadDir = 'img/OG_Return/';
    $empName = $_POST['emp_name'];  // Replace this with the actual identifier

    // Sanitize the filename using preg_replace
    $filename = preg_replace('/[^a-zA-Z0-9-_]/', '_', $empName);
    $fileTmpPath = $_FILES['emp_photo']['tmp_name'];
    $fileName = $filename . '.png';  // Assuming the image is PNG, you can change the extension based on the format you capture

    // Ensure the directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Move the uploaded file to the desired directory
    if (move_uploaded_file($fileTmpPath, $uploadDir . $fileName)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
    }
}
if (isset($_GET['act'])) {
    $statement = $pdo->prepare("SELECT HDR.trn_no,HDR.trn_date,HDR.cus_mob AS MobileNo,HDR.remarks FROM oginward_hdr HDR
    WHERE HDR.trn_no=?");
    $statement->execute([$_REQUEST['trn_no']]);
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
        $trn_no = $row['trn_no'];
        $trn_date = $row['trn_date'];
        $cus_mob = $row['MobileNo'];
        $remarks = $row['remarks'];
    }
} else {
    $statement = $pdo->prepare("SELECT IFNULL(MAX(trn_no),0) + 1 AS Auto_Inc FROM oginward_hdr WHERE 1=1");
    $statement->execute();
    $ex_no = $statement->fetch(PDO::FETCH_ASSOC);
    $trn_no = $ex_no['Auto_Inc'];
}
?>
<section class="content-header">
    <div class="content-header-left">
        <h1>Add OG</h1>
    </div>
    <div class="content-header-right">
        <a href="sales-return-list.php" class="btn btn-primary btn-sm">ViewAll</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php
            if ($success_message) {
                echo '<script>callSuccess();</script>';
            }
            if ($error_message) {
                echo '<script>callFailed();</script>';
                echo '<div class="alert  alert-danger">' . $error_message . '</div>';
            }
            ?>
            <form class="form-horizontal" action="" method="POST" id="Og_Os">
                <div class="box box-info">
                    <div class="box box-body">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <div class="row">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-3">
                                            <label for="" class="control-label">TrnNo <span style="color: green;"> *</span></label>
                                            <input type="text" name="trn_no" id="trn_no" class="form-control" value="<?php echo $trn_no; ?>" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="" class="control-label">Trn Date <span style="color: green;"> *</span></label>
                                            <!-- <input type="text" name="trn_date" id="trn_date" class="form-control" value="<?= isset($_GET['trn_date']) ? date("d-m-Y", strtotime($_GET['trn_date'])) : date("d-m-Y"); ?>" readonly> -->
                                            <input type="text" name="trn_date" id="trn_date" class="form-control"
                                                value="<?= (!empty($trn_date) && $trn_date != '0000-00-00') ? date("Y-m-d", strtotime($trn_date)) : date("Y-m-d"); ?>" readonly>

                                        </div>
                                        <div class="col-md-3"></div>
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    <div class="row">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-3">
                                            <label for="" class="control-label">Customer <span style="color: green;"> *</span></label>
                                            <select name="cus_mob" id="cus_mob" class="form-control select2" onchange="fetchInvoiceNo(this.value);">
                                                <option value="0">Select</option>
                                                <?php foreach ($tblcus as $row): ?>
                                                    <?php
                                                    $selected = (isset($cus_mob) && $cus_mob == $row['MobileNo']) ? 'selected' : '';
                                                    ?>
                                                    <option value="<?php echo htmlspecialchars($row['MobileNo']); ?>" <?php echo $selected; ?>>
                                                        <?php echo htmlspecialchars($row['MobileNo'] . ' - ' . $row['Name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="" class="control-label">Bill No <span style="color: green;"> *</span></label>
                                            <select id="bill_no" name="bill_no" class="form-control select2" onchange="fetchProductDet(this.value);">
                                                <option value="0">Select</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3"></div>
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    <div class="row">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-3">
                                            <label for="" class="control-label">Remarks <span style="color: green;"> *</span></label>
                                            <textarea type="text" name="remarks" id="remarks" class="form-control text-uppercase"><?php echo $remarks; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-12" style="margin-left: 0px !important;">
                                    <div class="row">
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="ExchangeDet">
                                                <thead class="bg-primary text-light">
                                                    <tr>
                                                        <th style="width:1%">#</th>
                                                        <th style="width:5%">Bill No</th>
                                                        <th style="width:7%">Product</th>
                                                        <th style="width:2%">Purity</th>
                                                        <th style="width:2%">Type</th>
                                                        <th style="width:2%">Met Wt</th>
                                                        <th style="width:2%">Gld Rt</th>
                                                        <th style="width:2%">Met Val</th>
                                                        <th style="width:2%">Gld Ded%</th>
                                                        <th style="width:2%">Dia Crt</th>
                                                        <th style="width:3%">Dia Val</th>
                                                        <th style="width:2%">Dia Ded %</th>
                                                        <th style="width:2%">Fin DiaVal</th>
                                                        <th style="width:2%">DedAmt</th>
                                                        <th style="width:2%">Fin Amt</th>
                                                        <th style="width:1%">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $j = 0;
                                                    
                                                    if (isset($_GET['act']) && ctype_digit($_GET['trn_no'])) {
                                                        try {
                                                            $stmt = $pdo->prepare("SELECT HDR.trn_no,HDR.trn_date,HDR.cus_mob,DTL.bill_no,DTL.product,DTL.purity,
                                                                CASE DTL.og_type WHEN 'O' THEN 'OWN' ELSE 'Others' END AS og_type,DTL.metal_wt,DTL.gold_price,DTL.metal_price,
                                                                DTL.stone_wt,DTL.diamond_price,DTL.diamond_ded,DTL.final_dia_val,DTL.final_amount
                                                            FROM oginward_hdr HDR
                                                            INNER JOIN oginward_dtl DTL 
                                                                ON DTL.trn_no = HDR.trn_no
                                                            WHERE HDR.trn_no = ?");
                                                            
                                                            $stmt->execute([$_GET['trn_no']]);
                                                            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            
                                                            foreach ($results as $row) {
                                                                // $final_amount += (float)$row['final_amount'];
                                                    ?>
                                                                <tr>
                                                                    <td><?php echo $j++ ?></td>
                                                                    <td>
                                                                        <input type="text" class="form-control" 
                                                                               name="bill_no[]" 
                                                                               value="<?= htmlspecialchars($row['bill_no']) ?>" 
                                                                               readonly>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" class="form-control" 
                                                                               name="product[]" 
                                                                               value="<?= htmlspecialchars($row['product']) ?>" 
                                                                               readonly>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" class="form-control" 
                                                                               name="purity[]" 
                                                                               value="<?= htmlspecialchars($row['purity']) ?>">
                                                                    </td>
                                                                    <td>
                                                                        <select class="form-control" name="og_type[]">
                                                                            <option value="O" <?= $row['og_type'] === 'OWN' ? 'selected' : '' ?>>OWN</option>
                                                                            <option value="T" <?= $row['og_type'] !== 'OWN' ? 'selected' : '' ?>>Others</option>
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control metal-wt" 
                                                                               name="metal_wt[]" step="0.01"
                                                                               value="<?= htmlspecialchars($row['metal_wt']) ?>">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control gold-price" 
                                                                               name="gold_price[]" step="0.01"
                                                                               value="<?= htmlspecialchars($row['gold_price']) ?>">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control metal-price" 
                                                                               name="metal_price[]" step="0.01"
                                                                               value="<?= htmlspecialchars($row['metal_price']) ?>" 
                                                                               readonly>
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control diamond-val" 
                                                                               name="diamond_ded[]" step="0.01"
                                                                               value="<?= htmlspecialchars($row['diamond_ded']) ?>">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control diamond-val" 
                                                                               name="diamond_ded[]" step="0.01"
                                                                               value="<?= htmlspecialchars($row['diamond_ded']) ?>">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control diamond-crt" 
                                                                               name="diamond_price[]" step="0.01"
                                                                               value="<?= htmlspecialchars($row['diamond_price']) ?>">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control diamond-val" 
                                                                               name="diamond_ded[]" step="0.01"
                                                                               value="<?= htmlspecialchars($row['diamond_ded']) ?>">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control final-dia-val" 
                                                                               name="final_dia_val[]" step="0.01"
                                                                               value="<?= htmlspecialchars($row['final_dia_val']) ?>" 
                                                                               readonly>
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control final-amt" 
                                                                               name="final_amount[]" step="0.01"
                                                                               value="<?= htmlspecialchars($row['final_amount']) ?>" 
                                                                               readonly>
                                                                    </td>
                                                                    <td>
                                                                        <button type="button" class="btn btn-danger btn-sm remove-row">
                                                                            <i class="fa fa-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                    <?php
                                                            }
                                                        } catch (PDOException $e) {
                                                            error_log("Database error: " . $e->getMessage());
                                                            echo '<tr><td colspan="13" class="text-danger">Error loading data</td></tr>';
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="14" style="text-align:right;"><b>Total Final Amount</b></td>
                                                        <td>
                                                            <input type="text" id="totalFinAmt" class="form-control" value="0.00" readonly>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1"></div>
                                <div class="form-group col-md-12">
                                    <div class="row">
                                        <div class="col-md-11"></div>
                                        <div class="col-md-1" style="width: 0px !important;">
                                            <label for="" class="control-label"></label>
                                            <button type="button" id="btn_Submit" class="btn btn-success float-right" name="form1"
                                                style="display: <?= isset($_GET['act']) ? 'none' : 'inline-block'; ?>;"
                                                onclick="mandatoryValidation();">Submit
                                            </button>
                                        <input type="hidden" id="brn_id" name="brn_id" value="<?php echo $trn_branch; ?>">
                                        </div>
                                        <!-- <div class="col-md-3"></div> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<script type="text/javascript">
    function fetchProductDet(InvoiceNo) {
        if(InvoiceNo !="N"){
        if (InvoiceNo && InvoiceNo !== "0") {
            const MobileNo = document.getElementById("cus_mob").value;

            $.ajax({
                type: 'POST',
                url: 'ajax/og_product.php',
                data: {
                    InvoiceNo: InvoiceNo,
                    MobileNo: MobileNo
                },
                success: function(data) {
                    // Assuming data is returned as JSON
                    let products;
                    try {
                        products = JSON.parse(data); // Parse JSON response
                    } catch (error) {
                        console.error('Failed to parse JSON:', error);
                        return;
                    }

                    // Check if data is in the expected format
                    if (!Array.isArray(products) || products.length === 0) {
                        console.error('No products found or invalid data format.');
                        return;
                    }

                    // Loop through each product in the returned data
                    products.forEach(product => {
                        const Barcode = product.Barcode;
                        const Bill_No = product.InvoiceNo;
                        const productName = product.P_name;
                        // const purity = product.Purity;
                        const metal_wt = product.metal_wt ?? 0.00;
                        const gold_price = product.price ?? 0.00;
                        const metal_price = product.metal_val ?? 0.00;
                        const stone_wt = product.stone_wt ?? 0.00;
                        const DiamondRate = product.DiamondRate ?? 0.00;

                        let skuExists = false;

                        // Check if the SKU already exists in the table
                        $('#ExchangeDet .Bill_No input').each(function() {
                            if ($(this).val() === Bill_No) {
                                skuExists = true;
                                return false; // Break the loop if found
                            }
                        });

                        if (skuExists) {
                            alert("Bill No Already Exists: " + Bill_No);
                        } else {
                            const rowCount = $('#ExchangeDet tr').length;
                            const html_code = `<tr id='row${rowCount}'>
                            <td class='rowCount'>${rowCount}<input type='hidden' class='form-control' name='rowCount[]' value=''></td>
                            <td class='Bill_Nos'><input type='text' class='form-control Bill_No' name='Bill_No[]' value='${Bill_No}' readonly></td>
                            <td class='product'><input type='text' class='form-control text-uppercase' name='product[]' value='${productName}' readonly></td>
                            
                            <td class='purity'>
                            <select class='form-control select2' name='purity[]'>
                                    <option value='0'>Select</option>
                                    <option value='14'>14KT</option>
                                    <option value='18'>18KT</option>
                                    <option value='22'>22KT</option>
                                    <option value='24'>24KT</option>
                                </select>
                            </td>
                            <td class='og_type'>
                                <select class='form-control select2 og_type' name='og_type[]' onchange='og_Type.call(this)'>
                                    <option value='S'>Select</option>
                                    <option value='O'>Own</option>
                                    <option value='T'>Others</option>
                                </select>
                            </td>
                            <td class='metal_wt'><input type='text' class='form-control text-uppercase' name='metal_wt[]' value='${metal_wt}' readonly></td>
                            <td class='gold_price'><input type='text' class='form-control text-uppercase' name='gold_price[]' value='${gold_price}' readonly></td>
                            <td><input type='text' class='form-control metal_price' name='metal_price[]' value='${metal_price}' autocomplete='off'></td>
                            <td><input type='number' class='form-control gold_ded' name='gold_ded[]' value='0' autocomplete='off' onblur="gold_ded.call(this)"></td>
                            <td><input type='text' class='form-control DiaVal' name='DiaVal[]' value='' autocomplete='off' readonly></td>
                            <td class='stone_wt'><input type='text' class='form-control text-uppercase' name='stone_wt[]' value='${stone_wt}' readonly></td>
                            <td><input type='text' class='form-control DiamondRate' name='DiamondRate[]' value='${DiamondRate}' autocomplete='off'></td>
                            <td><input type='number' class='form-control diamond_ded' name='diamond_ded[]' value='20' min='20' autocomplete='off' onblur="diamond_ded.call(this)"></td>
                            <td><input type='text' class='form-control DiaVal' name='DiaVal[]' value='' autocomplete='off' readonly></td>
                            <td><input type='text' class='form-control DedAmt' name='DedAmt[]' value='' autocomplete='off' readonly></td>
                            <td><input type='text' class='form-control FinAmt' name='FinAmt[]' value='' autocomplete='off' readonly></td>
                            <td style='text-align:center;'>
                                <button title='Delete Row' type='button' name='remove' data-row='row${rowCount}' class='btn btn-danger btn-xs remove'><i class='fa fa-trash'></i></button>
                            </td>
                        </tr>`;
                            $('#ExchangeDet').append(html_code);
                            $("#remarks").focus();
                        }
                        $("#remarks").focus();
                    });
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
        }
        else {
            const Bill_No = document.getElementById("cus_mob").value;
            const rowCount = $('#ExchangeDet tr').length;
            const html_code = `<tr id='row${rowCount}'>
                            <td class='rowCount'>${rowCount}<input type='hidden' class='form-control' name='rowCount[]' value=''></td>
                            <td class='Bill_Nos'><input type='text' class='form-control' name='Bill_No[]' value='${Bill_No}' readonly></td>
                            <td class='product'><input type='text' class='form-control text-uppercase' name='product[]' value=''></td>
                            <td class='purity'>
                            <select class='form-control select2' name='purity[]' onchange="GoldRate(this.value, this);">
                                    <option value='0'>Select</option>
                                    <option value='14'>14 KT</option>
                                    <option value='18'>18 KT</option>
                                    <option value='22'>22 KT</option>
                                    <option value='24'>24 KT</option>
                                </select></td>
                            <td class='og_type'>
                                <select class='form-control select2 og_type' name='og_type[]' onchange='og_Type.call(this)'>
                                    <option value='S'>Select</option>
                                    <option value='O'>Own</option>
                                    <option value='T'>Others</option>
                                </select>
                            </td>
                            <td class='metal_wt'><input type='text' class='form-control text-uppercase' name='metal_wt[]' onblur="MetalPrice(this)"></td>
                            <td class='gold_price'><input type='text' class='form-control text-uppercase' name='gold_price[]' readonly></td>
                            <td><input type='text' class='form-control metal_price' name='metal_price[]' value='' autocomplete='off' readonly></td>
                            <td><input type='number' class='form-control gold_ded' name='gold_ded[]' value='0' autocomplete='off' onblur="gold_ded.call(this)"></td>
                            <td class='stone_wt'><input type='text' class='form-control text-uppercase' name='stone_wt[]' value='' onblur="Dia_Val(this.value);"></td>
                            <td><input type='text' class='form-control DiamondRate' name='DiamondRate[]' id='DiamondRate[]' value='' autocomplete='off' readonly></td>
                            <td><input type='number' class='form-control diamond_ded' name='diamond_ded[]' value='20' autocomplete='off' onblur="diamond_ded.call(this)"></td>
                            <td><input type='text' class='form-control DiaVal' name='DiaVal[]' value='' autocomplete='off' readonly></td>
                            <td><input type='text' class='form-control DedAmt' name='DedAmt[]' value='' autocomplete='off' readonly></td>
                            <td><input type='text' class='form-control FinAmt' name='FinAmt[]' value='' autocomplete='off' readonly></td>
                            <td style='text-align:center;'>
                                <button title='Delete Row' type='button' name='remove' data-row='row${rowCount}' class='btn btn-danger btn-xs remove'><i class='fa fa-trash'></i></button>
                            </td>
                        </tr>`;
            $('#ExchangeDet').append(html_code);
            $("#remarks").focus();
        }
        $("#remarks").focus();
    }

    function MetalPrice(element) {
        // 1. Get the current row
        const row = $(element).closest('tr');
        
        // 2. Get metal weight (from the input that triggered this function)
        const metal_wt = parseFloat($(element).val()) || 0;
        
        // 3. Get gold price (from the gold_price input in the same row)
        const gold_price = parseFloat(row.find('input[name="gold_price[]"]').val()) || 0;
        
        // 4. Calculate metal value
        const Metal_Val = metal_wt * gold_price;
        
        // 5. Update metal price in the same row
        row.find('input[name="metal_price[]"]').val(Metal_Val.toFixed(2));
        
        // 6. Optional: Update the final amount
        // updateRowTotal(row); // (If you have this function)
    }
    
    function Dia_Val(Value) {
        const Dia_Val = Value;
        const Dia_Rate = 94800;

        const Diamond_Val = Dia_Val * Dia_Rate;
        $(".DiamondRate").val(Math.round(Diamond_Val, 2).toFixed(2));
    }

    function GoldRate(Carot, element) {
        if (Carot) {
            $.ajax({
                type: 'POST',
                url: 'ajax/Gold_Rate.php',
                data: {
                    Carot: Carot
                },
                success: function(data) {
                    // Find the closest row and then find the gold_price input within that row
                    $(element).closest('tr').find('.gold_price input').val(data);
                }
            })
        }
    }
    
    // Function to handle row removal
    function removeRow() {
        // Use event delegation for dynamically added elements
        $(document).on('click', '.remove', function() {
            // Get the row ID from the button's data attribute
            const rowId = $(this).data('row');
            
            // Remove the row from the table
            $('#' + rowId).remove();
            
            // Optionally, you can renumber the remaining rows
            renumberRows();
        });
    }
    
    // Helper function to renumber rows after deletion
    function renumberRows() {
        $('#ExchangeDet tr').each(function(index) {
            // Skip header row if exists (index starts from 0)
            if (index > 0) {
                // Update the row count in the first cell
                $(this).find('.rowCount').text(index);
                $(this).find('.rowCount input').val(index);
                
                // Update the row ID and button data-row attribute
                const newRowId = 'row' + index;
                $(this).attr('id', newRowId);
                $(this).find('.remove').data('row', newRowId);
            }
        });
    }
    
    // Call the function to initialize the event listener
    removeRow();

    // $(document).ready(function () {

    //     // Validate & calculate on diamond_ded input
    //     $(document).on('input', '.diamond_ded', function () {
    //         let row = $(this).closest('tr');
    //         let ogType = row.find('.og_type').val();
    //         let value = parseFloat($(this).val());
    
    //         if (ogType === "O") {
    //             // Own type => min 0
    //             if (isNaN(value) || value < 0) {
    //                 $(this).val(0);
    //                 value = 0;
    //             }
    //         } else {
    //             // Others => min 20
    //             if (isNaN(value) || value < 20) {
    //                 $(this).val(20);
    //                 value = 20;
    //             }
    //         }
    
    //         if (value > 100) {
    //             $(this).val(100);
    //             value = 100;
    //         }
    
    //         diamond_ded.call(this);
    //     });
    
    //     // Update default Dia Ded % when Type changes
    //     $(document).on('change', '.og_type', function () {
    //         let row = $(this).closest('tr');
    //         let dedInput = row.find('.diamond_ded');
    
    //         if ($(this).val() === 'O') {
    //             dedInput.val(0); // Own = 0%
    //         } else {
    //             dedInput.val(20); // Others = 20%
    //         }
    
    //         dedInput.trigger('input');
    //     });
    
    // });
    
    function og_Type() {
        let row = $(this).closest('tr');
        let dedInput = row.find('.diamond_ded');
        let selectedVal = $(this).val();
    
        if (selectedVal === 'O') {
            dedInput.val(0); // Own => always 0
        } else {
            let existingVal = parseFloat(dedInput.val());
            if (isNaN(existingVal) || existingVal < 20) {
                dedInput.val(20);
            } else if (existingVal > 100) {
                dedInput.val(100);
            } else {
                dedInput.val(existingVal);
            }
        }
    
        dedInput.trigger('input');
    }
    
    $(document).on('input', '.diamond_ded', function () {
        let row = $(this).closest('tr');
        let ogType = row.find('select.og_type').val(); // ✅ fixed line
        let value = parseFloat($(this).val());
    
        if (ogType === "O") {
            if (isNaN(value) || value < 0) value = 0;
            else if (value > 100) value = 100;
        } else {
            if (isNaN(value) || value < 20) value = 20;
            else if (value > 100) value = 100;
        }
    
        $(this).val(value);
        diamond_ded.call(this);
    });
    
    function gold_ded() {
        let value = parseFloat(this.value);
    
        // Ensure value is between 0 and 100
        if (isNaN(value) || value < 0) {
            value = 0;
        } else if (value > 100) {
            value = 100;
        }
    
        this.value = value;
    
        let row = $(this).closest('tr');
        let metal_price = parseFloat(row.find(".metal_price").val()) || 0;
        let defval = parseFloat(row.find(".gold_ded").val()) || 0;
    
        let DedPer = (metal_price * defval) / 100;
        let DedAmt = metal_price - DedPer;
        row.find(".DedAmt").val(DedAmt.toFixed(2));
    }
    
    function customRoundOff(amount) {
        let amt = Math.round(amount);
        let rem = amt % 10;
        if (rem === 0) return amt;
        if (rem <= 5) return amt - rem;
        return amt - rem + 5;
    }

    function diamond_ded() {
        let row = $(this).closest('tr');

        let fnlval = parseFloat(row.find(".DiamondRate").val()) || 0;
        let defval = parseFloat(row.find(".diamond_ded").val()) || 0;
        let DedAmt = parseFloat(row.find(".DedAmt").val()) || 0;

        let DedPer = (fnlval * defval) / 100;
        let FinDiaAmt = fnlval - DedPer;
        let FinAmt = DedAmt + FinDiaAmt;

        row.find(".DiaVal").val(FinDiaAmt.toFixed(2));
        // Use custom round-off for FinAmt
        row.find(".FinAmt").val(customRoundOff(FinAmt).toFixed(2));
    }
    
    function mandatoryValidation() {
        let brn_id = document.getElementById("brn_id").value; // Get brn_id
        
        // Check if brn_id is empty and show a user-friendly message
        if (!brn_id) {
            alert("Branch ID is required.");
            return; // Stop the AJAX request if brn_id is not provided
        }
    
        $.ajax({
            type: 'POST',
            url: 'model/og_os_save.php',
            data: $("#Og_Os").serialize() + "&brn_id=" + encodeURIComponent(brn_id), // Append brn_id
            dataType: 'json', // Expect JSON response
            success: function(response) {
                // Check if response is valid
                if (response && response.success !== undefined) {
                    if (response.success) {
                        alert(response.message); // Success message
                        window.location.href = 'sales-return-list.php'; // Redirect on success
                    } else {
                        alert(response.message); // Validation or error message from server
                    }
                } else {
                    alert("Invalid response format received from the server.");
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                console.error("Response:", xhr.responseText); // Log the full response for debugging
                alert("An error occurred while processing data. Please try again.");
            }
        });
    }

    function updateTotalFinAmt() {
        let total = 0;
        $('#ExchangeDet tbody tr').each(function() {
            let val = parseFloat($(this).find('.FinAmt').val()) || 0;
            total += val;
        });
        // Apply your custom round off to the total
        $('#totalFinAmt').val(customRoundOff(total).toFixed(2));
    }

    // Recalculate total when FinAmt changes
    $(document).on('input change', '.FinAmt', updateTotalFinAmt);

    // Also recalculate after diamond/gold deduction changes
    $(document).on('input change', '.diamond_ded, .gold_ded, .metal_price, .DiamondRate, .DedAmt', function() {
        setTimeout(updateTotalFinAmt, 100); // Wait for FinAmt to update
    });

    // Initial call on page load
    $(document).ready(function() {
        updateTotalFinAmt();
    });
    </script>
<?php require_once('footer.php'); ?>