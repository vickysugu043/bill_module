document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('TrnDate');
    if (dateInput) {
        const today = new Date().toISOString().slice(0, 10);
        dateInput.value = today;
    }
});

let targetInput = null;

function showPopup(inputId) {
    targetInput = inputId; // Store which input triggered the popup
    const myModal = new bootstrap.Modal(document.getElementById('customModal'));
    myModal.show();
}

function fillTrnNo(value) {
    if (targetInput) {
        document.getElementById(targetInput).value = value;
        const modalEl = document.getElementById('customModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
    }
}

function fillTrnNo(trnNo) {
    document.getElementById('TrnNo').value = trnNo;
    $("#customModal").modal('hide');

}

$(document).ready(function () {
    $('#TrnNo').focus(); // Autofocus on TrnNo when page loads
});

document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
        e.preventDefault();
        alert('Form submitted!');
    }

    if (e.key === 'F4') {
        e.preventDefault();
        const activeElement = document.activeElement;
        if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
            activeElement.select();
            if (activeElement.id) {
                showPopup();
            } else {
                alert("No ID found for this field.");
            }
        }
    }
});


function getCustomer() {
    const customerId = document.getElementById('CustomerID').value;
    if (customerId) {
        $.ajax({
            type: "POST",
            url: "/getCustomer",
            data: { customerId: customerId },
            success: function (response) {
                if (response.success) {
                    document.getElementById('CustomerName').value = response.customerName;
                    document.getElementById('CustomerAddress').value = response.customerAddress;
                } else {
                    alert("Customer not found.");
                }
            },
            error: function () {
                alert("An error occurred while fetching customer data.");
            }
        });
    } else {
        alert("Please enter a Customer ID.");
    }
}

function logout() {
    // localStorage.clear(); // optional
    window.location.href = "login.html";
}