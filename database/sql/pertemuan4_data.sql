USE praktikum_web_2401020047;

INSERT INTO program_studi (nama_prodi) VALUES
    ('Teknik Komputer'),
    ('Teknik Elektro');

INSERT INTO mahasiswa
    (nim, nama, email, usia, program_studi_id)
VALUES
    ('2401020001', 'Citra Lestari', 'citra@example.com', 20, 1),
    ('2401020002', 'Dewi Anggraini', 'dewi@example.com', 19, 1),
    ('2401020003', 'Eko Prasetyo', 'eko@example.com', 21, 2),
    ('2401020099', 'Data Sementara', 'temp@example.com', 18, 2);

UPDATE mahasiswa
SET email = 'citra.lestari@example.com'
WHERE nim = '2401020001';

DELETE FROM mahasiswa
WHERE nim = '2401020099';

SELECT m.nim, m.nama, m.email, m.usia, p.nama_prodi
FROM mahasiswa AS m
JOIN program_studi AS p
    ON p.id = m.program_studi_id
ORDER BY m.nim;