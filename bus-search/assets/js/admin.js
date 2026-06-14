jQuery(document).ready(function($) {
    const migrateBtn = $('#tb-migrate-btn');
    const progressContainer = $('#tb-migrate-progress');
    const progressFill = $('#tb-progress-fill');
    const progressText = $('#tb-progress-text');
    const resultsDiv = $('#tb-migrate-results');

    let offset = 0;
    let totalProcessed = 0;
    let totalUpdated = 0;

    migrateBtn.on('click', function() {
        migrateBtn.prop('disabled', true).text('Migration in progress...');
        progressContainer.show();
        resultsDiv.empty();
        offset = 0;
        totalProcessed = 0;
        totalUpdated = 0;
        runMigrationBatch();
    });

    function runMigrationBatch() {
        $.post(tbSearch.ajax_url, {
            action: 'tb_migrate_posts',
            nonce: tbSearch.nonce,
            offset: offset
        }, function(response) {
            if (response.success) {
                const data = response.data;
                totalProcessed += data.processed;
                totalUpdated += data.updated;
                offset = data.offset;

                const percent = data.total > 0 ? Math.round((offset / data.total) * 100) : 100;
                progressFill.css('width', percent + '%');
                progressText.text('Processed ' + totalProcessed + ' posts • ' + totalUpdated + ' updated with bus data');

                if (!data.done) {
                    // Continue with next batch
                    setTimeout(runMigrationBatch, 300);
                } else {
                    progressFill.css('width', '100%');
                    migrateBtn.text('Migration Complete!').removeClass('button-primary').addClass('button-secondary');
                    resultsDiv.html(
                        '<div style="color:#166534; background:#dcfce7; padding:12px; border-radius:4px;">' +
                        '<strong>Success!</strong> Processed ' + totalProcessed + ' posts and structured ' + totalUpdated + ' with bus details.<br>' +
                        'You can now use the [trainbasher_bus_search] shortcode on any page.' +
                        '</div>'
                    );
                }
            } else {
                resultsDiv.html('<div style="color:#b91c1c;">Error: ' + (response.data || 'Unknown error') + '</div>');
                migrateBtn.prop('disabled', false).text('Retry Migration');
            }
        }).fail(function() {
            resultsDiv.html('<div style="color:#b91c1c;">Network error. Please try again.</div>');
            migrateBtn.prop('disabled', false).text('Retry Migration');
        });
    }
});