<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Dashboard</title>
    <link rel="stylesheet" href="css/crm_dash.css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
</head>
<body>
    <!-- <div class="dashboard-container">
        <div class="logo">
            <img src="images/bg.png" alt="">
        </div>
        <div class="container">
          
          <form action="https://admin.oceansapphire.com" method="get" target="_blank" class="card-form">
            <button type="submit" class="card">Billing Panel</button>
          </form>

          
          <form action="https://crm.oceansapphire.com/index.php?emp_code=<?= $_SESSION['user']['emp_code']; ?>" method="POST" target="_blank" class="card-form">
            <input type="hidden" name="emp_code" value="<?php echo $_SESSION['user']['emp_code']; ?>">
            <button type="submit" class="card">CRM Panel</button>
          </form>
        </div>
    </div> -->

    <header class="header">
        <div class="logo">
            <img src="images/bg.png" alt="Logo" height="50">
        </div>
        <h1 class="title">CRM DASHBOARD</h1>
    </header>

    <div class="container">
        <form action="https://admin.oceansapphire.com" method="get" target="_blank" class="card-form">
            <button type="submit" class="card">Billing Panel</button>
        </form>

        <form action="https://crm.oceansapphire.com/index.php?emp_code=<?= $_SESSION['user']['emp_code']; ?>" method="POST" target="_blank" class="card-form">
            <input type="hidden" name="emp_code" value="<?php echo $_SESSION['user']['emp_code']; ?>">
            <button type="submit" class="card">CRM Panel</button>
        </form>
    </div>
    <style>
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .logo img {
            height: 50px;
        }
        .title {
            font-size: 36px;
            margin: 0;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }
    </style>
</body>
</html>