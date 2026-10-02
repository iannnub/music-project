-- Migration: Menambahkan UNIQUE constraint pada class_members (class_id, student_id)

-- 1. Update relasi foreign key pada attendances ke ID terkecil jika ada duplikat
UPDATE attendances a
JOIN class_members cm ON a.schedule_id = cm.id
JOIN (
    SELECT class_id, student_id, MIN(id) as keep_id
    FROM class_members
    GROUP BY class_id, student_id
    HAVING COUNT(*) > 1
) dup ON cm.class_id = dup.class_id AND cm.student_id = dup.student_id
SET a.schedule_id = dup.keep_id
WHERE cm.id != dup.keep_id;

-- 2. Update relasi foreign key pada teacher_attendances ke ID terkecil jika ada duplikat
UPDATE teacher_attendances ta
JOIN class_members cm ON ta.schedule_id = cm.id
JOIN (
    SELECT class_id, student_id, MIN(id) as keep_id
    FROM class_members
    GROUP BY class_id, student_id
    HAVING COUNT(*) > 1
) dup ON cm.class_id = dup.class_id AND cm.student_id = dup.student_id
SET ta.schedule_id = dup.keep_id
WHERE cm.id != dup.keep_id;

-- 3. Hapus baris duplikat dari class_members
DELETE cm FROM class_members cm
JOIN (
    SELECT class_id, student_id, MIN(id) as keep_id
    FROM class_members
    GROUP BY class_id, student_id
    HAVING COUNT(*) > 1
) dup ON cm.class_id = dup.class_id AND cm.student_id = dup.student_id
WHERE cm.id != dup.keep_id;

-- 4. Tambahkan UNIQUE constraint
ALTER TABLE class_members ADD UNIQUE KEY uniq_class_student (class_id, student_id);
