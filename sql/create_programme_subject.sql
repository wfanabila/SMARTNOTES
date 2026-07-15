-- create_programme_subject.sql
-- Create a programme_subject mapping table and helper INSERT examples.
-- Run this on the database where you want to restore the mapping (e.g. on your laptop).

DROP TABLE IF EXISTS `programme_subject`;

CREATE TABLE `programme_subject` (
  `programmeCode` VARCHAR(20) NOT NULL,
  `subjectID` INT NOT NULL,
  `semester` INT NOT NULL DEFAULT 1,
  PRIMARY KEY (`programmeCode`,`subjectID`),
  INDEX (`subjectID`)
  -- Optional foreign-key (uncomment only if `subject` table exists and you want FK):
  -- ,CONSTRAINT `fk_progsubj_subject` FOREIGN KEY (`subjectID`) REFERENCES `subject`(`subjectID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTE:
-- The table uses `subjectID` (numeric) to map subjects to programmes+semester.
-- Many installations identify subjects by `subjectCode` (like 'CSC270').
-- Use the helper INSERTs below to populate the mapping by looking up subjectID from subjectCode.

-- Example 1: add single mapping where the subject code is known
-- Replace 'CSC270' and semester as needed.
INSERT INTO programme_subject (programmeCode, subjectID, semester)
SELECT 'CSC270', subjectID, 1 FROM subject WHERE subjectCode = 'CSC270';

-- Example 2: add multiple subjects to programme 'CSC270' semester 1
-- Replace the subjectCode list with the actual codes for that semester.
INSERT INTO programme_subject (programmeCode, subjectID, semester)
SELECT 'CSC270', subjectID, 1 FROM subject WHERE subjectCode IN (
  'CSC270', 'CSC271' -- <-- replace with real subject codes
);

-- Example 3: bulk import from a CSV
-- If you have a CSV with columns (programmeCode,subjectCode,semester), you can load it into a temporary table
-- and convert subjectCode to subjectID. Example steps (run in MySQL):
-- CREATE TEMPORARY TABLE tmp_progsub (programmeCode VARCHAR(20), subjectCode VARCHAR(50), semester INT);
-- LOAD DATA LOCAL INFILE '/path/to/file.csv' INTO TABLE tmp_progsub FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n' (@pcode,@scode,@sem) SET programmeCode=@pcode, subjectCode=@scode, semester=@sem;
-- INSERT INTO programme_subject (programmeCode, subjectID, semester)
-- SELECT t.programmeCode, s.subjectID, t.semester FROM tmp_progsub t JOIN subject s ON s.subjectCode = t.subjectCode;
-- DROP TEMPORARY TABLE tmp_progsub;

-- VERIFICATION queries you can run after import:
-- 1) Count entries:
--    SELECT COUNT(*) FROM programme_subject;
-- 2) See mappings for a programme:
--    SELECT ps.programmeCode, ps.semester, s.subjectCode, s.subjectName FROM programme_subject ps JOIN subject s ON ps.subjectID = s.subjectID WHERE ps.programmeCode = 'CSC270' ORDER BY ps.semester, s.subjectCode;

-- End of script
