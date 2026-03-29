// assets/js/testimonials.js - Video testimonials functionality with local video files
(function() {
    'use strict';

    // Video modal elements
    const modal = document.getElementById('videoModal');
    const modalVideoPlayer = document.getElementById('modalVideoPlayer');
    const modalVideoSource = document.getElementById('modalVideoSource');
    const modalVideoTitle = document.getElementById('modalVideoTitle');
    const modalVideoDesc = document.getElementById('modalVideoDesc');
    const closeModal = document.querySelector('.close-modal');
    
    // Function to open video modal
    function openVideoModal(videoSrc, videoPoster, videoTitle, videoDesc) {
        if (!modal || !modalVideoPlayer) return;
        
        // Set video source
        modalVideoSource.src = videoSrc;
        modalVideoPlayer.load();
        
        // Set video poster if available
        if (videoPoster) {
            modalVideoPlayer.poster = videoPoster;
        }
        
        // Set modal info
        if (modalVideoTitle && videoTitle) {
            modalVideoTitle.textContent = videoTitle;
        }
        if (modalVideoDesc && videoDesc) {
            modalVideoDesc.textContent = videoDesc;
        }
        
        // Show modal and play video
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Play video after modal is shown
        setTimeout(() => {
            modalVideoPlayer.play().catch(e => {
                console.log('Autoplay prevented:', e);
            });
        }, 100);
    }
    
    // Function to close video modal
    function closeVideoModal() {
        if (!modal || !modalVideoPlayer) return;
        
        modal.style.display = 'none';
        modalVideoPlayer.pause();
        modalVideoSource.src = '';
        modalVideoPlayer.load();
        document.body.style.overflow = '';
    }
    
    // Add click listeners to all video play buttons
    const playButtons = document.querySelectorAll('.play-video-btn, .video-play-btn');
    playButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const videoSrc = btn.getAttribute('data-video-src');
            const videoPoster = btn.getAttribute('data-video-poster');
            const videoTitle = btn.getAttribute('data-video-title');
            const videoDesc = btn.getAttribute('data-video-desc');
            
            if (videoSrc) {
                openVideoModal(videoSrc, videoPoster, videoTitle, videoDesc);
            }
        });
    });
    
    // Also make clicking on the video wrapper open video
    const videoWrappers = document.querySelectorAll('.video-wrapper');
    videoWrappers.forEach(wrapper => {
        wrapper.addEventListener('click', (e) => {
            // Don't trigger if clicking on play button (already handled)
            if (e.target.closest('.play-video-btn') || e.target.closest('.video-play-btn')) return;
            
            const playBtn = wrapper.querySelector('.play-video-btn, .video-play-btn');
            if (playBtn) {
                playBtn.click();
            }
        });
    });
    
    // Close modal when clicking close button
    if (closeModal) {
        closeModal.addEventListener('click', closeVideoModal);
    }
    
    // Close modal when clicking outside the video
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeVideoModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
            closeVideoModal();
        }
    });
    
    // FAQ accordion functionality (if present on contact page)
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        if (question) {
            question.addEventListener('click', () => {
                faqItems.forEach(otherItem => {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                    }
                });
                item.classList.toggle('active');
            });
        }
    });
    
    console.log('Testimonials page enhanced with local video playback');
})();