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

  $sql = "SELECT * FROM produk WHERE produk.id='$id'";
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
}