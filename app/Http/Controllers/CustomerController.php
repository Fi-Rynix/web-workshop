<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        return view('pages.customer.index-customer', compact('customers'));
    }

    public function create1()
    {
        return view('pages.customer.create-customer1');
    }

    public function store1(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'alamat' => 'nullable|string',
            'provinsi' => 'nullable|string|max:100',
            'kota' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kelurahan' => 'nullable|string|max:100',
            'foto' => 'required|string',
        ]);

        $fotoBase64 = $request->foto;
        $fotoBlob = null;

        if ($fotoBase64) {
            if (strpos($fotoBase64, 'base64,') !== false) {
                $fotoBase64 = explode('base64,', $fotoBase64)[1];
            }
            $fotoBlob = base64_decode($fotoBase64);
        }

        Customer::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'provinsi' => $request->provinsi,
            'kota' => $request->kota,
            'kecamatan' => $request->kecamatan,
            'kelurahan' => $request->kelurahan,
            'blob_foto' => $fotoBlob,
            'path_foto' => null,
        ]);

        return redirect()->route('customer.index')->with('success', 'Customer berhasil ditambahkan dengan foto (BLOB)');
    }


    public function create2()
    {
        return view('pages.customer.create-customer2');
    }


    public function store2(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'alamat' => 'nullable|string',
            'provinsi' => 'nullable|string|max:100',
            'kota' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kelurahan' => 'nullable|string|max:100',
            'foto' => 'required|string',
        ]);

        $fotoBase64 = $request->foto;
        $pathFoto = null;

        if ($fotoBase64) {
            if (strpos($fotoBase64, 'base64,') !== false) {
                $fotoBase64 = explode('base64,', $fotoBase64)[1];
            }

            $fotoData = base64_decode($fotoBase64);

            $filename = 'customer_' . time() . '_' . uniqid() . '.png';
            $filepath = 'images/customer/' . $filename;

            Storage::disk('public')->put($filepath, $fotoData);

            $pathFoto = 'storage/' . $filepath;
        }

        Customer::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'provinsi' => $request->provinsi,
            'kota' => $request->kota,
            'kecamatan' => $request->kecamatan,
            'kelurahan' => $request->kelurahan,
            'blob_foto' => null,
            'path_foto' => $pathFoto,
        ]);

        return redirect()->route('customer.index')->with('success', 'Customer berhasil ditambahkan dengan foto (File)');
    }


    public function showBlob($id)
    {
        $customer = Customer::findOrFail($id);

        if (!$customer->blob_foto) {
            abort(404);
        }

        return response($customer->blob_foto)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=86400');
    }


    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('pages.customer.edit-customer', compact('customer'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'alamat' => 'nullable|string',
            'provinsi' => 'nullable|string|max:100',
            'kota' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kelurahan' => 'nullable|string|max:100',
        ]);

        $customer = Customer::findOrFail($id);

        $customer->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'provinsi' => $request->provinsi,
            'kota' => $request->kota,
            'kecamatan' => $request->kecamatan,
            'kelurahan' => $request->kelurahan,
        ]);

        return redirect()->route('customer.index')->with('success', 'Customer berhasil diperbarui');
    }


    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->path_foto) {
            $filepath = str_replace('storage/', '', $customer->path_foto);
            Storage::disk('public')->delete($filepath);
        }

        $customer->delete();

        return redirect()->route('customer.index')->with('success', 'Customer berhasil dihapus');
    }
}
