// Global variables
let selectedDate = null;
let currentCustomer = null;
let currentDisplayedMonth = new Date();
let currentLeadType = null;
let currentCustomerForLead = null;
let empCode = '';

// Utility functions
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function showStatusMessage(message, type) {
    const container = document.getElementById('data-container');
    const messageDiv = document.createElement('div');
    messageDiv.className = `status-message ${type}-message`;
    messageDiv.textContent = message;
    container.prepend(messageDiv);
    setTimeout(() => messageDiv.remove(), 5000);
}

// Modal functions
function showLeadTypeModal(customer) {
    currentCustomerForLead = customer;
    document.getElementById('leadTypeModal').style.display = 'block';
}

function closeLeadTypeModal() {
    document.getElementById('leadTypeModal').style.display = 'none';
}

function showCalendarModal(customer) {
    currentCustomer = customer;
    document.getElementById('calendarModal').style.display = 'block';
    currentDisplayedMonth = new Date();
    renderCalendar(currentDisplayedMonth);
}

function closeCalendarModal() {
    document.getElementById('calendarModal').style.display = 'none';
    selectedDate = null;
}

function renderCalendar(date) {
    const monthYear = document.getElementById('calendarMonthYear');
    const calendarDays = document.getElementById('calendarDays');

    const month = date.getMonth();
    const year = date.getFullYear();
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    monthYear.textContent = `${date.toLocaleString('default', { month: 'long' })} ${year}`;
    calendarDays.innerHTML = '';

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    for (let i = 0; i < firstDay; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'calendar-day';
        calendarDays.appendChild(emptyDay);
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        const currentDate = new Date(year, month, day);

        dayElement.className = 'calendar-day';
        dayElement.textContent = day;

        if (currentDate.toDateString() === today.toDateString()) {
            dayElement.classList.add('today');
        }

        if (currentDate < today) {
            dayElement.classList.add('disabled');
        } else {
            dayElement.addEventListener('click', () => {
                selectedDate = currentDate;
                document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
                dayElement.classList.add('selected');
            });
        }

        if (selectedDate && currentDate.toDateString() === selectedDate.toDateString()) {
            dayElement.classList.add('selected');
        }

        calendarDays.appendChild(dayElement);
    }
}

// Enhanced handleCall function
function handleCall(buttonElement) {
    const card = buttonElement.closest('.customer-card');
    const buttons = card.querySelectorAll('.action-btn');
    const textarea = card.querySelector('.remarks-textarea');

    // Hide all buttons except inactive and save
    buttons.forEach(btn => {
        if (!btn.classList.contains('inactive-btn') && !btn.classList.contains('save-btn')) {
            btn.style.display = 'none';
        }
    });

    // Show relevant elements
    card.querySelector('.inactive-btn').style.display = 'flex';
    card.querySelector('.save-btn').style.display = 'flex';
    textarea.style.display = 'block';
    textarea.focus();
}

function resetCard(card) {
    card.querySelector('.call-btn').style.display = 'flex';
    card.querySelector('.inactive-btn').style.display = 'none';
    card.querySelector('.save-btn').style.display = 'none';
    card.querySelector('.lead-btn').style.display = 'none';
    card.querySelector('.remarks-textarea').style.display = 'none';
    card.querySelector('.remarks-textarea').value = '';
}

async function handleInactive(customerElement) {
    const card = customerElement.closest('.customer-card');
    const mobileNo = card.querySelector('.customer-mobile').textContent.trim();
    const CustCd = card.querySelector('.CustCd').value.trim();

    try {
        const response = await fetch('api_keys/chit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=inactive&CustMobileNo=${encodeURIComponent(mobileNo)}&CustCd=${encodeURIComponent(CustCd)}`
        });

        const data = await response.json();
        if (data.success) {
            showStatusMessage('Customer marked as inactive!', 'success');
            resetCard(card);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        showStatusMessage(error.message, 'error');
    }
}

