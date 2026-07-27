-- Run this in phpMyAdmin → your database → SQL tab → paste → Go.
-- It updates site_settings with the real Alpha Concern business info.
-- Safe to run multiple times.

INSERT INTO site_settings (setting_key, setting_value) VALUES
('company_name',     'Alpha Concern Pvt. Ltd.'),
('tagline',          'Building Tomorrow, Today'),
('description',      'Premium construction and real estate development based in Maharajgunj, Kathmandu — building residences and commercial spaces engineered to endure.'),
('address',          'Maharajgunj 4, Kathmandu, Nepal'),
('phone_primary',    '+977-1-4515467'),
('phone_tel',        '+97714515467'),
('email_primary',    'alpha.concern@gmail.com'),
('whatsapp_number',  '9779851108892'),
('whatsapp_display', '+977 9851108892'),
('office_hours',     'Sunday – Friday, 10:00 AM – 6:00 PM'),
('map_embed_url',    'https://maps.google.com/maps?q=Maharajgunj+4,+Kathmandu,+Nepal&t=&z=15&ie=UTF8&iwloc=&output=embed'),
('map_share_url',    'https://maps.app.goo.gl/WS54FhxKFtyg9dBc6')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
