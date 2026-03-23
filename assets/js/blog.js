// assets/js/blog.js – complete blog functionality with search, filter, and dynamic content
(function() {
    // Blog posts database (simulated – in real app would come from API/backend)
    const blogPosts = [
        // Blog posts
        {
            id: 1,
            title: "10 reasons why vocational training is the future of work",
            excerpt: "Discover how vocational training prepares you for real-world jobs faster and with better employability rates.",
            content: "Full article content here...",
            image: "../assets/images/hero1.jpg",
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
            image: "../assets/images/graduation.jpg",
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
            image: "../assets/images/nurses.jpg",
            category: "blog",
            tags: ["it", "design"],
            date: "2025-03-05",
            author: "Mlle Wanguiet Alida",
            readTime: "6 min read"
        },
        // News articles
        {
            id: 4,
            title: "VTI UARD signs partnership with 5 new health centers",
            excerpt: "Expanding internship opportunities for our health students.",
            image: "../assets/images/medical secret.jpg",
            category: "news",
            tags: ["announcements", "health"],
            date: "2025-03-12",
            author: "Communications Team",
            readTime: "3 min read"
        },
        {
            id: 5,
            title: "MINEFOP praises VTI UARD's 100% exam success rate",
            excerpt: "Official recognition during the annual inspection visit.",
            image: "../assets/images/defo-classroom.jpg",
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
            image: "../assets/images/hero2.jpg",
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
            image: "../assets/images/defo-digital.jpg",
            category: "events",
            tags: ["events", "health"],
            date: "2025-03-08",
            author: "Academic Team",
            readTime: "2 min read"
        },
        // Publications
        {
            id: 8,
            title: "Annual Pedagogical Report 2024-2025",
            excerpt: "Official report submitted to MINEFOP with key achievements.",
            image: "../assets/images/multmedia.avif",
            category: "publications",
            tags: ["publications", "announcements"],
            date: "2025-02-15",
            author: "Director's Office",
            readTime: "10 min read"
        },
        {
            id: 9,
            title: "Student Handbook 2025-2026 now available",
            excerpt: "Download the latest version with program details and policies.",
            image: "../assets/images/nutrition.png",
            category: "publications",
            tags: ["publications"],
            date: "2025-02-01",
            author: "Academic Affairs",
            readTime: "5 min read"
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

    // State
    let currentCategory = 'all';
    let currentSearch = '';
    let currentTag = '';
    let visiblePosts = 6; // Number of posts to show initially
    let filteredPosts = [];

    // Initialize page
    function init() {
        renderFeaturedPost();
        filterAndRenderPosts();
        setupEventListeners();
    }

    // Render featured post
    function renderFeaturedPost() {
        const featured = blogPosts.find(post => post.featured === true);
        if (featured && featuredContainer) {
            const date = new Date(featured.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            featuredContainer.innerHTML = `
                <div class="featured-post-card">
                    <div class="featured-post-image" style="background-image: url('${featured.image}')">
                        <span class="featured-post-badge">Featured</span>
                    </div>
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
        }
    }

    // Filter posts based on category, search, and tag
    function filterPosts() {
        filteredPosts = blogPosts.filter(post => {
            // Skip featured post from regular grid (optional)
            if (post.featured) return false;
            
            // Category filter
            if (currentCategory !== 'all' && post.category !== currentCategory) {
                return false;
            }
            
            // Search filter
            if (currentSearch) {
                const searchLower = currentSearch.toLowerCase();
                const titleMatch = post.title.toLowerCase().includes(searchLower);
                const excerptMatch = post.excerpt.toLowerCase().includes(searchLower);
                const authorMatch = post.author.toLowerCase().includes(searchLower);
                if (!titleMatch && !excerptMatch && !authorMatch) {
                    return false;
                }
            }
            
            // Tag filter
            if (currentTag && !post.tags.includes(currentTag)) {
                return false;
            }
            
            return true;
        });
        
        return filteredPosts;
    }

    // Render posts to grid
    function renderPosts(posts) {
        if (!blogGrid) return;
        
        if (posts.length === 0) {
            blogGrid.innerHTML = '';
            noResults.style.display = 'block';
            loadMoreBtn.style.display = 'none';
            return;
        }
        
        noResults.style.display = 'none';
        
        // Show only visiblePosts count
        const postsToShow = posts.slice(0, visiblePosts);
        
        let html = '';
        postsToShow.forEach(post => {
            const date = new Date(post.date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            
            // Determine badge color based on category
            let badgeColor = 'var(--blue)'; // default
            if (post.category === 'news') badgeColor = 'var(--green)';
            if (post.category === 'events') badgeColor = 'var(--red)';
            if (post.category === 'publications') badgeColor = '#9B59B6'; // purple
            
            html += `
                <div class="blog-post-card" data-id="${post.id}" data-category="${post.category}" data-tags="${post.tags.join(',')}">
                    <div class="post-card-image">
                        <img src="${post.image}" alt="${post.title}" loading="lazy">
                        <span class="post-category-badge" style="background: ${badgeColor}">${post.category}</span>
                    </div>
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
        
        // Show/hide load more button
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

    // Setup event listeners
    function setupEventListeners() {
        // Search input
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                currentSearch = e.target.value;
                resetAndRender();
            });
        }
        
        // Category buttons
        categoryButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                categoryButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentCategory = btn.getAttribute('data-category');
                
                // Update mobile select
                if (mobileFilter) {
                    mobileFilter.value = currentCategory;
                }
                
                resetAndRender();
            });
        });
        
        // Mobile filter dropdown
        if (mobileFilter) {
            mobileFilter.addEventListener('change', (e) => {
                currentCategory = e.target.value;
                
                // Update active button
                categoryButtons.forEach(btn => {
                    btn.classList.toggle('active', btn.getAttribute('data-category') === currentCategory);
                });
                
                resetAndRender();
            });
        }
        
        // Load more button
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                visiblePosts += 3;
                filterAndRenderPosts();
            });
        }
        
        // Tag clicks
        tags.forEach(tag => {
            tag.addEventListener('click', (e) => {
                e.preventDefault();
                const tagValue = tag.getAttribute('data-tag');
                
                if (currentTag === tagValue) {
                    currentTag = '';
                    tag.classList.remove('active');
                } else {
                    currentTag = tagValue;
                    
                    // Remove active from all tags
                    tags.forEach(t => t.classList.remove('active'));
                    tag.classList.add('active');
                }
                
                resetAndRender();
            });
        });
        
        // Newsletter form
        const newsletterForm = document.querySelector('.newsletter-form-large');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const email = newsletterForm.querySelector('input').value;
                // In real implementation, send to backend
                alert(`Thank you for subscribing! (${email})`);
                newsletterForm.reset();
            });
        }
        
        // Regular newsletter form in footer
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
    `;
    document.head.appendChild(style);

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', init);
})();