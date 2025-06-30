<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - KTM Billing</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #c9c0c0;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .form-control:focus {
            box-shadow: none;
        }
    </style>
</head>

<body class="bg-dark">

    <div class="login-container">
        <h4 class="text-center mb-4">KTM Billing Login</h4>
        <form onsubmit="event.preventDefault(); ReDirect();">
            <div class="mb-3">
                <label for="username" class="form-label">Username / Email</label>
                <input type="text" class="form-control" id="username" placeholder="Enter username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" placeholder="Enter password" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">👁️</button>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-dark">Login</button>
            </div>

            <div class="mt-3 text-end">
                <a href="#">Forgot password?</a>
            </div>
        </form>
    </div>

    <script>
        function togglePassword() {
            const pass = document.getElementById("password");
            pass.type = pass.type === "password" ? "text" : "password";
        }

        function ReDirect() {
            const username = document.getElementById("username").value.trim();
            const password = document.getElementById("password").value.trim();

            if (!username) {
                alert("Please enter your username.");
                return;
            }

            if (!password) {
                alert("Please enter your password.");
                return;
            }

            // ✅ Continue with login API without encryption
            fetch(`http://localhost/billingsystem/api_keys/login_key.php?username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`)
                .then(async response => {
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.includes("application/json")) {
                        return response.json();
                    } else {
                        const text = await response.text();
                        throw new Error("Server returned non-JSON:\n" + text);
                    }
                })
                .then(data => {
                    if (data.status === "success") {
                        alert(data.message);
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message || "Login failed. Please try again.");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Something went wrong:\n\n" + error.message);
                });
        }
    </script>

</body>

</html>