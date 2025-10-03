-- Add price column to appointment_services table
ALTER TABLE `appointment_services` ADD `price` DECIMAL(10, 2) NOT NULL AFTER `service_id`;
