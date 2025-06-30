<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="theme-color" content="#121212">
    <title>CRM Dashboard</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <div class="theme-icon-toggle" onclick="toggleTheme()">
        <i id="theme-icon" class="fas fa-moon"></i>
    </div>

    <div class="dashboard-container">
        <div class="logo">
            <img src="images/bg.png" alt="Logo" />
        </div>

        <h1 class="dashboard-title" data-typetext="ASHBOARD">DASHBOARD</h1>

        <div class="section-buttons">
            <button id="sales-btn" class="action-btn active" onclick="sales();">SALES</button>
            <button id="chit-btn" class="action-btn" onclick="chit();">CHIT</button>
            <button id="lead-btn" class="action-btn" onclick="leadreport();">LEAD REPORT</button>
        </div>

        <div class="cards-container">
            <div class="card purple">
                <div class="card-title">TOTAL CUSTOMERS</div>
                <div class="card-value" id="total-customers">0</div>
                <hr class="divider" />
                <div class="card-subtext">
                    <span id="existing-customers">0 EXISTING</span>
                    <span id="new-customers">0 NEW</span>
                </div>
            </div>

            <div class="card green">
                <div class="card-title">TODAY CALLS</div>
                <div class="card-value" id="today-calls">0</div>
                <hr class="divider" />
                <div class="card-subtext">Last updated: <span id="last-updated">Loading...</span></div>
            </div>

            <div class="card teal">
                <div class="card-title">LEAD SALES</div>
                <div class="card-value" id="sales-leads-total">0</div>
                <hr class="divider" />
                <div class="card-subtext">
                    <span id="sales-leads-opening">0 OPENING</span>
                    <span id="sales-leads-today">0 TODAY</span>
                </div>
            </div>

            <div class="card blue-violet">
                <div class="card-title">LEAD CHIT</div>
                <div class="card-value" id="chit-leads-total">0</div>
                <hr class="divider" />
                <div class="card-subtext">
                    <span id="chit-leads-opening">0 OPENING</span>
                    <span id="chit-leads-today">0 TODAY</span>
                </div>
            </div>

            <div class="card pink">
                <div class="card-title">VISITED BY LEAD</div>
                <div class="card-value" id="visits-today">0</div>
                <hr class="divider" />
                <div class="card-subtext">Today's visits</div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>

</html>