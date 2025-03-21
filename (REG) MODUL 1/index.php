<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "pembelian_mobil";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$nama = $email = $nomor = $mobil = $alamat = "";
$namaErr = $emailErr = $nomorErr = $alamatErr = "";
$successMessage = "";

if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    $delete_stmt = $conn->prepare("DELETE FROM pembelian WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    
    if ($delete_stmt->execute()) {
        $successMessage = "Data pembelian berhasil dihapus!";
    } else {
        $successMessage = "Error: " . $delete_stmt->error;
    }
    
    $delete_stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST["nama"];
    if (empty($nama)) {
        $namaErr = "Nama wajib diisi";
    }

    $email = $_POST["email"];
    if (empty($email)) {
        $emailErr = "Email wajib diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Format email tidak valid";
    } elseif (!preg_match('/@gmail\.com$/i', $email)) {
        $emailErr = "Email harus menggunakan domain @gmail.com";
    }

    $nomor = $_POST["nomor"];
    if (empty($nomor)) {
        $nomorErr = "Nomor Telepon wajib diisi";
    } elseif (!preg_match('/^[0-9]+$/', $nomor)) {
        $nomorErr = "Nomor Telepon hanya boleh berisi angka";
    }

    $alamat = $_POST["alamat"];
    if (empty($alamat)) {
        $alamatErr = "Alamat wajib diisi";
    }

    $mobil = $_POST["mobil"];

    if (empty($namaErr) && empty($emailErr) && empty($nomorErr) && empty($alamatErr)) {
        $stmt = $conn->prepare("INSERT INTO pembelian (nama, email, nomor_telepon, mobil, alamat) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nama, $email, $nomor, $mobil, $alamat);
        
        if ($stmt->execute()) {
            $successMessage = "Data pembelian berhasil disimpan ke database!";
        } else {
            $successMessage = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$data_pembelian = [];
$sql = "SELECT * FROM pembelian ORDER BY id DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data_pembelian[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pembelian Mobil</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Form Pembelian Mobil</h2>
        
        <?php if (!empty($successMessage)) { ?>
            <div class="success-message"><?php echo $successMessage; ?></div>
        <?php } ?>
        
        <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
            <div class="form-group">
                <label for="nama">Nama:</label>
                <input type="text" id="nama" name="nama" value="<?php echo $nama; ?>">
                <span class="error"><?php echo $namaErr ? "* $namaErr" : ""; ?></span>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" value="<?php echo $email; ?>" placeholder="contoh@gmail.com">
                <span class="error"><?php echo $emailErr ? "* $emailErr" : ""; ?></span>
            </div>

            <div class="form-group">
                <label for="nomor">Nomor Telepon:</label>
                <input type="text" id="nomor" name="nomor" value="<?php echo $nomor; ?>" placeholder="Hanya angka saja">
                <span class="error"><?php echo $nomorErr ? "* $nomorErr" : ""; ?></span>
            </div>

            <div class="form-group">
                <label for="mobil">Pilih Mobil:</label>
                <select id="mobil" name="mobil">
                    <option value="Sedan" <?php echo ($mobil == "Sedan") ? "selected" : ""; ?>>Sedan</option>
                    <option value="SUV" <?php echo ($mobil == "SUV") ? "selected" : ""; ?>>SUV</option>
                    <option value="Hatchback" <?php echo ($mobil == "Hatchback") ? "selected" : ""; ?>>Hatchback
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat Pengiriman:</label>
                <textarea id="alamat" name="alamat"><?php echo $alamat; ?></textarea>
                <span class="error"><?php echo $alamatErr ? "* $alamatErr" : ""; ?></span>
            </div>

            <div class="button-container">
                <button type="submit">Beli Mobil</button>
            </div>
        </form>
    </div>

    <?php if (count($data_pembelian) > 0) { ?>
    <div class="container">
        <h3>Data Pembelian:</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="18%">Nama</th>
                        <th width="18%">Email</th>
                        <th width="12%">Nomor Telepon</th>
                        <th width="12%">Mobil</th>
                        <th width="28%">Alamat Pengiriman</th>
                        <th width="12%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data_pembelian as $data) { ?>
                    <tr>
                        <td><?php echo $data['nama']; ?></td>
                        <td><?php echo $data['email']; ?></td>
                        <td><?php echo $data['nomor_telepon']; ?></td>
                        <td><?php echo $data['mobil']; ?></td>
                        <td><?php echo $data['alamat']; ?></td>
                        <td>
                            <a href="?delete=<?php echo $data['id']; ?>" class="delete-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php } ?>
</body>

</html>
<?php
$conn->close();
?>