<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Kreait\Laravel\Firebase\Facades\Firebase;

class DonationController extends Controller
{
    protected $database;
    protected $firebaseStorage;

    public function __construct()
    {
        $this->database = Firebase::database();
        $this->firebaseStorage = Firebase::storage();
    }

    public function index()
    {
        try {
            $donations = [];
            $donationsRef = $this->database->getReference('donations');
            $snapshot = $donationsRef->getSnapshot();

            if ($snapshot->exists()) {
                $donationsData = $snapshot->getValue();

                foreach ($donationsData as $key => $donation) {
                    if (isset($donation['image'])) {
                        $imageUrl = $donation['image'];
                        $donations[] = [
                            'id' => $key,
                            'title' => $donation['title'],
                            'targetAmount' => $donation['targetAmount'],
                            'totalAmount' => $donation['totalAmount'],
                            'image' => $imageUrl,
                        ];
                    }
                }
            }

            return view('donations.index', compact('donations'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }



    public function create()
    {
        return view('donations.create');
    }

    public function store(Request $request)
    {
        try {
            // Validasi data
            $data = $request->validate([
                'title' => 'required|string',
                'targetAmount' => 'required|numeric',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Upload gambar ke Firebase Storage
            $image = $request->file('image');
            $imagePath = 'donation_images/' . $image->getClientOriginalName();

            $this->firebaseStorage->getBucket()->upload(
                fopen($image->getRealPath(), 'r'),
                [
                    'name' => $imagePath,
                    'predefinedAcl' => 'publicRead'
                ]
            );

            // URL gambar di Firebase Storage
            $imageUrl = $this->firebaseStorage->getBucket()->object($imagePath)->signedUrl(strtotime('+5 minutes'));

            // Data donasi baru
            $newDonation = [
                'title' => $data['title'],
                'targetAmount' => $data['targetAmount'],
                'totalAmount' => 0,
                'image' => $imageUrl,
                'progress' => 0,
            ];

            // Simpan ke Firebase Realtime Database
            $newDonationRef = $this->database->getReference('donations')->push($newDonation);
            $donationId = $newDonationRef->getKey();

            // Update data dengan ID yang dihasilkan
            $newDonation['id'] = $donationId;
            $this->database->getReference('donations/' . $donationId)->update($newDonation);

            return redirect()->route('donations.index')->with('success', 'Donasi berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $donationRef = $this->database->getReference('donations/' . $id);
            $snapshot = $donationRef->getSnapshot();

            if ($snapshot->exists()) {
                $donation = $snapshot->getValue();
                return view('donations.edit', compact('donation', 'id'));
            } else {
                return redirect()->route('donations.index')->with('error', 'Donasi tidak ditemukan');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validasi data
            $data = $request->validate([
                'title' => 'required|string',
                'targetAmount' => 'required|numeric',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $donationRef = $this->database->getReference('donations/' . $id);
            $snapshot = $donationRef->getSnapshot();

            if ($snapshot->exists()) {
                $updatedDonation = [
                    'title' => $data['title'],
                    'targetAmount' => $data['targetAmount'],
                ];

                // Cek apakah pengguna mengunggah gambar baru
                if ($request->hasFile('image')) {
                    // Hapus gambar lama dari Firebase Storage
                    $currentImageUrl = $snapshot->child('image')->getValue();
                    $currentImagePath = parse_url($currentImageUrl, PHP_URL_PATH);
                    $this->firebaseStorage->getBucket()->object($currentImagePath)->delete();

                    // Upload gambar baru ke Firebase Storage
                    $image = $request->file('image');
                    $imagePath = 'donation_images/' . $image->getClientOriginalName(); // Path di Firebase Storage

                    $this->firebaseStorage->getBucket()->upload(
                        fopen($image->getRealPath(), 'r'),
                        [
                            'name' => $imagePath,
                            'predefinedAcl' => 'publicRead'
                        ]
                    );

                    // URL gambar di Firebase Storage
                    $imageUrl = $this->firebaseStorage->getBucket()->object($imagePath)->signedUrl(strtotime('+5 minutes'));

                    // Simpan URL gambar baru
                    $updatedDonation['image'] = $imageUrl;
                }

                // Update data donasi di Firebase
                $donationRef->update($updatedDonation);

                return redirect()->route('donations.index')->with('success', 'Donasi berhasil diperbarui');
            } else {
                return redirect()->route('donations.index')->with('error', 'Donasi tidak ditemukan');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $donationRef = $this->database->getReference('donations/' . $id);
            $snapshot = $donationRef->getSnapshot();

            if ($snapshot->exists()) {
                $donationData = $snapshot->getValue();


                // Hapus data dari Firebase Realtime Database
                $donationRef->remove();

                // Redirect kembali ke halaman index setelah penghapusan berhasil
                return redirect()->route('donations.index')->with('success', 'Donasi berhasil dihapus');
            } else {
                return redirect()->route('donations.index')->with('error', 'Donasi tidak ditemukan');
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting donation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

}
