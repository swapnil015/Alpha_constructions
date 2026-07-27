-- Alpha Concern — MySQL Schema
-- Import via phpMyAdmin into your cPanel-created database.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- Admin users
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS admin_users;
CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('superadmin','editor') DEFAULT 'editor',
  is_active TINYINT(1) DEFAULT 1,
  failed_attempts INT DEFAULT 0,
  locked_until DATETIME NULL,
  last_login DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default superadmin: admin@alphaconcern.com / ChangeMe!2026
-- Hash generated with password_hash('ChangeMe!2026', PASSWORD_BCRYPT, ['cost'=>12])
INSERT INTO admin_users (name, email, password_hash, role)
VALUES ('Super Admin', 'admin@alphaconcern.com',
        '$2y$12$MZxHy9nRgY5uFp5VqWqKQ.E4eL7B7hF8xN1kQp.GZ3wK4oD2sLqVO', 'superadmin');

-- ---------------------------------------------------------------------------
-- Projects
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS projects;
CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(200) UNIQUE NOT NULL,
  location VARCHAR(200),
  type ENUM('Residential','Commercial','Mixed-Use') NOT NULL,
  status ENUM('Ongoing','Completed') NOT NULL,
  short_description TEXT,
  full_description LONGTEXT,
  hero_image VARCHAR(500),
  key_specs JSON,
  amenities JSON,
  map_lat DECIMAL(10,8) NULL,
  map_lng DECIMAL(11,8) NULL,
  seo_title VARCHAR(200),
  seo_description TEXT,
  og_image VARCHAR(500),
  is_published TINYINT(1) DEFAULT 0,
  is_featured TINYINT(1) DEFAULT 0,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS project_images;
CREATE TABLE project_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  image_path VARCHAR(500) NOT NULL,
  alt_text VARCHAR(300),
  type ENUM('gallery','floor_plan') DEFAULT 'gallery',
  sort_order INT DEFAULT 0,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Imperial Apartment + 3 placeholder projects
INSERT INTO projects (title, slug, location, type, status, short_description, full_description, hero_image, key_specs, amenities, is_published, is_featured, sort_order) VALUES
('Imperial Apartment', 'imperial-apartment', 'Naxal, Kathmandu', 'Residential', 'Ongoing',
 'A 14-storey premium residential development blending minimal neoclassical architecture with contemporary luxury, set in the heart of Naxal.',
 '<p>Imperial Apartment is Alpha Concern''s flagship residential development in Naxal, Kathmandu. Spanning approximately 87,000 square feet across 14 stories, the project offers 2 BHK, 3 BHK, and 4 BHK configurations designed for discerning urban families.</p><p>The architecture pairs minimal neoclassical lines with contemporary detailing — full-height windows frame valley views, while the structure is engineered to be earthquake-resistant and Vastu-compliant. Every residence is appointed to deliver privacy, light, and timeless craftsmanship.</p>',
 '/uploads/projects/imperial-apartment/hero.jpg',
 '{"floors":"14 Stories","total_area":"~87,000 sq.ft.","unit_types":"2 BHK, 3 BHK, 4 BHK","structure":"Earthquake-resistant","compliance":"Vastu-compliant"}',
 '["EV charging stations","Swimming pool","Fully equipped gym","Children''s play area","Community hall","Multi-level parking","24/7 power backup","High-speed elevators","Water treatment plant","Sewage treatment plant","Valley views","Full-height windows"]',
 1, 1, 1),
('Sundara Heights', 'sundara-heights', 'Lalitpur', 'Mixed-Use', 'Completed',
 'A mixed-use landmark blending boutique retail at street level with thoughtfully designed residences above.',
 '<p>Sundara Heights is a completed mixed-use development in Lalitpur, integrating boutique retail at the ground level with twelve floors of residences above. The project demonstrates Alpha Concern''s capability in delivering complex, multi-program buildings on time and on budget.</p>',
 '/uploads/projects/placeholder-1.jpg',
 '{"floors":"13 Stories","retail_space":"15,000 sq.ft.","residences":"36 units"}',
 '["Retail arcade","Rooftop garden","Underground parking","Power backup"]',
 1, 1, 2),
