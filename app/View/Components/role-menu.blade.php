<ul class="list-group">
  @role('pasien')
    <li class="list-group-item">Menu: Laporan Kesehatan</li>
  @endrole

  @role('kader_tbc')
    <li class="list-group-item">Menu: Data Kader</li>
  @endrole

  @role('puskesmas')
    <li class="list-group-item">Menu: Data Puskesmas</li>
  @endrole

  @role('kelurahan')
    <li class="list-group-item">Menu: Data Kelurahan</li>
  @endrole

  @role('pemda')
    <li class="list-group-item">Menu: Dashboard Pemda</li>
  @endrole
</ul>
