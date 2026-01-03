/**
 * Snow Alerts Public JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Lazy load images
        if ('loading' in HTMLImageElement.prototype) {
            // Native lazy loading supported
            var images = document.querySelectorAll('img[loading="lazy"]');
            images.forEach(function(img) {
                img.src = img.dataset.src || img.src;
            });
        } else {
            // Fallback for browsers that don't support lazy loading
            var lazyImages = document.querySelectorAll('img[loading="lazy"]');
            
            if ('IntersectionObserver' in window) {
                var imageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var image = entry.target;
                            image.src = image.dataset.src || image.src;
                            image.classList.remove('lazy');
                            imageObserver.unobserve(image);
                        }
                    });
                });
                
                lazyImages.forEach(function(image) {
                    imageObserver.observe(image);
                });
            } else {
                // Very old browsers - load immediately
                lazyImages.forEach(function(image) {
                    image.src = image.dataset.src || image.src;
                });
            }
        }
        
        // Smooth scroll to anchors
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            
            if (target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });
        
        // Share buttons (if implemented)
        $('.share-button').on('click', function(e) {
            e.preventDefault();
            
            var url = $(this).data('url') || window.location.href;
            var width = 600;
            var height = 400;
            var left = (screen.width - width) / 2;
            var top = (screen.height - height) / 2;
            
            window.open(
                url,
                'share',
                'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top
            );
        });
        
    });
    
})(jQuery);
