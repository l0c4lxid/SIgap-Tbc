@php
    $icon = $icon ?? 'default';
@endphp

@switch($icon)
    @case('dashboard')
        <i class="fa-solid fa-chart-simple text-success" style="color: #198754;"></i>
        @break
    @case('folder')
        <i class="fa-solid fa-folder-open text-success" style="color: #198754;"></i>
        @break
    @case('screening')
        <i class="fa-solid fa-notes-medical text-success" style="color: #198754;"></i>
        @break
    @case('berobat')
        <i class="fa-solid fa-syringe text-success" style="color: #198754;"></i>
        @break
    @case('sembuh')
        <i class="fa-solid fa-heart-pulse text-success" style="color: #198754;"></i>
        @break
    @case('anggota')
        <i class="fa-solid fa-people-group text-success" style="color: #198754;"></i>
        @break
    @case('verify')
        <i class="fa-solid fa-user-check text-success" style="color: #198754;"></i>
        @break
    @case('profile')
        <i class="fa-solid fa-id-card text-success" style="color: #198754;"></i>
        @break
    @case('users')
        <i class="fa-solid fa-users text-success" style="color: #198754;"></i>
        @break
    @default
        <i class="fa-solid fa-circle-info text-success" style="color: #198754;"></i>
@endswitch