async function saveCallLog(customerElement) {
    const card = customerElement.closest('.customer-card');
    const mobileNo = card.querySelector('.customer-mobile').textContent.trim();
    const remarks = card.querySelector('.remarks-textarea').value.trim();
    const CustCd = card.querySelector('.CustCd').value.trim();

    try {
        const response = await fetch('api_keys/chit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=call_log&CustMobileNo=${encodeURIComponent(mobileNo)}&remarks=${encodeURIComponent(remarks)}&CustCd=${encodeURIComponent(CustCd)}`
        });

        const data = await response.json();
        if (data.success) {
            showStatusMessage('Call saved successfully!', 'success');
            card.querySelector('.inactive-btn').style.display = 'none';
            card.querySelector('.save-btn').style.display = 'none';
            card.querySelector('.lead-btn').style.display = 'flex';
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        showStatusMessage(error.message, 'error');
    }
}

function handleLead(customerElement) {
    const card = customerElement.closest('.customer-card');

    // First check if all required elements exist
    const mobileNoElement = card.querySelector('.customer-mobile');
    const remarksElement = card.querySelector('.remarks-textarea');
    const custCdElement = card.querySelector('.CustCd');

    if (!mobileNoElement || !remarksElement || !custCdElement) {
        showStatusMessage('Customer data elements not found', 'error');
        return;
    }

    currentCustomerForLead = {
        mobileNo: mobileNoElement.textContent.trim(),
        remarks: remarksElement.value.trim(),
        CustCd: custCdElement.value.trim(),
        goldDetails: {},
        chitDetails: {},
        visitDetails: {}
    };

    if (!currentCustomerForLead.mobileNo || !currentCustomerForLead.CustCd) {
        showStatusMessage('Customer data not loaded properly', 'error');
        return;
    }

    showLeadTypeModal(currentCustomerForLead);
}

async function handleSaveLead() {
    if (!selectedDate) {
        showStatusMessage('Please select a date', 'error');
        return;
    }

    if (!currentCustomerForLead) {
        showStatusMessage('No customer selected for lead', 'error');
        return;
    }

    // Fix for timezone issue - use local date without timezone conversion
    const formattedDate = formatDateForSQL(selectedDate);

    try {
        const response = await fetch('api_keys/chit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=save_lead&CustMobileNo=${encodeURIComponent(currentCustomerForLead.mobileNo)}&lead_time=${encodeURIComponent(formattedDate)}&CustCd=${encodeURIComponent(currentCustomerForLead.CustCd)}`
        });

        const data = await response.json();
        if (data.success) {
            showStatusMessage('Lead saved successfully!', 'success');
            closeCalendarModal();
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        showStatusMessage(error.message, 'error');
    }
}

// Helper function to format date correctly for SQL without timezone issues
function formatDateForSQL(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}


// Gold Weight Modal Functions
function showGoldWeightModal() {
    try {
        // Get modal and validate it exists
        const modal = document.getElementById('goldWeightModal');
        if (!modal) {
            throw new Error('Gold weight modal not found');
        }

        // Get all required input elements
        const goldWeightInput = document.getElementById('goldWeight');
        const purchaseValueInput = document.getElementById('purchaseValue');
        const weightDisplay = document.getElementById('weightValue');
        const valueDisplay = document.getElementById('valueValue');

        // Validate all form elements exist
        if (!goldWeightInput || !purchaseValueInput || !weightDisplay || !valueDisplay) {
            throw new Error('Required form elements missing');
        }

        // Initialize with default values
        goldWeightInput.value = 0;
        purchaseValueInput.value = 0;
        weightDisplay.textContent = '0g';
        valueDisplay.textContent = '₹0';

        // Set up live value updates
        goldWeightInput.addEventListener('input', function () {
            weightDisplay.textContent = `${this.value}g`;
        });

        purchaseValueInput.addEventListener('input', function () {
            valueDisplay.textContent = `₹${parseInt(this.value || 0).toLocaleString('en-IN')}`;
        });

        // Show the modal
        modal.style.display = 'block';

    } catch (error) {
        console.error('Error in showGoldWeightModal:', error);
        showStatusMessage(error.message || 'Failed to load gold weight modal', 'error');
    }
}

