$(function() {
    // Import CSV AJAX
    $('#importClientsForm').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);
        $.ajax({
            url: window.baseUrl + '/client/importCsv',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                // Recharge la page pour afficher les nouveaux clients et le message
                location.reload();
            },
            error: function(xhr) {
                $('#client-message').html('<div class="alert alert-danger">Erreur lors de l\'import CSV.</div>');
            }
        });
    });

    // Export PDF AJAX (ouvre le PDF dans un nouvel onglet)
    $('#exportClientsPdfForm').on('submit', function(e) {
        e.preventDefault();
        window.open(window.baseUrl + '/client/exportPdf', '_blank');
    });
});
