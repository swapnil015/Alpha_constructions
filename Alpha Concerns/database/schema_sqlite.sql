-- Alpha Concern — SQLite preview schema (auto-imported on first run)
-- Differs from MySQL schema: no ENUM, no JSON column type (TEXT), no ON UPDATE,
--   AUTOINCREMENT instead of AUTO_INCREMENT, INTEGER PRIMARY KEY for ids.

CREATE TABLE admin_users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  role TEXT DEFAULT 'editor',
  is_active INTEGER DEFAULT 1,
  failed_attempts INTEGER DEFAULT 0,
  locked_until TEXT NULL,
  last_login TEXT NULL,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
-- Default admin: admin@alphaconcern.com / preview1234
INSERT INTO admin_users (name,email,password_hash,role) VALUES
('Super Admin','admin@alphaconcern.com','$2y$10$GO..3sEiB5QvI8/Q8Qqw/O/U1uVstbhYA.2xVRCdxXWw1UbQ.2JH2','superadmin');

CREATE TABLE projects (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  slug TEXT UNIQUE NOT NULL,
  location TEXT,
  type TEXT NOT NULL,
  status TEXT NOT NULL,
  short_description TEXT,
  full_description TEXT,
  hero_image TEXT,
  key_specs TEXT,
  amenities TEXT,
  map_lat REAL NULL,
  map_lng REAL NULL,
  seo_title TEXT,
  seo_description TEXT,
  og_image TEXT,
  is_published INTEGER DEFAULT 0,
  is_featured INTEGER DEFAULT 0,
  sort_order INTEGER DEFAULT 0,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE project_images (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  project_id INTEGER NOT NULL,
  image_path TEXT NOT NULL,
  alt_text TEXT,
  type TEXT DEFAULT 'gallery',
  sort_order INTEGER DEFAULT 0,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

INSERT INTO projects (title,slug,location,type,status,short_description,full_description,hero_image,key_specs,amenities,is_published,is_featured,sort_order) VALUES
('Imperial Apartment','imperial-apartment','Naxal, Kathmandu','Residential','Ongoing',
 'A 14-storey premium residential development blending minimal neoclassical architecture with contemporary luxury, set in the heart of Naxal.',
 '<p>Imperial Apartment is Alpha Concern''s flagship residential development in Naxal, Kathmandu. Spanning approximately 87,000 square feet across 14 stories, the project offers 2 BHK, 3 BHK, and 4 BHK configurations designed for discerning urban families.</p><p>The architecture pairs minimal neoclassical lines with contemporary detailing — full-height windows frame valley views, while the structure is engineered to be earthquake-resistant and Vastu-compliant. Every residence is appointed to deliver privacy, light, and timeless craftsmanship.</p>',
 '/uploads/projects/imperial-apartment/hero.jpg',
 '{"floors":"14 Stories","total_area":"~87,000 sq.ft.","unit_types":"2 BHK, 3 BHK, 4 BHK","structure":"Earthquake-resistant","compliance":"Vastu-compliant"}',
 '["EV charging stations","Swimming pool","Fully equipped gym","Children''s play area","Community hall","Multi-level parking","24/7 power backup","High-speed elevators","Water treatment plant","Sewage treatment plant","Valley views","Full-height windows"]',
 1,1,1),
('Sundara Heights','sundara-heights','Lalitpur','Mixed-Use','Completed',
 'A mixed-use landmark blending boutique retail at street level with thoughtfully designed residences above.',
 '<p>Sundara Heights is a completed mixed-use development in Lalitpur, integrating boutique retail at the ground level with twelve floors of residences above.</p>',
 '/uploads/projects/placeholder-1.jpg',
 '{"floors":"13 Stories","retail_space":"15,000 sq.ft.","residences":"36 units"}',
 '["Retail arcade","Rooftop garden","Underground parking","Power backup"]',
 1,1,2),
('The Annex Commercial','annex-commercial','New Baneshwor, Kathmandu','Commercial','Completed',
 'A grade-A commercial tower delivering flexible floor plates for modern enterprises in central Kathmandu.',
 '<p>The Annex is a grade-A commercial tower in New Baneshwor offering flexible, column-free floor plates engineered for modern offices.</p>',
 '/uploads/projects/placeholder-2.jpg',
 '{"floors":"10 Stories","floor_plate":"6,500 sq.ft.","parking":"3 basement levels"}',
 '["High-speed elevators","Backup power","Fibre-ready","Cafeteria"]',
 1,1,3),
('Maharajgunj Villas','maharajgunj-villas','Maharajgunj, Kathmandu','Residential','Ongoing',
 'A gated community of nine bespoke villas appointed for diplomatic and executive families.',
 '<p>Nine bespoke villas designed as a gated community in Maharajgunj.</p>',
 '/uploads/projects/placeholder-3.jpg',
 '{"plots":"9 villas","plot_size":"4 aana avg","construction":"Premium fit-out"}',
 '["Private gardens","Direct-access parking","Smart home wiring","Solar-ready"]',
 1,0,4);

CREATE TABLE blog_posts (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  slug TEXT UNIQUE NOT NULL,
  excerpt TEXT,
  body TEXT,
  featured_image TEXT,
  category TEXT,
  tags TEXT,
  author_name TEXT,
  primary_keyword TEXT,
  seo_title TEXT,
  seo_description TEXT,
  og_image TEXT,
  faq_schema TEXT,
  status TEXT DEFAULT 'draft',
  published_at TEXT NULL,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO blog_posts (title,slug,excerpt,body,category,author_name,status,published_at) VALUES
('Earthquake-Resistant Construction in Nepal: What You Should Know','earthquake-resistant-construction-nepal',
 'A practical overview of seismic-resistant building practices used in modern Kathmandu construction.',
 '<p>Following the lessons of 2015, every responsible builder in Nepal now treats seismic resilience as the baseline, not the bonus. At Alpha Concern, we engineer every structure to NBC 105 with margin to spare.</p><h2>What is earthquake-resistant construction?</h2><p>Earthquake-resistant construction refers to design and detailing practices that allow a building to absorb seismic energy without catastrophic failure.</p>',
 'Construction Tips','Alpha Concern Editorial','published',CURRENT_TIMESTAMP),
('Why Vastu-Compliant Apartments Matter to Modern Buyers','vastu-compliant-apartments',
 'Vastu Shastra is more than tradition — it shapes light, ventilation, and flow in ways modern buyers value.',
 '<p>Vastu Shastra, the ancient Indian science of architecture, is often dismissed as superstition. Look closer and you''ll find a coherent design language for orientation, ventilation, and flow.</p>',
 'Market Insights','Alpha Concern Editorial','published',CURRENT_TIMESTAMP),
('The Rise of Mixed-Use Developments in Kathmandu','rise-of-mixed-use-kathmandu',
 'Mixed-use is reshaping how Kathmandu lives, works, and shops — here is why the trend is durable.',
 '<p>The pure residential tower is no longer the default in Kathmandu. Buyers and tenants increasingly want amenities, retail, and workspace within walking distance.</p>',
 'Market Insights','Alpha Concern Editorial','published',CURRENT_TIMESTAMP);

CREATE TABLE services (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  slug TEXT UNIQUE NOT NULL,
  description TEXT,
  full_content TEXT,
  hero_image TEXT,
  icon_name TEXT,
  seo_title TEXT,
  seo_description TEXT,
  sort_order INTEGER DEFAULT 0,
  is_active INTEGER DEFAULT 1
);

INSERT INTO services (title,slug,description,full_content,icon_name,sort_order) VALUES
('Residential Construction','residential','Bespoke homes and apartment developments engineered for comfort, longevity, and resale value.','<p>From single-family residences to multi-storey apartment blocks, our residential practice delivers homes that balance livability with timeless architectural language.</p>','home',1),
('Commercial Construction','commercial','Grade-A office towers, retail, and hospitality construction delivered on time and to spec.','<p>We deliver commercial projects with a focus on flexible floor plates, MEP excellence, and the operational reliability tenants demand.</p>','building',2),
('Real Estate Development','real-estate','End-to-end development — from land acquisition through design, construction, and sales.','<p>Our development arm originates and executes projects from concept to handover.</p>','map',3),
('Interior Design & Finishing','interior-design','Cohesive interior architecture that completes the experience of every space we build.','<p>Our in-house interiors team specifies materials, lighting, joinery, and finishes that elevate the architecture without overwhelming it.</p>','sparkle',4),
('Structural Engineering','structural-engineering','Earthquake-resistant structural design and consultancy meeting NBC 105 and beyond.','<p>We provide structural design, peer review, and seismic retrofit consultancy for projects across Nepal.</p>','beam',5),
('Project Management','project-management','Owner-side and turnkey project management with rigorous cost, schedule, and quality control.','<p>From procurement through commissioning, our PM team safeguards your investment with disciplined controls and transparent reporting.</p>','clipboard',6);

CREATE TABLE inquiries (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT,
  subject TEXT,
  message TEXT NOT NULL,
  source_page TEXT,
  ip_address TEXT,
  status TEXT DEFAULT 'new',
  admin_notes TEXT,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE job_listings (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  department TEXT,
  employment_type TEXT NOT NULL,
  location TEXT DEFAULT 'Kathmandu, Nepal',
  description TEXT,
  requirements TEXT,
  deadline TEXT NULL,
  is_active INTEGER DEFAULT 1,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO job_listings (title,department,employment_type,description,requirements) VALUES
('Senior Site Engineer','Construction','Full-time',
 '<p>Lead day-to-day execution on our Imperial Apartment build, coordinating subcontractors, QA/QC, and safety.</p>',
 '<ul><li>BE Civil + 6 years on residential high-rise</li><li>Strong working knowledge of NBC 105</li><li>Fluent English and Nepali</li></ul>'),
('Architectural Designer','Design','Full-time',
 '<p>Develop schematic through construction drawings for residential and mixed-use projects.</p>',
 '<ul><li>B.Arch with 3+ years experience</li><li>Revit + AutoCAD fluency</li><li>Portfolio of built work preferred</li></ul>');

CREATE TABLE job_applications (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  job_id INTEGER NOT NULL,
  applicant_name TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT,
  cover_note TEXT,
  cv_file_path TEXT,
  status TEXT DEFAULT 'new',
  created_at TEXT DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (job_id) REFERENCES job_listings(id)
);

CREATE TABLE team_members (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  title TEXT,
  bio TEXT,
  photo TEXT,
  sort_order INTEGER DEFAULT 0,
  is_active INTEGER DEFAULT 1
);

INSERT INTO team_members (name,title,bio,sort_order) VALUES
('[Founder Name]','Founder & Managing Director','Two decades of experience in construction and real estate development across Nepal.',1),
('[Director Name]','Director, Operations','Oversees execution across all active projects with a focus on quality and timeline.',2),
('[Architect Name]','Head of Design','Leads our in-house architecture and interiors practice.',3);

CREATE TABLE testimonials (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  client_name TEXT NOT NULL,
  project_name TEXT,
  review_text TEXT NOT NULL,
  photo TEXT,
  sort_order INTEGER DEFAULT 0,
  is_active INTEGER DEFAULT 1
);

INSERT INTO testimonials (client_name,project_name,review_text,sort_order) VALUES
('[Client Name]','Imperial Apartment','Alpha Concern delivered exactly what they promised. The attention to structural detail and finish quality is genuinely best-in-class for Kathmandu.',1),
('[Client Name]','Sundara Heights','From design through handover, the team kept us informed and in control. We''d build with them again without hesitation.',2),
('[Client Name]','Maharajgunj Villas','Refined craft, honest communication, and a build quality that holds up under scrutiny. Five years on, the home still feels new.',3);

CREATE TABLE site_settings (
  setting_key TEXT PRIMARY KEY,
  setting_value TEXT,
  updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO site_settings (setting_key, setting_value) VALUES
('company_name','Alpha Concern Pvt. Ltd.'),
('tagline','Building Tomorrow, Today'),
('description','Premium construction and real estate development based in Maharajgunj, Kathmandu — building residences and commercial spaces engineered to endure.'),
('address','Maharajgunj 4, Kathmandu, Nepal'),
('phone_primary','+977-1-4515467'),
('phone_tel','+97714515467'),
('email_primary','alpha.concern@gmail.com'),
('whatsapp_number','9779851108892'),
('whatsapp_display','+977 9851108892'),
('office_hours','Sunday – Friday, 10:00 AM – 6:00 PM'),
('social_facebook','#'),
('social_instagram','#'),
('social_linkedin','#'),
('social_youtube','#'),
('map_embed_url','https://maps.google.com/maps?q=Maharajgunj+4,+Kathmandu,+Nepal&t=&z=15&ie=UTF8&iwloc=&output=embed'),
('map_share_url','https://maps.app.goo.gl/WS54FhxKFtyg9dBc6'),
('hero_headline','Building Tomorrow, Today'),
('hero_subheadline','A premium construction and real estate development practice based in Kathmandu — engineering homes and commercial spaces that endure.'),
('hero_image',''),
('stat_years','10'),
('stat_projects','50'),
('stat_clients','200'),
('stat_team','80'),
('about_snapshot','Founded in Kathmandu, Alpha Concern has spent over a decade building residences, commercial spaces, and mixed-use developments that combine engineering rigour with architectural restraint. Our work is defined by what endures.'),
('seo_default_description','Alpha Concern is a premium construction and real estate development company in Kathmandu, Nepal.'),
('seo_default_og_image','/assets/img/favicon.svg'),
('ga4_measurement_id',''),
('gsc_verification','');

CREATE TABLE partners (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  logo TEXT,
  website TEXT,
  sort_order INTEGER DEFAULT 0,
  is_active INTEGER DEFAULT 1
);

CREATE TABLE media_files (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  filename TEXT NOT NULL,
  original_name TEXT,
  file_path TEXT NOT NULL,
  file_type TEXT,
  file_size INTEGER,
  uploaded_by INTEGER NULL,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin_activity_log (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NULL,
  action TEXT,
  entity_type TEXT,
  entity_id INTEGER,
  description TEXT,
  ip_address TEXT,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE form_rate_limits (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ip_address TEXT NOT NULL,
  form_type TEXT,
  submitted_at TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_rl ON form_rate_limits(ip_address, form_type, submitted_at);

CREATE TABLE redirects (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  from_path TEXT NOT NULL,
  to_url TEXT NOT NULL,
  type TEXT DEFAULT '301',
  is_active INTEGER DEFAULT 1
);
