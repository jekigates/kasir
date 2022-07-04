<?php

session_start();
require "koneksi.php";

$cmd = $_GET["cmd"];

function formatAngka($angka, $tipe = "titik") {
  if ($tipe === "uang") {
    $angka = str_replace("Rp", "", $angka);
  }
  $angka = str_replace(".", "", $angka);
  return $angka;
}

function loadKategori() {
  global $conn;

  $sql = "SELECT DISTINCT produk.kategori FROM produk";
  if (isset($_GET["status"])) {
    $status = $_GET["status"];
    $sql = "SELECT DISTINCT produk.kategori FROM produk WHERE produk.status='$status'";
  }

  $query = mysqli_query($conn, $sql) or die("error: $sql");

  $rows = [];
  while($result = mysqli_fetch_assoc($query)) {
    $rows[] = $result;
  }

  echo json_encode([
    "kategori" => $rows,
  ]);
}

function loadProduk() {
  global $conn;

  $kategori = "";
  $nama = "";

  if (isset($_GET["kategori"])) {
    $kategori = $_GET["kategori"];
  }
  if (isset($_GET["nama"])) {
    $nama = $_GET["nama"];
  }

  if (isset($_GET["status"])) {
    $status = $_GET["status"];
    if (isset($_GET["stok"])) {
      $stok = $_GET["stok"];
      $sql = "SELECT * FROM produk WHERE produk.nama LIKE '%$nama%' AND produk.kategori LIKE '%$kategori%' AND produk.status='$status' AND produk.stok >= '$stok'";
    } else {
      $sql = "SELECT * FROM produk WHERE produk.nama LIKE '%$nama%' AND produk.kategori LIKE '%$kategori%' AND produk.status='$status'";
    }
  } else {
    if (isset($_GET["stok"])) {
      $stok = $_GET["stok"];
      $sql = "SELECT * FROM produk WHERE produk.nama LIKE '%$nama%' AND produk.kategori LIKE '%$kategori%' AND produk.stok >= '$stok'";
    } else {
      $sql = "SELECT * FROM produk WHERE produk.nama LIKE '%$nama%' AND produk.kategori LIKE '%$kategori%'";
    }
  }

  $query = mysqli_query($conn, $sql) or die("error: $sql");
  
  $rows = [];
  while($result = mysqli_fetch_assoc($query)) {
    $rows[] = $result;
  }

  echo json_encode([
    "produk" => $rows,
  ]);
}

function buatProduk() {
  global $conn;

  $data = json_decode(file_get_contents("php://input"), true);
  $nama = $data["nama"];
  $stok = formatAngka($data["stok"]);
  $satuan = $data["satuan"];
  $harga = formatAngka($data["harga"], "uang");
  $kategori = $data["kategori"];
  $status = $data["status"];

  $sql = "INSERT INTO produk(nama, stok, satuan, harga, kategori, status) VALUES('$nama', '$stok', '$satuan', '$harga', '$kategori', '$status')";
  $query = mysqli_query($conn, $sql) or die("error: $sql");

  echo json_encode([
    "result" => true,
  ]);
}

function detailProduk() {
  global $conn;

  $id = $_GET["id"];

  $sql = "SELECT *, produk.id AS id FROM produk LEFT JOIN keranjang ON produk.id = keranjang.id WHERE produk.id='$id'";
  $query = mysqli_query($conn, $sql) or die("error: $sql");
  $result = mysqli_fetch_assoc($query);

  echo json_encode([
    "produk" => $result,
  ]);
}

function updateProduk() {
  global $conn;

  $data = json_decode(file_get_contents("php://input"), true);
  $id = $_GET["id"];
  $nama = $data["nama"];
  $stok = formatAngka($data["stok"]);
  $satuan = $data["satuan"];
  $harga = formatAngka($data["harga"], "uang");
  $kategori = $data["kategori"];
  $status = $data["status"];

  $sql = "UPDATE produk SET produk.nama='$nama', produk.stok='$stok', produk.satuan='$satuan', produk.harga='$harga', produk.kategori='$kategori', produk.status='$status' WHERE produk.id='$id'";
  $query = mysqli_query($conn, $sql) or die("error: $sql");

  echo json_encode([
    "result" => true,
  ]);
}