async function saveGoldDetails() {
    try {
        // Get form inputs
        const goldWeightInput = document.getElementById('goldWeight');
        const purchaseValueInput = document.getElementById('purchaseValue');

        // Validate form inputs exist
        if (!goldWeightInput || !purchaseValueInput) {
            throw new Error('Required form fields are missing');
        }

        // Get values from inputs
        const weight = goldWeightInput.value.trim();
        const value = purchaseValueInput.value.trim();

        // Validate required fields
        if (!weight) throw new Error('Gold weight is required');
        if (!value) throw new Error('Gold value is required');

        // Verify customer data exists
        if (!currentCustomerForLead || !currentCustomerForLead.mobileNo) {
            throw new Error('Customer data not loaded');
        }

        // Prepare form data
        const formData = {
            action: 'gold_weight',
            lead_type: 'S',
            range_weight: weight,
            range_value: value,
            CustMobileNo: currentCustomerForLead.mobileNo.trim(),
            CustCd: currentCustomerForLead.CustCd?.trim() || ''
        };

        // Send request
        const response = await fetch('api_keys/chit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(formData)
        });

        // Handle response
        const responseText = await response.text();
        let data;

        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Invalid JSON response:', responseText);
            throw new Error('Invalid server response');
        }

        if (!data.success) {
            throw new Error(data.message || 'Failed to save gold details');
        }

        // Update state and UI
        if (currentCustomerForLead) {
            currentCustomerForLead.goldDetails = { weight, value };
        }

        showStatusMessage('Gold details saved successfully!', 'success');
        closeGoldWeightModal();

        if (currentCustomerForLead) {
            showCalendarModal(currentCustomerForLead);
        }

    } catch (error) {
        showStatusMessage(error.message, 'error');
        console.error('Error:', error);
    }
}

function closeGoldWeightModal() {
    document.getElementById('goldWeightModal').style.display = 'none';
}

// Chit Scheme Modal Functions
function showChitSchemeModal() {
    try {
        // Get modal and validate it exists
        const modal = document.getElementById('chitSchemeModal');
        if (!modal) {
            throw new Error('Chit scheme modal not found');
        }

        // Get all required input elements
        const schemeTypeInput = document.getElementById('schemeType');
        const schemeValueInput = document.getElementById('schemeValue');

        // Validate all form elements exist
        if (!schemeTypeInput || !schemeValueInput) {
            throw new Error('Required form elements missing');
        }

        // Initialize with default values
        schemeTypeInput.value = '';
        schemeValueInput.value = '';

        // Show the modal
        modal.style.display = 'block';

    } catch (error) {
        console.error('Error in showChitSchemeModal:', error);
        showStatusMessage(error.message || 'Failed to load chit scheme modal', 'error');
    }
}

async function saveChitDetails() {
    try {
        // Get form inputs
        const schemeTypeInput = document.getElementById('schemeType');
        const schemeValueInput = document.getElementById('schemeValue');

        // Validate form inputs exist
        if (!schemeTypeInput || !schemeValueInput) {
            throw new Error('Required form fields are missing');
        }

        // Get values from inputs
        const type = schemeTypeInput.value.trim();
        const value = schemeValueInput.value.trim();

        // Validate required fields
        if (!type) throw new Error('Chit scheme type is required');
        if (!value) throw new Error('Chit scheme value is required');

        // Verify customer data exists
        if (!currentCustomerForLead || !currentCustomerForLead.mobileNo) {
            throw new Error('Customer data not loaded');
        }

        // Prepare form data
        const formData = {
            action: 'chit_scheme',
            lead_type: 'C',
            chit_type: type,
            join_value: value,
            CustMobileNo: currentCustomerForLead.mobileNo.trim(),
            CustCd: currentCustomerForLead.CustCd?.trim() || ''
        };

        // Send request
        const response = await fetch('api_keys/chit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(formData)
        });

        // Handle response
        const responseText = await response.text();
        let data;

        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Invalid JSON response:', responseText);
            throw new Error('Invalid server response');
        }

        if (!data.success) {
            throw new Error(data.message || 'Failed to save gold details');
        }

        // Update state and UI
        if (currentCustomerForLead) {
            currentCustomerForLead.chitDetails = { type, value };
        }

        showStatusMessage('Chit details saved successfully!', 'success');
        closeChitSchemeModal();

        if (currentCustomerForLead) {
            showCalendarModal(currentCustomerForLead);
        }

    } catch (error) {
        showStatusMessage(error.message, 'error');
        console.error('Error:', error);
    }
}

