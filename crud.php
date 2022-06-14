<?php

session_start();
require "koneksi.php";

$cmd = $_GET["cmd"];

function loadProduk() {
  global $conn;

  $sql = "SELECT * FROM produk";
  $query = mysqli_query($conn, $sql) or die("error: $sql");
  
  $rows = [];
  while($result = mysqli_fetch_assoc($query)) {
    $rows[] = $result;
  }

  echo json_encode([
    "produk" => $rows,
  ]);
}

if ($cmd == "login") {
  $data = json_decode(file_get_contents('php://input'), true);
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
}