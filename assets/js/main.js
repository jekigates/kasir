function formatAngkaKeRupiah(angka) {
  return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(angka);
}

function formatAngkaDariRupiah(rupiah) {
  return parseFloat(rupiah.replace("Rp", "").replace(/\./g, "").replace("&nbsp;", ""));
}

function formatAngkaTitik(angka) {
  return new Intl.NumberFormat("id-ID", { minimumFractionDigits: 0 }).format(angka);
}

function formatAngkaDariTitik(titik) {
  return parseFloat(titik.replace(".", ""));
}

function displayMenu(namaMenu) {
  permalinkOverlay.classList.toggle("d-none");
  document.getElementById(namaMenu).classList.toggle("d-none");
}

function initDataTable(namaTable, init = true, cetak = false) {
  if (init) {
    if (cetak) {
      $(`#${namaTable}`).DataTable({
        ordering: false,
        searching: false,
        lengthMenu: [5],
        lengthChange: false,
        dom: "Bfrtip",
        buttons: [
          {
            extend: "excelHtml5",
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5],
            },
          },
          {
            extend: "pdfHtml5",
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5],
            },
          },
        ],
      });
    } else {
      $(`#${namaTable}`).DataTable({
        ordering: false,
        searching: false,
        lengthMenu: [5],
        lengthChange: false,
      });
    }
  } else {
    $(`#${namaTable}`).DataTable().destroy();
  }
}

function initDatepicker(namaInput) {
  $(`#${namaInput}`).datepicker({
    dateFormat: "yy-mm-dd",
  });
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

$("input[data-type='currency']").on({
  keyup: function () {
    this.value = formatAngkaInput(this.value, true, true);
  },
});

$("input[data-type='number']").on({
  keyup: function () {
    this.value = formatAngkaInput(this.value);
  },
});

function formatAngkaInput(angka, prefix, uang = false) {
  var number_string = angka.replace(/[^,\d]/g, "").toString(),
    split = number_string.split(","),
    sisa = split[0].length % 3,
    rupiah = split[0].substr(0, sisa),
    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

  if (ribuan) {
    separator = sisa ? "." : "";
    rupiah += separator + ribuan.join(".");
  }

  rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;

  var hasil_return = prefix == undefined ? rupiah : rupiah ? "Rp " + rupiah : "";

  if (uang === false) {
    hasil_return = prefix == undefined ? rupiah : rupiah ? rupiah : "";
  }

  return hasil_return;
}

function cariValueDariKeyUrl(katakunci) {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  var nilaiKatakunci = urlParams.get(katakunci);

  return nilaiKatakunci;
}

function getColorFromRoot(name) {
  return getComputedStyle(document.body).getPropertyValue(name);
}

const swalKasirButtons = Swal.mixin({
  customClass: {
    confirmButton: "btn btn-success",
    cancelButton: "btn btn-danger",
  },
  buttonsStyling: false,
});
