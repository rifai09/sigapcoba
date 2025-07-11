@extends('adminlte.layouts.app')

@section('title', 'Persetujuan Usulan')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid pt-3">
        <h4>Daftar Usulan Barang/Jasa</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-striped mt-3">
            <thead>
                <tr>
                    <th>Nama Barang/Jasa</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Link e-Katalog</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usulans as $usulan)
                <tr>
                    <td>{{ $usulan->nama_barang }}</td>
                    <td>{{ $usulan->jumlah }}</td>
                    <td>Rp{{ number_format($usulan->perkiraan_harga, 0, ',', '.') }}</td>
                    <td>
                        @if($usulan->link_ekatalog)
                            <a href="{{ $usulan->link_ekatalog }}" target="_blank">Lihat</a>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <span class="badge 
                            @if($usulan->status == 'Disetujui') badge-success 
                            @elseif($usulan->status == 'Ditolak') badge-danger 
                            @else badge-warning @endif">
                            {{ $usulan->status }}
                        </span>
                    </td>
                    <td>
                        @if($usulan->status == 'Menunggu')
                            <form action="{{ route('persetujuan.setujui', $usulan->id) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Setujui</button>
                            </form>
                            <form action="{{ route('persetujuan.tolak', $usulan->id) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">Tolak</button>
                            </form>
                        @else
                            Tidak ada aksi
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