function closeChitSchemeModal() {
    document.getElementById('chitSchemeModal').style.display = 'none';
}

// Visit Purpose Modal Functions
function showVisitPurposeModal() {
    try {
        // Get modal and validate it exists
        const modal = document.getElementById('visitPurposeModal');
        if (!modal) {
            throw new Error('Visit purpose modal not found');
        }

        // Get all required input elements
        const purposeInput = document.getElementById('visitPurpose');

        // Validate all form elements exist
        if (!purposeInput) {
            throw new Error('Required form elements missing');
        }

        // Initialize with default values
        purposeInput.value = '';

        // Show the modal
        modal.style.display = 'block';

    } catch (error) {
        console.error('Error in showVisitPurposeModal:', error);
        showStatusMessage(error.message || 'Failed to load visit purpose modal', 'error');
    }
}

async function saveVisitDetails() {
    try {
        // Get form inputs
        const purposeInput = document.getElementById('visitPurpose');

        // Validate form inputs exist
        if (!purposeInput) {
            throw new Error('Required form fields are missing');
        }

        // Get values from inputs
        const purpose = purposeInput.value.trim();

        // Validate required fields
        if (!purpose) throw new Error('Visit purpose is required');

        // Verify customer data exists
        if (!currentCustomerForLead || !currentCustomerForLead.mobileNo) {
            throw new Error('Customer data not loaded');
        }

        // Prepare form data
        const formData = {
            action: 'visit_purpose',
            lead_type: 'V',
            visit_purpose: purpose,
            CustMobileNo: currentCustomerForLead.mobileNo.trim(),
            CustCd: currentCustomerForLead.CustCd?.trim() || ''
        };

        // Send request
        const response = await fetch('api_keys/chit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(formData)
        });

        // Handle response
        const responseText = await response.text();
        let data;

        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Invalid JSON response:', responseText);
            throw new Error('Invalid server response');
        }

        if (!data.success) {
            throw new Error(data.message || 'Failed to save visit details');
        }

        // Update state and UI
        if (currentCustomerForLead) {
            currentCustomerForLead.visitDetails = { purpose };
        }

        showStatusMessage('Visit details saved successfully!', 'success');
        closeVisitPurposeModal();

    } catch (error) {
        showStatusMessage(error.message, 'error');
        console.error('Error:', error);
    }
}

function closeVisitPurposeModal() {
    document.getElementById('visitPurposeModal').style.display = 'none';
}

