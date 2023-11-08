SELECT k.id as id, 
k.ip_semester as ip_semester,
k.mahasiswa_id as mahasiswa_id,
k.riwayat_status_akademik_id as riwayat_status_akademik_id,
k.semester_akademik_id as semester_akademik_id,
k.created_at as created_at, 
k.updated_at as updated_at,
m.nim as nim,
m.name as nama,
sa.tahun_ajaran as tahun_ajaran,
sa.semester as semester,
m.phone_number as no_telp,
k.status_code as status_code,
k.file_scan_khs as file_scan_khs
FROM khs k 
LEFT JOIN mahasiswa m ON k.mahasiswa_id = m.id
LEFT JOIN semester_akademik sa ON k.semester_akademik_id = sa.id
LEFT JOIN riwayat_status_akademik rsa ON k.riwayat_status_akademik_id = rsa.id
WHERE m.dosen_wali_id = 1