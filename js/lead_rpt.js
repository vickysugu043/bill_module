// Helper to format date for input[type=date]
    function toDateInputValue(date) {
        const d = new Date(date);
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        return d.toISOString().slice(0, 10);
    }

    let allLeads = [];
    let filteredLeads = [];
    let currentPage = 1;
    const leadsPerPage = 10;

    // Render leads to cards and export table (paginated)
    function renderLeads(leads) {
        const container = document.getElementById('results-container');
        const exportBody = document.getElementById('export-table-body');
        exportBody.innerHTML = '';
        if (!leads || leads.length === 0) {
            container.innerHTML = '<div class="no-data">No leads found for the selected filters</div>';
            renderPagination(0, 1);
            return;
        }

        // Pagination
        const totalPages = Math.ceil(leads.length / leadsPerPage);
        if (currentPage > totalPages) currentPage = 1;
        const startIdx = (currentPage - 1) * leadsPerPage;
        const pageLeads = leads.slice(startIdx, startIdx + leadsPerPage);

        // Cards
        let cardsHtml = '<div class="cards-container">';
        pageLeads.forEach((lead, idx) => {
            cardsHtml += `
                <div class="lead-card">
                    <div class="lead-header">
                        <div class="lead-id">Lead #${startIdx + idx + 1}</div>
                        <div class="lead-mobile">${lead.CustMobileNo || ''}</div>
                    </div>
                    <div class="lead-details">
                        <div class="detail-row">
                            <span class="detail-label">Employee Code:</span>
                            <span class="detail-value">${lead.clempid || ''}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Call Time:</span>
                            <span class="detail-value">${lead.cltime ? formatDateTime(lead.cltime) : ''}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Lead Type:</span>
                            <span class="detail-value">
                                <span class="badge ${
                                    lead.clleadtype === 'S' ? 'badge-sales' :
                                    lead.clleadtype === 'C' ? 'badge-chit' :
                                    lead.clleadtype === 'V' ? 'badge-visit' : ''
                                }">
                                    ${
                                        lead.clleadtype === 'S' ? 'Sales' :
                                        lead.clleadtype === 'C' ? 'Chit' :
                                        lead.clleadtype === 'V' ? 'Visit' : 'N/A'
                                    }
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Follow-up Time:</span>
                            <span class="detail-value">${lead.clleadtime ? formatDateTime(lead.clleadtime) : 'N/A'}</span>
                        </div>
                        <div class="detail-row remarks">
                            <span class="detail-label">Remarks:</span>
                            <span class="detail-value">${lead.clremarks ? escapeHtml(lead.clremarks) : 'No remarks'}</span>
                        </div>
                    </div>
                </div>
            `;
            // Export table row
            exportBody.innerHTML += `
                <tr>
                    <td>${escapeHtml(lead.CustName || '')}</td>
                    <td>${escapeHtml(lead.CustMobileNo || '')}</td>
                    <td>${
                        lead.clleadtype === 'S' ? 'Sales' :
                        lead.clleadtype === 'C' ? 'Chit' :
                        lead.clleadtype === 'V' ? 'Visit' : 'N/A'
                    }</td>
                    <td>${escapeHtml(lead.status || '')}</td>
                    <td>${lead.cltime ? formatDateTime(lead.cltime) : ''}</td>
                    <td>${escapeHtml(lead.clremarks || '')}</td>
                </tr>
            `;
        });
        cardsHtml += '</div>';
        container.innerHTML = cardsHtml;
        renderPagination(totalPages, currentPage);
    }

    // Pagination controls
    function renderPagination(totalPages, currentPage) {
        const controls = document.getElementById('pagination-controls');
        controls.innerHTML = '';
        if (totalPages <= 1) return;
        controls.innerHTML +=
            `<button class="btn btn-secondary" ${currentPage === 1 ? 'disabled' : ''} onclick="gotoPage(1)">First</button>`;
        controls.innerHTML +=
            `<button class="btn btn-secondary" ${currentPage === 1 ? 'disabled' : ''} onclick="gotoPage(${currentPage - 1})">Prev</button>`;
        controls.innerHTML += `<span style="color:#aaa;">Page ${currentPage} of ${totalPages}</span>`;
        controls.innerHTML +=
            `<button class="btn btn-secondary" ${currentPage === totalPages ? 'disabled' : ''} onclick="gotoPage(${currentPage + 1})">Next</button>`;
        controls.innerHTML +=
            `<button class="btn btn-secondary" ${currentPage === totalPages ? 'disabled' : ''} onclick="gotoPage(${totalPages})">Last</button>`;
    }
    window.gotoPage = function(page) {
        currentPage = page;
        renderLeads(filteredLeads);
    };

    // Escape HTML for safe rendering
    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>"']/g, function(m) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[m];
        });
    }

    // Format SQL Server datetime string or object
    function formatDateTime(dt) {
        if (!dt) return '';
        // If dt is an object (from PHP), try to parse
        if (typeof dt === 'object' && dt.date) dt = dt.date;
        // dt may be "2024-06-01 12:34:56.000"
        const d = new Date(dt.replace(' ', 'T'));
        if (isNaN(d)) return dt;
        return d.toLocaleString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Search box filter
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('search-box').addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            // Always filter from allLeads, not filteredLeads
            if (!q) {
                filteredLeads = allLeads.slice();
            } else {
                filteredLeads = allLeads.filter(lead =>
                    (lead.CustName && lead.CustName.toLowerCase().includes(q)) ||
                    (lead.clremarks && lead.clremarks.toLowerCase().includes(q)) ||
                    (lead.CustMobileNo && lead.CustMobileNo.includes(q)) ||
                    (lead.clempid && lead.clempid.toLowerCase().includes(q)) ||
                    (lead.clleadtype && lead.clleadtype.toLowerCase().includes(q)) ||
                    (lead.status && lead.status.toLowerCase().includes(q)) ||
                    (lead.cltime && formatDateTime(lead.cltime).toLowerCase().includes(q)) ||
                    (lead.clleadtime && formatDateTime(lead.clleadtime).toLowerCase().includes(q))
                );
            }
            currentPage = 1;
            renderLeads(filteredLeads);
            // Update counts
            const count = filteredLeads.length;
            document.getElementById('results-count').textContent =
                `${count} ${count === 1 ? 'lead' : 'leads'} found`;
            document.getElementById('results-count-2').textContent =
                `${count} ${count === 1 ? 'lead' : 'leads'} found`;
        });
    });

    // Fetch leads from API and update UI
    function fetchLeads() {
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;
        const mobile = document.getElementById('mobile').value;
        const url =
            `api_keys/lead_rpt.php?from_date=${encodeURIComponent(fromDate)}&to_date=${encodeURIComponent(toDate)}&mobileNo=${encodeURIComponent(mobile)}`;
        document.getElementById('results-count').textContent = 'Loading...';
        document.getElementById('results-count-2').textContent = 'Loading...';
        fetch(url)
            .then(res => res.json())
            .then(data => {
                allLeads = data.leads || [];
                filteredLeads = allLeads.slice();
                currentPage = 1;
                const count = filteredLeads.length;
                document.getElementById('results-count').textContent =
                    `${count} ${count === 1 ? 'lead' : 'leads'} found`;
                document.getElementById('results-count-2').textContent =
                    `${count} ${count === 1 ? 'lead' : 'leads'} found`;
                renderLeads(filteredLeads);
            })
            .catch(err => {
                document.getElementById('results-count').textContent = 'Error loading leads';
                document.getElementById('results-count-2').textContent = 'Error loading leads';
                document.getElementById('results-container').innerHTML =
                    '<div class="no-data">Error loading leads</div>';
            });
    }

    // Set default dates and fetch leads on page load
    document.addEventListener('DOMContentLoaded', function() {
        const fromDate = document.getElementById('from_date');
        const toDate = document.getElementById('to_date');
        const today = new Date();
        if (!fromDate.value) fromDate.value = toDateInputValue(new Date(today.getFullYear(), today.getMonth(),
            1));
        if (!toDate.value) toDate.value = toDateInputValue(today);
        fetchLeads();

        document.getElementById('filter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            fetchLeads();
        });
        document.getElementById('reset-btn').addEventListener('click', function() {
            fromDate.value = toDateInputValue(new Date(today.getFullYear(), today.getMonth(), 1));
            toDate.value = toDateInputValue(today);
            document.getElementById('mobile').value = '';
            fetchLeads();
        });
    });

    // Helper: Fill export table with all filtered leads
    function fillExportTableWithAllFiltered() {
        const exportBody = document.getElementById('export-table-body');
        exportBody.innerHTML = '';
        filteredLeads.forEach(lead => {
            exportBody.innerHTML += `
                <tr>
                    <td>${escapeHtml(lead.CustName || '')}</td>
                    <td>${escapeHtml(lead.CustMobileNo || '')}</td>
                    <td>${
                        lead.clleadtype === 'S' ? 'Sales' :
                        lead.clleadtype === 'C' ? 'Chit' :
                        lead.clleadtype === 'V' ? 'Visit' : 'N/A'
                    }</td>
                    <td>${escapeHtml(lead.status || '')}</td>
                    <td>${lead.cltime ? formatDateTime(lead.cltime) : ''}</td>
                    <td>${escapeHtml(lead.clremarks || '')}</td>
                </tr>
            `;
        });
    }

    function exportTableToExcel(tableID, filename = '') {
        fillExportTableWithAllFiltered(); // <-- Fill with all filtered records
        try {
            const table = document.getElementById(tableID);
            if (!table) throw new Error('Table not found');
            const dataRows = table.querySelectorAll('tbody tr');
            if (dataRows.length === 0) {
                alert('No data available to export');
                return;
            }
            let html = table.outerHTML;
            const blob = new Blob([html], {
                type: 'application/vnd.ms-excel'
            });
            filename = filename || 'lead_report_' + new Date().toISOString().slice(0, 10);
            if (navigator.msSaveBlob) {
                navigator.msSaveBlob(blob, filename + '.xls');
            } else {
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename + '.xls';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        } catch (error) {
            console.error('Export error:', error);
            alert('Error during export: ' + error.message);
        }
    }

    // Helper: Fill export table with current page leads
    function fillExportTableWithCurrentPage() {
        const exportBody = document.getElementById('export-table-body');
        exportBody.innerHTML = '';
        const startIdx = (currentPage - 1) * leadsPerPage;
        const pageLeads = filteredLeads.slice(startIdx, startIdx + leadsPerPage);
        pageLeads.forEach(lead => {
            exportBody.innerHTML += `
                <tr>
                    <td>${escapeHtml(lead.CustName || '')}</td>
                    <td>${escapeHtml(lead.CustMobileNo || '')}</td>
                    <td>${
                        lead.clleadtype === 'S' ? 'Sales' :
                        lead.clleadtype === 'C' ? 'Chit' :
                        lead.clleadtype === 'V' ? 'Visit' : 'N/A'
                    }</td>
                    <td>${escapeHtml(lead.status || '')}</td>
                    <td>${lead.cltime ? formatDateTime(lead.cltime) : ''}</td>
                    <td>${escapeHtml(lead.clremarks || '')}</td>
                </tr>
            `;
        });
    }

    function exportTableToPDF(tableID, filename = '') {
        fillExportTableWithAllFiltered(); // Fill with all filtered records
        const table = document.getElementById(tableID);
        if (!table) {
            alert('Table not found');
            return;
        }
        const doc = new window.jspdf.jsPDF('l', 'pt', 'a4');
        doc.text("Lead Report", 40, 40);

        // Collect table headers
        const headers = [];
        table.querySelectorAll('thead tr th').forEach(th => {
            headers.push(th.textContent.trim());
        });

        // Collect table rows
        const data = [];
        table.querySelectorAll('tbody tr').forEach(tr => {
            const row = [];
            tr.querySelectorAll('td').forEach(td => {
                row.push(td.textContent.trim());
            });
            data.push(row);
        });

        // Generate PDF table
        doc.autoTable({
            head: [headers],
            body: data,
            startY: 60,
            styles: {
                fontSize: 9
            }
        });

        filename = filename || 'lead_report_' + new Date().toISOString().slice(0, 10) + '.pdf';
        doc.save(filename);
    }