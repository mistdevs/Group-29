-- uums.sql
DROP DATABASE IF EXISTS uums_db;
CREATE DATABASE uums_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE uums_db;

-- tables --
CREATE TABLE `user` (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Admin','MeterReader','BillingClerk','Manager') NOT NULL,
  name VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tariff (
  tariff_id INT AUTO_INCREMENT PRIMARY KEY,
  service_id INT NOT NULL,
  slab_start DECIMAL(12,3),
  slab_end DECIMAL(12,3),
  rate DECIMAL(12,4) NOT NULL,
  fixed_charge DECIMAL(12,2) DEFAULT 0,
  effective_from DATE,
  effective_to DATE,
  FOREIGN KEY (service_id) REFERENCES service(service_id) ON DELETE CASCADE
);

CREATE TABLE bill (
  bill_id INT AUTO_INCREMENT PRIMARY KEY,
  meter_id INT NOT NULL,
  period_start DATE NOT NULL,
  period_end DATE NOT NULL,
  consumption DECIMAL(12,3) NOT NULL,
  amount_before_tax DECIMAL(12,2) NOT NULL,
  tax DECIMAL(12,2) DEFAULT 0,
  total_amount DECIMAL(12,2) NOT NULL,
  outstanding DECIMAL(12,2) NOT NULL DEFAULT 0,
  due_date DATE NOT NULL,
  status ENUM('Generated','Paid','Partial','Overdue') DEFAULT 'Generated',
  generated_by INT,
  UNIQUE (meter_id, period_start, period_end),
  FOREIGN KEY (meter_id) REFERENCES meter(meter_id),
  FOREIGN KEY (generated_by) REFERENCES `user`(user_id)
);

CREATE TABLE meter_reading (
  reading_id INT AUTO_INCREMENT PRIMARY KEY,
  meter_id INT NOT NULL,
  reading_date DATE NOT NULL,
  reading_value DECIMAL(12,3) NOT NULL,
  recorded_by INT,
  CONSTRAINT uc_meter_date UNIQUE (meter_id, reading_date),
  FOREIGN KEY (meter_id) REFERENCES meter(meter_id) ON DELETE CASCADE,
  FOREIGN KEY (recorded_by) REFERENCES `user`(user_id)
);

CREATE TABLE payment (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  bill_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  method ENUM('Cash','Card','Online') NOT NULL,
  payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  recorded_by INT,
  reference VARCHAR(100),
  FOREIGN KEY (bill_id) REFERENCES bill(bill_id) ON DELETE CASCADE,
  FOREIGN KEY (recorded_by) REFERENCES `user`(user_id)
);

-- values --

-- SAMPLE DATA: Tariffs (simple fixed rate entries)
INSERT INTO tariff (service_id, slab_start, slab_end, rate, fixed_charge, effective_from, effective_to) VALUES
(1, NULL, NULL, 0.30, 0.00, '2025-01-01', NULL), 
(2, NULL, NULL, 0.10, 5.00, '2025-01-01', NULL), 
(3, NULL, NULL, 0.50, 0.00, '2025-01-01', NULL);

-- SAMPLE DATA: Readings (monthly)
INSERT INTO meter_reading (meter_id, reading_date, reading_value, recorded_by) VALUES
(1,'2025-07-01', 1200.000, 2),
(1,'2025-08-01', 1250.000, 2),
(1,'2025-09-01', 1305.000, 2),
(2,'2025-09-01', 350.000, 2),
(3,'2025-08-01', 340.000, 2),
(3,'2025-09-01', 560.000, 2),
(4,'2025-09-01', 220.000, 2),
(5,'2025-09-01', 45.000, 2),
(6,'2025-09-01', 30.000, 2),
(7,'2025-09-01', 900.000, 2),
(8,'2025-09-01', 370.000, 2),
(9,'2025-09-01', 200.000, 2),
(10,'2025-09-01', 80.000, 2);

INSERT INTO `user` (username, password_hash, role, name) VALUES
('admin', '$2y$10$e0NRyJv1OnmnuZgGf6zjSuoB.0Q0vR0pW6zEeZ8u8/o8aZ6Sg3f8e', 'Admin', 'System Administrator'),
('reader1', '$2y$10$e0NRyJv1OnmnuZgGf6zjSuoB.0Q0vR0pW6zEeZ8u8/o8aZ6Sg3f8e', 'MeterReader', 'Meter Reader 1'),
('clerk1', '$2y$10$e0NRyJv1OnmnuZgGf6zjSuoB.0Q0vR0pW6zEeZ8u8/o8aZ6Sg3f8e', 'BillingClerk', 'Billing Clerk');

-- SQl quries --

-- Basic function to calculate bill amount for a meter (simple: last - first * rate)

DELIMITER //
CREATE FUNCTION calc_bill_amount(meter INT, startDate DATE, endDate DATE)
RETURNS DECIMAL(12,2)
DETERMINISTIC
BEGIN
  DECLARE first_read DECIMAL(12,3);
  DECLARE last_read DECIMAL(12,3);
  DECLARE cons DECIMAL(12,3);
  DECLARE rate DECIMAL(12,4);
  DECLARE fixed DECIMAL(12,2);

  SELECT reading_value INTO first_read FROM meter_reading WHERE meter_id = meter AND reading_date = startDate LIMIT 1;
  SELECT reading_value INTO last_read FROM meter_reading WHERE meter_id = meter AND reading_date = endDate LIMIT 1;

  IF first_read IS NULL OR last_read IS NULL THEN
    RETURN 0.00;
  END IF;

  SET cons = last_read - first_read;
  SELECT t.rate, t.fixed_charge INTO rate, fixed FROM tariff t
    JOIN meter m ON m.service_id = t.service_id
    WHERE m.meter_id = meter
    ORDER BY t.effective_from DESC LIMIT 1;

  IF rate IS NULL THEN SET rate = 0; END IF;
  IF fixed IS NULL THEN SET fixed = 0; END IF;

  RETURN ROUND(cons * rate + fixed,2);
END;
//
DELIMITER ;

-- Stored Procedure to generate bill for a meter
DELIMITER //
CREATE PROCEDURE sp_generate_bill_for_meter(IN p_meter INT, IN p_start DATE, IN p_end DATE, IN p_generated_by INT)
BEGIN
  DECLARE amt DECIMAL(12,2) DEFAULT 0;
  DECLARE tax DECIMAL(12,2) DEFAULT 0;
  DECLARE total DECIMAL(12,2) DEFAULT 0;
  DECLARE cons DECIMAL(12,3) DEFAULT 0;
  DECLARE fr DECIMAL(12,3);
  DECLARE lr DECIMAL(12,3);

  SELECT reading_value INTO fr FROM meter_reading WHERE meter_id = p_meter AND reading_date = p_start LIMIT 1;
  SELECT reading_value INTO lr FROM meter_reading WHERE meter_id = p_meter AND reading_date = p_end LIMIT 1;

  IF fr IS NULL OR lr IS NULL OR lr < fr THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Missing or invalid readings for selected period.';
  END IF;

  SET cons = lr - fr;
  SET amt = calc_bill_amount(p_meter, p_start, p_end);
  SET tax = ROUND(amt * 0.0,2);
  SET total = amt + tax;

  INSERT INTO bill (meter_id, period_start, period_end, consumption, amount_before_tax, tax, total_amount, outstanding, due_date, status, generated_by)
  VALUES (p_meter, p_start, p_end, cons, amt, tax, total, total, DATE_ADD(p_end, INTERVAL 14 DAY), 'Generated', p_generated_by);
END;
//
DELIMITER ;
-- payment insert, reduce outstanding on bill and update status --
DELIMITER //
CREATE TRIGGER trg_after_payment_insert
AFTER INSERT ON payment
FOR EACH ROW
BEGIN
  UPDATE bill
  SET outstanding = outstanding - NEW.amount,
      status = CASE
                WHEN outstanding - NEW.amount <= 0 THEN 'Paid'
                WHEN outstanding - NEW.amount < total_amount THEN 'Partial'
                ELSE status
               END
  WHERE bill.bill_id = NEW.bill_id;
END;
//
DELIMITER ;
-- TRIGGER: before insert on bill to set outstanding = total_amount
DELIMITER //
CREATE TRIGGER trg_before_bill_insert
BEFORE INSERT ON bill
FOR EACH ROW
BEGIN
  SET NEW.outstanding = NEW.total_amount;
END;
//
DELIMITER ;

-- VIEWS
CREATE VIEW vw_unpaid_bills AS
SELECT b.bill_id, c.name AS customer, s.name AS service, b.period_start, b.period_end, b.total_amount, b.outstanding, b.due_date
FROM bill b
JOIN meter m ON b.meter_id = m.meter_id
JOIN customer c ON m.customer_id = c.customer_id
JOIN service s ON m.service_id = s.service_id
WHERE b.outstanding > 0;

CREATE VIEW vw_monthly_revenue AS
SELECT DATE_FORMAT(payment_date, '%Y-%m') AS month, SUM(amount) AS total_collected
FROM payment
GROUP BY DATE_FORMAT(payment_date, '%Y-%m');

-- Example: generate a few bills by calling the procedure for existing readings (so reports show something)
CALL sp_generate_bill_for_meter(1,'2025-07-01','2025-08-01',1);
CALL sp_generate_bill_for_meter(1,'2025-08-01','2025-09-01',1);
CALL sp_generate_bill_for_meter(3,'2025-08-01','2025-09-01',1);

-- Insert a payment for demo
INSERT INTO payment (bill_id, amount, method, recorded_by) VALUES (1, 150.00, 'Cash', 3);

-- SQL End --












