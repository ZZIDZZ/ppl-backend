-- calculate total ipk mahasiswa
SELECT
	ROUND((SUM(k.ip_semester*i.sks_semester) / SUM(i.sks_semester))::numeric, 2) as ipk,
	SUM(i.sks_semester) AS total_sks
FROM 
    irs i 
    LEFT JOIN mahasiswa m ON i.mahasiswa_id = m.id 
    LEFT JOIN khs k ON k.irs_id = i.id  
WHERE 
    i.status_code = 'approved' AND k.status_code = 'approved' AND m.id = 1
GROUP BY 
    m.id, m.tahun_masuk

-- calculate last semester_akademik picked by mahasiswa
SELECT dummy.*, sa.tahun_ajaran as tahun_ajaran,
CASE WHEN sa.semester = 1 THEN 'Ganjil' ELSE 'Genap' END as semester_akademik FROM (
    SELECT
        m.id as id,
        m.tahun_masuk as angkatan,
        MAX(sa.id) as semester_akademik_id
    FROM
        irs i
        LEFT JOIN mahasiswa m ON i.mahasiswa_id = m.id
        LEFT JOIN semester_akademik sa ON i.semester_akademik_id = sa.id
    WHERE
        i.status_code = 'approved' AND m.id = 1
    GROUP BY
        m.id, m.tahun_masuk
) as dummy LEFT JOIN semester_akademik sa ON dummy.semester_akademik_id = sa.id
-- calculate at what semester mahasiswa currently
SELECT
    m.id as id,
    m.tahun_masuk as angkatan,
    COUNT(i.id) as semester
FROM
    irs i
    LEFT JOIN mahasiswa m ON i.mahasiswa_id = m.id
WHERE
    i.status_code = 'approved' AND m.id = 1
GROUP BY
    m.id, m.tahun_masuk

-- Get Latest PKL status
SELECT
    p.id AS id,
    p.irs_id AS irs_id,
    p.nilai AS nilai,
    p.status_code AS status_code,
    p.is_selesai AS is_selesai,
    p.is_lulus AS is_lulus,
    p.created_at AS created_at,
    p.updated_at AS updated_at,
    CASE
        WHEN p.id IS NOT NULL THEN TRUE
        ELSE FALSE
    END AS is_diambil
FROM
    pkl p
    LEFT JOIN irs i ON p.irs_id = i.id
    LEFT JOIN mahasiswa m ON i.mahasiswa_id = m.id
WHERE
    m.id = 1 AND p.status_code = 'approved'
ORDER BY
    p.id DESC
LIMIT 1


