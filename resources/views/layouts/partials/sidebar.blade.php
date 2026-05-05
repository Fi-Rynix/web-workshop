@php
    $userRole = auth()->user()->idrole ?? null;
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <div class="nav-profile-image">
          <img src="{{ asset('images/faces/face1.jpg') }}" alt="profile" />
          <span class="login-status online"></span>
        </div>
        <div class="nav-profile-text d-flex flex-column">
          <span class="font-weight-bold mb-2">{{ auth()->user()->nama ?? 'User' }}</span>
          <span class="text-secondary text-small">{{ $userRole == 1 ? 'Administrator' : ($userRole == 2 ? 'Vendor' : ($userRole == 3 ? 'Pelanggan' : 'Guest')) }}</span>
        </div>
        <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
      </a>
    </li>

    {{-- Menu Admin (idrole = 1) --}}
    @if($userRole == 1)
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
      <li class="nav-item">
        <a class="nav-link" href="{{ route('index-barang') }}">
          <span class="menu-title">Barang</span>
          <i class="mdi mdi-package menu-icon"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('generate-pdf') }}">
          <span class="menu-title">Generate PDF</span>
          <i class="mdi mdi-file menu-icon"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#modul4Menu" aria-expanded="false" aria-controls="modul4Menu">
          <span class="menu-title">Modul 4</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="modul4Menu">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('modul-4-js-non-datatables') }}">Non-DataTables</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('modul-4-js-datatables') }}">DataTables</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('modul-4-js-select-kota') }}">Select Kota</a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#modul5Menu" aria-expanded="false" aria-controls="modul5Menu">
          <span class="menu-title">Modul 5</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="modul5Menu">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('modul-5-ajax-wilayah-ajax') }}">Wilayah Ajax</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('modul-5-ajax-wilayah-axios') }}">Wilayah Axios</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('modul-5-ajax-pos-ajax') }}">POS Ajax</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('modul-5-ajax-pos-axios') }}">POS Axios</a>
            </li>
          </ul>
        </div>
      </li>

      {{-- Menu Customer (Studi Kasus 3 - Akses Kamera) --}}
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#customerMenu" aria-expanded="false" aria-controls="customerMenu">
          <span class="menu-title">Customer</span>
          <i class="menu-arrow"></i>
          <i class="mdi mdi-account-multiple menu-icon"></i>
        </a>
        <div class="collapse" id="customerMenu">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('customer.index') }}">Data Customer</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('customer.create1') }}">Tambah Customer 1 (BLOB)</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('customer.create2') }}">Tambah Customer 2 (File)</a>
            </li>
          </ul>
        </div>
      </li>

    {{-- Menu Vendor (idrole = 2) --}}
    @elseif($userRole == 2)
      <li class="nav-item">
        <a class="nav-link" href="{{ route('vendor.dashboard') }}">
          <span class="menu-title">Dashboard</span>
          <i class="mdi mdi-home menu-icon"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('vendor.menu.index') }}">
          <span class="menu-title">Kelola Menu</span>
          <i class="mdi mdi-food menu-icon"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('vendor.pesanan.index') }}">
          <span class="menu-title">Pesanan Masuk</span>
          <i class="mdi mdi-cart menu-icon"></i>
        </a>
      </li>

    {{-- Menu Pelanggan (idrole = 3) --}}
    @elseif($userRole == 3)
      <li class="nav-item">
        <a class="nav-link" href="{{ route('pelanggan.dashboard') }}">
          <span class="menu-title">Dashboard</span>
          <i class="mdi mdi-home menu-icon"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('pesan.public') }}">
          <span class="menu-title">Pesan Menu</span>
          <i class="mdi mdi-food menu-icon"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('pelanggan.transaksi.index') }}">
          <span class="menu-title">Riwayat Pesanan</span>
          <i class="mdi mdi-history menu-icon"></i>
        </a>
      </li>
    @endif

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
