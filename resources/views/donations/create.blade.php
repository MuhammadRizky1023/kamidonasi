@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Tambah Donasi') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('donations.store') }}" enctype="multipart/form-data" id="donation-form">
                        @csrf
                        <div class="form-group">
                            <label for="title">Judul Donasi</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="targetAmount">Target Jumlah Donasi</label>
                            <input type="number" class="form-control" id="targetAmount" name="targetAmount" value="{{ old('targetAmount') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="image">Gambar</label>
                            <input type="file" class="form-control-file" id="image" name="image" accept="image/*" required onchange="previewImage(event)">
                        </div>
                        <div class="form-group">
                            <img id="imagePreview" src="#" alt="Pratinjau Gambar" style="display: none; max-width: 100%; height: auto; margin-top: 10px;">
                        </div>
                        <button type="submit" class="btn btn-primary">Tambah Donasi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('imagePreview');
            output.src = reader.result;
            output.style.display = 'block';
        }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

@endsection
