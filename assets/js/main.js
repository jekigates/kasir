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
    formatCurrency($(this));
  },
  blur: function () {
    formatCurrency($(this));
  },
});

$("input[data-type='number']").on({
  keyup: function () {
    formatCurrency($(this), false);
  },
  blur: function () {
    formatCurrency($(this), false);
  },
});

function formatNumber(n) {
  // format number 1000000 to 1.234.567
  return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function formatCurrency(input, money = true) {
  // appends $ to value, validates decimal side
  // and puts cursor back in right position.

  // get input value
  var input_val = input.val();

  // don't validate empty input
  if (input_val === "") {
    return;
  }

  // original length
  var original_len = input_val.length;

  // initial caret position
  var caret_pos = input.prop("selectionStart");

  // no decimal entered
  // add commas to number
  // remove all non-digits
  input_val = formatNumber(input_val);
  if (money === true) {
    input_val = "Rp" + input_val;
  }

  // send updated string to input
  input.val(input_val);

  // put caret back in the right position
  var updated_len = input_val.length;
  caret_pos = updated_len - original_len + caret_pos;
  input[0].setSelectionRange(caret_pos, caret_pos);
}
