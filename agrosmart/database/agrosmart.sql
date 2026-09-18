-- ==========================================================
-- AgroSmart – Smart Agriculture Assistant & Farmer Market Portal
-- BCA Field Project Database Schema & Sample Demonstration Data
-- Database Name: agrosmart
-- Compatible with MySQL 8.0+ / MariaDB 10.4+
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `agrosmart` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `agrosmart`;

-- 1. Users Table (Multi-role Authentication)
DROP TABLE IF EXISTS `expert_questions`;
DROP TABLE IF EXISTS `enquiries`;
DROP TABLE IF EXISTS `complaints`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `farmer_crops`;
DROP TABLE IF EXISTS `crop_diseases`;
DROP TABLE IF EXISTS `market_prices`;
DROP TABLE IF EXISTS `schemes`;
DROP TABLE IF EXISTS `crops`;
DROP TABLE IF EXISTS `experts`;
DROP TABLE IF EXISTS `buyers`;
DROP TABLE IF EXISTS `farmers`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `mobile` VARCHAR(15) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('farmer', 'buyer', 'expert', 'admin') NOT NULL DEFAULT 'farmer',
  `status` ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Farmers Table
CREATE TABLE `farmers` (
  `farmer_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `village` VARCHAR(100) NOT NULL,
  `district` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL DEFAULT 'Maharashtra',
  `land_area` DECIMAL(6, 2) NOT NULL COMMENT 'In Acres',
  `main_crop` VARCHAR(100) NOT NULL,
  `soil_type` VARCHAR(50) NOT NULL,
  `water_availability` ENUM('Rainfed', 'Canal', 'Well/Borewell', 'Drip Irrigation') NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_farmer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Buyers Table
CREATE TABLE `buyers` (
  `buyer_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `business_name` VARCHAR(150) NOT NULL,
  `address` TEXT NOT NULL,
  `district` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL DEFAULT 'Maharashtra',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_buyer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Agriculture Experts Table
CREATE TABLE `experts` (
  `expert_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `qualification` VARCHAR(150) NOT NULL,
  `specialization` VARCHAR(150) NOT NULL,
  `experience` INT NOT NULL COMMENT 'Years of experience',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_expert_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Crops Information Table
CREATE TABLE `crops` (
  `crop_id` INT AUTO_INCREMENT PRIMARY KEY,
  `crop_name` VARCHAR(100) NOT NULL,
  `season` ENUM('Kharif', 'Rabi', 'Zaid', 'Annual') NOT NULL,
  `soil_type` VARCHAR(100) NOT NULL,
  `water_requirement` ENUM('Low', 'Medium', 'High') NOT NULL,
  `cultivation_info` TEXT NOT NULL,
  `sowing_period` VARCHAR(100) NOT NULL,
  `harvest_period` VARCHAR(100) NOT NULL,
  `precautions` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Farmer Crops (Farmer's current cultivation)
CREATE TABLE `farmer_crops` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `farmer_id` INT NOT NULL,
  `crop_id` INT NOT NULL,
  `area` DECIMAL(6, 2) NOT NULL COMMENT 'Acres',
  `sowing_date` DATE NOT NULL,
  `expected_harvest_date` DATE NOT NULL,
  `status` ENUM('Sown', 'Vegetative', 'Flowering', 'Harvested') DEFAULT 'Sown',
  CONSTRAINT `fk_farmer_crops_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_farmer_crops_crop` FOREIGN KEY (`crop_id`) REFERENCES `crops` (`crop_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Crop Diseases and Pest Information
CREATE TABLE `crop_diseases` (
  `disease_id` INT AUTO_INCREMENT PRIMARY KEY,
  `crop_id` INT NOT NULL,
  `disease_name` VARCHAR(150) NOT NULL,
  `symptoms` TEXT NOT NULL,
  `causes` TEXT NOT NULL,
  `management` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_disease_crop` FOREIGN KEY (`crop_id`) REFERENCES `crops` (`crop_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Marketplace Products Table
CREATE TABLE `products` (
  `product_id` INT AUTO_INCREMENT PRIMARY KEY,
  `farmer_id` INT NOT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `category` ENUM('Grains', 'Pulses', 'Oilseeds', 'Vegetables', 'Fruits', 'Cotton/Fiber', 'Spices') NOT NULL,
  `quantity` DECIMAL(10, 2) NOT NULL,
  `unit` VARCHAR(20) NOT NULL DEFAULT 'Quintal',
  `expected_price` DECIMAL(10, 2) NOT NULL COMMENT 'Price per unit in INR',
  `location` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `photo` VARCHAR(255) DEFAULT 'default_crop.jpg',
  `status` ENUM('pending', 'approved', 'rejected', 'sold') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_product_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Market Prices (APMC Mandi records)
CREATE TABLE `market_prices` (
  `price_id` INT AUTO_INCREMENT PRIMARY KEY,
  `crop_name` VARCHAR(100) NOT NULL,
  `market_name` VARCHAR(100) NOT NULL,
  `district` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL DEFAULT 'Maharashtra',
  `min_price` DECIMAL(10, 2) NOT NULL,
  `max_price` DECIMAL(10, 2) NOT NULL,
  `modal_price` DECIMAL(10, 2) NOT NULL,
  `price_date` DATE NOT NULL,
  `source` VARCHAR(150) NOT NULL DEFAULT 'APMC Market Yard Bulletin (Demo Data)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Buyer Enquiries
CREATE TABLE `enquiries` (
  `enquiry_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `buyer_id` INT NOT NULL,
  `farmer_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `quantity_required` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('Pending', 'Accepted', 'Rejected') NOT NULL DEFAULT 'Pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_enquiry_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enquiry_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`buyer_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enquiry_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Farmer Complaints Table
CREATE TABLE `complaints` (
  `complaint_id` INT AUTO_INCREMENT PRIMARY KEY,
  `farmer_id` INT NOT NULL,
  `complaint_type` ENUM('Market', 'Crop', 'Irrigation', 'Marketplace', 'Other') NOT NULL,
  `description` TEXT NOT NULL,
  `status` ENUM('Pending', 'In Progress', 'Resolved') NOT NULL DEFAULT 'Pending',
  `admin_response` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` DATETIME NULL,
  CONSTRAINT `fk_complaint_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Government Agricultural Schemes
CREATE TABLE `schemes` (
  `scheme_id` INT AUTO_INCREMENT PRIMARY KEY,
  `scheme_name` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `eligibility` TEXT NOT NULL,
  `benefits` TEXT NOT NULL,
  `required_documents` TEXT NOT NULL,
  `application_process` TEXT NOT NULL,
  `official_source` VARCHAR(255) NOT NULL,
  `updated_date` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Expert Questions and Consultations
CREATE TABLE `expert_questions` (
  `question_id` INT AUTO_INCREMENT PRIMARY KEY,
  `farmer_id` INT NOT NULL,
  `expert_id` INT NULL,
  `question` TEXT NOT NULL,
  `answer` TEXT NULL,
  `status` ENUM('Pending', 'Answered') NOT NULL DEFAULT 'Pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `answered_at` DATETIME NULL,
  CONSTRAINT `fk_question_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_question_expert` FOREIGN KEY (`expert_id`) REFERENCES `experts` (`expert_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- SAMPLE DEMONSTRATION DATA (FOR BCA PROJECT VIVA & TESTING)
-- Default Password for all demo accounts: password123
-- (Hashed with password_hash('password123', PASSWORD_BCRYPT))
-- $2y$10$4.q1g19rC9J0vP/r/2nB6.b9s30FwWvI3sM0b57mXk/vD7wYkUf72
-- ==========================================================

-- Users
INSERT INTO `users` (`user_id`, `name`, `email`, `mobile`, `password`, `role`, `status`) VALUES
(1, 'AgroSmart Admin', 'admin@agrosmart.com', '9876543210', '$2y$10$tQ1z19rC9J0vP/r/2nB6.b9s30FwWvI3sM0b57mXk/vD7wYkUf72', 'admin', 'active'),
(2, 'Ramesh Patil', 'ramesh.farmer@gmail.com', '9822012345', '$2y$10$tQ1z19rC9J0vP/r/2nB6.b9s30FwWvI3sM0b57mXk/vD7wYkUf72', 'farmer', 'active'),
(3, 'Suresh Jadhav', 'suresh.farmer@gmail.com', '9822054321', '$2y$10$tQ1z19rC9J0vP/r/2nB6.b9s30FwWvI3sM0b57mXk/vD7wYkUf72', 'farmer', 'active'),
(4, 'Maha Agro Traders', 'buyer@mahaagro.com', '9890112233', '$2y$10$tQ1z19rC9J0vP/r/2nB6.b9s30FwWvI3sM0b57mXk/vD7wYkUf72', 'buyer', 'active'),
(5, 'Dr. Anand Deshmukh', 'anand.expert@agrosmart.com', '9422001122', '$2y$10$tQ1z19rC9J0vP/r/2nB6.b9s30FwWvI3sM0b57mXk/vD7wYkUf72', 'expert', 'active');

-- Farmers Profiles
INSERT INTO `farmers` (`farmer_id`, `user_id`, `village`, `district`, `state`, `land_area`, `main_crop`, `soil_type`, `water_availability`) VALUES
(1, 2, 'Khadki', 'Pune', 'Maharashtra', 5.50, 'Soybean', 'Black Soil', 'Canal'),
(2, 3, 'Baramati', 'Pune', 'Maharashtra', 8.00, 'Cotton', 'Black Soil', 'Well/Borewell');

-- Buyers Profiles
INSERT INTO `buyers` (`buyer_id`, `user_id`, `business_name`, `address`, `district`, `state`) VALUES
(1, 4, 'Maha Agro Grain Wholesalers', 'Shop 42, APMC Market Yard, Gultekdi', 'Pune', 'Maharashtra');

-- Experts Profiles
INSERT INTO `experts` (`expert_id`, `user_id`, `qualification`, `specialization`, `experience`) VALUES
(1, 5, 'Ph.D. in Agronomy (MPKV Rahuri)', 'Crop Protection & Soil Fertility', 12);

-- Crops
INSERT INTO `crops` (`crop_id`, `crop_name`, `season`, `soil_type`, `water_requirement`, `cultivation_info`, `sowing_period`, `harvest_period`, `precautions`) VALUES
(1, 'Cotton', 'Kharif', 'Black Soil', 'Medium', 'Cotton is a major fiber cash crop. Requires warm climate, deep fertile black cotton soil with good drainage. Spacing: 90cm x 60cm.', 'June - July', 'November - January', 'Monitor for pink bollworm and sucking pests regularly. Avoid waterlogging at seedling stage.'),
(2, 'Soybean', 'Kharif', 'Black Soil', 'Medium', 'Soybean is an important oilseed legume crop fixing atmospheric nitrogen. Optimum seed rate 25-30 kg/acre with Rhizobium treatment.', 'June - July', 'September - October', 'Avoid sowing before receiving at least 75-100mm monsoon rainfall. Do not sow seeds deeper than 4 cm.'),
(3, 'Tur (Pigeon Pea)', 'Kharif', 'Loamy / Black Soil', 'Low', 'Tur is a drought-tolerant pulse crop often intercropped with soybean or cotton in a 1:2 or 1:4 ratio.', 'June - July', 'December - January', 'Protect against pod borer (Helicoverpa armigera) during flowering and pod development stages.'),
(4, 'Wheat', 'Rabi', 'Loamy Soil', 'Medium', 'Wheat is a staple rabi cereal. Requires cool winter weather with temperature 15-20 deg C. 4-5 irrigations at critical stages: CRI, tillering, flowering, milk stage.', 'November - December', 'March - April', 'First irrigation at Crown Root Initiation (21 days after sowing) is essential.'),
(5, 'Onion', 'Rabi', 'Sandy Loam', 'Medium', 'Major commercial vegetable crop. Nursery raised for 6-7 weeks before transplanting in raised beds or flat beds.', 'October - November', 'March - April', 'Avoid excessive nitrogen late in season as it leads to thick necks and reduced storage life.'),
(6, 'Tomato', 'Annual', 'Sandy Loam / Red Soil', 'High', 'High yielding vegetable crop grown year-round. Nursery bed preparation with net cover prevents early viral infections.', 'Round the year', '70-90 days after planting', 'Stake plants with bamboo or wires to prevent fruit rotting on wet soil.'),
(7, 'Maize', 'Kharif', 'Well-drained Loam', 'Medium', 'Versatile cereal for food, feed, and industrial starch. Needs high organic matter and warm sunny days.', 'June - July / Oct - Nov', '90-105 days after sowing', 'Fall Armyworm (FAW) monitoring from emergence is critical; apply neem spray early.'),
(8, 'Gram (Chickpea)', 'Rabi', 'Clay Loam / Black Soil', 'Low', 'Key rabi pulse crop utilizing residual moisture. N-fixing crop improving soil structure.', 'October - November', 'February - March', 'Avoid over-irrigation which induces wilt and excessive vegetative growth.');

-- Farmer Crops
INSERT INTO `farmer_crops` (`farmer_id`, `crop_id`, `area`, `sowing_date`, `expected_harvest_date`, `status`) VALUES
(1, 2, 3.50, '2026-06-25', '2026-10-10', 'Flowering'),
(1, 3, 2.00, '2026-06-28', '2026-12-20', 'Vegetative'),
(2, 1, 5.00, '2026-07-02', '2026-11-25', 'Flowering');

-- Crop Diseases
INSERT INTO `crop_diseases` (`disease_id`, `crop_id`, `disease_name`, `symptoms`, `causes`, `management`) VALUES
(1, 1, 'Pink Bollworm', 'Rosetted flowers, premature boll opening, damaged seed kernels with internal staining.', 'Lepidopteran insect pest (Pectinophora gossypiella)', 'Use pheromone traps @ 5/acre for monitoring. Apply Trichogramma parasitoids. Timely spray of recommended insecticides if ETL exceeds 5-10% damaged bolls.'),
(2, 2, 'Rust and Yellow Mosaic Virus', 'Yellow-green mosaic patches on leaves, premature defoliation, stunted growth and pod shriveling.', 'Fungal pathogen and Whitefly insect vector transmission.', 'Sow resistant varieties. Control whitefly vectors using yellow sticky traps and systemic insecticidal seed treatment.'),
(3, 4, 'Brown / Yellow Rust', 'Yellowish-orange pustules arranged linearly along leaf veins resembling stripes.', 'Fungus Puccinia striiformis favored by high humidity and cool temperatures.', 'Grow rust-tolerant certified seeds. Spray Propiconazole 25 EC (1 ml/liter) upon first appearance.'),
(4, 5, 'Purple Blotch', 'Small water-soaked lesions developing purple centers with yellow halos on older leaves.', 'Fungus Alternaria porri during warm, humid conditions.', 'Maintain proper spacing and field sanitation. Spray Mancozeb (2.5 g/L) or Chlorothalonil on symptom onset.');

-- Products (Marketplace Listings)
INSERT INTO `products` (`product_id`, `farmer_id`, `product_name`, `category`, `quantity`, `unit`, `expected_price`, `location`, `description`, `photo`, `status`) VALUES
(1, 1, 'Organic Soybean (JS-335 Grade A)', 'Oilseeds', 45.00, 'Quintal', 4750.00, 'Khadki, Haveli, Pune', 'Naturally sun-dried soybean with moisture below 10%. High oil and protein content.', 'soybean.jpg', 'approved'),
(2, 2, 'Long Staple BT Cotton', 'Cotton/Fiber', 60.00, 'Quintal', 7200.00, 'Baramati, Pune', 'Clean white cotton, minimum trash, first picking quality harvest.', 'cotton.jpg', 'approved'),
(3, 1, 'Fresh Red Onion (Nashik Quality)', 'Vegetables', 120.00, 'Quintal', 1850.00, 'Khadki, Haveli, Pune', 'Medium to large size red onions, excellent shelf life and firmness.', 'onion.jpg', 'approved'),
(4, 2, 'Desi Chana (Gram)', 'Pulses', 25.00, 'Quintal', 5600.00, 'Baramati, Pune', 'Purity above 98%, cleaned and sorted, suitable for dal mills and retailing.', 'chana.jpg', 'pending');

-- Market Prices (APMC Benchmark Data clearly tagged as educational demonstration data)
INSERT INTO `market_prices` (`crop_name`, `market_name`, `district`, `state`, `min_price`, `max_price`, `modal_price`, `price_date`, `source`) VALUES
('Cotton', 'Baramati APMC', 'Pune', 'Maharashtra', 6800.00, 7550.00, 7250.00, '2026-09-15', 'Baramati APMC Bulletin (Demonstration Data)'),
('Soybean', 'Gultekdi APMC', 'Pune', 'Maharashtra', 4300.00, 4850.00, 4650.00, '2026-09-15', 'Pune Mandi Daily Register (Demonstration Data)'),
('Tur (Pigeon Pea)', 'Latur APMC', 'Latur', 'Maharashtra', 8900.00, 9800.00, 9400.00, '2026-09-15', 'Latur APMC Bulletin (Demonstration Data)'),
('Onion', 'Lasalgaon APMC', 'Nashik', 'Maharashtra', 1400.00, 2200.00, 1850.00, '2026-09-15', 'Lasalgaon APMC Official (Demonstration Data)'),
('Wheat', 'Jalgaon APMC', 'Jalgaon', 'Maharashtra', 2300.00, 2750.00, 2550.00, '2026-09-15', 'Jalgaon Mandi Bulletin (Demonstration Data)'),
('Tomato', 'Narayangaon Market', 'Pune', 'Maharashtra', 1200.00, 2100.00, 1600.00, '2026-09-15', 'Narayangaon Tomato APMC (Demonstration Data)');

-- Buyer Enquiries
INSERT INTO `enquiries` (`enquiry_id`, `product_id`, `buyer_id`, `farmer_id`, `message`, `quantity_required`, `status`) VALUES
(1, 1, 1, 1, 'Interested in purchasing 30 Quintals for our wholesale processing unit in Pune. Can you arrange transport?', 30.00, 'Accepted'),
(2, 2, 1, 2, 'Need ginning quality sample testing for 40 Quintals. Kindly confirm availability.', 40.00, 'Pending');

-- Farmer Complaints
INSERT INTO `complaints` (`complaint_id`, `farmer_id`, `complaint_type`, `description`, `status`, `admin_response`, `created_at`, `resolved_at`) VALUES
(1, 1, 'Irrigation', 'Canal water rotation delayed by 8 days in Haveli sub-division, affecting flowering soybean crops.', 'In Progress', 'Forwarded to local irrigation engineer division for scheduling review.', '2026-09-10 10:30:00', NULL),
(2, 2, 'Marketplace', 'Buyer delayed payment settlement for accepted enquiry.', 'Resolved', 'Assisted in mediating contact between buyer and farmer; payment confirmed released.', '2026-09-05 14:15:00', '2026-09-08 16:00:00');

-- Government Schemes (Official Central and State schemes)
INSERT INTO `schemes` (`scheme_id`, `scheme_name`, `description`, `eligibility`, `benefits`, `required_documents`, `application_process`, `official_source`, `updated_date`) VALUES
(1, 'PM-KISAN (Pradhan Mantri Kisan Samman Nidhi)', 'Central sector scheme providing income support to all landholding farmer families across India.', 'All landholding farmer families with cultivable land in their name (subject to exclusion criteria).', 'Direct financial benefit of Rs. 6,000 per year transferred in 3 equal installments of Rs. 2,000 directly into bank accounts.', 'Aadhaar Card, Land Records (7/12 extract), Bank Account details linked with Aadhaar.', 'Apply online through pmkisan.gov.in or visit common service centers (CSC).', 'https://pmkisan.gov.in', '2026-08-01'),
(2, 'PMFBY (Pradhan Mantri Fasal Bima Yojana)', 'Comprehensive crop insurance against non-preventable natural risks from pre-sowing to post-harvest.', 'All farmers including sharecroppers and tenant farmers growing notified crops in notified areas.', 'Subsidized premium (2% for Kharif, 1.5% for Rabi, 5% for commercial/horticultural crops); full sum insured coverage.', 'Aadhaar card, 7/12 extract, sowing certificate, bank passbook copy.', 'Apply through National Crop Insurance Portal (pmfby.gov.in), banks, or local agriculture office.', 'https://pmfby.gov.in', '2026-08-15'),
(3, 'Soil Health Card Scheme', 'Provides soil testing reports and customized fertilizer recommendations to optimize farm yields and soil vitality.', 'All farmers possessing agricultural land in participating states.', 'Free comprehensive testing of 12 soil health parameters (N, P, K, pH, EC, micronutrients) every 3 years with dosage advice.', 'Identity proof, land parcel identification number / 7/12 extract.', 'Soil samples collected by state agriculture department or local Krishi Vigyan Kendra (KVK).', 'https://soilhealth.dac.gov.in', '2026-07-20'),
(4, 'Magel Tyala Shettale (Farm Pond Scheme)', 'Maharashtra state government scheme offering subsidies to construct individual farm ponds for rainwater harvesting.', 'Farmers owning at least 0.60 hectare of land with clear title and no prior water harvesting subsidy.', 'Financial subsidy up to Rs. 50,000 directly credited into farmer bank accounts upon geo-tagged pond completion.', '7/12 extract, 8-A extract, Aadhaar card, bank passbook, self-declaration.', 'Apply through Aaple Sarkar DBT portal (mahadbt.maharashtra.gov.in).', 'https://mahadbt.maharashtra.gov.in', '2026-08-10');

-- Expert Consultations
INSERT INTO `expert_questions` (`question_id`, `farmer_id`, `expert_id`, `question`, `answer`, `status`, `created_at`, `answered_at`) VALUES
(1, 1, 1, 'My soybean crop is in flowering stage and I noticed yellowing on upper leaves. What micro-nutrient spray should I use?', 'Apply a foliar spray of Chelated Zinc (0.5 g/L) along with 19:19:19 water soluble fertilizer (5 g/L) during clear morning hours.', 'Answered', '2026-09-12 09:00:00', '2026-09-12 16:30:00'),
(2, 2, NULL, 'Whitefly count is increasing in cotton field. Should I spray imidacloprid or use yellow sticky traps?', NULL, 'Pending', '2026-09-16 11:20:00', NULL);
