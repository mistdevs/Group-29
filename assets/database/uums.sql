-- uums.sql
DROP DATABASE IF EXISTS uums_db;
CREATE DATABASE uums_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE uums_db;

-- tables --

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
(1, NULL, NULL, 0.30, 0.00, '2023-01-01', NULL), -- electricity 0.30 per kWh
(2, NULL, NULL, 0.10, 5.00, '2023-01-01', NULL), -- water 0.10 per m3 + fixed 5.00
(3, NULL, NULL, 0.50, 0.00, '2023-01-01', NULL); -- gas 0.50 per m3

-- SQl quries --

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


-- SQL End --