function pesanProdukKeKeranjang() {
  global $conn;

  $mode = $_GET["mode"];
  $data = json_decode(file_get_contents("php://input"), true);
  $id = $data["id"];
  $jumlah = $data["jumlah"];
  $total = $data["total"];
  $error = "";
  
  $sql1 = "SELECT * FROM produk WHERE produk.id='$id'";
  $query1 = mysqli_query($conn, $sql1) or die("error: $sql1");
  $result1 = mysqli_fetch_assoc($query1);

  if ($jumlah <= $result1["stok"]) {
    // Kalau jumlah lebih dari stok

    $sql2 = "SELECT * FROM keranjang INNER JOIN produk ON keranjang.id = produk.id WHERE keranjang.id='$id'";
    $query2 = mysqli_query($conn, $sql2) or die("error: $sql2");
    $result2 = mysqli_fetch_assoc($query2);
    $num2 = mysqli_num_rows($query2);

    if ($num2 === 0) {
      // Kalau produknya belum ada di keranjang
      $sql3 = "INSERT INTO keranjang(id, jumlah, total) VALUES('$id', '$jumlah', '$total')";
      $query3 = mysqli_query($conn, $sql3) or die("error: $sql3");
    } else {
      // Kalau produkmya sudah ada di keranjang
      if ($mode === "tambah") {
        $jumlah += $result2["jumlah"];
      }

      $total = $result1["harga"] * $jumlah;

      $jumlah = (int) $jumlah;
      if ($jumlah === 0) {
        $sql4 = "DELETE FROM keranjang WHERE keranjang.id='$id'";
        $query4 = mysqli_query($conn, $sql4) or die("error: $sql4");
      } else {
        if ($jumlah <= $result1["stok"]) {
          $sql5 = "UPDATE keranjang SET keranjang.jumlah='$jumlah', keranjang.total='$total' WHERE keranjang.id='$id'";
          $query5 = mysqli_query($conn, $sql5) or die("error: $sql5");
        } else {
          $error = "Maaf, jumlah produk ini bisa ditambahkan ke dalam keranjang lagi karena melebihi stok yang ada.";
        }
      }
    }
  } else {
    // Kalau jumlah tidak lebih dari stok
    $error = "Maaf, jumlah yang dimasukkan melebihi stok yang ada.";
  }

  echo json_encode([
    "result" => true,
    "error" => $error,
  ]);
}

function loadProdukDiKeranjang() {
  global $conn;

  $sql = "SELECT * FROM keranjang INNER JOIN produk ON keranjang.id = produk.id WHERE produk.status='Available' AND produk.id > 0";
  $query = mysqli_query($conn, $sql) or die("error: $sql");

  $total = 0;
  $jumlah_jenis_produk = 0;

  $rows = [];
  while($result = mysqli_fetch_assoc($query)) {
    $jumlah_jenis_produk += 1;
    $total += $result["total"];
    $rows[] = $result;
  }

  echo json_encode([
    "produk" => $rows,
    'jumlah_jenis_produk' => $jumlah_jenis_produk,
    'total' => $total,
  ]);
}

function kosongkanKeranjang($call = "ajax") {
  global $conn;

  $sql = "DELETE FROM keranjang";
  $query = mysqli_query($conn, $sql) or die("error: $sql");

  if ($call === "ajax") {
    echo json_encode([
      "result" => true,
    ]);
  }
}

