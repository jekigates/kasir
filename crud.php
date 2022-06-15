<?php

session_start();
require "koneksi.php";

$cmd = $_GET["cmd"];

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

  $kategori = $_GET["kategori"];
  $nama = $_GET["nama"];

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
  $stok = $data["stok"];
  $satuan = $data["satuan"];
  $harga = $data["harga"];
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
  $stok = $data["stok"];
  $satuan = $data["satuan"];
  $harga = $data["harga"];
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
  
  $sql1 = "SELECT * FROM keranjang INNER JOIN produk ON keranjang.id = produk.id WHERE keranjang.id='$id'";
  $query1 = mysqli_query($conn, $sql1) or die("error: $sql1");
  $result1 = mysqli_fetch_assoc($query1);
  $num1 = mysqli_num_rows($query1);

  if ($num1 === 0) {
    $sql2 = "INSERT INTO keranjang(id, jumlah, total) VALUES('$id', '$jumlah', '$total')";
    $query2 = mysqli_query($conn, $sql2) or die("error: $sql2");
  } else {
    if ($mode === "tambah") {
      $jumlah += $result1["jumlah"];
    }

    $total = $result1["harga"] * $jumlah;

    $jumlah = (int) $jumlah;
    if ($jumlah === 0) {
      $sql4 = "DELETE FROM keranjang WHERE keranjang.id='$id'";
      $query4 = mysqli_query($conn, $sql4) or die("error: $sql4");
    } else {
      $sql3 = "UPDATE keranjang SET keranjang.jumlah='$jumlah', keranjang.total='$total' WHERE keranjang.id='$id'";
      $query3 = mysqli_query($conn, $sql3) or die("error: $sql3");
    }
  }

  echo json_encode([
    "result" => true,
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

function kosongkanKeranjang() {
  global $conn;

  $sql = "DELETE FROM keranjang";
  $query = mysqli_query($conn, $sql) or die("error: $sql");

  echo json_encode([
    "result" => true,
  ]);
}

function bayarKeranjang() {
  global $conn;

  $metode_pembayaran = $_GET["metode_pembayaran"];

  $sql1 = "SELECT * FROM keranjang";
  $query1 = mysqli_query($conn, $sql1) or die("error: $sql1");
  
  date_default_timezone_set('Asia/Jakarta');
  $tgl_waktu = date("Y-m-d H:i:s");
  $total_harus_dibayar = 0;
  $total_sudah_dibayar = 0;
  
  $sql2 = "INSERT INTO transaksi(tgl_waktu, total_harus_dibayar, total_sudah_dibayar, metode_pembayaran) VALUES('$tgl_waktu', 0, 0, '$metode_pembayaran')";
  $query2 = mysqli_query($conn, $sql2) or die("error: $sql2");
  $transaksi_id = $conn->insert_id;

  while ($result1 = mysqli_fetch_assoc($query1)) {
    $produk_id = $result1["id"];
    $jumlah = $result1["jumlah"];
    $total = $result1["total"];
    $total_harus_dibayar += $total;
    $sql3 = "INSERT INTO transaksi_detail(id, produk_id, jumlah, total) VALUES('$transaksi_id', '$produk_id', '$jumlah', '$total')";
    $query3 = mysqli_query($conn, $sql3) or die("error: $sql3");
  }

  kosongkanKeranjang();
  $sql4 = "UPDATE transaksi SET transaksi.total_harus_dibayar='$total_harus_dibayar', transaksi.total_sudah_dibayar='$total_sudah_dibayar', transaksi.metode_pembayaran='$metode_pembayaran'";
  $query4 = mysqli_query($conn, $sql4) or die("error: $sql4");

  echo json_encode([
    "result" => true,
  ]);
}

function loadTransaksi() {
  global $conn;

  $tgl_transaksi = strtotime($_GET["tgl_transaksi"]);
  $metode_pembayaran = $_GET["metode_pembayaran"];

  $tgl_transaksi_baru = date("Y", $tgl_transaksi) . "-" . date("m", $tgl_transaksi) . "-" . date("d", $tgl_transaksi);
  $sql = "SELECT * FROM transaksi INNER JOIN transaksi_detail ON transaksi.id = transaksi_detail.id WHERE transaksi.tgl_waktu LIKE '%$tgl_transaksi_baru%' AND transaksi.metode_pembayaran LIKE '%$metode_pembayaran%'";
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

if ($cmd == "login") {
  $data = json_decode(file_get_contents("php://input"), true);
  $username = $data["username"];
  $password = $data["password"];

  if ($username === "admin" && $password === "admin123") {
    $_SESSION["auth"] = true;

    echo json_encode([
      "result" => true,
    ]);
  } else {
    echo json_encode([
      "result" => false,
    ]);
  }
 } else if ($cmd == "logout") {
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