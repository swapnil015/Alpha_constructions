-- Alpha Concern — team members
-- Imported from the previous site (alphaconcern.com/about-us).
-- Personal phone numbers, personal email addresses and home
-- localities from that page are intentionally excluded.

DELETE FROM team_members;

INSERT INTO team_members (name, title, bio, photo, sort_order, is_active) VALUES
  ('Ayushya Narsingh Rana', 'Managing Director', 'Board of Directors', '/assets/img/team/ayushya-narsingh-rana.jpg', 1, 1),
  ('Gaurav Thapa', 'Executive Director', 'Board of Directors', '/assets/img/team/gaurav-thapa.jpg', 2, 1),
  ('Ashray Lal Shrestha', 'Director', 'Board of Directors', '/assets/img/team/ashray-lal-shrestha.jpg', 3, 1),
  ('Norbu Tshering Lama', 'Director', 'Board of Directors', '/assets/img/team/norbu-tshering-lama.jpg', 4, 1),
  ('Gopal Mishra', 'General Manager', 'Management', '/assets/img/team/gopal-mishra.jpg', 5, 1),
  ('Financial Edge Consulting Private Limited', 'Financial Consultant', 'Finance', '/assets/img/team/financial-edge-consulting.jpg', 6, 1),
  ('Er. Utsab Gautam', 'Senior Engineer', 'Engineering', '/assets/img/team/utsab-gautam.jpg', 7, 1),
  ('Er. Kiran Paudel', 'Engineer', 'Engineering', '/assets/img/team/kiran-paudel.jpg', 8, 1),
  ('Er. Sashindra Raj Shrestha', 'Engineer', 'Engineering', '/assets/img/team/sashindra-raj-shrestha.jpg', 9, 1),
  ('Er. Ram Baniya', 'Engineer', 'Engineering', '/assets/img/team/ram-baniya.jpg', 10, 1),
  ('Bishal Waiba', 'Site Incharge', 'Site', '/assets/img/team/bishal-waiba.jpg', 11, 1);