function bayarKeranjang() {
  global $conn;

  $data = json_decode(file_get_contents("php://input"), true);
  $metode_pembayaran = $_GET["metode_pembayaran"];
  $total_harus_dibayar = 0;
  $total_sudah_dibayar = 0;
  $transaksi_id = (isset($_GET["id"])) ? $_GET["id"] : "";

  if (isset($data["total_sudah_dibayar"])) {
    $total_sudah_dibayar = formatAngka($data["total_sudah_dibayar"], "uang");
  }

  date_default_timezone_set('Asia/Jakarta');
  $tgl_waktu = date("Y-m-d H:i:s");

  if ($transaksi_id === "") {
    $sql1 = "SELECT * FROM keranjang INNER JOIN produk ON keranjang.id = produk.id";
    $query1 = mysqli_query($conn, $sql1) or die("error: $sql1");
  
    if ($metode_pembayaran === "Lunas") {
      $sql2 = "INSERT INTO transaksi(tgl_waktu, total_harus_dibayar, total_sudah_dibayar, tgl_lunas) VALUES('$tgl_waktu', 0, 0, '$tgl_waktu')";
    } else {
      $sql2 = "INSERT INTO transaksi(tgl_waktu, total_harus_dibayar, total_sudah_dibayar) VALUES('$tgl_waktu', 0, 0)";
    }
    $query2 = mysqli_query($conn, $sql2) or die("error: $sql2");
    $transaksi_id = $conn->insert_id;
  
    while ($result1 = mysqli_fetch_assoc($query1)) {
      $produk_id = $result1["id"];
      $harga = $result1["harga"];
      $jumlah = $result1["jumlah"];
      $total = $result1["total"];
      $stok = $result1["stok"];
      $total_harus_dibayar += $total;
      $sql3 = "INSERT INTO transaksi_detail(id, produk_id, harga_sekarang, jumlah, total) VALUES('$transaksi_id', '$produk_id', '$harga', '$jumlah','$total')";
      $query3 = mysqli_query($conn, $sql3) or die("error: $sql3");

      $jumlah_baru = $stok - $jumlah;
      $sql7 = "UPDATE produk SET produk.stok='$jumlah_baru' WHERE produk.id='$produk_id'";
      $query7 = mysqli_query($conn, $sql7) or die("error: $sql7");
    }
  
    kosongkanKeranjang("php");
    $sql4 = "UPDATE transaksi SET transaksi.total_harus_dibayar='$total_harus_dibayar', transaksi.total_sudah_dibayar='$total_sudah_dibayar' WHERE transaksi.id='$transaksi_id'";
    $query4 = mysqli_query($conn, $sql4) or die("error: $sql4");
  } else {
    if ($metode_pembayaran === "Lunas") {
      $sql5 = "UPDATE transaksi SET transaksi.tgl_lunas='$tgl_waktu', transaksi.total_sudah_dibayar='$total_sudah_dibayar' WHERE transaksi.id='$transaksi_id'";
      $query5 = mysqli_query($conn, $sql5) or die("error: $sql5");
    } else {
      // Return transaksi
      $sql6 = "UPDATE transaksi SET transaksi.tgl_return='$tgl_waktu' WHERE transaksi.id='$transaksi_id'";
      $query6 = mysqli_query($conn, $sql6) or die("error: $sql6");

      $sql8 = "SELECT * FROM transaksi_detail INNER JOIN produk ON transaksi_detail.produk_id = produk.id WHERE transaksi_detail.id='$transaksi_id'";
      $query8 = mysqli_query($conn, $sql8) or die("error: $sql8");
      
      while ($result8 = mysqli_fetch_assoc($query8)) {
        $produk_id = $result8["produk_id"];
        $jumlah = $result8["jumlah"];
        $stok = $result8["stok"];

        $jumlah_baru = $jumlah + $stok;
        $sql9 = "UPDATE produk SET produk.stok='$jumlah_baru' WHERE produk.id='$produk_id'";
        $query9 = mysqli_query($conn, $sql9) or die("error: $sql9");
      }
    }
  }

  echo json_encode([
    "result" => true,
    "transaksi_id" => $transaksi_id,
  ]);
}

function loadTransaksi() {
  global $conn;

  $tgl_transaksi = $_GET["tgl_transaksi"];
  $metode_pembayaran = $_GET["metode_pembayaran"];

  $sql = "SELECT * FROM transaksi INNER JOIN transaksi_detail ON transaksi.id = transaksi_detail.id ";
  if ($metode_pembayaran === "") {
    $sql .= "WHERE transaksi.tgl_waktu LIKE '%$tgl_transaksi%'";
  } else if ($metode_pembayaran === "lunas_tanpa_return") {
    $sql .= "WHERE transaksi.tgl_lunas IS NOT NULL AND transaksi.tgl_return IS NULL";
  } else if ($metode_pembayaran === "lunas_tapi_return") {
    $sql .= "WHERE transaksi.tgl_lunas IS NOT NULL AND transaksi.tgl_return IS NOT NULL";
  } else if ($metode_pembayaran === "utang_tanpa_return") {
    $sql .= "WHERE transaksi.tgl_lunas IS NULL AND transaksi.tgl_return IS NULL";
  } else if ($metode_pembayaran === "utang_tapi_return") {
    $sql .= "WHERE transaksi.tgl_lunas IS NULL AND transaksi.tgl_return IS NOT NULL";
  }
  
  $sql .= " GROUP BY transaksi.id";
  $query = mysqli_query($conn, $sql) or die("error: $sql");

  $rows = [];
  while($result = mysqli_fetch_assoc($query)) {
    $rows[] = $result;
  }

  echo json_encode([
    "transaksi" => $rows,
  ]);
}

