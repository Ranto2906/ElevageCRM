$(document).ready(function() {
    $('#createBudgetElementForm').submit(function(event) {
        event.preventDefault();
        $.ajax({
            url: 'home/createBudgetElement',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert('Budget element created successfully.');
                location.reload();
            },
            error: function(response) {
                alert('Error creating budget element.');
            }
        });
    });

    $('#filterForm').submit(function(event) {
        event.preventDefault();
        const exerciseId = $('#exercise').val();
        window.location.href = 'home?exercise=' + exerciseId;
    });

    $('#updateTransactionsForm').submit(function(event) {
        event.preventDefault();
        $.ajax({
            url: 'home/updateTransactions',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert('Transactions updated successfully.');
                location.reload();
            },
            error: function(response) {
                alert('Error updating transactions.');
            }
        });
    });
    
    $('#exportPdf').click(function() {
        const exerciseId = $('#exercise').val();
        window.location.href = 'home/exportTransactionsPdf?exercise=' + exerciseId;
    });

    $('#exportCsv').click(function() {
        const exerciseId = $('#exercise').val();
        window.location.href = 'home/exportTransactionsCsv?exercise=' + exerciseId;
    });

    $(document).ready(function () {
        $('#createBudgetElement').click(function () {
            const category_id = $('#category_id').val();
            const type_id = $('#type_id').val();
            const description = $('#description').val();
    
            if (!category_id || !type_id || !description.trim()) {
                alert('All fields are required.');
                return;
            }
    
            $.ajax({
                url: 'home/insertBudgetElement',
                method: 'POST',
                data: { category_id, type_id, description },
                success: function () {
                    alert('Budget element created successfully.');
                    location.reload(); 
                },
                error: function () {
                    alert('Error creating budget element.');
                }
            });
        });
    
        $('#budgetElementTable').on('click', '.update-budget-element', function () {
            const row = $(this).closest('tr');
            const id = row.data('id');
            const category_id = row.find('.category-id').val();
            const type_id = row.find('.type-id').val();
            const description = row.find('.description').val();
    
            if (!category_id || !type_id || !description.trim()) {
                alert('All fields are required.');
                return;
            }
    
            $.ajax({
                url: 'home/updateBudgetElement',
                method: 'POST',
                data: { id, category_id, type_id, description },
                success: function () {
                    alert('Budget element updated successfully.');
                },
                error: function () {
                    alert('Error updating budget element.');
                }
            });
        });
    
        $('#budgetElementTable').on('click', '.delete-budget-element', function () {
            const row = $(this).closest('tr');
            const id = row.data('id');
    
            if (!confirm('Are you sure you want to delete this budget element?')) {
                return;
            }
    
            $.ajax({
                url: 'home/deleteBudgetElement',
                method: 'POST',
                data: { id },
                success: function () {
                    alert('Budget element deleted successfully.');
                    row.remove(); 
                },
                error: function () {
                    alert('Error deleting budget element.');
                }
            });
        });
    });
});