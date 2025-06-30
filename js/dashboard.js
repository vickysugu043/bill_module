document.addEventListener("DOMContentLoaded", function () {
    // Set first button as active by default
    document.getElementById("sales-btn").classList.add("active");

    // Button active state toggle
    const buttons = document.querySelectorAll(".action-btn");
    buttons.forEach(button => {
        button.addEventListener("click", function () {
            buttons.forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");
        });
    });

    // Initial data load
    updateDashboard();

    // Auto-refresh data every 1 minute
    setInterval(updateDashboard, 60000);

    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            // Remove any existing ripples
            const ripples = this.querySelectorAll('.ripple');
            ripples.forEach(ripple => ripple.remove());

            // Create new ripple
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');

            // Position ripple
            const rect = this.getBoundingClientRect();
            ripple.style.left = `${e.clientX - rect.left}px`;
            ripple.style.top = `${e.clientY - rect.top}px`;

            this.appendChild(ripple);

            // Remove ripple after animation
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});

// Function to toggle theme when icon is clicked
function toggleTheme() {
    const html = document.documentElement;
    const icon = document.getElementById('theme-icon');

    if (html.classList.contains('light-mode')) {
        html.classList.remove('light-mode');
        localStorage.setItem('theme', 'dark');
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
    } else {
        html.classList.add('light-mode');
        localStorage.setItem('theme', 'light');
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    }
}

// On page load, apply saved theme preference
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    const html = document.documentElement;
    const icon = document.getElementById('theme-icon');

    if (savedTheme === 'light') {
        html.classList.add('light-mode');
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    } else {
        html.classList.remove('light-mode');
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
    }
});

// Function to fetch and update dashboard data
function updateDashboard() {
    $.ajax({
        url: 'config.php', // Your API endpoint
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.status === 'success') {
                // Update customer data
                $('#total-customers').text(data.data.customers.total.toLocaleString());
                $('#existing-customers').text(data.data.customers.total.toLocaleString() + " EXISTING");
                $('#new-customers').text(data.data.customers.new_today + " NEW");

                // Update today calls
                $('#today-calls').text(data.data.today_calls);

                // Update sales leads
                $('#sales-leads-total').text(data.data.sales_leads.total);
                $('#sales-leads-opening').text(data.data.sales_leads.total + " OPENING");
                $('#sales-leads-today').text(data.data.sales_leads.today + " TODAY");

                // Update chit leads
                $('#chit-leads-total').text(data.data.chit_leads.total);
                $('#chit-leads-opening').text(data.data.chit_leads.total + " OPENING");
                $('#chit-leads-today').text(data.data.chit_leads.today + " TODAY");

                // Update visits
                $('#visits-today').text(data.data.visits_today);

                // Update last updated time
                const now = new Date();
                $('#last-updated').text(now.toLocaleTimeString());
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
        }
    });
}

// Navigation functions
function sales() {
    window.location.href = "sales.php";
}

function chit() {
    window.location.href = "chit.php";
}

function leadreport() {
    window.location.href = "leadreport.php";
}

const typeJsText = document.querySelector(".dashboard-title");
const fixedText = "D";
const animatedText = typeJsText.dataset.typetext;
let counter = 0;
let isDeleting = false;

function typeJs() {
    if (!isDeleting) {
        // Typing mode
        typeJsText.innerHTML = fixedText + animatedText.substring(0, counter + 1);
        counter++;

        if (counter === animatedText.length) {
            isDeleting = true;
            setTimeout(typeJs, 1000); // pause before deleting
            return;
        }
    } else {
        // Deleting mode
        typeJsText.innerHTML = fixedText + animatedText.substring(0, counter - 1);
        counter--;

        if (counter === 0) {
            isDeleting = false;
        }
    }
}

setInterval(() => {
    typeJs();
}, 700);