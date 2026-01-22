@php
    $icon = $icon ?? 'default';
    $isActive = $active ?? false;
    $stateClass = $isActive ? 'soft-icon--active' : 'soft-icon--muted';
@endphp

@switch($icon)
    @case('dashboard')
        <i class="fa-solid fa-chart-simple fa-fw {{ $stateClass }}"></i>
        @break
    @case('folder')
        <i class="fa-solid fa-folder-open fa-fw {{ $stateClass }}"></i>
        @break
    @case('screening')
        <i class="fa-solid fa-notes-medical fa-fw {{ $stateClass }}"></i>
        @break
    @case('berobat')
        <i class="fa-solid fa-syringe fa-fw {{ $stateClass }}"></i>
        @break
    @case('sembuh')
        <i class="fa-solid fa-heart-pulse fa-fw {{ $stateClass }}"></i>
        @break
    @case('anggota')
        <i class="fa-solid fa-people-group fa-fw {{ $stateClass }}"></i>
        @break
    @case('verify')
        <i class="fa-solid fa-user-check fa-fw {{ $stateClass }}"></i>
        @break
    @case('profile')
        <i class="fa-solid fa-id-card fa-fw {{ $stateClass }}"></i>
        @break
    @case('users')
        <i class="fa-solid fa-users fa-fw {{ $stateClass }}"></i>
        @break
    @case('materi')
        <i class="fa-solid fa-book-open fa-fw {{ $stateClass }}"></i>
        @break
    @case('news')
        <i class="fa-solid fa-newspaper fa-fw {{ $stateClass }}"></i>
        @break
    @default
        <i class="fa-solid fa-circle-info fa-fw {{ $stateClass }}"></i>
@endswitch
