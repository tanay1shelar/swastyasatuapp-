-- Swasthya Setu HMCMS Seed Data

INSERT INTO medicines (id, name, category, batch_no, expiry_date, stock_qty, created_at) VALUES
(1, 'Paracetamol 500mg', 'Tablet', 'BCH-1001', '2028-12-31', 1500, NOW()),
(2, 'Cetirizine 10mg', 'Tablet', 'BCH-1002', '2027-11-20', 120, NOW()),
(3, 'Amoxicillin 500mg', 'Capsule', 'BCH-1003', '2028-05-15', 850, NOW())
ON DUPLICATE KEY UPDATE stock_qty=VALUES(stock_qty);
