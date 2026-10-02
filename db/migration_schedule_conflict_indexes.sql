-- Migration: Add indexes for student and teacher schedule conflict checking
ALTER TABLE class_members ADD INDEX idx_member_day_time (student_id, day, start_time, end_time);
ALTER TABLE class_members ADD INDEX idx_teacher_day_time (class_id, day, start_time, end_time);
