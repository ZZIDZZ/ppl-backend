SELECT 
i.id as irs_id, 
p.id as pkl_id, 
i.semester as semester,
p.nilai as nilai
FROM irs i LEFT JOIN pkl p ON i.mahasiswa_id = p.mahasiswa_id AND i.semester = p.semester
WHERE p.mahasiswa_id=1 AND p.status_code = 'approved' AND i.status_code = 'approved'