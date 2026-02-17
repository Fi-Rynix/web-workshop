<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <div class="nav-profile-image">
          <img src="{{ asset('images/faces/face1.jpg') }}" alt="profile" />
          <span class="login-status online"></span>
        </div>
        <div class="nav-profile-text d-flex flex-column">
          <span class="font-weight-bold mb-2">{{ auth()->user()->name ?? 'User' }}</span>
          <span class="text-secondary text-small">{{ auth()->user()->role ?? 'Member' }}</span>
        </div>
        <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route('dashboard') }}">
        <span class="menu-title">Dashboard</span>
        <i class="mdi mdi-home menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route('index-kategori') }}">
        <span class="menu-title">Kategori</span>
        <i class="mdi mdi-tag menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{ route('index-buku') }}">
        <span class="menu-title">Buku</span>
        <i class="mdi mdi-book menu-icon"></i>
      </a>
    </li>
    @forelse($sidebarMenu ?? [] as $menu)
      <li class="nav-item">
        @if(!empty($menu['submenu']))
          <a class="nav-link" data-bs-toggle="collapse" href="#{{ $menu['id'] }}" aria-expanded="false" aria-controls="{{ $menu['id'] }}">
            <span class="menu-title">{{ $menu['title'] }}</span>
            <i class="menu-arrow"></i>
            <i class="mdi {{ $menu['icon'] }} menu-icon"></i>
          </a>
          <div class="collapse" id="{{ $menu['id'] }}">
            <ul class="nav flex-column sub-menu">
              @foreach($menu['submenu'] as $submenu)
                <li class="nav-item">
                  <a class="nav-link" href="{{ $submenu['route'] ?? '#' }}">{{ $submenu['title'] }}</a>
                </li>
              @endforeach
            </ul>
          </div>
        @else
          <a class="nav-link" href="{{ $menu['route'] ?? '#' }}" @if($menu['target'] ?? false) target="_blank" @endif>
            <span class="menu-title">{{ $menu['title'] }}</span>
            <i class="mdi {{ $menu['icon'] }} menu-icon"></i>
          </a>
        @endif
      </li>
    @empty
    @endforelse
  </ul>
</nav>