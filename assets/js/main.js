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
