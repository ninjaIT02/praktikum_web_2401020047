<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Data Peserta Pelatihan</title>
</head>
<body>
    <h1>Sistem Data Peserta Pelatihan</h1>

    {{-- Feedback Operasi --}}
    @if(session('berhasil'))
        <p><strong>[BERHASIL]</strong> {{ session('berhasil') }}</p>
    @endif

    @if(session('gagal'))
        <p><strong>[GAGAL]</strong> {{ session('gagal') }}</p>
    @endif

    {{-- Pesan Error Validasi Server-Side Laravel --}}
    @if($errors->any())
        <div>
            <strong>Terjadi Kesalahan Input:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Tambah Data --}}
    <h2>Form Tambah Peserta</h2>
    <form action="{{ route('peserta.store') }}" method="POST">
        @csrf
        <p>
            <label>Kode Peserta (10 Karakter Alfanumerik):</label><br>
            <input type="text" name="kode_peserta" value="{{ old('kode_peserta') }}">
        </p>
        <p>
            <label>Nama Peserta:</label><br>
            <input type="text" name="nama_peserta" value="{{ old('nama_peserta') }}">
        </p>
        <p>
            <label>Email:</label><br>
            <input type="email" name="email" value="{{ old('email') }}">
        </p>
        <p>
            <label>Usia (17 - 60 Tahun):</label><br>
            <input type="number" name="usia" value="{{ old('usia') }}">
        </p>
        <p>
            <label>Program Pelatihan:</label><br>
            <select name="program_pelatihan_id">
                <option value="">-- Pilih Program --</option>
                @foreach($daftarProgram as $program)
                    <option value="{{ $program['id'] }}" {{ old('program_pelatihan_id') == $program['id'] ? 'selected' : '' }}>
                        {{ $program['nama_program'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Tambah Data Peserta</button>
    </form>

    <hr>

    {{-- Form Pencarian dan Filter --}}
    <h2>Pencarian dan Filter</h2>
    <form action="{{ route('peserta.index') }}" method="GET">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari Kode, Nama, atau Email...">
        
        <select name="program_pelatihan_id">
            <option value="">-- Semua Program Pelatihan --</option>
            @foreach($daftarProgram as $program)
                <option value="{{ $program['id'] }}" {{ request('program_pelatihan_id') == $program['id'] ? 'selected' : '' }}>
                    {{ $program['nama_program'] }}
                </option>
            @endforeach
        </select>
        
        <button type="submit">Cari / Filter</button>
        <a href="{{ route('peserta.index') }}">Reset</a>
    </form>

    <hr>

    {{-- Tabel Daftar Peserta --}}
    <h2>Daftar Peserta Pelatihan</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Peserta</th>
                <th>Nama Peserta</th>
                <th>Email</th>
                <th>Usia</th>
                <th>Status Usia</th>
                <th>Program Pelatihan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($daftarPeserta as $peserta)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $peserta['kode_peserta'] }}</td>
                    <td>{{ $peserta['nama_peserta'] }}</td>
                    <td>{{ $peserta['email'] }}</td>
                    <td>{{ $peserta['usia'] }} tahun</td>
                    <td>{{ statusUsia((int)$peserta['usia']) }}</td>
                    <td>{{ $peserta['nama_program'] }}</td>
                    <td>
                        <a href="{{ route('peserta.edit', $peserta['id']) }}">Edit</a>
                        
                        <form action="{{ route('peserta.destroy', $peserta['id']) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Tidak ada data peserta pelatihan yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>