jQuery(document).ready(function($) {
    
    // Test connection button
    $('#test-connection').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var originalText = $button.text();
        
        $button.text('Testing...').prop('disabled', true);
        
        $.ajax({
            url: snefuru_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'snefuru_test_connection',
                nonce: snefuru_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#connection-status').html('<span class="status-success">Connected</span><br><small>Connection test successful</small>');
                    alert('Success: ' + response.data);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Connection test failed. Please try again.');
            },
            complete: function() {
                $button.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Sync now button
    $('#sync-now').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var originalText = $button.text();
        
        $button.text('Syncing...').prop('disabled', true);
        
        $.ajax({
            url: snefuru_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'snefuru_sync_now',
                nonce: snefuru_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Success: ' + response.data);
                    location.reload(); // Refresh to show updated sync time
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Sync failed. Please try again.');
            },
            complete: function() {
                $button.text(originalText).prop('disabled', false);
            }
        });
    });
    
}); 