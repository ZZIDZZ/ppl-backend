SELECT * FROM (
    SELECT semester.semester, 
    CASE 
    WHEN k.id IS NOT NULL THEN true 
    ELSE false END as is_khs, 
    CASE 
    WHEN p.id IS NOT NULL THEN true 
    ELSE false END as is_pkl,
    CASE 
    WHEN s.id IS NOT NULL THEN true 
    ELSE false END as is_skripsi,
    irs_mahasiswa.id, 
    irs_mahasiswa.sks_semester as sks_semester, 
    k.ip_semester as ip_semester,
    p.nilai as nilai_pkl,
    s.nilai as nilai_skripsi,
    sa.tahun_ajaran as tahun_ajaran, 
    sa.semester as semester_akademik
    FROM generate_series(1, 14) AS semester
    LEFT JOIN 
    (
        SELECT irs.*, ROW_NUMBER() OVER (ORDER BY id) as irs_number FROM irs WHERE mahasiswa_id=:mahasiswa_id ORDER BY id
    ) irs_mahasiswa 
    ON irs_mahasiswa.irs_number = semester.semester
    LEFT JOIN semester_akademik sa ON sa.id = irs_mahasiswa.semester_akademik_id
    LEFT JOIN khs k ON k.irs_id = irs_mahasiswa.id
    LEFT JOIN pkl p ON p.irs_id = irs_mahasiswa.id
    LEFT JOIN skripsi s ON s.irs_id = irs_mahasiswa.id
    ORDER BY semester.semester
    ) as dummy


    SELECT * FROM (
    SELECT semester.semester, 
    CASE 
    WHEN k.id IS NOT NULL THEN true 
    ELSE false END as is_khs, 
    CASE 
    WHEN p.id IS NOT NULL THEN true 
    ELSE false END as is_pkl,
    CASE 
    WHEN s.id IS NOT NULL THEN true 
    ELSE false END as is_skripsi,
    irs_mahasiswa.id, 
    irs_mahasiswa.sks_semester as sks_semester, 
    k.ip_semester as ip_semester,
    p.nilai as nilai_pkl,
    s.nilai as nilai_skripsi,
    sa.tahun_ajaran as tahun_ajaran, 
    sa.semester as semester_akademik
    FROM generate_series(1, 14) AS semester
    LEFT JOIN 
    (
        SELECT irs.*, ROW_NUMBER() OVER (ORDER BY id) as irs_number FROM irs WHERE mahasiswa_id=1 ORDER BY id
    ) irs_mahasiswa 
    ON irs_mahasiswa.irs_number = semester.semester
    LEFT JOIN semester_akademik sa ON sa.id = irs_mahasiswa.semester_akademik_id
    LEFT JOIN khs k ON k.irs_id = irs_mahasiswa.id
    LEFT JOIN pkl p ON p.irs_id = irs_mahasiswa.id
    LEFT JOIN skripsi s ON s.irs_id = irs_mahasiswa.id
    ORDER BY semester.semester
    ) as dummy