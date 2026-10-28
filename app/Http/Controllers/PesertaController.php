<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PesertaController extends Controller
{
    private function getPdo()
    {
        return DB::connection()->getPdo();
    }

    public function index(Request $request)
    {
        try {
            $pdo = $this->getPdo();

            $stmtProdi = $pdo->prepare('SELECT id, nama_program FROM program_pelatihan ORDER BY nama_program');
            $stmtProdi->execute();
            $daftarProgram = $stmtProdi->fetchAll(\PDO::FETCH_ASSOC);

            $sql = 'SELECT p.id, p.kode_peserta, p.nama_peserta, p.email, p.usia, pr.nama_program, p.program_pelatihan_id 
                    FROM peserta p 
                    JOIN program_pelatihan pr ON pr.id = p.program_pelatihan_id 
                    WHERE 1=1';
            $params = [];

            if ($request->filled('cari')) {
                $keyword = '%' . trim(strip_tags((string) $request->query('cari'))) . '%';
                $sql .= ' AND (p.kode_peserta LIKE :c1 OR p.nama_peserta LIKE :c2 OR p.email LIKE :c3)';
                $params['c1'] = $keyword;
                $params['c2'] = $keyword;
                $params['c3'] = $keyword;
            }

            if ($request->filled('program_pelatihan_id')) {
                $sql .= ' AND p.program_pelatihan_id = :filter_prodi';
                $params['filter_prodi'] = (int) $request->query('program_pelatihan_id');
            }

            $sql .= ' ORDER BY p.id DESC';
            $stmtPeserta = $pdo->prepare($sql);
            $stmtPeserta->execute($params);
            $daftarPeserta = $stmtPeserta->fetchAll(\PDO::FETCH_ASSOC);

            return view('peserta.index', compact('daftarPeserta', 'daftarProgram'));
        } catch (Throwable $error) {
            report($error);
            return response('Terjadi kesalahan sistem saat membaca data.', 500);
        }
    }

    public function store(Request $request)
    {
        // Konversi ke string agar tidak melempar TypeError saat input kosong
        $request->merge([
            'kode_peserta' => trim(strip_tags((string) $request->input('kode_peserta'))),
            'nama_peserta' => trim(strip_tags((string) $request->input('nama_peserta'))),
            'email' => trim(strip_tags((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'kode_peserta' => ['required', 'string', 'alpha_num', 'size:10'],
            'nama_peserta' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'usia' => ['required', 'integer', 'between:17,60'],
            'program_pelatihan_id' => ['required', 'integer', 'exists:program_pelatihan,id'],
        ]);

        try {
            $pdo = $this->getPdo();

            $stmtCek = $pdo->prepare('SELECT COUNT(*) FROM peserta WHERE kode_peserta = :kode OR email = :email');
            $stmtCek->execute(['kode' => $data['kode_peserta'], 'email' => $data['email']]);
            if ((int) $stmtCek->fetchColumn() > 0) {
                return back()->withInput()->with('gagal', 'Kode Peserta atau Email sudah terdaftar.');
            }

            // Menyertakan created_at dan updated_at secara eksplisit
            $stmtInsert = $pdo->prepare(
                'INSERT INTO peserta (kode_peserta, nama_peserta, email, usia, program_pelatihan_id, created_at, updated_at) 
                 VALUES (:kode, :nama, :email, :usia, :prodi_id, NOW(), NOW())'
            );
            $stmtInsert->execute([
                'kode' => $data['kode_peserta'],
                'nama' => $data['nama_peserta'],
                'email' => $data['email'],
                'usia' => $data['usia'],
                'prodi_id' => $data['program_pelatihan_id'],
            ]);

            return redirect()->route('peserta.index')->with('berhasil', 'Data peserta berhasil ditambahkan.');
        } catch (Throwable $error) {
            report($error);
            return back()->withInput()->with('gagal', 'Gagal menyimpan data.');
        }
    }

    public function edit($id)
    {
        try {
            $pdo = $this->getPdo();

            $stmt = $pdo->prepare('SELECT * FROM peserta WHERE id = :id');
            $stmt->execute(['id' => (int) $id]);
            $peserta = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$peserta) {
                return redirect()->route('peserta.index')->with('gagal', 'Data peserta tidak ditemukan.');
            }

            $stmtProdi = $pdo->prepare('SELECT id, nama_program FROM program_pelatihan ORDER BY nama_program');
            $stmtProdi->execute();
            $daftarProgram = $stmtProdi->fetchAll(\PDO::FETCH_ASSOC);

            return view('peserta.edit', compact('peserta', 'daftarProgram'));
        } catch (Throwable $error) {
            report($error);
            return response('Gagal memuat halaman edit.', 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'kode_peserta' => trim(strip_tags((string) $request->input('kode_peserta'))),
            'nama_peserta' => trim(strip_tags((string) $request->input('nama_peserta'))),
            'email' => trim(strip_tags((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'kode_peserta' => ['required', 'string', 'alpha_num', 'size:10'],
            'nama_peserta' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'usia' => ['required', 'integer', 'between:17,60'],
            'program_pelatihan_id' => ['required', 'integer', 'exists:program_pelatihan,id'],
        ]);

        try {
            $pdo = $this->getPdo();

            $stmtCek = $pdo->prepare('SELECT COUNT(*) FROM peserta WHERE (kode_peserta = :kode OR email = :email) AND id != :id');
            $stmtCek->execute(['kode' => $data['kode_peserta'], 'email' => $data['email'], 'id' => (int) $id]);
            if ((int) $stmtCek->fetchColumn() > 0) {
                return back()->withInput()->with('gagal', 'Kode Peserta atau Email sudah digunakan oleh peserta lain.');
            }

            $stmtUpdate = $pdo->prepare(
                'UPDATE peserta SET kode_peserta = :kode, nama_peserta = :nama, email = :email, usia = :usia, program_pelatihan_id = :prodi_id, updated_at = NOW() WHERE id = :id'
            );
            $stmtUpdate->execute([
                'kode' => $data['kode_peserta'],
                'nama' => $data['nama_peserta'],
                'email' => $data['email'],
                'usia' => $data['usia'],
                'prodi_id' => $data['program_pelatihan_id'],
                'id' => (int) $id,
            ]);

            return redirect()->route('peserta.index')->with('berhasil', 'Data peserta berhasil diperbarui.');
        } catch (Throwable $error) {
            report($error);
            return back()->withInput()->with('gagal', 'Gagal memperbarui data.');
        }
    }

    public function destroy($id)
    {
        try {
            $pdo = $this->getPdo();

            $stmtDelete = $pdo->prepare('DELETE FROM peserta WHERE id = :id');
            $stmtDelete->execute(['id' => (int) $id]);

            return redirect()->route('peserta.index')->with('berhasil', 'Data peserta berhasil dihapus.');
        } catch (Throwable $error) {
            report($error);
            return redirect()->route('peserta.index')->with('gagal', 'Gagal menghapus data.');
        }
    }
}