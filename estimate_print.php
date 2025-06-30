<?php @session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estimate Slip - Print Preview</title>
    <style>
    /* General Styles */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f5f5f5;
    }

    /* Print Preview Container */
    .print-container {
        max-width: 800px;
        margin: 0 auto;
        background-color: white;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    /* Estimate Slip Styles */
    .estimate-header {
        font-weight: bold;
        text-align: center;
        font-size: 20px;
        margin-bottom: 15px;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .info-table td {
        padding: 3px 0;
        white-space: nowrap;
    }

    .info-table td:nth-child(2),
    .info-table td:nth-child(5) {
        padding: 0 5px;
        width: 1%;
    }

    .main-table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
        font-size: 14px;
    }

    .main-table th,
    .main-table td {
        border: 1px solid black;
        padding: 5px;
        text-align: center;
    }

    .main-table th {
        font-weight: bold;
        background-color: #f0f0f0;
    }

    .divider {
        text-align: center;
        margin: 10px 0;
        font-weight: bold;
        font-size: 16px;
    }

    .footer-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
    }

    .footer-text {
        font-size: 12px;
        color: #555;
    }

    .footer-fields {
    width: 100%;
    max-width: 175px; /* adjust as needed */
    /* margin: 0 auto; */
    font-size: 14px;
}

.field-group {
    display: flex;
    justify-content: space-between;
    padding: 4px 0;
    border-bottom: 1px dotted #ccc;
}

.field-label {
    font-weight: bold;
}

    .field-group {
        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    .field-label {
        margin-right: 5px;
    }

    .fixed-rate {
        text-align: center;
        font-weight: bold;
        margin-top: 10px;
        font-size: 14px;
    }

    /* Print Controls */
    .print-controls {
        text-align: center;
        margin: 20px 0;
    }

    .print-btn {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 4px 2px;
        cursor: pointer;
        border-radius: 4px;
    }

    /* Print-specific Styles */
    @media print {
        body {
            background-color: white;
            padding: 0;
        }

        .print-container {
            box-shadow: none;
            padding: 0;
            max-width: 100%;
        }

        .print-controls {
            display: none;
        }

        /* Adjust font sizes for print */
        .estimate-header {
            font-size: 24px;
        }

        .main-table {
            font-size: 12px;
        }
    }

    .EcNo{
        margin-top: 87px !important;
    }
    </style>
</head>

<body>
    <div class="print-controls" style="display: none;">
        <button class="print-btn" onclick="window.print()">Print Estimate</button>
    </div>

    <div class="print-container">
        <div class="estimate-header">ESTIMATE</div>

        <table class="info-table">
            <tr>
                <td>Tm No.</td>
                <td>:</td>
                <td>720</td>
                <td style="padding-left: 20px;">Gold Rate</td>
                <td>:</td>
                <td>14K</td>
                <td>5730.00</td>
            </tr>
            <tr>
                <td>Date</td>
                <td>:</td>
                <td>26/04/2025</td>
                <td style="padding-left: 20px;">Silver Rate</td>
                <td>:</td>
                <td>22K</td>
                <td>9005.00</td>
            </tr>
        </table>

        <table class="main-table">
            <thead>
                <tr>
                    <th>Barcode</th>
                    <th>Pcs</th>
                    <th>Net Wt.</th>
                    <th>St Wt.</th>
                    <th>St .Crt</th>
                    <th>St Val.</th>
                    <th>Wst%</th>
                    <th>MC</th>
                    <th>Oth</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>DIM21618</td>
                    <td>1</td>
                    <td>2.740</td>
                    <td>0.080</td>
                    <td>0.40</td>
                    <td>37920</td>
                    <td>15.0</td>
                    <td>1000.0</td>
                    <td>90.00</td>
                    <td>63966.57</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;border: none;"></td>
                    <td style="border: none;font-weight: bold;">2.740</td>
                </tr>
        </table>

        <!-- <div class="divider">2.740</div> -->

        <div class="footer-container">
            <div class="field-group EcNo">
                <span class="field-label">EC :</span>
                <span><?php echo $_SESSION['empid']; ?></span>
            </div>
            <div class="footer-fields">
                <div class="field-group">
                    <span class="field-label">SGST</span>
                    <span>988.00</span>
                </div>
                <div class="field-group">
                    <span class="field-label">CGST</span>
                    <span>988.00</span>
                </div>
                <div class="field-group">
                    <span class="field-label">Round Off</span>
                    <span>-0.57</span>
                </div>
                <div class="field-group">
                    <span class="field-label">Net Amount</span>
                    <span>65885.00</span>
                </div>
            </div>
        </div>
        <hr style="border: 2px solid black;font-weight: bold;">
        <div class="footer-text" style="text-align: center;">This slip is for internal use only</div>
        <hr style="border: 2px solid black;">
        <div class="fixed-rate">** FIXED RATE **</div>
        <hr style="border: 2px solid black;">
    </div>

    <script>
    // You can add additional JavaScript here if needed
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-trigger print dialog (optional)
        // window.print();

        // Or add more interactive features
        const printBtn = document.querySelector('.print-btn');
        printBtn.addEventListener('click', function() {
            // You could add analytics or other tracking here
            console.log('Print button clicked');
        });
    });
    </script>
</body>

</html>