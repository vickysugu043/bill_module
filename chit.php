<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Chit Dashboard</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="css/chit.css" />
</head>

<body>
    <div class="container">
        <div class="logo">
            <a href="index.php?emp_code=<?php echo urlencode($emp_code); ?>">
                <img src="images/bg.png" alt="Logo" height="50" />
            </a>
        </div>

        <h1>CHIT - <span id="brn-name"></span><span class="color"></span></h1>

        <a class="dashboard-link" href="dashboard.php">
            <i class="fa fa-dashboard"></i> Dashboard
        </a>

        <div class="btn-group">
            <button type="button" class="btn action-btn" data-action="customer">Customers</button>
            <button type="button" class="btn action-btn" data-action="schemes">Schemes</button>
            <button type="button" class="btn action-btn" data-action="pendings">Pending</button>
            <button type="button" class="btn action-btn" data-action="defaulters">Defaulters</button>
            <button type="button" class="btn action-btn" data-action="active">Active</button>
        </div>

        <div class="search-pagination-row"
            style="display: flex; justify-content: space-between; align-items: center; margin: 15px 0;">
            <input type="text" id="search-box" placeholder="Search by name or mobile..."
                style="padding:6px 12px; border-radius:6px; border:1px solid #ccc; min-width:220px;">
            <div id="pagination-controls" style="display: flex; gap: 8px; align-items: center;"></div>
        </div>

        <div class="cards-container" id="data-container">
            <div class="no-data">Select an option to load data</div>
        </div>
    </div>

    <!-- Lead Type Modal -->
    <div id="leadTypeModal" class="modal">
        <div class="modal-content">
            <div class="calendar-header">
                <div class="calendar-title">Select Lead Type</div>
            </div>

            <div class="lead-type-options">
                <button class="lead-type-btn sales" id="salesLead">Sales</button>
                <button class="lead-type-btn chit" id="chitLead">Chit</button>
                <button class="lead-type-btn visit" id="visitLead">Visit</button>
            </div>

            <div class="modal-actions">
                <button class="modal-btn cancel" id="cancelLeadType">Cancel</button>
            </div>
        </div>
    </div>

    <div id="goldWeightModal" class="modal">
        <div class="modal-content">
            <div class="calendar-header">
                <div class="calendar-title">Gold Details</div>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Gold Weight (grams)</label>
                    <input type="range" id="goldWeight" min="0" max="100" value="0" class="form-range">
                    <span id="weightValue">0g</span>
                </div>
                <div class="form-group">
                    <label>Purchase Value (₹)</label>
                    <input type="range" id="purchaseValue" min="0" max="1000000" step="1000" value="0"
                        class="form-range">
                    <span id="valueValue">₹0</span>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn cancel" id="cancelGoldWeight">Cancel</button>
                <button class="modal-btn confirm" id="confirmGoldWeight">OK</button>
            </div>
        </div>
    </div>

    <!-- Chit Scheme Modal -->
    <div id="chitSchemeModal" class="modal">
        <div class="modal-content">
            <div class="calendar-header">
                <div class="calendar-title">Chit Scheme Details</div>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Scheme Type</label>
                    <select id="schemeType" class="form-select">
                        <option value="">Select Scheme</option>
                        <option value="GR">GR</option>
                        <option value="SWD">SWD</option>
                        <option value="SUB">Surabhi</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Scheme Value</label>
                    <input type="text" id="schemeValue" class="form-control" placeholder="Describe scheme value">
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn cancel" onclick="closeChitSchemeModal()">Cancel</button>
                <button class="modal-btn confirm" onclick="saveChitDetails()">OK</button>
            </div>
        </div>
    </div>

    <!-- Visit Purpose Modal -->
    <div id="visitPurposeModal" class="modal">
        <div class="modal-content">
            <div class="calendar-header">
                <div class="calendar-title">Visit Details</div>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Purpose of Visit</label>
                    <textarea id="visitPurpose" class="form-control" rows="4"
                        placeholder="Enter visit purpose..."></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn cancel" onclick="closeVisitPurposeModal()">Cancel</button>
                <button class="modal-btn confirm" onclick="saveVisitDetails()">OK</button>
            </div>
        </div>
    </div>

    <!-- Calendar Modal -->
    <div id="calendarModal" class="modal">
        <div class="modal-content">
            <div class="calendar-header">
                <button class="calendar-nav" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                <div class="calendar-title" id="calendarMonthYear">June 2023</div>
                <button class="calendar-nav" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
            </div>

            <div class="calendar-grid" id="dayHeaders">
                <div class="calendar-day-header">Sun</div>
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>
            </div>

            <div class="calendar-grid" id="calendarDays"></div>

            <div class="modal-actions">
                <button class="modal-btn cancel" id="cancelSchedule">Cancel</button>
                <button class="modal-btn confirm" id="confirmSchedule">Schedule</button>
            </div>
        </div>
    </div>

    <script src="js/chit.js"></script>
</body>

</html>