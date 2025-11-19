@php
    $icon = $icon ?? 'default';
    $isActive = $active ?? false;
    $colorClass = $isActive ? 'text-white' : 'text-success';
@endphp

@switch($icon)
    @case('dashboard')
        <i class="fa-solid fa-chart-simple {{ $colorClass }}"></i>
        @break
    @case('folder')
        <i class="fa-solid fa-folder-open {{ $colorClass }}"></i>
        @break
    @case('screening')
        <i class="fa-solid fa-notes-medical {{ $colorClass }}"></i>
        @break
    @case('berobat')
        <i class="fa-solid fa-syringe {{ $colorClass }}"></i>
        @break
    @case('sembuh')
        <i class="fa-solid fa-heart-pulse {{ $colorClass }}"></i>
        @break
    @case('anggota')
        <i class="fa-solid fa-people-group {{ $colorClass }}"></i>
        @break
    @case('verify')
        <i class="fa-solid fa-user-check {{ $colorClass }}"></i>
        @break
    @case('profile')
        <i class="fa-solid fa-id-card {{ $colorClass }}"></i>
        @break
    @case('users')
        <i class="fa-solid fa-users {{ $colorClass }}"></i>
        @break
    @default
        <i class="fa-solid fa-circle-info {{ $colorClass }}"></i>
@endswitch
