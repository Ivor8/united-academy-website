// assets/js/main.js - updated with accurate palette & additional translations
(function() {
    'use strict';

    // ----- i18n DICTIONARY (EN/FR) with expanded content -----
    const i18n = {
        en: {
            nav_home: "Home", nav_about: "About", nav_programs: "Programs", nav_why: "Why us",
            nav_testimonials: "Testimonials", nav_blog: "Blog", nav_news: "News", nav_contact: "Contact",
            hero_title1: "Shape your future with <span class='accent'>VTI UARD</span>",
            hero_sub1: "MINEFOP accredited · 100% success · School of multiple talents",
            hero_btn1: "Read more", hero_btn2: "Contact us", hero_btn3: "Requirements",
            about_title: "About VTI UARD",
            about_desc1: "<strong>Vocational Training Institute of United Academy (VTI UARD)</strong> is a MINEFOP-accredited center (Agreement N°00300) located in Yaoundé, Simbock. Since 2024, we blend academic excellence with hands-on skills to prepare the next generation of health, IT, and management professionals.",
            about_desc2: "Our mission: deliver certified qualifications (DQP/CQP) with a 100% success rate, thanks to expert trainers and modern facilities. We are <em>the school of multiple talents</em> – rooted in Cameroonian values and global standards.",
            about_success: "success rate", about_programs: "specialized programs", about_partners: "clinic partners", about_employ: "employment rate",
            programs_title: "Our professional programs",
            prog_pharma: "Pharmacy salesperson", prog_pharma_desc: "Prepare for national exams, internships in partner pharmacies. DQP level.",
            prog_avs: "Social care assistant", prog_avs_desc: "Elderly care, nursing basics, ethics – 100% employability. BEPC/Prob level.",
            prog_infograph: "Multimedia infographics", prog_infograph_desc: "Photoshop, Illustrator, AI tools (DALL·E), web design, portfolio, freelancing.",
            prog_digital: "Digital marketing / CM", prog_digital_desc: "Community management, social media strategy, analytics, Meta/Google tools.",
            prog_lab: "Lab technician assistant", prog_lab_desc: "Chemistry, biology, microscopy, lab safety – work in medical analysis labs.",
            prog_web: "Web design & development", prog_web_desc: "HTML/CSS, JavaScript, WordPress, UX/UI – build modern websites.",
            learn_more: "Learn more →", all_programs: "View full catalog →",
            why_title: "Why choose VTI UARD?",
            why_official: "Official MINEFOP approval", why_official_desc: "Agreement N°00300 – your diploma is state-recognized.",
            why_expert: "Expert trainers", why_expert_desc: "PhD holders, medical doctors, senior engineers with field experience.",
            why_partners: "Strong partnerships", why_partners_desc: "Hôpital de Nomayos, Zenith Pro Clinic, NES DIGITAL, KL PRINT and more.",
            why_ai: "AI & digital innovation", why_ai_desc: "Training in ChatGPT, Midjourney, Canva, and professional software.",
            why_internship: "Internship placement", why_internship_desc: "3 to 5 months of internship in top clinics, pharmacies, agencies.",
            why_support: "Personalized support", why_support_desc: "Tutoring, CV workshops, job interview prep – we stay with you.",
            testimonials_title: "What our students say",
            testimonial1: "\"The pharmacy course gave me real skills. My internship at Zenith Pro Clinic turned into a job offer!\"",
            testimonial2: "\"I learned infographics and AI tools. Now I work as a freelance designer while studying.\"",
            testimonial3: "\"The trainers are amazing – they really care. I passed my DQP with honors.\"",
            blog_title: "Blog & News", search_placeholder: "Search articles...",
            all_categories: "All categories", cat_health: "Health", cat_tech: "IT & design", cat_event: "Events",
            no_results: "No matching posts.",
            contact_title: "Contact us", get_in_touch: "Get in touch",
            name_placeholder: "Your name", email_placeholder: "Email address", subject_placeholder: "Subject",
            msg_placeholder: "Message", send_msg: "Send message",
            footer_about: "Vocational Training Institute of United Academy – MINEFOP accredited. Excellence, employability, innovation.",
            quick_links: "Quick links", privacy: "Privacy policy", footer_contact: "Contact",
            newsletter: "Newsletter", newsletter_desc: "Subscribe for updates and news.",
            footer_rights: "All rights reserved.", footer_approval: "MINEFOP Agreement N°00300",
            // blog items
            blog1_title: "Pharmacy internship at Zenith Pro Clinic",
            blog1_cat: "health",
            blog2_title: "Infography students use AI for portfolios",
            blog2_cat: "tech",
            blog3_title: "Open house: discover our health labs",
            blog3_cat: "event",
            blog4_title: "Digital marketing workshop with Google",
            blog4_cat: "tech",
        },
        fr: {
            nav_home: "Accueil", nav_about: "À propos", nav_programs: "Formations", nav_why: "Pourquoi nous",
            nav_testimonials: "Témoignages", nav_blog: "Blog", nav_news: "Actualités", nav_contact: "Contact",
            hero_title1: "Préparez votre avenir avec <span class='accent'>VTI UARD</span>",
            hero_sub1: "Agréé MINEFOP · 100% de réussite · L'école des talents multiples",
            hero_btn1: "Lire plus", hero_btn2: "Contactez-nous", hero_btn3: "Prérequis",
            about_title: "À propos du VTI UARD",
            about_desc1: "<strong>Institut de Formation Professionnelle United Academy (VTI UARD)</strong> est un centre agréé MINEFOP (Agrément N°00300) situé à Yaoundé, Simbock. Depuis 2024, nous allions excellence académique et compétences pratiques pour former les futurs professionnels de la santé, de l'informatique et du management.",
            about_desc2: "Notre mission : délivrer des certifications (DQP/CQP) avec un taux de réussite de 100 %, grâce à des formateurs experts et des installations modernes. Nous sommes <em>l'école des talents multiples</em> – ancrée dans les valeurs camerounaises et les standards mondiaux.",
            about_success: "taux de réussite", about_programs: "programmes", about_partners: "partenaires", about_employ: "taux d'emploi",
            programs_title: "Nos formations professionnelles",
            prog_pharma: "Vendeur en pharmacie", prog_pharma_desc: "Préparez les examens nationaux, stages en pharmacies partenaires. Niveau DQP.",
            prog_avs: "Auxiliaire de vie sociale", prog_avs_desc: "Soins aux personnes âgées, bases infirmières, éthique – 100% d'employabilité.",
            prog_infograph: "Infographie multimédia", prog_infograph_desc: "Photoshop, Illustrator, outils IA (DALL·E), web design, portfolio, freelancing.",
            prog_digital: "Marketing digital / CM", prog_digital_desc: "Community management, stratégie sociale, analytics, outils Meta/Google.",
            prog_lab: "Assistant de laboratoire", prog_lab_desc: "Chimie, biologie, microscopie, sécurité – travaillez en laboratoire d'analyses.",
            prog_web: "Web design & développement", prog_web_desc: "HTML/CSS, JavaScript, WordPress, UX/UI – créez des sites modernes.",
            learn_more: "En savoir plus →", all_programs: "Voir tout le catalogue →",
            why_title: "Pourquoi choisir VTI UARD ?",
            why_official: "Agrément MINEFOP officiel", why_official_desc: "N°00300 – votre diplôme est reconnu par l'État.",
            why_expert: "Formateurs experts", why_expert_desc: "Doctorants, médecins, ingénieurs avec expérience terrain.",
            why_partners: "Partenariats solides", why_partners_desc: "Hôpital de Nomayos, Zenith Pro Clinic, NES DIGITAL, KL PRINT et plus.",
            why_ai: "Innovation IA & numérique", why_ai_desc: "Formation à ChatGPT, Midjourney, Canva, logiciels pros.",
            why_internship: "Placement en stage", why_internship_desc: "3 à 5 mois de stage dans des cliniques, pharmacies, agences.",
            why_support: "Accompagnement personnalisé", why_support_desc: "Tutorat, ateliers CV, simulation d'entretien – nous restons avec vous.",
            testimonials_title: "Ce que disent nos étudiants",
            testimonial1: "« La formation de vendeur en pharmacie m'a donné des compétences réelles. Mon stage à la Zenith Pro Clinic a débouché sur une offre d'emploi ! »",
            testimonial2: "« J'ai appris l'infographie et les outils IA. Maintenant je travaille en freelance tout en étudiant. »",
            testimonial3: "« Les formateurs sont incroyables – ils se soucient vraiment de nous. J'ai obtenu mon DQP avec mention. »",
            blog_title: "Blog & Actualités", search_placeholder: "Rechercher...",
            all_categories: "Toutes catégories", cat_health: "Santé", cat_tech: "Info & design", cat_event: "Événements",
            no_results: "Aucun article trouvé.",
            contact_title: "Contactez-nous", get_in_touch: "Prenons contact",
            name_placeholder: "Votre nom", email_placeholder: "Adresse email", subject_placeholder: "Sujet",
            msg_placeholder: "Message", send_msg: "Envoyer",
            footer_about: "Institut de Formation Professionnelle United Academy – agréé MINEFOP. Excellence, employabilité, innovation.",
            quick_links: "Liens rapides", privacy: "Politique de confidentialité", footer_contact: "Contact",
            newsletter: "Newsletter", newsletter_desc: "Abonnez-vous pour les mises à jour.",
            footer_rights: "Tous droits réservés.", footer_approval: "Agrément MINEFOP N°00300",
            blog1_title: "Stage pharmacie à la Zenith Pro Clinic",
            blog1_cat: "health",
            blog2_title: "Les infographistes utilisent l'IA pour leurs portfolios",
            blog2_cat: "tech",
            blog3_title: "Portes ouvertes : découvrez nos labos santé",
            blog3_cat: "event",
            blog4_title: "Atelier marketing digital avec Google",
            blog4_cat: "tech",
        }
    };

    let currentLang = 'en';

    // DOM elements
    const langEnBtn = document.getElementById('lang-en');
    const langFrBtn = document.getElementById('lang-fr');
    const mobileToggle = document.getElementById('mobileToggle');
    const navLinks = document.getElementById('navLinks');
    const dots = document.querySelectorAll('.dot');
    const slides = document.querySelectorAll('.slide');
    const blogGrid = document.getElementById('blogGrid');
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const noResults = document.getElementById('noResultsMsg');

    // helper: translate page
    function translatePage(lang) {
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (i18n[lang] && i18n[lang][key]) {
                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                    // placeholder handled separately
                } else {
                    el.innerHTML = i18n[lang][key];
                }
            }
        });
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            if (i18n[lang] && i18n[lang][key]) {
                el.placeholder = i18n[lang][key];
            }
        });
        renderBlogPosts(lang);
    }

    // blog dataset
    const blogPosts = [
        { img: 'assets/images/blog1.jpg', titleKey: 'blog1_title', catKey: 'blog1_cat' },
        { img: 'assets/images/blog2.jpg', titleKey: 'blog2_title', catKey: 'blog2_cat' },
        { img: 'assets/images/blog3.jpg', titleKey: 'blog3_title', catKey: 'blog3_cat' },
        { img: 'assets/images/blog4.jpg', titleKey: 'blog4_title', catKey: 'blog4_cat' },
    ];

    function renderBlogPosts(lang) {
        if (!blogGrid) return;
        let html = '';
        blogPosts.forEach(post => {
            const title = i18n[lang][post.titleKey] || 'post';
            const cat = i18n[lang][post.catKey] || 'general';
            const catDisplay = cat === 'health' ? (lang==='en'?'Health':'Santé') : (cat === 'tech' ? (lang==='en'?'IT & design':'Info & design') : (lang==='en'?'Event':'Événement'));
            html += `<div class="blog-card" data-category="${cat}">
                <img src="${post.img}" alt="${title}" loading="lazy" onerror="this.src='https://placehold.co/600x400?text=VTI+UARD'">
                <div class="card-content">
                    <span class="card-category">${catDisplay}</span>
                    <h4>${title}</h4>
                    <a href="#" class="card-link" data-i18n="learn_more">${i18n[lang].learn_more}</a>
                </div>
            </div>`;
        });
        blogGrid.innerHTML = html;
        filterPosts();
    }

    // filter
    function filterPosts() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const category = categoryFilter ? categoryFilter.value : 'all';
        const cards = document.querySelectorAll('.blog-card');
        let visibleCount = 0;
        cards.forEach(card => {
            const title = card.querySelector('h4')?.innerText.toLowerCase() || '';
            const cat = card.dataset.category;
            const matchesSearch = title.includes(searchTerm);
            const matchesCat = category === 'all' || cat === category;
            if (matchesSearch && matchesCat) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    // language set
    function setLanguage(lang) {
        currentLang = lang;
        translatePage(lang);
        langEnBtn.classList.toggle('active-lang', lang === 'en');
        langFrBtn.classList.toggle('active-lang', lang === 'fr');
    }

    // slider
    let slideIndex = 0;
    function nextSlide() {
        slides.forEach(s => s.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));
        slideIndex = (slideIndex + 1) % slides.length;
        slides[slideIndex]?.classList.add('active');
        dots[slideIndex]?.classList.add('active');
    }
    setInterval(nextSlide, 5000);
    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));
            slideIndex = idx;
            slides[idx].classList.add('active');
            dot.classList.add('active');
        });
    });

    // fade-up observer
    const faders = document.querySelectorAll('.fade-up');
    const appearOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const appearOnScroll = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, appearOptions);
    faders.forEach(f => appearOnScroll.observe(f));

    // mobile toggle
    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
            navLinks.classList.toggle('mobile-open');
        });
    }

    // search/filter listeners
    if (searchInput) searchInput.addEventListener('input', filterPosts);
    if (categoryFilter) categoryFilter.addEventListener('change', filterPosts);

    // initial
    setLanguage('en');
    renderBlogPosts('en');

    // lang buttons
    langEnBtn.addEventListener('click', () => setLanguage('en'));
    langFrBtn.addEventListener('click', () => setLanguage('fr'));

    // contact form prevent
    document.getElementById('quickContactForm')?.addEventListener('submit', (e) => e.preventDefault());
})();