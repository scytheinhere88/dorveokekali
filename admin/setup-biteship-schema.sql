-- =====================================================
-- BITESHIP SHIPPING INTEGRATION SCHEMA
-- =====================================================

-- Settings table (if not exists)
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Biteship default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('biteship_api_key', 'biteship_live.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoiRG9ydmUuaWQiLCJ1c2VySWQiOiI2OTI4NDVhNDM4MzQ5ZjAyZjdhM2VhNDgiLCJpYXQiOjE3NjQ2NTYwMjV9.xmkeeT2ghfHPe7PItX5HJ0KptlC5xbIhL1TlHWn6S1U'),
('biteship_environment', 'production'),
('biteship_webhook_secret', ''),
('store_name', 'Dorve.id Official Store'),
('store_phone', '+62-813-7737-8859'),
('store_address', 'Jakarta, Indonesia'),
('store_city', 'Jakarta'),
('store_province', 'DKI Jakarta'),
('store_postal_code', '12345'),
('store_country', 'ID')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Extend orders table with shipping columns
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS fulfillment_status ENUM('new', 'waiting_print', 'waiting_pickup', 'in_transit', 'delivered', 'cancelled', 'returned') DEFAULT 'new' AFTER payment_status,
ADD COLUMN IF NOT EXISTS shipping_courier VARCHAR(100) NULL AFTER fulfillment_status,
ADD COLUMN IF NOT EXISTS shipping_service VARCHAR(100) NULL AFTER shipping_courier,
ADD COLUMN IF NOT EXISTS shipping_cost DECIMAL(15,2) DEFAULT 0 AFTER shipping_service,
ADD COLUMN IF NOT EXISTS tracking_number VARCHAR(255) NULL AFTER shipping_cost,
ADD COLUMN IF NOT EXISTS notes TEXT NULL AFTER tracking_number,
ADD INDEX idx_fulfillment (fulfillment_status),
ADD INDEX idx_tracking (tracking_number);

-- Order addresses table
CREATE TABLE IF NOT EXISTS order_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    type ENUM('billing', 'shipping') NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address_line TEXT NOT NULL,
    district VARCHAR(255),
    city VARCHAR(255) NOT NULL,
    province VARCHAR(255) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(5) DEFAULT 'ID',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Biteship shipments table
CREATE TABLE IF NOT EXISTS biteship_shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    biteship_order_id VARCHAR(255) NOT NULL UNIQUE,
    courier_company VARCHAR(100) NOT NULL COMMENT 'e.g. jne, jnt, sicepat',
    courier_name VARCHAR(100) NOT NULL COMMENT 'e.g. JNE, J&T Express',
    courier_service_name VARCHAR(100) NOT NULL COMMENT 'e.g. REG, EZ',
    courier_service_code VARCHAR(100) NULL,
    rate_id VARCHAR(255) NULL,
    shipping_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
    insurance_cost DECIMAL(15,2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'pending' COMMENT 'Biteship status: pending, confirmed, allocated, picking_up, picked, dropping_off, delivered, cancelled, etc.',
    waybill_id VARCHAR(255) NULL COMMENT 'Tracking number',
    pickup_code VARCHAR(50) NULL,
    delivery_date DATETIME NULL,
    pickup_time VARCHAR(100) NULL,
    destination_province VARCHAR(255),
    destination_city VARCHAR(255),
    destination_postal_code VARCHAR(20),
    origin_province VARCHAR(255),
    origin_city VARCHAR(255),
    origin_postal_code VARCHAR(20),
    weight_kg DECIMAL(10,2) DEFAULT 0,
    raw_response TEXT COMMENT 'Full JSON response from Biteship',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_biteship_id (biteship_order_id),
    INDEX idx_waybill (waybill_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Biteship webhook logs
CREATE TABLE IF NOT EXISTS biteship_webhook_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event VARCHAR(100) NOT NULL,
    biteship_order_id VARCHAR(255) NULL,
    payload TEXT NOT NULL COMMENT 'Full JSON payload',
    processed TINYINT(1) DEFAULT 0,
    error_message TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event (event),
    INDEX idx_biteship_id (biteship_order_id),
    INDEX idx_processed (processed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Shipping rate cache (optional, for performance)
CREATE TABLE IF NOT EXISTS shipping_rate_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cache_key VARCHAR(255) NOT NULL UNIQUE,
    rates_json TEXT NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_key_expiry (cache_key, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
