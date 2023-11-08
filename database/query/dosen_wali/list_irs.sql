SELECT i.id as id, 
            i.sks_semester as sks_semester,
            i.mahasiswa_id as mahasiswa_id,
            i.riwayat_status_akademik_id as riwayat_status_akademik_id,
            i.semester_akademik_id as semester_akademik_id,
            i.created_at as created_at, 
            i.updated_at as updated_at,
            m.nim as nim,
            m.name as nama,
            sa.tahun_ajaran as tahun_ajaran,
            sa.semester as semester,
            m.phone_number as no_telp,
            i.status_code as status_code,
            i.file_scan_irs as file_scan_irs
            FROM irs i 
            LEFT JOIN mahasiswa m ON i.mahasiswa_id = m.id
            LEFT JOIN semester_akademik sa ON i.semester_akademik_id = sa.id
            LEFT JOIN riwayat_status_akademik rsa ON i.riwayat_status_akademik_id = rsa.id
            WHERE m.dosen_wali_id = 1