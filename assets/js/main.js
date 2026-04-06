// assets/js/main.js - updated with complete translations for all pages
(function() {
    'use strict';

    // ----- i18n DICTIONARY (EN/FR) with expanded content for all pages -----
    const i18n = {
        en: {
            // Navigation
            nav_home: "Home",
            nav_about: "About",
            nav_programs: "Programs",
            nav_testimonials: "Testimonials",
            nav_blog: "Blog",
            nav_news: "Updates",
            nav_contact: "Contact",
            
            // Hero
            hero_title1: "Shape your future with <span class='accent'>UNITED ACADEMY-UARD</span>",
            hero_sub1: "MINEFOP accredited · 100% success · School of multiple talents",
            hero_btn1: "Read more",
            hero_btn2: "Contact us",
            hero_btn3: "Requirements",
            
            // About section
            about_title: "About UNITED ACADEMY-UARD",
            about_desc1: "<strong>Vocational Training Institute UNITED ACADEMY-UARD</strong> is a MINEFOP-accredited center (Agreement N° <strong>00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC</strong>) located in Yaoundé, Simbock. Since 2024, we blend academic excellence with hands-on skills to prepare the next generation of health, IT, and management professionals.",
            about_desc2: "Our mission: deliver certified qualifications (DQP/CQP) with a 100% success rate, thanks to expert trainers and modern facilities. We are <em>the school of multiple talents</em> – rooted in Cameroonian values and global standards.",
            about_success: "success rate",
            about_programs: "specialized programs",
            about_partners: "clinic partners",
            about_employ: "employment rate",
            
            // Programs
            programs_title: "Our professional programs",
            prog_pharma: "Pharmacy Salesperson",
            prog_pharma_desc: "Prepare for national exams, internships in partner pharmacies. DQP level.",
            prog_avs: "Social Care Assistant",
            prog_avs_desc: "Elderly care, nursing basics, ethics – 100% employability. BEPC/Prob level.",
            prog_infograph: "Multimedia Infographics",
            prog_infograph_desc: "Photoshop, Illustrator, AI tools (DALL·E), web design, portfolio, freelancing.",
            prog_digital: "Digital Marketing / CM",
            prog_digital_desc: "Community management, social media strategy, analytics, Meta/Google tools.",
            prog_lab: "Lab Technician Assistant",
            prog_lab_desc: "Chemistry, biology, microscopy, lab safety – work in medical analysis labs.",
            prog_medrep: "Medical Representative",
            prog_medrep_desc: "Advanced program in pharmaceutical sales, medical terminology, and detailing techniques.",
            prog_nutrition: "Nutrition & Dietetics",
            prog_nutrition_desc: "Human nutrition, diet planning, food science, clinical nutrition, community nutrition.",
            prog_medsec: "Medical Secretary",
            prog_medsec_desc: "Medical terminology, office management, patient records, billing, communication.",
            prog_accounting: "Computerized Accounting",
            prog_accounting_desc: "Accounting principles, Sage, QuickBooks, Excel, Business Management.",
            learn_more: "Learn more →",
            all_programs: "View full catalog →",
            
            // Programs Page (detailed)
            programs_page_title: "Our Programs",
            programs_page_sub: "Choose your path to a successful career",
            health_programs: "Health & Paramedical",
            it_programs: "IT & Management",
            short_courses: "Short Courses",
            prog_pharma_full: "Pharmacy Salesperson",
            prog_pharma_desc_full: "Prepare for national exams (DQP). Modules: Biology, Anatomy, Pharmacology, Pharmacy Management, Communication, Marketing, Pharmaceutical Legislation. Internship in partner pharmacies.",
            prog_avs_full: "Social Care Assistant",
            prog_avs_desc_full: "Modules: Ethics, Health Promotion, Anatomy, Pathology, Nursing, Psychology, Nutrition, Geriatrics. Prepare for DQP/CQP. High demand in clinics and home care.",
            prog_lab_full: "Lab Technician Assistant",
            prog_lab_desc_full: "Modules: Chemistry, Biology, Microscopy, Lab Safety, Hematology, Biochemistry. Hands-on training in our equipped lab.",
            prog_medrep_full: "Medical Representative",
            prog_medrep_desc_full: "Advanced program: Pharmacology, Medical Terminology, Detailing Techniques, Sales Strategy, Ethics. Prepare for pharmaceutical industry roles.",
            prog_nutrition_full: "Nutrition & Dietetics",
            prog_nutrition_desc_full: "Modules: Human Nutrition, Diet Planning, Food Science, Clinical Nutrition, Community Nutrition. Work in hospitals, wellness centers.",
            prog_medsec_full: "Medical Secretary",
            prog_medsec_desc_full: "Modules: Medical Terminology, Office Management, Patient Records, Billing, Communication. Work in clinics, hospitals, doctors' offices.",
            prog_infograph_full: "Multimedia Infographics",
            prog_infograph_desc_full: "Master Photoshop, Illustrator, InDesign, Canva, and AI tools (Midjourney, DALL·E). Create logos, posters, social media visuals. Build your portfolio.",
            prog_digital_full: "Digital Marketing & Community Management",
            prog_digital_desc_full: "Social media strategy, content creation, analytics, Meta/Google ads, community engagement. Manage brands online.",
            prog_accounting_full: "Computerized Accounting & Management",
            prog_accounting_desc_full: "Modules: Accounting principles, Sage, QuickBooks, Excel, Business Management. Prepare for accounting assistant roles.",
            prog_secretary_full: "Secretariat & Office Management",
            prog_secretary_desc_full: "Office tools, filing, communication, bookkeeping. Prepare for administrative assistant roles.",
            short_ai_full: "AI for Creatives",
            short_ai_desc: "Master ChatGPT, Midjourney, DALL·E, Canva AI. 6 weeks intensive.",
            short_phlebotomy_full: "Phlebotomy Techniques",
            short_phlebotomy_desc: "Blood collection, patient preparation, safety. 4 weeks practical.",
            details: "Details",
            
            // Why Us
            why_title: "Why choose UNITED ACADEMY-UARD?",
            why_official: "Official MINEFOP approval",
            why_official_desc: "Agreement N° 00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC – your diploma is state-recognized.",
            why_expert: "Expert trainers",
            why_expert_desc: "PhD holders, medical doctors, senior engineers with field experience.",
            why_partners: "Strong partnerships",
            why_partners_desc: "Hôpital de Nomayos, Zenith Pro Clinic, NES DIGITAL, KL PRINT and more.",
            why_ai: "AI & digital innovation",
            why_ai_desc: "Training in ChatGPT, Midjourney, Canva, and professional software.",
            why_internship: "Internship placement",
            why_internship_desc: "3 to 5 months of internship in top clinics, pharmacies, agencies.",
            why_support: "Personalized support",
            why_support_desc: "Tutoring, CV workshops, job interview prep – we stay with you.",
            
            // Testimonials
            testimonials_title: "What our students say",
            testimonial1: "\"The pharmacy course gave me real skills. My internship at Zenith Pro Clinic turned into a job offer!\"",
            testimonial2: "\"I learned infographics and AI tools. Now I work as a freelance designer while studying.\"",
            testimonial3: "\"The trainers are amazing – they really care. I passed my DQP with honors.\"",
            testimonials_page_title: "Student Testimonials",
            testimonials_page_sub: "Real stories from our graduates",
            video_testimonials_title: "Video Testimonials",
            video_testimonials_sub: "Watch our students share their experiences",
            video1_title: "Marie Claire - Pharmacy Salesperson",
            video1_desc: "\"How I got hired immediately after my internship at Zenith Pro Clinic\"",
            video2_title: "Jean Paul - Multimedia Infographics",
            video2_desc: "\"From student to freelance designer using AI tools learned at UNITED ACADEMY-UARD\"",
            video3_title: "Stéphanie A. - Social Care Assistant",
            video3_desc: "\"The caring trainers and practical training that prepared me for my career\"",
            video4_title: "Hervé N. - Digital Marketing",
            video4_desc: "\"Managing real social media accounts during my training gave me the edge\"",
            featured_testimonial_text: "\"UNITED ACADEMY-UARD didn't just train me – they transformed my life. The hands-on approach, the caring trainers, and the internship opportunity at a top clinic gave me the confidence and skills to start my career immediately after graduation. I'm now working as a pharmacy assistant and planning to open my own pharmacy one day.\"",
            more_stories: "More success stories",
            stat_success: "Success rate",
            stat_graduates: "Graduates",
            stat_employment: "Employment rate",
            stat_partners: "Partner clinics",
            cta_testimonials: "Ready to write your own success story?",
            cta_testimonials_sub: "Join UNITED ACADEMY-UARD and become part of our growing family.",
            
            // About Page
            about_page_title: "About UNITED ACADEMY-UARD",
            about_page_sub: "Excellence, employability, innovation – since 2024",
            our_story: "Our story",
            story_p1: "The <strong>Vocational Training Institute UNITED ACADEMY-UARD</strong> was founded with a clear vision: to bridge the gap between academic knowledge and professional reality in Cameroon.",
            story_p2: "Officially accredited on <strong>June 28, 2024</strong> by the Ministry of Employment and Vocational Training (MINEFOP) under agreement <strong>N° 00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC</strong>, our institute started with pioneering programs in health, IT, and management. In just one year, we achieved a 100% success rate at national exams and placed our graduates in top clinics, pharmacies and digital agencies.",
            story_p3: "Today, UNITED ACADEMY-UARD is known as <em>\"the school of multiple talents\"</em> – a place where students gain both technical skills and human values. We continue to expand our programs, facilities and partnerships to serve Cameroonian youth.",
            mission_title: "Our mission",
            mission_text: "To train qualified, competent and ethical professionals who meet the needs of the Cameroonian job market and can contribute to national development.",
            vision_title: "Our vision",
            vision_text: "To become a center of excellence in vocational training in Central Africa, recognized for innovation, inclusion and high employability rates.",
            values_title: "Our values",
            values_text: "Excellence, integrity, practical innovation, respect, and commitment to each student's success.",
            // Council Section Translations
            council_title: "Le Conseil d'Établissement",
            council_intro: "Le Conseil d'Établissement du Vocational Training Institute of United Academy (VTI-UARD) assures the strategic direction, pedagogical governance, and institutional oversight of the establishment, in strict compliance with the missions and requirements of the Ministry of Employment and Vocational Training (MINEFOP).",
            council_vision: "In accordance with the national vocational training policy, VTI-UARD aligns its actions with a competency-based training approach, aiming for professional qualification, employability, and sustainable socio-professional integration of learners.",
            council_competence_title: "Competency-Based Training and Professional Frameworks",
            council_competence_text: "The Conseil d'Établissement ensures that all programs offered by VTI-UARD are designed and implemented based on:",
            council_competence_1: "professional and competency frameworks, in line with labor market realities",
            council_competence_2: "programs structured around professional situations, practical know-how, and measurable competencies",
            council_competence_3: "a balanced articulation between theoretical instruction, practical work, professional internships, and workplace immersion",
            council_competence_conclusion: "This approach enables learners to develop skills directly usable in their future professional environment, in accordance with MINEFOP guidelines.",
            council_qualification_title: "Professional Qualification and Certification",
            council_qualification_text: "The Conseil d'Établissement places particular importance on professional qualification, understood as the formal recognition of competencies acquired at the end of a training pathway.",
            council_qualification_assures: "In this regard, it ensures:",
            council_qualification_1: "the conformity of training pathways to recognized professional qualification levels",
            council_qualification_2: "the implementation of objective, standardized, and transparent evaluation methods aligned with MINEFOP requirements",
            council_qualification_3: "the credibility and professional value of the certificates and attestations issued",
            council_insertion_title: "Socio-Professional Integration and Partnerships",
            council_insertion_text: "In line with the professional integration approach promoted by MINEFOP, the Conseil d'Établissement encourages:",
            council_insertion_1: "the development of solid partnerships with companies, professional structures, and public or private institutions",
            council_insertion_2: "the organization and rigorous follow-up of academic and professional internships, integrated as a mandatory component of training pathways",
            council_insertion_3: "support for learners toward employment, self-employment, and entrepreneurship",
            council_insertion_conclusion: "VTI-UARD thus positions itself as a key player in the fight against unemployment, training a qualified workforce that is immediately operational and adapted to the needs of the socio-economic landscape.",
            council_governance_title: "Governance, Discipline, and Quality Assurance",
            council_governance_text: "The Conseil d'Établissement ensures:",
            council_governance_1: "strict application of regulatory texts governing private vocational training establishments",
            council_governance_2: "the implementation of quality assurance and continuous improvement mechanisms",
            council_governance_3: "respect for discipline, ethics, responsibility, and professionalism by all stakeholders of the institution",
            council_governance_conclusion: "These requirements constitute a guarantee of credibility with MINEFOP, professional partners, and training beneficiaries.",
            council_engagement_title: "Institutional Commitment",
            council_engagement_text: "The Conseil d'Établissement reaffirms its determination to make the Vocational Training Institute of United Academy (VTI-UARD) a reference institution in competency-based vocational training, effectively contributing to human capital development and socio-economic growth.",
            council_engagement_invitation: "It invites learners, trainers, administrative staff, and partners to work collectively, in a spirit of rigor and excellence, for the success and recognition of VTI-UARD.",
            infra_title: "Our infrastructure",
            infra_lab: "Fully equipped paramedical lab with microscopes, centrifuges, stethoscopes, and simulation materials.",
            infra_computer: "Modern computer stations, video projectors, and creative software suite.",
            infra_class: "Spacious classrooms with modern teaching aids and multimedia equipment.",
            infra_library: "Quiet study space with reference books and digital resources.",
            partners_title: "Our trusted partners",
            partners_more: "... and many more clinics, pharmacies and agencies.",
            cta_about: "Ready to start your journey?",
            cta_about_sub: "Join UNITED ACADEMY-UARD and become part of the school of multiple talents.",
            
            // Contact Page
            contact_page_title: "Contact Us",
            contact_page_sub: "We're here to help and answer any questions",
            contact_intro: "Have questions about our programs, admissions, or partnerships? Reach out to us – we're happy to help.",
            address: "Address",
            phone: "Phone",
            email: "Email",
            hours: "Office Hours",
            connect_with_us: "Connect with us",
            send_message: "Send us a message",
            form_intro: "We'll get back to you within 24 hours.",
            full_name: "Full Name *",
            email_address: "Email Address *",
            phone_number: "Phone Number",
            program_interest: "Program of Interest",
            select_option: "Select a program",
            opt_pharmacy: "Pharmacy Salesperson",
            opt_avs: "Social Care Assistant",
            opt_infography: "Multimedia Infographics",
            opt_digital: "Digital Marketing / CM",
            opt_lab: "Lab Technician Assistant",
            opt_medrep: "Medical Representative",
            opt_nutrition: "Nutrition & Dietetics",
            opt_medsec: "Medical Secretary",
            opt_accounting: "Computerized Accounting",
            opt_other: "Other",
            subject: "Subject",
            message: "Message *",
            consent_text: "I agree to the privacy policy and consent to being contacted.",
            send_message_btn: "Send Message",
            thank_you: "Thank you!",
            message_sent: "Your message has been sent. We'll contact you soon.",
            find_us: "Find us",
            faq_title: "Frequently Asked Questions",
            faq1_q: "How do I apply?",
            faq1_a: "You can apply by visiting our campus in Simbock with the required documents: CNI photocopy, diploma, 2 photos, and registration fee. You can also start the process by contacting us by phone or email.",
            faq2_q: "What are the tuition fees?",
            faq2_a: "Please contact us directly for detailed fee information. We offer competitive pricing and flexible installment plans. Registration fee is 25,000 FCFA for all programs.",
            faq3_q: "Do you offer scholarships?",
            faq3_a: "We have limited scholarships for outstanding students from disadvantaged backgrounds. Contact our office for more information.",
            faq4_q: "Are internships guaranteed?",
            faq4_a: "Yes, every student completes a 3-5 month internship in one of our partner clinics, pharmacies, or agencies. We help place you.",
            faq5_q: "What diplomas do you offer?",
            faq5_a: "We prepare students for DQP (Diplôme de Qualification Professionnelle) and CQP (Certificat de Qualification Professionnelle), recognized nationally by MINEFOP under agreement N° 00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC.",
            faq6_q: "Can I visit the campus?",
            faq6_a: "Absolutely! We welcome visitors during office hours. You can tour our labs, classrooms, and meet our team.",
            cta_contact_page: "Ready to start your journey?",
            cta_contact_sub: "Take the first step toward a successful career.",
            browse_programs: "Browse Programs",
            
            // Blog
            blog_title: "Blog & News",
            search_placeholder: "Search articles...",
            all_categories: "All categories",
            cat_health: "Health",
            cat_tech: "IT & design",
            cat_event: "Events",
            no_results: "No matching posts.",
            blog_page_title: "Blog & News",
            blog_page_sub: "Stories, updates, and insights from UNITED ACADEMY-UARD",
            blog_search_placeholder: "Search articles, news, events...",
            all_items: "All",
            blog_posts: "Blog Posts",
            news_articles: "News",
            events: "Events",
            publications: "Publications",
            latest_updates: "Latest updates",
            no_posts_found: "No posts found",
            try_adjusting: "Try adjusting your search or filter",
            load_more: "Load more articles",
            subscribe_blog: "Subscribe to our newsletter",
            subscribe_blog_desc: "Get the latest news and updates delivered to your inbox",
            subscribe: "Subscribe",
            popular_tags: "Popular tags:",
            
            // Admission
            admission_title: "Admission requirements",
            admission_docs: "Documents required",
            admission_doc1: "Photocopy of CNI",
            admission_doc2: "Relevant diploma (BEPC/Probatoire/BAC)",
            admission_doc3: "2 passport photos (4x4)",
            admission_doc4: "One ream of A4 80g paper",
            admission_dates: "Key dates",
            admission_date1: "Registration opens: September 2025",
            admission_date2: "Administrative rentrée: October 6, 2025",
            admission_date3: "Classes begin: October 15, 2025",
            admission_fees: "Registration fee",
            fee_registration: "Registration: 25,000 FCFA (all programs)",
            fee_installment: "Flexible installment plans available",
            fee_contact: "Contact us for tuition details",
            cta_programs: "Find your program today",
            cta_programs_sub: "Contact us on WhatsApp for more information or to schedule a visit.",
            cta_contact: "Contact us today",
            
            // Footer
            footer_about: "Vocational Training Institute UNITED ACADEMY-UARD – MINEFOP accredited. Excellence, employability, innovation.",
            quick_links: "Quick links",
            privacy: "Privacy policy",
            footer_contact: "Contact",
            newsletter: "Newsletter",
            newsletter_desc: "Subscribe for updates and news.",
            footer_rights: "All rights reserved.",
            footer_approval: "MINEFOP Agreement N° 00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC",
            
            // Blog posts
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
            // Navigation
            nav_home: "Accueil",
            nav_about: "À propos",
            nav_programs: "Formations",
            nav_testimonials: "Témoignages",
            nav_blog: "Blog",
            nav_news: "Actualités",
            nav_contact: "Contact",
            
            // Hero
            hero_title1: "Préparez votre avenir avec <span class='accent'>UNITED ACADEMY-UARD</span>",
            hero_sub1: "Agréé MINEFOP · 100% de réussite · L'école des talents multiples",
            hero_btn1: "Lire plus",
            hero_btn2: "Contactez-nous",
            hero_btn3: "Prérequis",
            
            // About section
            about_title: "À propos de UNITED ACADEMY-UARD",
            about_desc1: "<strong>Institut de Formation Professionnelle UNITED ACADEMY-UARD</strong> est un centre agréé MINEFOP (Agrément N° <strong>00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC</strong>) situé à Yaoundé, Simbock. Depuis 2024, nous allions excellence académique et compétences pratiques pour former les futurs professionnels de la santé, de l'informatique et du management.",
            about_desc2: "Notre mission : délivrer des certifications (DQP/CQP) avec un taux de réussite de 100 %, grâce à des formateurs experts et des installations modernes. Nous sommes <em>l'école des talents multiples</em> – ancrée dans les valeurs camerounaises et les standards mondiaux.",
            about_success: "taux de réussite",
            about_programs: "programmes",
            about_partners: "partenaires",
            about_employ: "taux d'emploi",
            
            // Programs
            programs_title: "Nos formations professionnelles",
            prog_pharma: "Vendeur en pharmacie",
            prog_pharma_desc: "Préparez les examens nationaux, stages en pharmacies partenaires. Niveau DQP.",
            prog_avs: "Auxiliaire de vie sociale",
            prog_avs_desc: "Soins aux personnes âgées, bases infirmières, éthique – 100% d'employabilité.",
            prog_infograph: "Infographie multimédia",
            prog_infograph_desc: "Photoshop, Illustrator, outils IA (DALL·E), web design, portfolio, freelancing.",
            prog_digital: "Marketing digital / CM",
            prog_digital_desc: "Community management, stratégie sociale, analytics, outils Meta/Google.",
            prog_lab: "Assistant de laboratoire",
            prog_lab_desc: "Chimie, biologie, microscopie, sécurité – travaillez en laboratoire d'analyses.",
            prog_medrep: "Représentant médical",
            prog_medrep_desc: "Programme avancé en vente pharmaceutique, terminologie médicale et techniques de détailing.",
            prog_nutrition: "Nutrition & Diététique",
            prog_nutrition_desc: "Nutrition humaine, planification diététique, science alimentaire, nutrition clinique.",
            prog_medsec: "Secrétaire médicale",
            prog_medsec_desc: "Terminologie médicale, gestion de bureau, dossiers patients, facturation, communication.",
            prog_accounting: "Comptabilité informatisée",
            prog_accounting_desc: "Principes comptables, Sage, QuickBooks, Excel, Gestion d'entreprise.",
            learn_more: "En savoir plus →",
            all_programs: "Voir tout le catalogue →",
            
            // Programs Page (detailed)
            programs_page_title: "Nos Formations",
            programs_page_sub: "Choisissez votre voie vers une carrière réussie",
            health_programs: "Santé & Paramedical",
            it_programs: "Informatique & Management",
            short_courses: "Cours courts",
            prog_pharma_full: "Vendeur en pharmacie",
            prog_pharma_desc_full: "Préparez les examens nationaux (DQP). Modules: Biologie, Anatomie, Pharmacologie, Gestion de pharmacie, Communication, Marketing, Législation pharmaceutique. Stage en pharmacies partenaires.",
            prog_avs_full: "Auxiliaire de vie sociale",
            prog_avs_desc_full: "Modules: Éthique, Promotion de la santé, Anatomie, Pathologie, Soins infirmiers, Psychologie, Nutrition, Gériatrie. Préparez le DQP/CQP. Forte demande en cliniques et soins à domicile.",
            prog_lab_full: "Assistant de laboratoire",
            prog_lab_desc_full: "Modules: Chimie, Biologie, Microscopie, Sécurité de laboratoire, Hématologie, Biochimie. Formation pratique dans notre laboratoire équipé.",
            prog_medrep_full: "Représentant médical",
            prog_medrep_desc_full: "Programme avancé: Pharmacologie, Terminologie médicale, Techniques de détailing, Stratégie de vente, Éthique. Préparez-vous pour l'industrie pharmaceutique.",
            prog_nutrition_full: "Nutrition & Diététique",
            prog_nutrition_desc_full: "Modules: Nutrition humaine, Planification diététique, Science alimentaire, Nutrition clinique, Nutrition communautaire. Travaillez en hôpitaux, centres de bien-être.",
            prog_medsec_full: "Secrétaire médicale",
            prog_medsec_desc_full: "Modules: Terminologie médicale, Gestion de bureau, Dossiers patients, Facturation, Communication. Travaillez en cliniques, hôpitaux, cabinets médicaux.",
            prog_infograph_full: "Infographie multimédia",
            prog_infograph_desc_full: "Maîtrisez Photoshop, Illustrator, InDesign, Canva, et les outils IA (Midjourney, DALL·E). Créez logos, affiches, visuels pour réseaux sociaux. Construisez votre portfolio.",
            prog_digital_full: "Marketing digital & Community Management",
            prog_digital_desc_full: "Stratégie réseaux sociaux, création de contenu, analytics, publicités Meta/Google, engagement communautaire. Gérez des marques en ligne.",
            prog_accounting_full: "Comptabilité informatisée & Gestion",
            prog_accounting_desc_full: "Modules: Principes comptables, Sage, QuickBooks, Excel, Gestion d'entreprise. Préparez-vous aux postes d'assistant comptable.",
            prog_secretary_full: "Secrétariat & Gestion de bureau",
            prog_secretary_desc_full: "Outils bureautiques, classement, communication, comptabilité. Préparez-vous aux postes d'assistant administratif.",
            short_ai_full: "IA pour créatifs",
            short_ai_desc: "Maîtrisez ChatGPT, Midjourney, DALL·E, Canva AI. 6 semaines intensives.",
            short_phlebotomy_full: "Techniques de phlébotomie",
            short_phlebotomy_desc: "Prélèvement sanguin, préparation du patient, sécurité. 4 semaines pratiques.",
            details: "Détails",
            
            // Why Us
            why_title: "Pourquoi choisir UNITED ACADEMY-UARD ?",
            why_official: "Agrément MINEFOP officiel",
            why_official_desc: "N° 00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC – votre diplôme est reconnu par l'État.",
            why_expert: "Formateurs experts",
            why_expert_desc: "Doctorants, médecins, ingénieurs avec expérience terrain.",
            why_partners: "Partenariats solides",
            why_partners_desc: "Hôpital de Nomayos, Zenith Pro Clinic, NES DIGITAL, KL PRINT et plus.",
            why_ai: "Innovation IA & numérique",
            why_ai_desc: "Formation à ChatGPT, Midjourney, Canva, logiciels pros.",
            why_internship: "Placement en stage",
            why_internship_desc: "3 à 5 mois de stage dans des cliniques, pharmacies, agences.",
            why_support: "Accompagnement personnalisé",
            why_support_desc: "Tutorat, ateliers CV, simulation d'entretien – nous restons avec vous.",
            
            // Testimonials
            testimonials_title: "Ce que disent nos étudiants",
            testimonial1: "« La formation de vendeur en pharmacie m'a donné des compétences réelles. Mon stage à la Zenith Pro Clinic a débouché sur une offre d'emploi ! »",
            testimonial2: "« J'ai appris l'infographie et les outils IA. Maintenant je travaille en freelance tout en étudiant. »",
            testimonial3: "« Les formateurs sont incroyables – ils se soucient vraiment de nous. J'ai obtenu mon DQP avec mention. »",
            testimonials_page_title: "Témoignages étudiants",
            testimonials_page_sub: "Des histoires vraies de nos diplômés",
            video_testimonials_title: "Témoignages vidéo",
            video_testimonials_sub: "Regardez nos étudiants partager leurs expériences",
            video1_title: "Marie Claire - Vendeuse en pharmacie",
            video1_desc: "\"Comment j'ai été embauchée immédiatement après mon stage à Zenith Pro Clinic\"",
            video2_title: "Jean Paul - Infographie multimédia",
            video2_desc: "\"D'étudiant à designer freelance grâce aux outils IA appris à UNITED ACADEMY-UARD\"",
            video3_title: "Stéphanie A. - Auxiliaire de vie sociale",
            video3_desc: "\"Des formateurs attentionnés et une formation pratique qui m'a préparée à ma carrière\"",
            video4_title: "Hervé N. - Marketing digital",
            video4_desc: "\"Gérer de vrais comptes de médias sociaux pendant ma formation m'a donné un avantage\"",
            featured_testimonial_text: "\"UNITED ACADEMY-UARD ne m'a pas seulement formé – ils ont transformé ma vie. L'approche pratique, les formateurs attentionnés, et le stage dans une clinique de premier plan m'ont donné la confiance et les compétences pour commencer ma carrière immédiatement après l'obtention de mon diplôme. Je travaille maintenant comme assistante en pharmacie et je prévois d'ouvrir ma propre pharmacie un jour.\"",
            more_stories: "Plus d'histoires de réussite",
            stat_success: "Taux de réussite",
            stat_graduates: "Diplômés",
            stat_employment: "Taux d'emploi",
            stat_partners: "Cliniques partenaires",
            cta_testimonials: "Prêt à écrire votre propre histoire de réussite ?",
            cta_testimonials_sub: "Rejoignez UNITED ACADEMY-UARD et faites partie de notre famille grandissante.",
            
            // About Page
            about_page_title: "À propos de UNITED ACADEMY-UARD",
            about_page_sub: "Excellence, employabilité, innovation – depuis 2024",
            our_story: "Notre histoire",
            story_p1: "L'<strong>Institut de Formation Professionnelle UNITED ACADEMY-UARD</strong> a été fondé avec une vision claire : combler le fossé entre les connaissances académiques et la réalité professionnelle au Cameroun.",
            story_p2: "Officiellement agréé le <strong>28 juin 2024</strong> par le Ministère de l'Emploi et de la Formation Professionnelle (MINEFOP) sous l'agrément <strong>N° 00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC</strong>, notre institut a débuté avec des programmes pionniers dans les domaines de la santé, de l'informatique et du management. En seulement un an, nous avons atteint un taux de réussite de 100% aux examens nationaux et placé nos diplômés dans des cliniques, pharmacies et agences digitales de premier plan.",
            story_p3: "Aujourd'hui, UNITED ACADEMY-UARD est connu comme <em>\"l'école des talents multiples\"</em> – un endroit où les étudiants acquièrent à la fois des compétences techniques et des valeurs humaines. Nous continuons d'élargir nos programmes, nos installations et nos partenariats pour servir la jeunesse camerounaise.",
            mission_title: "Notre mission",
            mission_text: "Former des professionnels qualifiés, compétents et éthiques qui répondent aux besoins du marché du travail camerounais et peuvent contribuer au développement national.",
            vision_title: "Notre vision",
            vision_text: "Devenir un centre d'excellence en formation professionnelle en Afrique centrale, reconnu pour l'innovation, l'inclusion et les taux d'employabilité élevés.",
            values_title: "Nos valeurs",
            values_text: "Excellence, intégrité, innovation pratique, respect et engagement envers la réussite de chaque étudiant.",
            council_title: "Le Conseil d'Établissement",
            council_subtitle: "L'organe de gouvernance assurant l'excellence académique et la direction stratégique",
            council_president: "Président du Conseil",
            council_president_name: "Dr. Jean-Paul Mbarga",
            council_president_desc: "Ancien Directeur de la Formation Professionnelle, MINEFOP",
            council_academic: "Conseiller académique",
            council_academic_name: "Prof. Marie-Claire Ngo",
            council_academic_desc: "PhD en Sciences de l'Éducation, Université de Yaoundé I",
            council_industry: "Représentant du secteur industriel",
            council_industry_name: "M. Alain Djoumessi",
            council_industry_desc: "PDG, Groupement Interpatronal du Cameroun (GICAM)",
            council_parent: "Représentant des parents",
            council_parent_name: "Mme. Thérèse Essama",
            council_parent_desc: "Défenseur du bien-être des étudiants et de l'engagement communautaire",
            council_mission: "Le Conseil d'Établissement supervise l'orientation stratégique, assure l'assurance qualité et maintient l'alignement de nos programmes sur les normes nationales de formation professionnelle. Le conseil se réunit trimestriellement pour examiner les performances académiques, approuver les nouveaux programmes et renforcer les partenariats avec les parties prenantes de l'industrie.",
            infra_title: "Nos infrastructures",
            infra_lab: "Laboratoire paramédical entièrement équipé avec microscopes, centrifugeuses, stéthoscopes et matériel de simulation.",
            infra_computer: "Postes informatiques modernes, vidéoprojecteurs et suite logicielle créative.",
            infra_class: "Salles de classe spacieuses avec aides pédagogiques modernes et équipement multimédia.",
            infra_library: "Espace d'étude calme avec livres de référence et ressources numériques.",
            partners_title: "Nos partenaires de confiance",
            partners_more: "... et de nombreuses autres cliniques, pharmacies et agences.",
            cta_about: "Prêt à commencer votre voyage ?",
            cta_about_sub: "Rejoignez UNITED ACADEMY-UARD et faites partie de l'école des talents multiples.",
            
            // Contact Page
            contact_page_title: "Contactez-nous",
            contact_page_sub: "Nous sommes là pour vous aider et répondre à vos questions",
            contact_intro: "Vous avez des questions sur nos programmes, les admissions ou les partenariats ? Contactez-nous – nous serons ravis de vous aider.",
            address: "Adresse",
            phone: "Téléphone",
            email: "Email",
            hours: "Horaires d'ouverture",
            connect_with_us: "Connectez-vous avec nous",
            send_message: "Envoyez-nous un message",
            form_intro: "Nous vous répondrons dans les 24 heures.",
            full_name: "Nom complet *",
            email_address: "Adresse email *",
            phone_number: "Numéro de téléphone",
            program_interest: "Programme d'intérêt",
            select_option: "Sélectionnez un programme",
            opt_pharmacy: "Vendeur en pharmacie",
            opt_avs: "Auxiliaire de vie sociale",
            opt_infography: "Infographie multimédia",
            opt_digital: "Marketing digital / CM",
            opt_lab: "Assistant de laboratoire",
            opt_medrep: "Représentant médical",
            opt_nutrition: "Nutrition & Diététique",
            opt_medsec: "Secrétaire médicale",
            opt_accounting: "Comptabilité informatisée",
            opt_other: "Autre",
            subject: "Sujet",
            message: "Message *",
            consent_text: "J'accepte la politique de confidentialité et consens à être contacté.",
            send_message_btn: "Envoyer le message",
            thank_you: "Merci !",
            message_sent: "Votre message a été envoyé. Nous vous contacterons bientôt.",
            find_us: "Où nous trouver",
            faq_title: "Questions fréquentes",
            faq1_q: "Comment postuler ?",
            faq1_a: "Vous pouvez postuler en visitant notre campus à Simbock avec les documents requis : photocopie CNI, diplôme, 2 photos, et frais d'inscription. Vous pouvez également commencer le processus en nous contactant par téléphone ou par email.",
            faq2_q: "Quels sont les frais de scolarité ?",
            faq2_a: "Veuillez nous contacter directement pour des informations détaillées sur les frais. Nous offrons des prix compétitifs et des plans de paiement flexibles. Les frais d'inscription sont de 25 000 FCFA pour tous les programmes.",
            faq3_q: "Proposez-vous des bourses ?",
            faq3_a: "Nous avons des bourses limitées pour les étudiants exceptionnels issus de milieux défavorisés. Contactez notre bureau pour plus d'informations.",
            faq4_q: "Les stages sont-ils garantis ?",
            faq4_a: "Oui, chaque étudiant effectue un stage de 3 à 5 mois dans l'une de nos cliniques, pharmacies ou agences partenaires. Nous vous aidons à trouver un stage.",
            faq5_q: "Quels diplômes proposez-vous ?",
            faq5_a: "Nous préparons les étudiants au DQP (Diplôme de Qualification Professionnelle) et au CQP (Certificat de Qualification Professionnelle), reconnus nationalement par le MINEFOP sous l'agrément N° 00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC.",
            faq6_q: "Puis-je visiter le campus ?",
            faq6_a: "Absolument ! Nous accueillons les visiteurs pendant les heures de bureau. Vous pouvez visiter nos laboratoires, nos salles de classe et rencontrer notre équipe.",
            cta_contact_page: "Prêt à commencer votre voyage ?",
            cta_contact_sub: "Faites le premier pas vers une carrière réussie.",
            browse_programs: "Parcourir les programmes",
            
            // Blog
            blog_title: "Blog & Actualités",
            search_placeholder: "Rechercher...",
            all_categories: "Toutes catégories",
            cat_health: "Santé",
            cat_tech: "Info & design",
            cat_event: "Événements",
            no_results: "Aucun article trouvé.",
            blog_page_title: "Blog & Actualités",
            blog_page_sub: "Histoires, actualités et perspectives de UNITED ACADEMY-UARD",
            blog_search_placeholder: "Rechercher articles, actualités, événements...",
            all_items: "Tous",
            blog_posts: "Articles de blog",
            news_articles: "Actualités",
            events: "Événements",
            publications: "Publications",
            latest_updates: "Dernières actualités",
            no_posts_found: "Aucun article trouvé",
            try_adjusting: "Essayez d'ajuster votre recherche ou filtre",
            load_more: "Charger plus d'articles",
            subscribe_blog: "Abonnez-vous à notre newsletter",
            subscribe_blog_desc: "Recevez les dernières actualités directement dans votre boîte mail",
            subscribe: "S'abonner",
            popular_tags: "Tags populaires :",
            
            // Admission
            admission_title: "Conditions d'admission",
            admission_docs: "Documents requis",
            admission_doc1: "Photocopie de la CNI",
            admission_doc2: "Diplôme pertinent (BEPC/Probatoire/BAC)",
            admission_doc3: "2 photos d'identité (4x4)",
            admission_doc4: "Une rame de papier A4 80g",
            admission_dates: "Dates clés",
            admission_date1: "Ouverture des inscriptions : Septembre 2025",
            admission_date2: "Rentrée administrative : 6 octobre 2025",
            admission_date3: "Début des cours : 15 octobre 2025",
            admission_fees: "Frais d'inscription",
            fee_registration: "Inscription : 25 000 FCFA (tous programmes)",
            fee_installment: "Plans de paiement flexibles disponibles",
            fee_contact: "Contactez-nous pour les détails des frais de scolarité",
            cta_programs: "Trouvez votre programme aujourd'hui",
            cta_programs_sub: "Contactez-nous sur WhatsApp pour plus d'informations ou pour planifier une visite.",
            cta_contact: "Contactez-nous aujourd'hui",
            
            // Footer
            footer_about: "Institut de Formation Professionnelle UNITED ACADEMY-UARD – agréé MINEFOP. Excellence, employabilité, innovation.",
            quick_links: "Liens rapides",
            privacy: "Politique de confidentialité",
            footer_contact: "Contact",
            newsletter: "Newsletter",
            newsletter_desc: "Abonnez-vous pour les mises à jour.",
            footer_rights: "Tous droits réservés.",
            footer_approval: "Agrément MINEFOP N° 00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC",
            
            // Blog posts
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

    let currentLang = 'fr';

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
                <img src="${post.img}" alt="${title}" loading="lazy" onerror="this.src='https://placehold.co/600x400?text=UNITED+ACADEMY-UARD'">
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
        // Save language preference to localStorage
        localStorage.setItem('uau-language', lang);
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

    // initial setup - check for saved language preference
    const savedLang = localStorage.getItem('uau-language') || 'fr';
    setLanguage(savedLang);
    renderBlogPosts(savedLang);

    // lang buttons
    langEnBtn.addEventListener('click', () => setLanguage('en'));
    langFrBtn.addEventListener('click', () => setLanguage('fr'));

    // contact form prevent
    document.getElementById('quickContactForm')?.addEventListener('submit', (e) => e.preventDefault());
})();