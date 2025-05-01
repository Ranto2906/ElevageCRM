$(function() {
    // Import CSV AJAX pour actions effectuées
    $('#importActionsForm').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);
        $.ajax({
            url: window.baseUrl + '/action-client/importCsv',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                location.reload();
            },
            error: function(xhr) {
                $('.alert-info').remove();
                $('.container').prepend('<div class="alert alert-danger">Erreur lors de l\'import CSV.</div>');
            }
        });
    });

    // Import CSV AJAX pour actions de base
    $('#importActionsBaseForm').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);
        $.ajax({
            url: window.baseUrl + '/action-client/importActionsCsv',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                location.reload();
            },
            error: function(xhr) {
                $('.alert-info').remove();
                $('.container').prepend('<div class="alert alert-danger">Erreur lors de l\'import CSV Action.</div>');
            }
        });
    });

    // Export PDF AJAX (ouvre le PDF dans un nouvel onglet) pour actions effectuées
    $('#exportActionsPdfForm').on('submit', function(e) {
        e.preventDefault();
        window.open(window.baseUrl + '/action-client/exportPdf', '_blank');
    });

    // Export PDF AJAX (ouvre le PDF dans un nouvel onglet) pour actions de base
    $('#exportActionsBasePdfForm').on('submit', function(e) {
        e.preventDefault();
        window.open(window.baseUrl + '/action-client/exportActionsPdf', '_blank');
    });
});
