$(document).ready(function() {
    $('#filterForm').submit(function(event) {
        event.preventDefault();
        const exerciseId = $('#exercise').val();
        $.ajax({
            url: 'admin/getBudgetData',
            method: 'GET',
            data: { exercise: exerciseId },
            success: function(response) {
                renderBudgetData(response);
                bindUpdateTransactionsForm();
            },
            error: function(response) {
                alert('Error fetching budget data.');
            }
        });
    });

    function bindUpdateTransactionsForm() {
        $('#updateTransactionsForm').submit(function(event) {
            event.preventDefault();
            $.ajax({
                url: 'admin/updateTransactions',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    alert('Transactions updated successfully.');
                    const exerciseId = $('#exercise').val();
                    $.ajax({
                        url: 'admin/getBudgetData',
                        method: 'GET',
                        data: { exercise: exerciseId },
                        success: function(response) {
                            renderBudgetData(response);
                            bindUpdateTransactionsForm();
                        },
                        error: function(response) {
                            alert('Error fetching budget data.');
                        }
                    });
                },
                error: function(response) {
                    alert('Error updating transactions.');
                }
            });
        });
    }

    function renderBudgetData(budgetData) {
        let html = '<form id="updateTransactionsForm">';
        html += '<input type="hidden" name="exercise_id" value="' + budgetData.selectedExercise + '">';
        html += '<table class="table table-bordered">';
        html += '<thead><tr><th>Category</th><th>Rubric</th>';
        for (let i = 1; i <= budgetData.nb_period; i++) {
            html += '<th>Period ' + i + ' (Prevision)</th>';
            html += '<th>Period ' + i + ' (Realisation)</th>';
            html += '<th>Period ' + i + ' (Ecart)</th>';
        }
        html += '</tr></thead><tbody>';
        html += '<tr><td colspan="2">Solde depart</td>';
        for (let i = 1; i <= budgetData.nb_period; i++) {
            html += '<td colspan="3">' + budgetData.start_balance[i - 1] + '</td>';
        }
        html += '</tr>';
        budgetData.lines.forEach(line => {
            if (line.category !== 'Solde depart' && line.category !== 'Solde fin') {
                html += '<tr>';
                html += '<td>' + line.category + '</td>';
                html += '<td class="rubric-cell" data-department="' + line.department + '" data-type="' + line.type + '">' + line.description + '</td>';
                for (let i = 1; i <= budgetData.nb_period; i++) {
                    if (line['period_' + i + '_statusP'] == 0) {
                        html += '<td class="not-confirmed" style="text-align:center; color:  #ff8b1e;">En attente</td>';
                    } else {
                        html += '<td><input type="number" step="0.01" name="transactions[' + line.id + '][' + i + '][1]" value="' + Math.abs(line['period_' + i + '_prevision']) + '" class="form-control"></td>';
                    }
                    if (line['period_' + i + '_statusR'] == 0) {
                        html += '<td class="not-confirmed" style="text-align:center; color:  #ff8b1e;">En attente</td>';
                    } else {
                        html += '<td><input type="number" step="0.01" name="transactions[' + line.id + '][' + i + '][2]" value="' + Math.abs(line['period_' + i + '_realisation']) + '" class="form-control"></td>';
                    }
                    html += '<td>' + line['period_' + i + '_ecart'] + '</td>';
                }
                html += '</tr>';
            }
        });
        html += '<tr><td colspan="2">Solde fin</td>';
        for (let i = 1; i <= budgetData.nb_period; i++) {
            html += '<td>' + budgetData.lines[budgetData.lines.length - budgetData.nb_period + i - 1]['period_' + i + '_prevision'] + '</td>';
            html += '<td>' + budgetData.lines[budgetData.lines.length - budgetData.nb_period + i - 1]['period_' + i + '_realisation'] + '</td>';
            html += '<td>' + budgetData.lines[budgetData.lines.length - budgetData.nb_period + i - 1]['period_' + i + '_ecart'] + '</td>';
        }
        html += '</tr></tbody></table>';
        html += '<button type="submit" class="btn btn-primary">Update Transactions</button>';
        html += '</form>';
        $('#budgetMonitoringContent').html(html);
        bindRubricHover();
    }

    bindUpdateTransactionsForm();

    function bindRubricHover() {
        $('.rubric-cell').hover(function (event) {
            const department = $(this).data('department'); 
            const type = $(this).data('type'); 
            const tooltip = $('<div class="tooltip-box"></div>');
            tooltip.html('<strong>Department:</strong> ' + department + '<br><strong>Type:</strong> ' + type);
            $('body').append(tooltip);
            tooltip.css({
                top: event.pageY + 10 + 'px',
                left: event.pageX + 10 + 'px'
            });
            $(this).data('tooltip', tooltip);
        }, function () {
            const tooltip = $(this).data('tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
        $('.rubric-cell').mousemove(function (event) {
            const tooltip = $(this).data('tooltip');
            if (tooltip) {
                tooltip.css({
                    top: event.pageY + 10 + 'px',
                    left: event.pageX + 10 + 'px'
                });
            }
        });
    }

    $(document).on('input', 'input[type="number"]', function() {
        if ($(this).val() < 0) {
            $(this).val(Math.abs($(this).val()));
        }
    });

    $('#exportCsv').click(function() {
        const exerciseId = $('#exercise').val();
        $.ajax({
            url: 'admin/exportBudgetDataCsv',
            method: 'POST',
            data: { exercise: exerciseId },
            success: function(response) {
                const blob = new Blob([response], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', 'budget_data_exercise'+exerciseId+'.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function(response) {
                alert('Error exporting budget data.');
            }
        });
    });

    $('#exportPdf').click(function() {
        const exerciseId = $('#exercise').val();
        window.location.href = 'admin/exportBudgetDataPdf?exercise=' + exerciseId;
    });

    $(document).ready(function () {
        // Create Department
        $('#createDepartment').click(function () {
            const name = $('#department').val();
            if (name.trim() === '') {
                alert('Department name is required.');
                return;
            }
    
            $.ajax({
                url: 'admin/insertDepartment',
                method: 'POST',
                data: { name },
                success: function () {
                    alert('Department created successfully.');
                    location.reload(); // Reload the page to update the table
                },
                error: function () {
                    alert('Error creating department.');
                }
            });
        });
    
        // Update Department
        $('#departmentTable').on('click', '.update-department', function () {
            const row = $(this).closest('tr');
            const id = row.data('id');
            const name = row.find('.department-name').val();
    
            if (name.trim() === '') {
                alert('Department name is required.');
                return;
            }
    
            $.ajax({
                url: 'admin/updateDepartment',
                method: 'POST',
                data: { id, name },
                success: function () {
                    alert('Department updated successfully.');
                },
                error: function () {
                    alert('Error updating department.');
                }
            });
        });
    
        // Delete Department
        $('#departmentTable').on('click', '.delete-department', function () {
            const row = $(this).closest('tr');
            const id = row.data('id');
    
            if (!confirm('Are you sure you want to delete this department?')) {
                return;
            }
    
            $.ajax({
                url: 'admin/deleteDepartment',
                method: 'POST',
                data: { id },
                success: function () {
                    alert('Department deleted successfully.');
                    row.remove(); // Remove the row from the table
                },
                error: function () {
                    alert('Error deleting department.');
                }
            });
        });
    
        // Create Type
        $('#createType').click(function () {
            const name = $('#type').val();
            if (name.trim() === '') {
                alert('Type name is required.');
                return;
            }
    
            $.ajax({
                url: 'admin/insertType',
                method: 'POST',
                data: { name },
                success: function () {
                    alert('Type created successfully.');
                    location.reload(); // Reload the page to update the table
                },
                error: function () {
                    alert('Error creating type.');
                }
            });
        });
    
        // Update Type
        $('#typeTable').on('click', '.update-type', function () {
            const row = $(this).closest('tr');
            const id = row.data('id');
            const name = row.find('.type-name').val();
    
            if (name.trim() === '') {
                alert('Type name is required.');
                return;
            }
    
            $.ajax({
                url: 'admin/updateType',
                method: 'POST',
                data: { id, name },
                success: function () {
                    alert('Type updated successfully.');
                },
                error: function () {
                    alert('Error updating type.');
                }
            });
        });
    
        // Delete Type
        $('#typeTable').on('click', '.delete-type', function () {
            const row = $(this).closest('tr');
            const id = row.data('id');
    
            if (!confirm('Are you sure you want to delete this type?')) {
                return;
            }
    
            $.ajax({
                url: 'admin/deleteType',
                method: 'POST',
                data: { id },
                success: function () {
                    alert('Type deleted successfully.');
                    row.remove(); // Remove the row from the table
                },
                error: function () {
                    alert('Error deleting type.');
                }
            });
        });
    
        // Create Exercise
        $('#createExercise').click(function () {
            const start_date = $('#start_date').val();
            const nb_period = $('#nb_period').val();
            const start_balance = $('#start_balance').val();
    
            if (!start_date || !nb_period || !start_balance) {
                alert('All fields are required.');
                return;
            }
    
            $.ajax({
                url: 'admin/insertExercise',
                method: 'POST',
                data: { start_date, nb_period, start_balance },
                success: function () {
                    alert('Exercise created successfully.');
                    location.reload(); // Reload the page to update the table
                },
                error: function () {
                    alert('Error creating exercise.');
                }
            });
        });
    
        // Update Exercise
        $('#exerciseTable').on('click', '.update-exercise', function () {
            const row = $(this).closest('tr');
            const id = row.data('id');
            const start_date = row.find('.exercise-start-date').val();
            const nb_period = row.find('.exercise-nb-period').val();
            const start_balance = row.find('.exercise-start-balance').val();
    
            if (!start_date || !nb_period || !start_balance) {
                alert('All fields are required.');
                return;
            }
    
            $.ajax({
                url: 'admin/updateExercise',
                method: 'POST',
                data: { id, start_date, nb_period, start_balance },
                success: function () {
                    alert('Exercise updated successfully.');
                },
                error: function () {
                    alert('Error updating exercise.');
                }
            });
        });
    
        // Delete Exercise
        $('#exerciseTable').on('click', '.delete-exercise', function () {
            const row = $(this).closest('tr');
            const id = row.data('id');
    
            if (!confirm('Are you sure you want to delete this exercise?')) {
                return;
            }
    
            $.ajax({
                url: 'admin/deleteExercise',
                method: 'POST',
                data: { id },
                success: function () {
                    alert('Exercise deleted successfully.');
                    row.remove(); 
                },
                error: function () {
                    alert('Error deleting exercise.');
                }
            });
        });
    });

    $(document).ready(function () {
        $('#pendingTransactions').on('click', '.confirm-transaction', function () {
            const row = $(this).closest('tr');
            const transactionId = row.data('id');
    
            if (!confirm('Are you sure you want to confirm this transaction?')) {
                return;
            }
    
            $.ajax({
                url: 'admin/confirmTransaction',
                method: 'POST',
                data: { transaction_id: transactionId },
                success: function (response) {
                    if (response.success) {
                        alert('Transaction confirmed successfully.');
                        row.remove(); 
                    } else {
                        alert('Error confirming transaction: ' + response.message);
                    }
                },
                error: function () {
                    alert('Error confirming transaction.');
                }
            });
        });
    });

    $(document).ready(function () {
        $('#exportListsPdf').click(function () {
            window.location.href = 'admin/exportListsPdf';
        });
    });
    
});