('The Annex Commercial', 'annex-commercial', 'New Baneshwor, Kathmandu', 'Commercial', 'Completed',
 'A grade-A commercial tower delivering flexible floor plates for modern enterprises in central Kathmandu.',
 '<p>The Annex is a grade-A commercial tower in New Baneshwor offering flexible, column-free floor plates engineered for modern offices, co-working operators, and corporate headquarters.</p>',
 '/uploads/projects/placeholder-2.jpg',
 '{"floors":"10 Stories","floor_plate":"6,500 sq.ft.","parking":"3 basement levels"}',
 '["High-speed elevators","Backup power","Fibre-ready","Cafeteria"]',
 1, 1, 3),
('Maharajgunj Villas', 'maharajgunj-villas', 'Maharajgunj, Kathmandu', 'Residential', 'Ongoing',
 'A gated community of nine bespoke villas appointed for diplomatic and executive families.',
 '<p>Nine bespoke villas designed as a gated community in Maharajgunj. Each residence is individually planned, with private gardens and direct-access parking.</p>',
 '/uploads/projects/placeholder-3.jpg',
 '{"plots":"9 villas","plot_size":"4 aana avg","construction":"Premium fit-out"}',
 '["Private gardens","Direct-access parking","Smart home wiring","Solar-ready"]',
 1, 0, 4);

-- ---------------------------------------------------------------------------
-- Blog posts
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS blog_posts;
CREATE TABLE blog_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(300) NOT NULL,
  slug VARCHAR(300) UNIQUE NOT NULL,
  excerpt TEXT,
  body LONGTEXT,
  featured_image VARCHAR(500),
  category VARCHAR(100),
  tags VARCHAR(500),
  author_name VARCHAR(100),
  primary_keyword VARCHAR(200),
  seo_title VARCHAR(200),
  seo_description TEXT,
  og_image VARCHAR(500),
  faq_schema JSON,
  status ENUM('draft','published','scheduled') DEFAULT 'draft',
  published_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO blog_posts (title, slug, excerpt, body, category, author_name, status, published_at) VALUES
('Earthquake-Resistant Construction in Nepal: What You Should Know', 'earthquake-resistant-construction-nepal',
 'A practical overview of seismic-resistant building practices used in modern Kathmandu construction.',
 '<p>Following the lessons of 2015, every responsible builder in Nepal now treats seismic resilience as the baseline, not the bonus. At Alpha Concern, we engineer every structure to NBC 105 with margin to spare.</p><h2>What is earthquake-resistant construction?</h2><p>Earthquake-resistant construction refers to design and detailing practices that allow a building to absorb seismic energy without catastrophic failure. The goal is not zero damage — it is preserving life and ensuring the building can be repaired.</p>',
 'Construction Tips', 'Alpha Concern Editorial', 'published', NOW()),
('Why Vastu-Compliant Apartments Matter to Modern Buyers', 'vastu-compliant-apartments',
 'Vastu Shastra is more than tradition — it shapes light, ventilation, and flow in ways modern buyers value.',
 '<p>Vastu Shastra, the ancient Indian science of architecture, is often dismissed as superstition. Look closer and you''ll find a coherent design language for orientation, ventilation, and flow that modern buyers across Kathmandu actively seek out.</p>',
 'Market Insights', 'Alpha Concern Editorial', 'published', NOW()),
('The Rise of Mixed-Use Developments in Kathmandu', 'rise-of-mixed-use-kathmandu',
 'Mixed-use is reshaping how Kathmandu lives, works, and shops — here is why the trend is durable.',
 '<p>The pure residential tower is no longer the default in Kathmandu. Buyers and tenants increasingly want amenities, retail, and workspace within walking distance — and developers are responding.</p>',
 'Market Insights', 'Alpha Concern Editorial', 'published', NOW());

-- ---------------------------------------------------------------------------
-- Services
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS services;
CREATE TABLE services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(200) UNIQUE NOT NULL,
  description TEXT,
  full_content LONGTEXT,
  hero_image VARCHAR(500),
  icon_name VARCHAR(100),
  seo_title VARCHAR(200),
  seo_description TEXT,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO services (title, slug, description, full_content, icon_name, sort_order) VALUES
