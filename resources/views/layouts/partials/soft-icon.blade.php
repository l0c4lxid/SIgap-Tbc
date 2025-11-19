@php
    $icon = $icon ?? 'default';
    $isActive = $active ?? false;
    $stateClass = $isActive ? 'soft-icon--active' : 'soft-icon--muted';
@endphp

@switch($icon)
    @case('dashboard')
        <i class="fa-solid fa-chart-simple {{ $stateClass }}"></i>
        @break
    @case('folder')
        <i class="fa-solid fa-folder-open {{ $stateClass }}"></i>
        @break
    @case('screening')
        <i class="fa-solid fa-notes-medical {{ $stateClass }}"></i>
        @break
    @case('berobat')
        <i class="fa-solid fa-syringe {{ $stateClass }}"></i>
        @break
    @case('sembuh')
        <i class="fa-solid fa-heart-pulse {{ $stateClass }}"></i>
        @break
    @case('anggota')
        <i class="fa-solid fa-people-group {{ $stateClass }}"></i>
        @break
    @case('verify')
        <i class="fa-solid fa-user-check {{ $stateClass }}"></i>
        @break
    @case('profile')
        <i class="fa-solid fa-id-card {{ $stateClass }}"></i>
        @break
    @case('users')
        <i class="fa-solid fa-users {{ $stateClass }}"></i>
        @break
    @default
        <i class="fa-solid fa-circle-info {{ $stateClass }}"></i>
@endswitch
