// assets/js/testimonials.js - Video testimonials functionality
(function() {
    'use strict';

    // Video modal elements
    const modal = document.getElementById('videoModal');
    const videoPlayer = document.getElementById('videoPlayer');
    const videoSource = document.getElementById('videoSource');
    const closeModal = document.querySelector('.close-modal');
    
    // Function to play video (direct link)
    function playVideo(videoUrl) {
        if (videoUrl) {
            window.open(videoUrl, '_blank');
        }
    }
    
    // Function to close video modal
    function closeVideoModal() {
        if (!modal || !videoPlayer || !videoSource) return;
        
        modal.style.display = 'none';
        videoPlayer.pause();
        videoSource.src = '';
        videoPlayer.load();
        document.body.style.overflow = '';
    }
    
    // Add click listeners to all video play buttons
    const playButtons = document.querySelectorAll('.play-video-btn, .video-play-btn');
    playButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const videoUrl = btn.getAttribute('data-video-url');
            playVideo(videoUrl);
        });
    });
    
    // Close modal when clicking close button
    if (closeModal && modal && modal.style.display === 'flex') {
        // Modal click handlers removed - using direct links now
    
    // FAQ accordion functionality (if present on contact page)
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        if (question) {
            question.addEventListener('click', () => {
                // Close other items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                    }
                });
                // Toggle current item
                item.classList.toggle('active');
            });
        }
    });
    
    console.log('Testimonials page enhanced with video modal functionality');
})();