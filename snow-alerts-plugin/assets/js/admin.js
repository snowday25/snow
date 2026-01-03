/**
 * Snow Alerts Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Confirm before manual generation
        $('form').on('submit', function(e) {
            if ($(this).find('[name="snow_alerts_generate_nonce"]').length > 0) {
                if (!confirm('Generate a new article? This will check weather conditions and create a post if snow is detected.')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
        
        // Confirm before importing locations
        $('#import_locations').on('change', function() {
            if ($(this).is(':checked')) {
                if (!confirm('Import locations from JSON file? This may take a moment.')) {
                    $(this).prop('checked', false);
                }
            }
        });
        
        // Auto-save indicator
        var saveButton = $('input[type="submit"]');
        var originalText = saveButton.val();
        
        $('form').on('submit', function() {
            if ($(this).find('[name="snow_alerts_settings_nonce"]').length > 0) {
                saveButton.val('Saving...').prop('disabled', true);
                
                setTimeout(function() {
                    saveButton.val(originalText).prop('disabled', false);
                }, 2000);
            }
        });
        
        // Show/hide options based on auto-generation toggle
        $('#auto_generation_enabled').on('change', function() {
            var interval = $('#check_interval').closest('tr');
            
            if ($(this).is(':checked')) {
                interval.show();
            } else {
                interval.hide();
            }
        }).trigger('change');
        
    });
    
})(jQuery);
