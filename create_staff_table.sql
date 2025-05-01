CREATE TABLE IF NOT EXISTS `bnhs_staff` (
  `staff_id` VARCHAR(50) PRIMARY KEY,
  `staff_name` VARCHAR(200) NOT NULL,
  `staff_email` VARCHAR(200) UNIQUE NOT NULL,
  `staff_password` VARCHAR(200) NOT NULL
); 