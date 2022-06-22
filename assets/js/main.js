function formatAngkaKeRupiah(angka) {
  return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(angka);
}

function formatAngkaDariRupiah(rupiah) {
  return parseFloat(rupiah.replace("Rp&nbsp;", "").replace(/\./g, ""));
}

function formatAngkaTitik(angka) {
  return new Intl.NumberFormat("id-ID", { minimumFractionDigits: 0 }).format(angka);
}

function displayMenu(namaMenu) {
  permalinkOverlay.classList.toggle("d-none");
  document.getElementById(namaMenu).classList.toggle("d-none");
}

function initDataTable(namaTable, init = true) {
  if (init) {
    $(`#${namaTable}`).DataTable({
      ordering: false,
      searching: false,
      lengthMenu: [5],
      lengthChange: false,
    });
  } else {
    $(`#${namaTable}`).DataTable().destroy();
  }
}

function checkAuth(login) {
  fetch("crud.php?cmd=checkAuth")
    .then((response) => response.json())
    .then((data) => {
      if (data.result !== true) {
        if (!login) {
          alert("Maaf, Anda harus login terlebih dahulu.");
          location.href = "index.html";
        }
      } else {
        if (login) {
          location.href = "kasir.html";
        }
      }
    });
}
