-- ===========================================
-- Alter Table: pesanan
-- Add iduser column as Foreign Key to user table
-- ===========================================

-- Step 1: Tambah kolom iduser
ALTER TABLE pesanan
ADD COLUMN iduser INT NOT NULL
AFTER idpesanan;

-- Step 2: Buat Foreign Key Constraint
ALTER TABLE pesanan
ADD CONSTRAINT fk_pesanan_user
FOREIGN KEY (iduser) REFERENCES user(iduser)
ON DELETE RESTRICT
ON UPDATE CASCADE;

-- Step 3: Index untuk performa query
CREATE INDEX idx_pesanan_iduser ON pesanan(iduser);

-- ===========================================
-- Opsi: Kalau mau tambah customer_email juga
-- ===========================================

ALTER TABLE pesanan
ADD COLUMN customer_email VARCHAR(255) NULL
AFTER channel;
