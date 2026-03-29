// assets/js/blog.js – Complete blog functionality with video and image post support
(function() {
    'use strict';

    // Blog posts database with support for both images and videos
    const blogPosts = [
        // Blog posts with images
        {
            id: 1,
            title: "10 reasons why vocational training is the future of work",
            excerpt: "Discover how vocational training prepares you for real-world jobs faster and with better employability rates.",
            content: "Full article content here...",
            mediaType: "image",
            mediaUrl: "assets/images/blog/hero1.jpg",
            category: "blog",
            tags: ["health", "it", "student-success"],
            date: "2025-03-15",
            author: "Dr Diderot Fopa",
            readTime: "5 min read",
            featured: true
        },
        {
            id: 2,
            title: "My journey from student to pharmacy manager",
            excerpt: "Alumni story: how the Pharmacy Salesperson program changed my life.",
            mediaType: "image",
            mediaUrl: "assets/images/blog/graduation.jpg",
            category: "blog",
            tags: ["health", "student-success"],
            date: "2025-03-10",
            author: "Marie Claire",
            readTime: "4 min read"
        },
        {
            id: 3,
            title: "AI tools every graphic designer should know in 2025",
            excerpt: "Our infographics instructor shares the best AI tools for creatives.",
            mediaType: "image",
            mediaUrl: "assets/images/blog/nurses.jpg",
            category: "blog",
            tags: ["it", "design"],
            date: "2025-03-05",
            author: "Mlle Wanguiet Alida",
            readTime: "6 min read"
        },
        // News articles
        {
            id: 4,
            title: "UNITED ACADEMY-UARD signs partnership with 5 new health centers",
            excerpt: "Expanding internship opportunities for our health students.",
            mediaType: "image",
            mediaUrl: "assets/images/blog/medical-secret.jpg",
            category: "news",
            tags: ["announcements", "health"],
            date: "2025-03-12",
            author: "Communications Team",
            readTime: "3 min read"
        },
        {
            id: 5,
            title: "MINEFOP praises UNITED ACADEMY-UARD's 100% exam success rate",
            excerpt: "Official recognition during the annual inspection visit.",
            mediaType: "image",
            mediaUrl: "assets/images/blog/defo-classroom.jpg",
            category: "news",
            tags: ["announcements"],
            date: "2025-02-28",
            author: "Admin",
            readTime: "2 min read"
        },
        // Events
        {
            id: 6,
            title: "Open House Day 2025: Discover our labs and meet trainers",
            excerpt: "Join us on April 15 for tours, demonstrations, and career advice.",
            mediaType: "image",
            mediaUrl: "assets/images/blog/hero2.jpg",
            category: "events",
            tags: ["events"],
            date: "2025-03-20",
            author: "Events Team",
            readTime: "2 min read"
        },
        {
            id: 7,
            title: "Health careers workshop with industry experts",
            excerpt: "Panel discussion with pharmacists, lab directors, and nurses.",
            mediaType: "image",
            mediaUrl: "assets/images/blog/defo-digital.jpg",
            category: "events",
            tags: ["events", "health"],
            date: "2025-03-08",
            author: "Academic Team",
            readTime: "2 min read"
        },
        // Video posts
        {
            id: 8,
            title: "Student Success Story: From Training to Employment",
            excerpt: "Watch Marie Claire share her journey from pharmacy student to employed professional.",
            mediaType: "video",
            mediaUrl: "assets/videos/success-story-marie.mp4",
            videoPoster: "assets/images/video-thumb-marie.jpg",
            category: "video",
            tags: ["video", "student-success", "health"],
            date: "2025-03-18",
            author: "Media Team",
            readTime: "3:45 min",
            duration: "3:45"
        },
        {
            id: 9,
            title: "Infographics Showcase: Student Portfolio Highlights",
            excerpt: "See the amazing work created by our Multimedia Infographics students.",
            mediaType: "video",
            mediaUrl: "assets/videos/infographics-showcase.mp4",
            videoPoster: "assets/images/video-thumb-infographics.jpg",
            category: "video",
            tags: ["video", "it", "design"],
            date: "2025-03-14",
            author: "Media Team",
            readTime: "4:20 min",
            duration: "4:20"
        },
        {
            id: 10,
            title: "Interview with Dr. Fopa on Vocational Training in Cameroon",
            excerpt: "Our director discusses the importance of competency-based training.",
            mediaType: "video",
            mediaUrl: "assets/videos/interview-dr-fopa.mp4",
            videoPoster: "assets/images/video-thumb-interview.jpg",
            category: "video",
            tags: ["video", "announcements"],
            date: "2025-03-01",
            author: "Media Team",
            readTime: "8:15 min",
            duration: "8:15"
        }
    ];

    // DOM elements
    const searchInput = document.getElementById('blogSearchInput');
    const categoryButtons = document.querySelectorAll('.category-btn');
    const mobileFilter = document.getElementById('mobileCategoryFilter');
    const blogGrid = document.getElementById('blogPostsGrid');
    const featuredContainer = document.getElementById('featuredPost');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const noResults = document.getElementById('blogNoResults');
    const tags = document.querySelectorAll('.tag');

    // Video modal elements
    const videoModal = document.getElementById('blogVideoModal');
    const modalVideo = document.getElementById('blogModalVideo');
    const modalVideoSource = document.getElementById('blogModalVideoSource');
    const modalVideoTitle = document.getElementById('blogModalVideoTitle');
    const modalVideoDesc = document.getElementById('blogModalVideoDesc');
    const closeModalBtn = videoModal ? videoModal.querySelector('.close-modal') : null;

    // State
    let currentCategory = 'all';
    let currentSearch = '';
    let currentTag = '';
    let visiblePosts = 6;
    let filteredPosts = [];

    // Function to open video modal
    function openVideoModal(videoSrc, videoPoster, title, description) {
        if (!videoModal || !modalVideo) return;
        
        modalVideoSource.src = videoSrc;
        modalVideo.load();
        
        if (videoPoster) {
            modalVideo.poster = videoPoster;
        }
        
        if (modalVideoTitle) modalVideoTitle.textContent = title || '';
        if (modalVideoDesc) modalVideoDesc.textContent = description || '';
        
        videoModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            modalVideo.play().catch(e => console.log('Autoplay prevented:', e));
        }, 100);
    }

    // Function to close video modal
    function closeVideoModal() {
        if (!videoModal || !modalVideo) return;
        
        videoModal.style.display = 'none';
        modalVideo.pause();
        modalVideoSource.src = '';
        modalVideo.load();
        document.body.style.overflow = '';
    }

    // Initialize page
    function init() {
        renderFeaturedPost();
        filterAndRenderPosts();
        setupEventListeners();
        setupVideoModalListeners();
    }

    // Render featured post (supports both image and video)
    function renderFeaturedPost() {
        const featured = blogPosts.find(post => post.featured === true);
        if (featured && featuredContainer) {
            const date = new Date(featured.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            
            let mediaHtml = '';
            if (featured.mediaType === 'video') {
                mediaHtml = `
                    <div class="featured-post-image video-featured" style="position: relative;">
                        <video poster="${featured.videoPoster || featured.mediaUrl.replace('.mp4', '-poster.jpg')}" preload="metadata" style="width:100%; height:100%; object-fit:cover;">
                            <source src="${featured.mediaUrl}" type="video/mp4">
                        </video>
                        <span class="featured-post-badge">Featured Video</span>
                        <button class="play-featured-video" data-video-src="${featured.mediaUrl}" data-video-poster="${featured.videoPoster || ''}" data-video-title="${featured.title}" data-video-desc="${featured.excerpt}">
                            <i class="fas fa-play-circle"></i>
                        </button>
                    </div>
                `;
            } else {
                mediaHtml = `
                    <div class="featured-post-image" style="background-image: url('${featured.mediaUrl}')">
                        <span class="featured-post-badge">Featured</span>
                    </div>
                `;
            }
            
            featuredContainer.innerHTML = `
                <div class="featured-post-card">
                    ${mediaHtml}
                    <div class="featured-post-content">
                        <div class="featured-post-meta">
                            <span><i class="far fa-calendar"></i> ${date}</span>
                            <span><i class="far fa-user"></i> ${featured.author}</span>
                            <span><i class="far fa-clock"></i> ${featured.readTime}</span>
                        </div>
                        <h2>${featured.title}</h2>
                        <p class="featured-post-excerpt">${featured.excerpt}</p>
                        <div class="featured-post-footer">
                            <a href="#" class="read-more-btn">Read full story</a>
                            <span class="post-date">${featured.readTime} read</span>
                        </div>
                    </div>
                </div>
            `;
            
            // Add video play button listener for featured video
            const featuredPlayBtn = featuredContainer.querySelector('.play-featured-video');
            if (featuredPlayBtn) {
                featuredPlayBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const videoSrc = featuredPlayBtn.getAttribute('data-video-src');
                    const videoPoster = featuredPlayBtn.getAttribute('data-video-poster');
                    const videoTitle = featuredPlayBtn.getAttribute('data-video-title');
                    const videoDesc = featuredPlayBtn.getAttribute('data-video-desc');
                    openVideoModal(videoSrc, videoPoster, videoTitle, videoDesc);
                });
            }
        }
    }

    // Filter posts based on category, search, and tag
    function filterPosts() {
        filteredPosts = blogPosts.filter(post => {
            if (post.featured) return false;
            
            if (currentCategory !== 'all' && post.category !== currentCategory) {
                return false;
            }
            
            if (currentSearch) {
                const searchLower = currentSearch.toLowerCase();
                const titleMatch = post.title.toLowerCase().includes(searchLower);
                const excerptMatch = post.excerpt.toLowerCase().includes(searchLower);
                const authorMatch = post.author.toLowerCase().includes(searchLower);
                if (!titleMatch && !excerptMatch && !authorMatch) {
                    return false;
                }
            }
            
            if (currentTag && !post.tags.includes(currentTag)) {
                return false;
            }
            
            return true;
        });
        
        return filteredPosts;
    }

    // Render posts to grid (supports both images and videos)
    function renderPosts(posts) {
        if (!blogGrid) return;
        
        if (posts.length === 0) {
            blogGrid.innerHTML = '';
            noResults.style.display = 'block';
            loadMoreBtn.style.display = 'none';
            return;
        }
        
        noResults.style.display = 'none';
        
        const postsToShow = posts.slice(0, visiblePosts);
        
        let html = '';
        postsToShow.forEach(post => {
            const date = new Date(post.date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            
            let badgeColor = 'var(--blue)';
            if (post.category === 'news') badgeColor = 'var(--green)';
            if (post.category === 'events') badgeColor = 'var(--red)';
            if (post.category === 'publications') badgeColor = '#9B59B6';
            if (post.category === 'video') badgeColor = '#E67E22';
            
            let mediaHtml = '';
            if (post.mediaType === 'video') {
                mediaHtml = `
                    <div class="post-card-image video-post" style="position: relative;">
                        <video poster="${post.videoPoster || post.mediaUrl.replace('.mp4', '-poster.jpg')}" preload="metadata" style="width:100%; height:100%; object-fit:cover;">
                            <source src="${post.mediaUrl}" type="video/mp4">
                        </video>
                        <span class="post-category-badge" style="background: ${badgeColor}">${post.category}</span>
                        <button class="play-video-btn-small" data-video-src="${post.mediaUrl}" data-video-poster="${post.videoPoster || ''}" data-video-title="${post.title}" data-video-desc="${post.excerpt}">
                            <i class="fas fa-play-circle"></i>
                        </button>
                    </div>
                `;
            } else {
                mediaHtml = `
                    <div class="post-card-image">
                        <img src="${post.mediaUrl}" alt="${post.title}" loading="lazy">
                        <span class="post-category-badge" style="background: ${badgeColor}">${post.category}</span>
                    </div>
                `;
            }
            
            html += `
                <div class="blog-post-card" data-id="${post.id}" data-category="${post.category}" data-tags="${post.tags.join(',')}">
                    ${mediaHtml}
                    <div class="post-card-content">
                        <div class="post-meta">
                            <span><i class="far fa-calendar"></i> ${date}</span>
                            <span><i class="far fa-clock"></i> ${post.readTime}</span>
                        </div>
                        <h3>${post.title}</h3>
                        <p class="post-excerpt">${post.excerpt}</p>
                        <div class="post-card-footer">
                            <span class="post-author"><i class="far fa-user"></i> ${post.author}</span>
                            <a href="#" class="post-card-link">Read <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        blogGrid.innerHTML = html;
        
        // Add video play button listeners
        const videoPlayButtons = document.querySelectorAll('.play-video-btn-small');
        videoPlayButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const videoSrc = btn.getAttribute('data-video-src');
                const videoPoster = btn.getAttribute('data-video-poster');
                const videoTitle = btn.getAttribute('data-video-title');
                const videoDesc = btn.getAttribute('data-video-desc');
                openVideoModal(videoSrc, videoPoster, videoTitle, videoDesc);
            });
        });
        
        if (posts.length > visiblePosts) {
            loadMoreBtn.style.display = 'inline-block';
        } else {
            loadMoreBtn.style.display = 'none';
        }
    }

    // Filter and render
    function filterAndRenderPosts() {
        const filtered = filterPosts();
        renderPosts(filtered);
    }

    // Reset visible count and re-render
    function resetAndRender() {
        visiblePosts = 6;
        filterAndRenderPosts();
    }

    // Setup video modal listeners
    function setupVideoModalListeners() {
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', closeVideoModal);
        }
        
        if (videoModal) {
            videoModal.addEventListener('click', (e) => {
                if (e.target === videoModal) {
                    closeVideoModal();
                }
            });
        }
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && videoModal && videoModal.style.display === 'flex') {
                closeVideoModal();
            }
        });
    }

    // Setup event listeners
    function setupEventListeners() {
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                currentSearch = e.target.value;
                resetAndRender();
            });
        }
        
        categoryButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                categoryButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentCategory = btn.getAttribute('data-category');
                
                if (mobileFilter) {
                    mobileFilter.value = currentCategory;
                }
                
                resetAndRender();
            });
        });
        
        if (mobileFilter) {
            mobileFilter.addEventListener('change', (e) => {
                currentCategory = e.target.value;
                categoryButtons.forEach(btn => {
                    btn.classList.toggle('active', btn.getAttribute('data-category') === currentCategory);
                });
                resetAndRender();
            });
        }
        
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                visiblePosts += 3;
                filterAndRenderPosts();
            });
        }
        
        tags.forEach(tag => {
            tag.addEventListener('click', (e) => {
                e.preventDefault();
                const tagValue = tag.getAttribute('data-tag');
                
                if (currentTag === tagValue) {
                    currentTag = '';
                    tag.classList.remove('active');
                } else {
                    currentTag = tagValue;
                    tags.forEach(t => t.classList.remove('active'));
                    tag.classList.add('active');
                }
                
                resetAndRender();
            });
        });
        
        const newsletterForm = document.querySelector('.newsletter-form-large');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                alert('Thank you for subscribing!');
                newsletterForm.reset();
            });
        }
        
        const footerNewsletter = document.querySelector('.footer .newsletter-form');
        if (footerNewsletter) {
            footerNewsletter.addEventListener('submit', (e) => {
                e.preventDefault();
                alert('Thank you for subscribing!');
                footerNewsletter.reset();
            });
        }
    }

    // Add active class styling for tags
    const style = document.createElement('style');
    style.textContent = `
        .tag.active {
            background: var(--blue);
            color: white;
        }
        .play-video-btn-small {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--red);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 10;
        }
        .play-video-btn-small i {
            font-size: 1.5rem;
            color: white;
            margin-left: 4px;
        }
        .play-video-btn-small:hover {
            transform: translate(-50%, -50%) scale(1.1);
            background: var(--blue);
        }
        .play-featured-video {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--red);
            border: none;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.3s;
            z-index: 10;
        }
        .play-featured-video i {
            font-size: 2.5rem;
            color: white;
            margin-left: 6px;
        }
        .play-featured-video:hover {
            transform: translate(-50%, -50%) scale(1.1);
            background: var(--blue);
        }
        .video-post {
            position: relative;
        }
        .video-post video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    `;
    document.head.appendChild(style);

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', init);
})();