<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Midtrans\Snap;
use Midtrans\Notification;

class HomeController extends Controller
{
    public function index()
    {
        try {
            $database = Firebase::database();
            $donations = [];

            // Ambil referensi donasi dari Firebase Realtime Database
            $donationsRef = $database->getReference('donations');
            $snapshot = $donationsRef->getSnapshot();

            // Ambil nilai dari snapshot
            $donationsData = $snapshot->getValue();

            // Jika ada data donasi
            if ($donationsData) {
                foreach ($donationsData as $key => $donation) {
                    // Pastikan donasi memiliki image sebelum menambahkannya ke dalam array
                    if (isset($donation['image'])) {
                        // Ambil URL gambar dari Firebase Storage
                        $imageUrl = $donation['image'];

                        // Tetapkan nilai default untuk totalAmount jika tidak ada
                        $totalAmount = isset($donation['totalAmount']) ? $donation['totalAmount'] : 0;

                        // Tambahkan donasi ke dalam array $donations
                        $donations[] = [
                            'id' => $key,
                            'title' => $donation['title'],
                            'targetAmount' => $donation['targetAmount'],
                            'totalAmount' => $totalAmount,
                            'imageUrl' => $imageUrl,
                            // tambahkan atribut lain yang dibutuhkan
                        ];
                    }
                }
            }

            return view('home', compact('donations'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getToken(Request $request)
    {
        // Validasi request
        $validatedData = $request->validate([
            'donationId' => 'required',
            'amount' => 'required|numeric|min:1000', 
            'first_name' => 'required|string',
            'email' => 'required|email',
        ]);

        $donationId = $validatedData['donationId'];
        $amount = $validatedData['amount'];
        $first_name = $validatedData['first_name'];
        $email = $validatedData['email'];

        $params = [
            'transaction_details' => [
                'order_id' => uniqid(),
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $first_name,
                'email' => $email,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            return response()->json(['token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mendapatkan token pembayaran: ' . $e->getMessage()], 500);
        }
    }

    public function notificationHandler(Request $request)
    {
        // Validasi notifikasi menggunakan Midtrans\Notification
        $notification = new Notification();

        // Memastikan notifikasi transaksi yang valid
        if ($notification->transaction_status == 'capture' || $notification->transaction_status == 'settlement') {
            $donationId = $request->input('donationId');
            $amount = $request->input('amount');

            try {
                $donationsRef = Firebase::database()->getReference('donations/' . $donationId);
                $snapshot = $donationsRef->getSnapshot();

                if ($snapshot->exists()) {
                    $donation = $snapshot->getValue();
                    $progress = $donation['progress'] ?? 0;
                    $progress = min($progress + 10, 100);

                    $donationsRef->update([
                        'progress' => $progress,
                    ]);
                }

                return response()->json(['message' => 'Notifikasi berhasil ditangani']);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Gagal memperbarui progres donasi: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['message' => 'Notifikasi tidak perlu ditangani']);
    }
}