('Residential Construction', 'residential', 'Bespoke homes and apartment developments engineered for comfort, longevity, and resale value.', '<p>From single-family residences to multi-storey apartment blocks, our residential practice delivers homes that balance livability with timeless architectural language.</p>', 'home', 1),
('Commercial Construction', 'commercial', 'Grade-A office towers, retail, and hospitality construction delivered on time and to spec.', '<p>We deliver commercial projects with a focus on flexible floor plates, MEP excellence, and the operational reliability tenants demand.</p>', 'building', 2),
('Real Estate Development', 'real-estate', 'End-to-end development — from land acquisition through design, construction, and sales.', '<p>Our development arm originates and executes projects from concept to handover, working with select investors and joint-venture partners.</p>', 'map', 3),
('Interior Design & Finishing', 'interior-design', 'Cohesive interior architecture that completes the experience of every space we build.', '<p>Our in-house interiors team specifies materials, lighting, joinery, and finishes that elevate the architecture without overwhelming it.</p>', 'sparkle', 4),
('Structural Engineering', 'structural-engineering', 'Earthquake-resistant structural design and consultancy meeting NBC 105 and beyond.', '<p>We provide structural design, peer review, and seismic retrofit consultancy for projects across Nepal.</p>', 'beam', 5),
('Project Management', 'project-management', 'Owner-side and turnkey project management with rigorous cost, schedule, and quality control.', '<p>From procurement through commissioning, our PM team safeguards your investment with disciplined controls and transparent reporting.</p>', 'clipboard', 6);

-- ---------------------------------------------------------------------------
-- Inquiries
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS inquiries;
CREATE TABLE inquiries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(200) NOT NULL,
  phone VARCHAR(30),
  subject VARCHAR(300),
  message TEXT NOT NULL,
  source_page VARCHAR(300),
  ip_address VARCHAR(45),
  status ENUM('new','read','replied','archived') DEFAULT 'new',
  admin_notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Job listings + applications
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS job_listings;
CREATE TABLE job_listings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  department VARCHAR(100),
  employment_type ENUM('Full-time','Part-time','Contract','Internship') NOT NULL,
  location VARCHAR(150) DEFAULT 'Kathmandu, Nepal',
  description LONGTEXT,
  requirements LONGTEXT,
  deadline DATE NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO job_listings (title, department, employment_type, description, requirements) VALUES
('Senior Site Engineer', 'Construction', 'Full-time',
 '<p>Lead day-to-day execution on our Imperial Apartment build, coordinating subcontractors, QA/QC, and safety.</p>',
 '<ul><li>BE Civil + 6 years on residential high-rise</li><li>Strong working knowledge of NBC 105</li><li>Fluent English and Nepali</li></ul>'),
('Architectural Designer', 'Design', 'Full-time',
 '<p>Develop schematic through construction drawings for residential and mixed-use projects.</p>',
 '<ul><li>B.Arch with 3+ years experience</li><li>Revit + AutoCAD fluency</li><li>Portfolio of built work preferred</li></ul>');

