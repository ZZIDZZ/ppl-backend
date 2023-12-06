-- select total_sks and total_ipk from mahasiswa

SELECT m.name, m.jalur_masuk, m.status, SUM(i.sks_semester) as total_sks, SUM(k.ip_semester*i.sks_semester) / SUM(i.sks_semester) as total_ipk, p.nilai as nilai_pkl, s.nilai as nilai_skripsi
FROM mahasiswa m 
LEFT JOIN khs k ON k.mahasiswa_id = m.id 
LEFT JOIN irs i ON i.mahasiswa_id = m.id AND k.semester = i.semester
LEFT JOIN pkl p ON p.mahasiswa_id = m.id
LEFT JOIN skripsi s ON s.mahasiswa_id = m.id
WHERE tahun_masuk = 2021
GROUP BY m.name, m.jalur_masuk, m.status, p.nilai, s.nilai


-- statistic pkl for angkatan
SELECT SUM(has_not_pkl) AS total_not_pkl, SUM(has_pkl) AS total_pkl FROM (
SELECT 
    m.name, 
    CASE 
    WHEN p.id IS NOT NULL THEN 1 
    ELSE 0 END as has_pkl, 
    CASE WHEN p.id IS NULL THEN 1 
    ELSE 0 END as has_not_pkl
FROM mahasiswa m 
LEFT JOIN pkl p ON p.mahasiswa_id = m.id WHERE tahun_masuk = 2021
) dummy

-- statistic skripsi for angkatan
SELECT SUM(has_not_skripsi) AS total_not_skripsi, SUM(has_skripsi) AS total_skripsi FROM (
SELECT 
    m.name, 
    CASE 
    WHEN s.id IS NOT NULL THEN 1 
    ELSE 0 END as has_skripsi, 
    CASE WHEN s.id IS NULL THEN 1 
    ELSE 0 END as has_not_skripsi
FROM mahasiswa m
LEFT JOIN skripsi s ON s.mahasiswa_id = m.id WHERE tahun_masuk = 2021
) dummy