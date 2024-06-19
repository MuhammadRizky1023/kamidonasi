@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Edit Donasi') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('donations.update', $id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="title">Nama Donasi</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ $donation['title'] }}" required>
                        </div>

                        <div class="form-group">
                            <label for="targetAmount">Jumlah</label>
                            <input type="number" class="form-control" id="targetAmount" name="targetAmount" value="{{ $donation['targetAmount'] }}" required>
                        </div>

                        <div class="form-group">
                            <label for="image">Gambar</label><br>
                            @if ($donation['image'])
                                <img src="{{ $donation['image'] }}" alt="Gambar Donasi" style="max-width: 200px; height: auto;">
                            @else
                                <span>Tidak ada gambar tersedia</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="newImage">Ganti Gambar</label>
                            <input type="file" class="form-control-file" id="newImage" name="newImage" accept="image/*" onchange="previewNewImage(event)">
                        </div>

                        <div class="form-group">
                            <img id="newImagePreview" src="#" alt="Pratinjau Gambar" style="display: none; max-width: 100%; height: auto; margin-top: 10px;">
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewNewImage(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('newImagePreview');
            output.src = reader.result;
            output.style.display = 'block';
        }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

@endsection