DROP TABLE IF EXISTS job_applications;
CREATE TABLE job_applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  job_id INT NOT NULL,
  applicant_name VARCHAR(150) NOT NULL,
  email VARCHAR(200) NOT NULL,
  phone VARCHAR(30),
  cover_note TEXT,
  cv_file_path VARCHAR(500),
  status ENUM('new','reviewed','shortlisted','rejected') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (job_id) REFERENCES job_listings(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Team
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS team_members;
CREATE TABLE team_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  title VARCHAR(150),
  bio TEXT,
  photo VARCHAR(500),
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO team_members (name, title, bio, sort_order) VALUES
('[Founder Name]', 'Founder & Managing Director', 'Two decades of experience in construction and real estate development across Nepal.', 1),
('[Director Name]', 'Director, Operations', 'Oversees execution across all active projects with a focus on quality and timeline.', 2),
('[Architect Name]', 'Head of Design', 'Leads our in-house architecture and interiors practice.', 3);

-- ---------------------------------------------------------------------------
-- Testimonials
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS testimonials;
CREATE TABLE testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_name VARCHAR(150) NOT NULL,
  project_name VARCHAR(200),
  review_text TEXT NOT NULL,
  photo VARCHAR(500),
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO testimonials (client_name, project_name, review_text, sort_order) VALUES
('[Client Name]', 'Imperial Apartment',
 'Alpha Concern delivered exactly what they promised. The attention to structural detail and finish quality is genuinely best-in-class for Kathmandu.', 1),
('[Client Name]', 'Sundara Heights',
 'From design through handover, the team kept us informed and in control. We''d build with them again without hesitation.', 2),
('[Client Name]', 'Maharajgunj Villas',
 'Refined craft, honest communication, and a build quality that holds up under scrutiny. Five years on, the home still feels new.', 3);

-- ---------------------------------------------------------------------------
-- Site settings (key-value)
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS site_settings;
CREATE TABLE site_settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value LONGTEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('company_name', 'Alpha Concern Pvt. Ltd.'),
('tagline', 'Building Tomorrow, Today'),
('description', 'Premium construction and real estate development based in Maharajgunj, Kathmandu — building residences and commercial spaces engineered to endure.'),
('address', 'Maharajgunj 4, Kathmandu, Nepal'),
('phone_primary', '+977-1-4515467'),
('phone_tel', '+97714515467'),
('email_primary', 'alpha.concern@gmail.com'),
('whatsapp_number', '9779851108892'),
('whatsapp_display', '+977 9851108892'),
('office_hours', 'Sunday – Friday, 10:00 AM – 6:00 PM'),
('social_facebook', '#'),
('social_instagram', '#'),
('social_linkedin', '#'),
('social_youtube', '#'),
('map_embed_url', 'https://maps.google.com/maps?q=Maharajgunj+4,+Kathmandu,+Nepal&t=&z=15&ie=UTF8&iwloc=&output=embed'),
('map_share_url', 'https://maps.app.goo.gl/WS54FhxKFtyg9dBc6'),
('hero_headline', 'Building Tomorrow, Today'),
('hero_subheadline', 'A premium construction and real estate development practice based in Kathmandu — engineering homes and commercial spaces that endure.'),
('hero_image', '/assets/img/hero-default.jpg'),
('hero_video', ''),
('stat_years', '10'),
('stat_projects', '50'),
('stat_clients', '200'),
('stat_team', '80'),
('about_snapshot', 'Founded in Kathmandu, Alpha Concern has spent over a decade building residences, commercial spaces, and mixed-use developments that combine engineering rigour with architectural restraint. Our work is defined by what endures.'),
('seo_default_description', 'Alpha Concern is a premium construction and real estate development company in Kathmandu, Nepal. Building homes and commercial spaces engineered to last.'),
('seo_default_og_image', '/assets/img/og-default.jpg'),
('ga4_measurement_id', ''),
('gsc_verification', '');

-- ---------------------------------------------------------------------------
-- Partners
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS partners;
CREATE TABLE partners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  logo VARCHAR(500),
  website VARCHAR(500),
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Media library
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS media_files;
CREATE TABLE media_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(300) NOT NULL,
  original_name VARCHAR(300),
  file_path VARCHAR(500) NOT NULL,
  file_type VARCHAR(50),
  file_size INT,
  uploaded_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploaded_by) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Activity log
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS admin_activity_log;
CREATE TABLE admin_activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(100),
  entity_type VARCHAR(100),
  entity_id INT,
  description TEXT,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Form rate limits
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS form_rate_limits;
CREATE TABLE form_rate_limits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45) NOT NULL,
  form_type VARCHAR(50),
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip_form (ip_address, form_type, submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Redirects
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS redirects;
CREATE TABLE redirects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  from_path VARCHAR(300) NOT NULL,
  to_url VARCHAR(500) NOT NULL,
  type ENUM('301','302') DEFAULT '301',
  is_active TINYINT(1) DEFAULT 1,
  INDEX idx_from (from_path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
