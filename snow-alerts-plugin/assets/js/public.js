/**
 * Snow Alerts Plugin - Public/Frontend JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Placeholder for future frontend functionality
        // Could include interactive map features, real-time updates, etc.
        
        // Ensure Windy maps are responsive
        $(window).on('resize', function() {
            $('.snow-alerts-windy-map iframe').each(function() {
                var iframe = $(this);
                var container = iframe.parent();
                var containerWidth = container.width();
                
                if (containerWidth < 650) {
                    iframe.attr('width', containerWidth);
                }
            });
        }).trigger('resize');
    });
    
})(jQuery);
