SELECT p.id as id, 
p.nilai as nilai,
p.mahasiswa_id as mahasiswa_id,
p.riwayat_status_akademik_id as riwayat_status_akademik_id,
p.semester_akademik_id as semester_akademik_id,
p.created_at as created_at, 
p.updated_at as updated_at,
m.nim as nim,
m.name as nama,
sa.tahun_ajaran as tahun_ajaran,
sa.semester as semester,
m.phone_number as no_telp,
p.status_code as status_code,
p.tanggal_selesai as tanggal_selesai,
p.is_lulus as is_lulus,
p.file_pkl as file_pkl
FROM pkl p 
LEFT JOIN mahasiswa m ON p.mahasiswa_id = m.id
LEFT JOIN semester_akademik sa ON p.semester_akademik_id = sa.id
LEFT JOIN riwayat_status_akademik rsa ON p.riwayat_status_akademik_id = rsa.id
WHERE m.dosen_wali_id = 1