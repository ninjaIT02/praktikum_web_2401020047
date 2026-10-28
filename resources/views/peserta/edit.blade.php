<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Peserta Pelatihan</title>
</head>
<body>
    <h1>Edit Data Peserta Pelatihan</h1>

    @if(session('gagal'))
        <p><strong>[GAGAL]</strong> {{ session('gagal') }}</p>
    @endif

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

    <form action="{{ route('peserta.update', $peserta['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <p>
            <label>Kode Peserta:</label><br>
            <input type="text" name="kode_peserta" value="{{ old('kode_peserta', $peserta['kode_peserta']) }}" required maxlength="10">
        </p>
        <p>
            <label>Nama Peserta:</label><br>
            <input type="text" name="nama_peserta" value="{{ old('nama_peserta', $peserta['nama_peserta']) }}" required>
        </p>
        <p>
            <label>Email:</label><br>
            <input type="email" name="email" value="{{ old('email', $peserta['email']) }}" required>
        </p>
        <p>
            <label>Usia (17 - 60 Tahun):</label><br>
            <input type="number" name="usia" value="{{ old('usia', $peserta['usia']) }}" min="17" max="60" required>
        </p>
        <p>
            <label>Program Pelatihan:</label><br>
            <select name="program_pelatihan_id" required>
                @foreach($daftarProgram as $program)
                    <option value="{{ $program['id'] }}" {{ old('program_pelatihan_id', $peserta['program_pelatihan_id']) == $program['id'] ? 'selected' : '' }}>
                        {{ $program['nama_program'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Simpan Perubahan</button>
        <a href="{{ route('peserta.index') }}">Batal</a>
    </form>
</body>
</html>