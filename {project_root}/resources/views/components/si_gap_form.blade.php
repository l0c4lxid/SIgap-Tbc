{{-- SIgap Form snippet --}}
<form {{ $attributes->merge(['class' => 'row g-3']) }}>
  @csrf
  <div class="col-12 col-md-4">
    <label class="form-label">WNI</label>
    <select name="patient_is_wni" class="form-select" required>
      <option value="">Pilih</option>
      <option value="1">Ya</option>
      <option value="0">Tidak</option>
    </select>
  </div>
  <div class="col-12 col-md-8">
    <label class="form-label">Nama Peserta</label>
    <input type="text" name="patient_name" class="form-control" placeholder="Masukkan nama lengkap" required>
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">NIK</label>
    <input type="text" name="patient_nik" class="form-control" placeholder="Nomor identitas">
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">Jenis Kelamin</label>
    <select name="patient_gender" class="form-select" required>
      <option value="">Pilih</option>
      <option value="L">Laki-laki</option>
      <option value="P">Perempuan</option>
    </select>
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">Tempat Lahir</label>
    <input type="text" name="patient_birth_place" class="form-control" placeholder="Kota lahir" required>
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">Tanggal Lahir</label>
    <input type="date" name="patient_birth_date" class="form-control" required>
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">Umur (tahun)</label>
    <input type="number" name="patient_age" class="form-control" min="0" max="150" required readonly>
  </div>
  <div class="col-12 col-md-3">
    <label class="form-label">Berat Badan (kg)</label>
    <input type="number" step="0.1" name="patient_weight" class="form-control" min="0" required>
  </div>
  <div class="col-12 col-md-3">
    <label class="form-label">Tinggi Badan (cm)</label>
    <input type="number" step="0.1" name="patient_height" class="form-control" min="0" required>
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">Nomor HP (opsional)</label>
    <input type="text" name="patient_phone" class="form-control" placeholder="08xxxxxxxxxx">
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">RT</label>
    <input type="text" name="patient_address_rt" class="form-control" placeholder="RT" required>
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">RW</label>
    <input type="text" name="patient_address_rw" class="form-control" placeholder="RW" required>
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">Kelurahan</label>
    <input type="text" name="patient_address_kelurahan" class="form-control" placeholder="Kelurahan" required>
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">Alamat KTP</label>
    <input type="text" name="patient_address_ktp" class="form-control" placeholder="Alamat sesuai KTP" required>
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">Alamat Domisili</label>
    <input type="text" name="patient_address_domisili" class="form-control" placeholder="Alamat domisili" required>
    <div class="form-check mt-2">
      <input class="form-check-input" type="checkbox" id="domisiliSameComponent">
      <label class="form-check-label" for="domisiliSameComponent">Sama dengan alamat KTP</label>
    </div>
  </div>
  <div class="col-12">
    <label class="form-label">Catatan</label>
    <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan"></textarea>
  </div>
  <div class="col-12 d-flex gap-2 justify-content-end">
    <button type="submit" class="btn btn-si-primary">Simpan</button>
    <button type="reset" class="btn btn-outline-si">Reset</button>
  </div>
</form>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const birthDateField = document.querySelector('input[name="patient_birth_date"]');
    const ageField = document.querySelector('input[name="patient_age"]');
    const addressKtpField = document.querySelector('input[name="patient_address_ktp"]');
    const addressDomField = document.querySelector('input[name="patient_address_domisili"]');
    const domisiliSame = document.getElementById('domisiliSameComponent');

    const syncAge = () => {
      if (!birthDateField || !ageField) {
        return;
      }
      if (!birthDateField.value) {
        ageField.value = '';
        return;
      }
      const today = new Date();
      const birth = new Date(birthDateField.value);
      let age = today.getFullYear() - birth.getFullYear();
      const monthDiff = today.getMonth() - birth.getMonth();
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age -= 1;
      }
      ageField.value = age >= 0 ? age : '';
    };

    const syncDomisili = () => {
      if (!domisiliSame || !addressKtpField || !addressDomField) {
        return;
      }
      if (domisiliSame.checked) {
        addressDomField.value = addressKtpField.value;
        addressDomField.setAttribute('readonly', 'readonly');
      } else {
        addressDomField.removeAttribute('readonly');
      }
    };

    birthDateField?.addEventListener('change', syncAge);
    domisiliSame?.addEventListener('change', syncDomisili);
    addressKtpField?.addEventListener('input', () => {
      if (domisiliSame?.checked) {
        addressDomField.value = addressKtpField.value;
      }
    });

    syncAge();
    syncDomisili();
  });
</script>
