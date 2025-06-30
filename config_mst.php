<?php 
require_once("header.php"); 
session_start();
$updated_by = $_SESSION['empid'] ?? '';
?>

<div class="container" style="margin-top:20px;">
    <h2>Configuration Form</h2>
    <form id="configForm" method="post" action="">
        <div class="form-group">
            <label for="brn_id">Branch</label>
            <select id="brn_id" name="brn_id" class="form-control" required>
                <option value="">Select Branch</option>
            </select>
        </div>
        <div class="form-group">
            <label for="dia_disc">Dia Disc</label>
            <select id="dia_disc" name="dia_disc" class="form-control" required>
                <option value="Y">Yes</option>
                <option value="N">No</option>
            </select>
        </div>
        <div class="form-group">
            <label for="created_at">Created At</label>
            <input type="datetime-local" id="created_at" name="created_at" class="form-control" required>
        </div>
        <input type="hidden" id="updated_by" name="updated_by" value="<?php echo htmlspecialchars($updated_by); ?>">
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $.ajax({
        type: "POST",
        url: "../ajax/get_branches.php",
        success: function(response) {
            const data = JSON.parse(response);
            if(data.status === 'success') {
                const brnSelect = document.getElementById('brn_id');
                data.branches.forEach(branch => {
                    const option = document.createElement('option');
                    option.value = branch.brn_id;
                    option.textContent = branch.brn_name;
                    brnSelect.appendChild(option);
                });
            } else {
                alert('Failed to load branches: ' + data.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching branches:', error);
            alert('Error fetching branches');
        }
    });
});
</script>

<?php require_once("footer.php"); ?>
