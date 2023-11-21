-- query total mahasiswa lulus skripsi
SELECT 
COUNT(m.id) as total
FROM skripsi s LEFT JOIN mahasiswa m ON m.id = s.mahasiswa_id
WHERE s.is_selesai = true AND s.is_lulus = true 

-- query total mahasiswa sedang mengikuti skripsi
SELECT
COUNT(m.id) as total
FROM skripsi s LEFT JOIN mahasiswa m ON m.id = s.mahasiswa_id
WHERE s.is_selesai = false

-- query total mahasiswa aktif
SELECT
COUNT(m.id) as total
FROM mahasiswa m WHERE m.status = 'Aktif'

-- query total mahasiswa lulus
SELECT
COUNT(m.id) as total
FROM mahasiswa m WHERE m.status = 'Lulus'

-- Grab IPK of each mahasiswa, calculate from KHS
WITH rentang_ipk AS (
    SELECT
        '0.0-0.5' AS ipk_range
    UNION SELECT '0.5-1.0'
    UNION SELECT '1.0-1.5'
    UNION SELECT '1.5-2.0'
    UNION SELECT '2.0-2.5'
    UNION SELECT '2.5-3.0'
    UNION SELECT '3.0-3.5'
    UNION SELECT '3.5-4.0'
)
SELECT
    ri.ipk_range,
    COALESCE(COUNT(outer_query.ipk_range), 0) as jumlah_mahasiswa
FROM
    rentang_ipk ri
LEFT JOIN (
    SELECT
        CASE 
            WHEN ipk >= 0.0 AND ipk < 0.5 THEN '0.0-0.5'
            WHEN ipk >= 0.5 AND ipk < 1.0 THEN '0.5-1.0'
            WHEN ipk >= 1.0 AND ipk < 1.5 THEN '1.0-1.5'
            WHEN ipk >= 1.5 AND ipk < 2.0 THEN '1.5-2.0'
            WHEN ipk >= 2.0 AND ipk < 2.5 THEN '2.0-2.5'
            WHEN ipk >= 2.5 AND ipk < 3.0 THEN '2.5-3.0'
            WHEN ipk >= 3.0 AND ipk < 3.5 THEN '3.0-3.5'
            WHEN ipk >= 3.5 AND ipk <= 4.0 THEN '3.5-4.0'
        END as ipk_range
    FROM (
        SELECT
            m.id as id,
            m.tahun_masuk as angkatan,
            SUM(k.ip_semester*i.sks_semester) / SUM(i.sks_semester) as ipk
        FROM 
            irs i 
            LEFT JOIN mahasiswa m ON i.mahasiswa_id = m.id 
            LEFT JOIN khs k ON k.irs_id = i.id  
        WHERE 
            i.status_code = 'approved' AND k.status_code = 'approved'
        GROUP BY 
            m.id, m.tahun_masuk
    ) as inner_query
) as outer_query ON ri.ipk_range = outer_query.ipk_range
GROUP BY 
    ri.ipk_range
ORDER BY 
    ri.ipk_range;