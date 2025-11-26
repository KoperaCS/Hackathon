CREATE DATABASE tipsy_db;
USE tipsy_db;

CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_email VARCHAR(100) NOT NULL UNIQUE,
    admin_pass VARCHAR(255) NOT NULL   -- store hashed passwords
);

CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL UNIQUE,
    user_pass VARCHAR(255) NOT NULL   -- store hashed passwords
);

CREATE TABLE report (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,                      -- can be NULL for anonymous
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    severity ENUM('Low','Medium','High'),             -- optional, AI-generated
    category VARCHAR(50),             -- optional, AI-generated
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE SET NULL
);

INSERT INTO admin (admin_email, admin_pass) VALUES
('adminjerold@tipsy.com', 'jerold123'),
('admintherd@tipsy.com', 'therd123'),
('adminjustin@tipsy.com', 'justin123'),
('adminkong@tipsy.com', 'kong123');

INSERT INTO user (user_email, user_pass) VALUES
('jim@tipsy.com', 'jim123'),
('dan@tipsy.com', 'dan123'),
('saul@tipsy.com', 'saul123');

INSERT INTO report (user_id, title, content, severity, category) VALUES
(NULL, 'Manager misreporting expenses', 'I noticed discrepancies in the finance reports that suggest intentional misreporting.', 'High', 'Financial Misconduct'),
(1, 'Unsafe lab equipment', 'Equipment in the lab is being handled without proper safety protocols.', 'Medium', 'Safety Violation'),
(2, 'Harassment in meetings', 'Colleague made repeated inappropriate comments during meetings.', 'High', 'Harassment'),
(NULL, 'Unauthorized access to files', 'Someone accessed confidential files without permission.', 'High', 'Security Breach'),
(3, 'Waste of resources', 'Office supplies and budget are being wasted on unnecessary items.', 'Low', 'Financial Misconduct');
