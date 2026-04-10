// assets/js/testimonials-redesign.js - Independent video functionality

(function() {
    'use strict';

    // Get modal elements
    const modal = document.getElementById('videoModal');
    const videoIframe = document.getElementById('videoIframe');
    const modalTitle = document.getElementById('modalTitle');
    const modalDesc = document.getElementById('modalDesc');

    // Function to extract YouTube video ID
    function getYouTubeId(url) {
        const patterns = [
            /(?:youtube\.com\/watch\?v=)([^&]+)/,
            /(?:youtu\.be\/)([^?]+)/,
            /(?:youtube\.com\/embed\/)([^?]+)/
        ];
        
        for (let pattern of patterns) {
            const match = url.match(pattern);
            if (match) return match[1];
        }
        return null;
    }

    // Function to extract Vimeo video ID
    function getVimeoId(url) {
        const match = url.match(/(?:vimeo\.com\/)(\d+)/);
        return match ? match[1] : null;
    }

    // Function to get embed URL
    function getEmbedUrl(url) {
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            const videoId = getYouTubeId(url);
            return videoId ? `https://www.youtube.com/embed/${videoId}?autoplay=1` : null;
        } else if (url.includes('vimeo.com')) {
            const videoId = getVimeoId(url);
            return videoId ? `https://player.vimeo.com/video/${videoId}?autoplay=1` : null;
        } else if (url.match(/\.(mp4|webm|ogg)$/i)) {
            return url;
        }
        return url;
    }

    // Function to open video modal (global for onclick)
    window.openVideoFromCard = function(button) {
        const card = button.closest('.video-card');
        if (!card) return;
        
        // Get testimonial data from data attribute
        const testimonialData = card.getAttribute('data-testimonial');
        if (testimonialData) {
            const data = JSON.parse(testimonialData);
            const embedUrl = getEmbedUrl(data.url);
            
            if (embedUrl) {
                if (videoIframe) {
                    videoIframe.src = embedUrl;
                }
                if (modalTitle) modalTitle.textContent = data.name + ' - ' + data.program;
                if (modalDesc) modalDesc.textContent = data.text;
                
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            } else {
                // Fallback: open in new tab
                window.open(data.url, '_blank');
            }
        }
    };

    // Function to close modal
    window.closeVideoModal = function() {
        if (modal) {
            modal.style.display = 'none';
            if (videoIframe) {
                videoIframe.src = '';
            }
            document.body.style.overflow = '';
        }
    };

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
            closeVideoModal();
        }
    });

    // Add click listeners to all video buttons (for dynamically loaded content)
    document.addEventListener('click', function(e) {
        // Handle featured video button
        if (e.target.closest('.featured-video-btn')) {
            const btn = e.target.closest('.featured-video-btn');
            const url = btn.getAttribute('onclick');
            if (url) {
                // The onclick already handles opening in new tab
                return;
            }
        }
        
        // Handle play buttons that might not have the onclick attribute set
        const playBtn = e.target.closest('.play-button');
        if (playBtn && !playBtn.hasAttribute('onclick')) {
            const card = playBtn.closest('.video-card');
            if (card) {
                const testimonialData = card.getAttribute('data-testimonial');
                if (testimonialData) {
                    const data = JSON.parse(testimonialData);
                    const embedUrl = getEmbedUrl(data.url);
                    
                    if (embedUrl && (embedUrl.includes('youtube') || embedUrl.includes('vimeo'))) {
                        if (videoIframe) videoIframe.src = embedUrl;
                        if (modalTitle) modalTitle.textContent = data.name + ' - ' + data.program;
                        if (modalDesc) modalDesc.textContent = data.text;
                        if (modal) {
                            modal.style.display = 'flex';
                            document.body.style.overflow = 'hidden';
                        }
                    } else {
                        window.open(data.url, '_blank');
                    }
                }
            }
        }
    });

    // Add scroll animations
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.video-card, .testimonial-card, .stat-card');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight;
            
            if (elementPosition < screenPosition - 100) {
                element.style.opacity = '1';
            }
        });
    };

    // Initial check for visible elements
    setTimeout(animateOnScroll, 100);
    
    // Listen for scroll events
    window.addEventListener('scroll', animateOnScroll);
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(animateOnScroll, 250);
    });

    console.log('Testimonials page redesigned and ready!');
})();