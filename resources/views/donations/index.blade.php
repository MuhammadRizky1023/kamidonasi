@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Donasi</h2>
        <a href="{{ route('donations.create') }}" class="btn btn-primary">Tambah Donasi</a>
    </div>
    <div class="row">
        @foreach($donations as $donation)
        <div class="col-md-4 mb-4">
            <div class="card">
                <img src="{{ $donation['image'] }}" class="card-img-top" alt="{{ $donation['title'] }}">
                <div class="card-body">
                    <h5 class="card-title">{{ $donation['title'] }}</h5>
                    <p class="card-text">Target: Rp{{ number_format($donation['targetAmount'], 0, ',', '.') }}</p>
                    <p class="card-text">Terkumpul: Rp{{ number_format($donation['totalAmount'], 0, ',', '.') }}</p>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('donations.edit', $donation['id']) }}" class="btn btn-primary">Perbarui</a>
                        <form action="{{ route('donations.destroy', $donation['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus donasi ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        @if (empty($donations))
        <div class="col-md-12">
            <div class="alert alert-info" role="alert">
                Tidak ada donasi yang tersedia saat ini.
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