// Main data loading function
async function loadData(action) {
    currentAction = action;
    const container = document.getElementById('data-container');
    container.innerHTML = `
        <div class="loader-container">
            <span class="loader"></span>
            <div class="loader-text">Loading data...</div>
        </div>
        `;

    try {
        const response = await fetch(`api_keys/chit.php?action=${action}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();

        if (!data.success) throw new Error(data.message || 'Failed to load data');
        if (!data.data || data.data.length === 0) {
            container.innerHTML = '<div class="no-data">No records found</div>';
            return;
        }

        renderData(action, data.data);
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = `<div class="no-data">${error.message || 'Error loading data'}</div>`;
    }
}

function renderData(action, data) {
    const container = document.getElementById('data-container');
    container.innerHTML = data.map(row => {
        const mobileNo = escapeHtml(row.CustMobileNo || 'N/A');
        const isVerified = row.OTPVerified === 'Y';
        const CustCd = escapeHtml(row.CustCd || '');

        return `
        <div class="customer-card ${action === 'schemes' ? 'expandable-bill' : ''}">
            <!-- Card Header -->
            <div class="customer-header" ${action === 'schemes' ? `onclick="toggleBillDetails('${mobileNo}', this.parentElement)"` : ''}>
            <input type="hidden" class="CustCd" value="${CustCd}">
                <div class="customer-name">
                    ${escapeHtml(row.CustName || 'N/A')}
                    ${isVerified ? '<img src="images/social-media.png" alt="Verified" class="verified-badge" title="Verified Customer">' : ''}
                </div>
                <div class="customer-mobile">${mobileNo}</div>
                ${action === 'schemes' ? '<i class="expand-icon fas fa-chevron-down"></i>' : ''}
            </div>

            <!-- Default Details -->
            <div class="customer-details">
                ${renderCustomerDetails(action, row)}
            </div>

            <!-- Expandable Bill Section -->
            ${action === 'schemes' ? `
                <div class="bill-details" style="display:none;"></div>
            ` : ''}

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="action-btn call-btn" onclick="handleCall(this)">
                    <i class="fa-solid fa-phone"></i> Call
                </button>
                ${action === 'pendings' ? `
                    <button class="action-btn message-btn" onclick="handleMessage(this)">
                        <i class="fa-solid fa-message"></i> Message
                    </button>
                ` : ''}
                <button class="action-btn inactive-btn" onclick="handleInactive(this)" style="display:none;">
                    <i class="fa-solid fa-ban"></i> InActive
                </button>
                <button class="action-btn save-btn" onclick="saveCallLog(this)" style="display:none;">
                    <i class="fas fa-save"></i> Save Call
                </button>
                <button class="action-btn lead-btn" onclick="handleLead(this)" style="display:none;">
                    <i class="fa-regular fa-calendar-days"></i> Lead
                </button>
                <textarea class="remarks-textarea" placeholder="Enter remarks" style="display:none;"></textarea>
            </div>
        </div>`;
    }).join('');

    renderCards(data, action);
}

// Improved toggle function with loading state
function toggleBillDetails(mobileNo, cardElement) {
    const billDetails = cardElement.querySelector('.bill-details');
    const expandIcon = cardElement.querySelector('.expand-icon');

    if (cardElement.classList.contains('expanded')) {
        // Collapse if already expanded
        billDetails.style.display = 'none';
        cardElement.classList.remove('expanded');
        expandIcon.classList.replace('fa-chevron-up', 'fa-chevron-down');
    } else {
        // Expand and load data
        billDetails.style.display = 'block';
        cardElement.classList.add('expanded');
        expandIcon.classList.replace('fa-chevron-down', 'fa-chevron-up');

        // Only load if not already loaded
        if (!billDetails.dataset.loaded) {
            fetchBillDetails(mobileNo, billDetails);
        }
    }
}


function renderCustomerDetails(action, row) {
    let html = '';
    switch (action) {
        case 'customer':
            html += `<div class="customer-info">
                        <input type="hidden" class="CustCd" value="${escapeHtml(row.CustCd || '')}">
                        <strong>Name:</strong> ${escapeHtml(row.CustName || 'N/A')}<br>
                        <strong>Mobile:</strong> ${escapeHtml(row.CustMobileNo || 'N/A')}
                     </div>`;
            break;
        case 'schemes':
            html += `<div class="bill-info">
                        <strong>Scheme:</strong> ${escapeHtml(row.Schemes || 0)}
                     </div>`;
            break;
        case 'pendings':
            html += `<div class="pendings-info">
                        <strong>Paid:</strong> ${escapeHtml(row.PaidIns || 0)}
                        <strong>Pending:</strong> ${escapeHtml(row.PendIns || 0)}
                     </div>`;
            break;
        case 'defaulters':
            html += `<div class="visit-info">
                        <strong>Scheme:</strong> ${escapeHtml(row.smjointrnno || 0)}
                     </div>`;
            break;
        case 'active':
            html += `<div class="visit-info">
                        <strong>Name:</strong> ${escapeHtml(row.CustName || 'N/A')} <br>
                        <strong>Mobile:</strong> ${escapeHtml(row.CustMobileNo || 'N/A')}
                     </div>`;
            break;
        default:
            html += '<div class="no-data">No details available</div>';
    }
    return html;
}

// Fetch bill data (example with mock data)
async function fetchBillDetails(mobileNo, billDetailsContainer) {
    // Show loading state
    billDetailsContainer.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i> Loading bills...
        </div>
    `;

    try {
        // Call your API endpoint
        const response = await fetch(`http://localhost/billingsystem/api_keys/chit.php?action=scheme_details&mobileNo=${encodeURIComponent(mobileNo)}`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log("API Response:", data); // Debug log

        if (!data.success) {
            throw new Error(data.message || 'Failed to load bill details');
        }

        if (!data.data || data.data.length === 0) {
            billDetailsContainer.innerHTML = `
                <div class="no-bills">
                    <i class="fas fa-info-circle"></i>
                    No Schemes found for this customer
                </div>
            `;
            return;
        }

        renderBillDetails(data.data, billDetailsContainer, mobileNo);

    } catch (error) {
        console.error('Fetch Error:', error);
        billDetailsContainer.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                ${error.message || 'Failed to load bill details'}
            </div>
        `;
    }
}

function renderBillDetails(bills, container, mobileNo, customerName) {
    if (!bills || bills.length === 0) {
        container.innerHTML = '<div class="no-bills">No Scheme history found</div>';
        return;
    }

    container.innerHTML = `
        <div class="bill-history">
            <h4>
                <i class="fas fa-receipt"></i>
                Scheme History: ${escapeHtml(customerName || '')} (${mobileNo})
            </h4>
            <div class="bill-summary">
                <span>Total Schemes: ${bills.length}</span>
            </div>
            <table class="bill-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Scheme No.</th>
                        <th class="text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    ${bills.map(bill => `
                        <tr>
                            <td>${bill.sjtrndate}</td>
                            <td>${bill.smjointrnno}</td>
                            <td class="text-right">${parseFloat(bill.LastPaidDate).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    container.dataset.loaded = true;
}

// --- Search & Pagination for Customer/Lead Cards ---

let allCardsData = []; // Fill this with your fetched data
let filteredCardsData = [];
let currentPage = 1;
const cardsPerPage = 12;

// Call this after you fetch/load your data
function renderCards(cardsData, action) {
    allCardsData = cardsData;
    filteredCardsData = allCardsData.slice();
    currentPage = 1;
    renderPaginatedCards(action);
}

function renderPaginatedCards(action) {
    const container = document.getElementById('data-container');
    if (!filteredCardsData.length) {
        container.innerHTML = '<div class="no-data">No data found</div>';
        renderPaginationControls(1, 1);
        return;
    }
    const totalPages = Math.ceil(filteredCardsData.length / cardsPerPage);
    if (currentPage > totalPages) currentPage = 1;
    const startIdx = (currentPage - 1) * cardsPerPage;
    const pageData = filteredCardsData.slice(startIdx, startIdx + cardsPerPage);

    // Use your existing card rendering logic for each card
    container.innerHTML = pageData.map(row => {
        const mobileNo = escapeHtml(row.CustMobileNo || 'N/A');
        const isVerified = row.OTPVerified === 'Y';
        const CalledToday = row.CalledToday === 'Y';
        let bg_color = '';
        let text_color = '';
        if (CalledToday) {
            bg_color = 'background-color: rgb(179 181 65);'; // Light green background for called today
            text_color = 'color: black;'; // White text for better contrast
        }
        const CustCd = escapeHtml(row.CustCd || '');
        return `
        <div class="customer-card ${action === 'schemes' ? 'expandable-bill' : ''}" style="${bg_color}${text_color}">
            <div class="customer-header" ${action === 'schemes' ? `onclick="toggleBillDetails('${mobileNo}', this.parentElement)"` : ''}>
                <input type="hidden" class="CustCd" value="${CustCd}">
                <div class="customer-name" style="${text_color}">
                    ${escapeHtml(row.CustName || 'N/A')}
                    ${isVerified ? '<img src="images/social-media.png" alt="Verified" class="verified-badge" title="Verified Customer">' : ''}
                </div>
                <div class="customer-mobile" style="${text_color}">${mobileNo}</div>
                ${action === 'schemes' ? '<i class="expand-icon fas fa-chevron-down"></i>' : ''}
            </div>
            <div class="customer-details">
                ${renderCustomerDetails(action, row)}
            </div>
            ${action === 'schemes' ? `<div class="bill-details" style="display:none;"></div>` : ''}
            <div class="action-buttons">
                <button class="action-btn call-btn" onclick="handleCall(this)">
                    <i class="fa-solid fa-phone"></i> Call
                </button>
                ${action === 'pendings' ? `
                    <button class="action-btn message-btn" onclick="handleMessage(this)">
                        <i class="fa-solid fa-message"></i> Message
                    </button>
                ` : ''}
                <button class="action-btn inactive-btn" onclick="handleInactive(this)" style="display:none;">
                    <i class="fa-solid fa-ban"></i> InActive
                </button>
                <button class="action-btn save-btn" onclick="saveCallLog(this)" style="display:none;">
                    <i class="fas fa-save"></i> Save Call
                </button>
                <button class="action-btn lead-btn" onclick="handleLead(this)" style="display:none;">
                    <i class="fa-regular fa-calendar-days"></i> Lead
                </button>
                <textarea class="remarks-textarea" placeholder="Enter remarks" style="display:none;"></textarea>
            </div>
        </div>`;
    }).join('');
    renderPaginationControls(totalPages, currentPage);
}

function renderPaginationControls(totalPages, currentPage) {
    const controls = document.getElementById('pagination-controls');
    controls.innerHTML = '';
    if (totalPages <= 1) return;
    controls.innerHTML += `<button class="btn" ${currentPage === 1 ? 'disabled' : ''} onclick="gotoPage(1)">First</button>`;
    controls.innerHTML += `<button class="btn" ${currentPage === 1 ? 'disabled' : ''} onclick="gotoPage(${currentPage - 1})">Prev</button>`;
    controls.innerHTML += `<span>Page ${currentPage} of ${totalPages}</span>`;
    controls.innerHTML += `<button class="btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="gotoPage(${currentPage + 1})">Next</button>`;
    controls.innerHTML += `<button class="btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="gotoPage(${totalPages})">Last</button>`;
}

// Always pass currentAction to renderPaginatedCards
window.gotoPage = function (page) {
    currentPage = page;
    renderPaginatedCards(currentAction);
};

// Search box logic
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('search-box').addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        if (!q) {
            filteredCardsData = allCardsData.slice();
        } else {
            filteredCardsData = allCardsData.filter(card =>
                (card.CustName && card.CustName.toLowerCase().includes(q)) ||
                (card.CustMobileNo && card.CustMobileNo.toLowerCase().includes(q)) ||
                (card.CustCd && card.CustCd.toLowerCase().includes(q)) ||
                (card.schemes && card.schemes.toString().includes(q)) ||
                (card.CustDOB && card.CustDOB.toLowerCase().includes(q)) ||
                (card.visit_count && card.visit_count.toString().includes(q)) ||
                (card.last_visit && card.last_visit.toLowerCase().includes(q)) ||
                (card.Cnt && card.Cnt.toString().includes(q)) ||
                (card.purpose && card.purpose.toLowerCase().includes(q))
            );
        }
        currentPage = 1;
        renderPaginatedCards(currentAction); // Pass the current action
    });
});

// Example: After fetching data, call renderCards(data);
// renderCards(yourFetchedData);

// Initialize the dashboard
document.addEventListener('DOMContentLoaded', function () {
    // Load branch name
    fetch('api_keys/brn_name.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                document.getElementById("brn-name").innerText = data.brn_name;
            } else {
                console.error("Failed to fetch BRN name:", data.message);
                document.getElementById("brn-name").innerText = "Unknown Branch";
            }
        })
        .catch(error => {
            console.error("Error fetching BRN name:", error);
            document.getElementById("brn-name").innerText = "Error loading branch";
        });

    // Action buttons
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', async function () {
            const action = this.dataset.action;
            await loadData(action);
        });
    });

    // Lead Type Modal Events
    document.getElementById('salesLead')?.addEventListener('click', () => {
        currentLeadType = 'S';
        closeLeadTypeModal();
        showGoldWeightModal();
    });

    document.getElementById('chitLead')?.addEventListener('click', () => {
        currentLeadType = 'C';
        closeLeadTypeModal();
        showChitSchemeModal();
    });

    document.getElementById('visitLead')?.addEventListener('click', () => {
        currentLeadType = 'V';
        closeLeadTypeModal();
        showVisitPurposeModal();
    });

    document.getElementById('cancelLeadType')?.addEventListener('click', closeLeadTypeModal);

    // Calendar Events
    document.getElementById('prevMonth')?.addEventListener('click', () => {
        currentDisplayedMonth.setMonth(currentDisplayedMonth.getMonth() - 1);
        renderCalendar(currentDisplayedMonth);
    });

    document.getElementById('nextMonth')?.addEventListener('click', () => {
        currentDisplayedMonth.setMonth(currentDisplayedMonth.getMonth() + 1);
        renderCalendar(currentDisplayedMonth);
    });

    document.getElementById('cancelSchedule')?.addEventListener('click', closeCalendarModal);
    document.getElementById('confirmSchedule')?.addEventListener('click', handleSaveLead);

    // Modal close events
    document.getElementById('confirmGoldWeight')?.addEventListener('click', saveGoldDetails);
    document.getElementById('cancelGoldWeight')?.addEventListener('click', closeGoldWeightModal);
    document.getElementById('confirmChitScheme')?.addEventListener('click', saveChitDetails);
    document.getElementById('cancelChitScheme')?.addEventListener('click', closeChitSchemeModal);
    document.getElementById('confirmVisitPurpose')?.addEventListener('click', saveVisitDetails);
    document.getElementById('cancelVisitPurpose')?.addEventListener('click', closeVisitPurposeModal);

    // Close modals when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === document.getElementById('calendarModal')) {
            closeCalendarModal();
        }
        if (event.target === document.getElementById('leadTypeModal')) {
            closeLeadTypeModal();
        }
    });

    // Add this to your DOMContentLoaded event listener

    let currentLeadFilter = ''; // Track current filter

    // Handle filter selection
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            currentLeadFilter = this.dataset.leadType;

            // Update UI
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Load data
            loadLeadData(currentLeadFilter);

            // Hide filters
            leadFilters.style.display = 'none';
        });
    });

    // Enhanced loadLeadData function
    async function loadLeadData(leadType) {
        try {
            currentAction = 'lead';
            const container = document.getElementById('data-container');
            container.innerHTML = '<div class="loading">Loading leads...</div>';

            const params = new URLSearchParams();
            params.append('action', 'lead');
            if (leadType) params.append('leadType', leadType);

            const response = await fetch('api_keys/chit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Failed to load leads');
            }

            renderData('lead', data.data);

        } catch (error) {
            console.error('Lead loading error:', error);
            showStatusMessage(error.message, 'error');
            document.getElementById('data-container').innerHTML =
                `<div class="error">${error.message}</div>`;
        }
    }

    // Default action on load
    let currentAction = 'customer'; // or whatever your default is

    loadData(currentAction); // Initial data load

});

document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.action-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});