function loadProdukDiTransaksi() {
  global $conn;

  $sql = "SELECT * FROM transaksi_detail INNER JOIN produk ON transaksi_detail.produk_id = produk.id";
  $query = mysqli_query($conn, $sql) or die("error: $sql");

  $total = 0;
  $jumlah_jenis_produk = 0;

  $rows = [];
  while($result = mysqli_fetch_assoc($query)) {
    $jumlah_jenis_produk += 1;
    $total += $result["total"];
    $rows[] = $result;
  }

  echo json_encode([
    "produk" => $rows,
    'jumlah_jenis_produk' => $jumlah_jenis_produk,
    'total' => $total,
  ]);
}

function detailTransaction() {
  global $conn;

  $id = $_GET["id"];

  $sql = "SELECT *, transaksi.id AS id FROM transaksi INNER JOIN transaksi_detail ON transaksi.id = transaksi_detail.id INNER JOIN produk ON transaksi_detail.produk_id = produk.id WHERE transaksi.id='$id'";
  $query = mysqli_query($conn, $sql) or die("error: $sql");

  $rows = [];
  while($result = mysqli_fetch_assoc($query)) {
    $rows[] = $result;
  }

  echo json_encode([
    "transaction" => $rows,
  ]);
}

function loadProfil($call = "ajax") {
  global $conn;

  $sql = "SELECT * FROM profil";
  $query = mysqli_query($conn, $sql) or die("error: $sql");
  $profil = [];

  while ($result = mysqli_fetch_assoc($query)) {
    $profil[$result["nama"]] = $result["nilai"];
  }

  if ($call === "ajax") {
    echo json_encode([
      "profil" => $profil,
    ]);
  } else {
    return $profil;
  }
}

function login() {
  global $conn;

  $data = json_decode(file_get_contents("php://input"), true);
  $username = $data["username"];
  $password = $data["password"];
  $result = false;

  $profil = loadprofil("php");

  if ($profil["username"] === $username && $profil["password"] === $password || isset($_SESSION["auth"])) {
    $_SESSION["auth"] = true;
    $result = true;
  }

  echo json_encode([
    "result" => $result,
  ]);
}

if ($cmd === "loadProfil") {
  loadProfil();
} else if ($cmd === "login") {
  login();
 } else if ($cmd === "logout") {
  session_unset();
  session_destroy();

  header("Location: index.html");
 } else if ($cmd === "checkAuth") {
  if (isset($_SESSION["auth"])) {
    echo json_encode([
      "result" => true,
    ]);
  } else {
    echo json_encode([
      "result" => false,
    ]);
  }
} else if ($cmd === "loadProduk") {
  loadProduk();
} else if ($cmd === "buatProduk") {
  buatProduk();
} else if ($cmd == "detailProduk") {
  detailProduk();
} else if ($cmd === "updateProduk") {
  updateProduk();
} else if ($cmd === "loadKategori") {
  loadKategori();
} else if ($cmd === "pesanProdukKeKeranjang") {
  pesanProdukKeKeranjang();
} else if ($cmd === "loadProdukDiKeranjang") {
  loadProdukDiKeranjang();
} else if ($cmd === "kosongkanKeranjang") {
  kosongkanKeranjang();
} else if ($cmd == "bayarKeranjang") {
  bayarKeranjang();
} else if ($cmd === "loadTransaksi") {
  loadTransaksi();
} else if ($cmd === "loadProdukDiTransaksi") {
  loadProdukDiTransaksi();
} else if ($cmd === "detailTransaction") {
  detailTransaction();
}