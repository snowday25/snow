/**
 * Snow Alerts Plugin - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Save API Key
        $('.save-api-key').on('click', function() {
            var button = $(this);
            var apiName = button.data('api');
            var apiKey = $('#' + apiName + '-key').val();
            
            if (!apiKey) {
                alert(snowAlertsAdmin.strings.error);
                return;
            }
            
            button.addClass('loading').prop('disabled', true);
            
            $.ajax({
                url: snowAlertsAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'snow_alerts_save_api_key',
                    nonce: snowAlertsAdmin.nonce,
                    api_name: apiName,
                    api_key: apiKey
                },
                success: function(response) {
                    button.removeClass('loading').prop('disabled', false);
                    
                    if (response.success) {
                        alert(response.data.message);
                        $('#' + apiName + '-key').val('');
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    button.removeClass('loading').prop('disabled', false);
                    alert(snowAlertsAdmin.strings.error);
                }
            });
        });
        
        // Test API Key
        $('.test-api-key').on('click', function() {
            var button = $(this);
            var apiName = button.data('api');
            var apiKey = $('#' + apiName + '-key').val();
            
            if (!apiKey) {
                alert('Please enter an API key first');
                return;
            }
            
            button.addClass('loading').prop('disabled', true);
            
            $.ajax({
                url: snowAlertsAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'snow_alerts_test_api',
                    nonce: snowAlertsAdmin.nonce,
                    api_name: apiName,
                    api_key: apiKey
                },
                success: function(response) {
                    button.removeClass('loading').prop('disabled', false);
                    
                    if (response.success) {
                        alert(response.data.message);
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    button.removeClass('loading').prop('disabled', false);
                    alert(snowAlertsAdmin.strings.error);
                }
            });
        });
        
        // Generate Now Button
        $('#generate-now-btn').on('click', function() {
            var button = $(this);
            var count = parseInt($('#generate-count').val()) || 5;
            var resultsDiv = $('#generate-results');
            
            if (count < 1 || count > 20) {
                alert('Please enter a number between 1 and 20');
                return;
            }
            
            if (!confirm('Generate ' + count + ' article(s) now? This may take several minutes.')) {
                return;
            }
            
            button.addClass('loading').prop('disabled', true);
            resultsDiv.hide().removeClass('success error');
            
            $.ajax({
                url: snowAlertsAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'snow_alerts_generate_now',
                    nonce: snowAlertsAdmin.nonce,
                    count: count
                },
                success: function(response) {
                    button.removeClass('loading').prop('disabled', false);
                    
                    if (response.success) {
                        resultsDiv
                            .addClass('success')
                            .html('<strong>Success!</strong> ' + response.data.message)
                            .show();
                        
                        // Reload page after 3 seconds to show new articles
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    } else {
                        resultsDiv
                            .addClass('error')
                            .html('<strong>Error:</strong> ' + response.data.message)
                            .show();
                    }
                },
                error: function() {
                    button.removeClass('loading').prop('disabled', false);
                    resultsDiv
                        .addClass('error')
                        .html('<strong>Error:</strong> ' + snowAlertsAdmin.strings.error)
                        .show();
                },
                timeout: 300000 // 5 minute timeout for long-running generation
            });
        });
        
        // Allow Enter key to trigger password field buttons
        $('input[type="password"]').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $(this).closest('.api-config-item').find('.save-api-key').click();
            }
        });
    });
    
})(jQuery);
