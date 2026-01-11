-- uums.sql
DROP DATABASE IF EXISTS uums_db;
CREATE DATABASE uums_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE uums_db;

-- tables --

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

