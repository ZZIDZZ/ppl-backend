-- Rekap mahasiswa yang sudah lulus dan belum PKL untuk semua angkatan 

SELECT 
    t.tahun_masuk,
    COALESCE(r.sudah_lulus, 0) AS sudah_lulus,
    COALESCE(r.belum_lulus, 0) AS belum_lulus
FROM 
    (SELECT DISTINCT tahun_masuk FROM mahasiswa ORDER BY tahun_masuk DESC LIMIT 7) t
LEFT JOIN (
    SELECT 
        m.tahun_masuk,
        SUM(CASE WHEN COALESCE(p.is_lulus, false) = true THEN 1 ELSE 0 END) AS sudah_lulus,
        SUM(CASE WHEN COALESCE(p.is_lulus, false) = false AND p.id IS NULL THEN 1 ELSE 0 END) AS belum_lulus
    FROM 
        mahasiswa m
    LEFT JOIN 
        pkl p ON p.mahasiswa_id = m.id
    GROUP BY 
        m.tahun_masuk, p.is_lulus
) r ON t.tahun_masuk = r.tahun_masuk
ORDER BY 
    t.tahun_masuk 
    

-- new query    
WITH RECURSIVE YearSequence AS (
  SELECT 2018 AS Year
  UNION ALL
  SELECT Year + 1
  FROM YearSequence
  WHERE Year < 2023
)

SELECT 
  ys.Year AS tahun_masuk,
  COALESCE(r.sudah_lulus, 0) AS sudah_lulus,
  COALESCE(r.belum_lulus, 0) AS belum_lulus
FROM 
  YearSequence ys
LEFT JOIN (
  SELECT 
    m.tahun_masuk,
    SUM(CASE WHEN COALESCE(p.nilai, 'X') IN ('A', 'B', 'C') THEN 1 ELSE 0 END) AS sudah_lulus,
    SUM(CASE WHEN COALESCE(p.nilai, 'X') = 'X' AND p.id IS NULL THEN 1 ELSE 0 END) AS belum_lulus
  FROM 
    mahasiswa m
  LEFT JOIN 
    pkl p ON p.mahasiswa_id = m.id
  GROUP BY 
    m.tahun_masuk
) r ON ys.Year = r.tahun_masuk
ORDER BY 
  ys.Year DESC;


-- for skripsi
WITH RECURSIVE YearSequence AS (
  SELECT 2018 AS Year
  UNION ALL
  SELECT Year + 1
  FROM YearSequence
  WHERE Year < 2023
)

SELECT 
  ys.Year AS tahun_masuk,
  COALESCE(r.sudah_lulus, 0) AS sudah_lulus,
  COALESCE(r.belum_lulus, 0) AS belum_lulus
FROM 
  YearSequence ys
LEFT JOIN (
  SELECT 
    m.tahun_masuk,
    SUM(CASE WHEN COALESCE(s.nilai, 'X') IN ('A', 'B', 'C') THEN 1 ELSE 0 END) AS sudah_lulus,
    SUM(CASE WHEN COALESCE(s.nilai, 'X') = 'X' AND s.id IS NULL THEN 1 ELSE 0 END) AS belum_lulus
  FROM 
    mahasiswa m
  LEFT JOIN 
    skripsi s ON s.mahasiswa_id = m.id
  GROUP BY 
    m.tahun_masuk
) r ON ys.Year = r.tahun_masuk
ORDER BY 
  ys.Year DESC;