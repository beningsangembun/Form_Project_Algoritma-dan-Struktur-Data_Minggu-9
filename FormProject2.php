<?php
function bersihkan($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Whitelist untuk select dan radio
$daftar_kategori = ['Web Development', 'Competitive Programming', 'UI/UX Design'];
$daftar_peserta  = ['Individu', 'Tim'];

$errors = [];
$nama = $email = $no_hp = $umur = $github = $kategori = $peserta = $nama_anggota1 = $umur_anggota1 = $nama_anggota2 = $umur_anggota2 = "";
$berhasil = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = bersihkan($_POST['nama'] ?? '');
    $email    = bersihkan($_POST['email'] ?? '');
    $no_hp    = bersihkan($_POST['no_hp'] ?? '');
    $umur     = bersihkan($_POST['umur'] ?? '');
    $github   = bersihkan($_POST['github'] ?? '');
    $kategori = $_POST['kategori'] ?? '';
    $peserta  = $_POST['peserta'] ?? '';
    $nama_anggota1 = bersihkan($_POST['nama_anggota1'] ?? '');
    $umur_anggota1 = bersihkan($_POST['umur_anggota1'] ?? '');
    $nama_anggota2 = bersihkan($_POST['nama_anggota2'] ?? '');
    $umur_anggota2 = bersihkan($_POST['umur_anggota2'] ?? '');

    // 1. Validasi Nama (wajib, 3-50 karakter)
    if (empty($nama)) {
        $errors['nama'] = "Nama wajib diisi.";
    } elseif (strlen($nama) < 3 || strlen($nama) > 50) {
        $errors['nama'] = "Nama harus terdiri dari 3 hingga 50 karakter.";
    }

    // 2. Validasi Email (wajib & format valid)
    if (empty($email)) {
        $errors['email'] = "Email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format email tidak valid.";
    }

    // 3. Validasi Nomor HP dengan REGEXP (contoh: diawali 08 / +62, 10-15 digit)
    if (empty($no_hp)) {
        $errors['no_hp'] = "Nomor HP wajib diisi.";
    } elseif (!preg_match('/^(\+62|62|0)8[1-9][0-9]{7,11}$/', $no_hp)) {
        $errors['no_hp'] = "Format Nomor HP tidak valid (contoh: 081234567890).";
    }

    // 4. Validasi Umur (rentang 17-19 tahun)
    if ($umur === '') {
        $errors['umur'] = "Umur wajib diisi.";
    } elseif (!filter_var($umur, FILTER_VALIDATE_INT, ["options" => ["min_range" => 17, "max_range" => 19]])) {
        $errors['umur'] = "Peringatan: Umur harus dalam rentang 17 - 19 tahun. Data tidak dapat disimpan.";
    }

    // 5. Validasi URL GitHub (opsional, jika diisi harus URL valid)
    if (!empty($github) && !filter_var($github, FILTER_VALIDATE_URL)) {
        $errors['github'] = "Format URL repositori GitHub tidak valid.";
    }

    // 6. Validasi Kategori (Select) dengan Whitelist
    if (empty($kategori)) {
        $errors['kategori'] = "Kategori lomba wajib dipilih.";
    } elseif (!in_array($kategori, $daftar_kategori, true)) {
        $errors['kategori'] = "Pilihan kategori tidak valid.";
    }

    // 7. Validasi Peserta (Radio) dengan Whitelist
    if (empty($peserta)) {
        $errors['peserta'] = "Jenis peserta wajib dipilih.";
    } elseif (!in_array($peserta, $daftar_peserta, true)) {
        $errors['peserta'] = "Pilihan peserta tidak valid.";
    }

    // Validasi tambahan jika peserta adalah Tim
    if ($peserta === 'Tim') {
        // Anggota 1 wajib diisi jika Tim
        if (empty($nama_anggota1)) {
            $errors['nama_anggota1'] = "Nama Anggota 1 wajib diisi.";
        } elseif (strlen($nama_anggota1) < 3 || strlen($nama_anggota1) > 50) {
            $errors['nama_anggota1'] = "Nama Anggota 1 harus 3 - 50 karakter.";
        }

        if ($umur_anggota1 === '') {
            $errors['umur_anggota1'] = "Umur Anggota 1 wajib diisi.";
        } elseif (!filter_var($umur_anggota1, FILTER_VALIDATE_INT, ["options" => ["min_range" => 17, "max_range" => 19]])) {
            $errors['umur_anggota1'] = "Peringatan: Umur Anggota 1 harus 17 - 19 tahun.";
        }

        // Anggota 2 opsional, tapi jika diisi salah satu, keduanya harus valid
        if (!empty($nama_anggota2) || $umur_anggota2 !== '') {
            if (strlen($nama_anggota2) < 3 || strlen($nama_anggota2) > 50) {
                $errors['nama_anggota2'] = "Nama Anggota 2 harus 3 - 50 karakter.";
            }
           if ($umur_anggota2 === '') {
            $errors['umur_anggota2'] = "Umur Anggota 2 wajib diisi.";
        } elseif (!filter_var($umur_anggota2, FILTER_VALIDATE_INT, ["options" => ["min_range" => 17, "max_range" => 19]])) {
            $errors['umur_anggota2'] = "Peringatan: Umur Anggota 2 harus 17 - 19 tahun.";
        }
        }
    }  else {
        if (!empty($nama_anggota1) || $umur_anggota1 !== '' || !empty($nama_anggota2) || $umur_anggota2 !== '') {
            $errors['peserta'] = "Data anggota tidak boleh diisi jika memilih peserta Individu.";
            
            // Reset nilai agar input anggota kembali kosong
            $nama_anggota1 = $umur_anggota1 = $nama_anggota2 = $umur_anggota2 = "";
        }
    

    // Cek jika tidak ada error sama sekali
    } if (empty($errors)) {
        $berhasil = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Lomba Coding</title>
</head>
<body>

<h2>Formulir Registrasi Lomba Coding</h2>

<form action="" method="post">
    <label for="nama">Nama:</label><br>
    <input type="text" id="nama" name="nama" value="<?= $nama ?>">
    <?php if (isset($errors['nama'])) echo "<br><small style='color:red;'>{$errors['nama']}</small>"; ?>
    <br><br>

    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" value="<?= $email ?>">
    <?php if (isset($errors['email'])) echo "<br><small style='color:red;'>{$errors['email']}</small>"; ?>
    <br><br>

    <label for="no_hp">Nomor HP:</label><br>
    <input type="text" id="no_hp" name="no_hp" value="<?= $no_hp ?>">
    <?php if (isset($errors['no_hp'])) echo "<br><small style='color:red;'>{$errors['no_hp']}</small>"; ?>
    <br><br>

    <label for="umur">Umur (17-19 tahun):</label><br>
    <input type="number" id="umur" name="umur" value="<?= $umur ?>">
    <?php if (isset($errors['umur'])) echo "<br><small style='color:red;'>{$errors['umur']}</small>"; ?>
    <br><br>

    <label for="github">URL Repositori GitHub (Opsional):</label><br>
    <input type="text" id="github" name="github" value="<?= $github ?>">
    <?php if (isset($errors['github'])) echo "<br><small style='color:red;'>{$errors['github']}</small>"; ?>
    <br><br>

    <label for="kategori">Kategori Lomba:</label><br>
    <select id="kategori" name="kategori">
        <option value="">-- Pilih Kategori --</option>
        <?php foreach ($daftar_kategori as $kat): ?>
            <option value="<?= bersihkan($kat) ?>" <?= ($kategori === $kat) ? 'selected' : '' ?>>
                <?= bersihkan($kat) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['kategori'])) echo "<br><small style='color:red;'>{$errors['kategori']}</small>"; ?>
    <br><br>

    
    <label>Jenis Peserta:</label><br>
    <?php foreach ($daftar_peserta as $pes): ?>
        <input type="radio" id="<?= bersihkan($pes) ?>" name="peserta" value="<?= bersihkan($pes) ?>" <?= ($peserta === $pes) ? 'checked' : '' ?>>
        <label for="<?= bersihkan($pes) ?>"><?= bersihkan($pes) ?></label>
    <?php endforeach; ?>
    <?php if (isset($errors['peserta'])) echo "<br><small style='color:red;'>{$errors['peserta']}</small>"; ?>
    <br><br>
    <fieldset style="width: fit-content;">
        <legend>Data Anggota (Isi jika memilih Tim)</legend>
        
        <label for="nama_anggota1">Nama Anggota 1:</label><br>
        <input type="text" id="nama_anggota1" name="nama_anggota1" value="<?= $nama_anggota1 ?>">
        <?php if (isset($errors['nama_anggota1'])) echo "<br><small style='color:red;'>{$errors['nama_anggota1']}</small>"; ?>
        <br><br>

        <label for="umur_anggota1">Umur Anggota 1 (17-19 tahun):</label><br>
        <input type="number" id="umur_anggota1" name="umur_anggota1" value="<?= $umur_anggota1 ?>">
        <?php if (isset($errors['umur_anggota1'])) echo "<br><small style='color:red;'>{$errors['umur_anggota1']}</small>"; ?>
        <br><br>

        <label for="nama_anggota2">Nama Anggota 2 (Opsional):</label><br>
        <input type="text" id="nama_anggota2" name="nama_anggota2" value="<?= $nama_anggota2 ?>">
        <?php if (isset($errors['nama_anggota2'])) echo "<br><small style='color:red;'>{$errors['nama_anggota2']}</small>"; ?>
        <br><br>

        <label for="umur_anggota2">Umur Anggota 2 (17-19 tahun):</label><br>
        <input type="number" id="umur_anggota2" name="umur_anggota2" value="<?= $umur_anggota2 ?>">
        <?php if (isset($errors['umur_anggota2'])) echo "<br><small style='color:red;'>{$errors['umur_anggota2']}</small>"; ?>
    </fieldset>
    <br>

    <button type="submit">Kirim</button>
</form>

<?php if ($berhasil): ?>
    <hr>
    <h3>Data Registrasi Berhasil Disimpan:</h3>
    <ul>
        <li><strong>Nama:</strong> <?= $nama ?></li>
        <li><strong>Email:</strong> <?= $email ?></li>
        <li><strong>Nomor HP:</strong> <?= $no_hp ?></li>
        <li><strong>Umur:</strong> <?= $umur ?> tahun</li>
        <li><strong>GitHub:</strong> <?= !empty($github) ? $github : '-' ?></li>
        <li><strong>Kategori:</strong> <?= bersihkan($kategori) ?></li>
        <li><strong>Peserta:</strong> <?= bersihkan($peserta) ?></li>
        <li><strong>Nama Anggota Tim:</strong> <?= !empty($nama_anggota1) ? $nama_anggota1 . ' dan ' . $nama_anggota2 : '-' ?></li>
    </ul>
<?php endif; ?>

</body>
</html>