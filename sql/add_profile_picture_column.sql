-- Add profilePicture and bio columns to student table
-- Run this migration to enable profile picture upload and bio features

ALTER TABLE student ADD COLUMN profilePicture VARCHAR(255) DEFAULT NULL AFTER studentEmail;
ALTER TABLE student ADD COLUMN bio LONGTEXT DEFAULT NULL AFTER profilePicture;
