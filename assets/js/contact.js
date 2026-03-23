// assets/js/contact.js – contact page functionality
(function() {
    // Contact form submission
    const contactForm = document.getElementById('contactForm');
    const formSuccess = document.getElementById('formSuccess');
    
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Simulate form submission
            // In real implementation, send to backend
            
            // Show success message
            contactForm.style.display = 'none';
            formSuccess.style.display = 'block';
            
            // Reset form after 5 seconds (demo)
            setTimeout(() => {
                contactForm.reset();
                contactForm.style.display = 'flex';
                formSuccess.style.display = 'none';
            }, 5000);
        });
    }
    
    // FAQ accordion
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // Close all other FAQs
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle current
            item.classList.toggle('active');
            
            // Smooth scroll into view if opening
            if (!isActive) {
                setTimeout(() => {
                    item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 100);
            }
        });
    });
    
    // Map interaction (optional)
    const mapIframe = document.querySelector('.map-container iframe');
    if (mapIframe) {
        // Add a subtle loading effect
        mapIframe.style.opacity = '0';
        mapIframe.style.transition = 'opacity 1s';
        
        mapIframe.addEventListener('load', () => {
            mapIframe.style.opacity = '1';
        });
    }
    
    // Program select enhancement
    const programSelect = document.getElementById('program');
    if (programSelect) {
        // You could populate this dynamically from program data
        console.log('Program select ready');
    }
    
    // Add active class to current page in nav
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        if (linkHref === currentPage) {
            link.classList.add('active');
        }
    });
})();