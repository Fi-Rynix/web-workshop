@extends('layouts.app')

@section('title', 'Data Customer')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/pages/customer.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="mdi mdi-account-group me-2"></i>Data Customer</h4>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="mdi mdi-plus me-1"></i>Tambah Customer
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('customer.create1') }}">
                                <i class="mdi mdi-camera me-2"></i>Tambah Customer 1 (BLOB)
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('customer.create2') }}">
                                <i class="mdi mdi-camera me-2"></i>Tambah Customer 2 (File)
                            </a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>Wilayah</th>
                                    <th>Storage Type</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $index => $customer)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-center">
                                        @if($customer->blob_foto)
                                            <img src="{{ route('customer.show-blob', $customer->idcustomer) }}"
                                                 alt="Foto" class="img-thumbnail"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                            <br><small class="text-muted">BLOB</small>
                                        @elseif($customer->path_foto)
                                            <img src="{{ asset($customer->path_foto) }}"
                                                 alt="Foto" class="img-thumbnail"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                            <br><small class="text-muted">File</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $customer->nama }}</td>
                                    <td>{{ $customer->alamat ?? '-' }}</td>
                                    <td>
                                        {{ $customer->kelurahan ?? '' }}
                                        {{ $customer->kecamatan ? ', ' . $customer->kecamatan : '' }}
                                        {{ $customer->kota ? ', ' . $customer->kota : '' }}
                                        {{ $customer->provinsi ? ', ' . $customer->provinsi : '' }}
                                    </td>
                                    <td>
                                        @if($customer->blob_foto)
                                            <span class="badge bg-info">BLOB Database</span>
                                        @elseif($customer->path_foto)
                                            <span class="badge bg-success">File Storage</span>
                                        @else
                                            <span class="badge bg-secondary">No Photo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('customer.edit', $customer->idcustomer) }}" class="btn btn-sm btn-warning">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <form action="{{ route('customer.destroy', $customer->idcustomer) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus customer ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data customer</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
