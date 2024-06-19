@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('List Donasi') }}</div>
                <div class="card-body">
                    <div id="donation-list" class="row">
                        @forelse ($donations as $donation)
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <img src="{{ $donation['imageUrl'] }}" class="card-img-top" alt="Gambar Donasi">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $donation['title'] }}</h5>
                                    <p class="card-text">Target: Rp{{ number_format($donation['targetAmount']) }}</p>
                                    <p class="card-text">Total Terkumpul: Rp{{ number_format($donation['totalAmount']) }}</p>
                                    <div class="progress">
                                        @if (isset($donation['progress']))
                                        <div class="progress-bar" role="progressbar" style="width: {{ $donation['progress'] }}%" aria-valuenow="{{ $donation['progress'] }}" aria-valuemin="0" aria-valuemax="100">{{ $donation['progress'] }}%</div>
                                        @else
                                        <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                        @endif
                                    </div>
                                    <button class="btn btn-primary mt-3 donate-btn"
                                        data-id="{{ $donation['id'] }}"
                                        data-amount="{{ $donation['targetAmount'] - $donation['totalAmount'] }}">Donasi</button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p id="no-donations-message" class="text-center">Tidak ada donasi tersedia</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    // Tambahkan event listener pada tombol donasi
    document.querySelectorAll('.donate-btn').forEach(button => {
        button.addEventListener('click', function() {
            const donationId = this.dataset.id;
            const amount = parseInt(this.dataset.amount);
            fetch('{{ route("get.token") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ donationId, amount })
            })
            .then(response => response.json())
            .then(data => {
                snap.pay(data.token, {
                    onSuccess: function(result) {
                        updateProgress(donationId, amount);
                    },
                    onPending: function(result) {
                        console.log('Menunggu: ', result);
                    },
                    onError: function(result) {
                        console.log('Error: ', result);
                        alert('Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi nanti.');
                    }
                });
            })
            .catch(error => {
                console.error('Error mendapatkan token:', error);
                alert('Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi nanti.');
            });
        });
    });

    function updateProgress(donationId, amount) {
        fetch('{{ route("notification.handler") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ donationId, amount })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Progress donasi berhasil diperbarui:', data.message);
            // Jika perlu, tambahkan logika untuk memperbarui tampilan setelah pembayaran berhasil
        })
        .catch(error => {
            console.error('Error memperbarui progress donasi:', error);
            // Tampilkan pesan kesalahan jika terjadi masalah dalam memperbarui progress donasi
        });
    }
</script>
@endsection
