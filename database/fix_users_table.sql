-- Add the missing is_active column to the users table
USE motorcycle_parts_db;

-- Add is_active column if it doesn't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE AFTER is_admin;

-- Update existing users to be active
UPDATE users SET is_active = TRUE WHERE is_active IS NULL;
