<?php @session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>QR Code Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Bootstrap & SweetAlert -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <style>
        body {
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            padding: 20px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="login-box">
        <h2>Login via QR Code</h2>
         <!-- <button class="btn btn-success mt-3" onclick="startScanner()">Scan QR</button> -->
     <input type="text" name="empid" id="empid" placeholder="Enter Employee ID" value="<?php //echo date("Y-m-d");?>" class="form-control" onchange="validateLogin(this.value);">
    </div>

    <!-- Modal -->
    <div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrScannerModalLabel">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="stopScanner()"></button>
                </div>
                <div class="modal-body">
                    <div id="qr-reader" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.7/html5-qrcode.min.js"></script>

    <script>
        let html5QrCode;

        function startScanner() {
            const modal = new bootstrap.Modal(document.getElementById('qrScannerModal'));
            modal.show();

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("qr-reader");
            }

            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                qrCodeMessage => {
                    html5QrCode.stop().then(() => {
                        modal.hide();
                        validateLogin(qrCodeMessage);
                    });
                },
                error => {
                    // silent scan error
                }
            ).catch(err => {
                console.error("Camera error: ", err);
                Swal.fire('Error', 'Camera access denied or not supported.', 'error');
            });
        }

        function stopScanner() {
            if (html5QrCode) {
                html5QrCode.stop().catch(err => {
                    console.error("Stop error: ", err);
                });
            }
        }

        function validateLogin(empid) {
            if (!empid) return;

            fetch(`http://localhost/BillingSystem/validate_login.php?empid=${empid}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {  // Changed here
                        Swal.fire({
                            title: 'Login successful',
                            text: 'Redirecting to Estimate Page...',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'dash3.php';
                        });
                    } else {
                        Swal.fire({
                            title: 'Failed',
                            text: 'Invalid QR Code',
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Login Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Something went wrong. Try again.',
                        icon: 'error'
                    });
                });
        }
    </script>

</body>

</html>