$(function() {
    // Import CSV AJAX pour réactions effectuées
    $('#importReactionsForm').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);
        $.ajax({
            url: window.baseUrl + '/reaction-client/importCsv',
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

    // Import CSV AJAX pour réactions de base
    $('#importReactionsBaseForm').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);
        $.ajax({
            url: window.baseUrl + '/reaction-client/importReactionsCsv',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                location.reload();
            },
            error: function(xhr) {
                $('.alert-info').remove();
                $('.container').prepend('<div class="alert alert-danger">Erreur lors de l\'import CSV Réaction.</div>');
            }
        });
    });

    // Export PDF AJAX (ouvre le PDF dans un nouvel onglet) pour réactions effectuées
    $('#exportReactionsPdfForm').on('submit', function(e) {
        e.preventDefault();
        window.open(window.baseUrl + '/reaction-client/exportPdf', '_blank');
    });

    // Export PDF AJAX (ouvre le PDF dans un nouvel onglet) pour réactions de base
    $('#exportReactionsBasePdfForm').on('submit', function(e) {
        e.preventDefault();
        window.open(window.baseUrl + '/reaction-client/exportReactionsPdf', '_blank');
    });
});

