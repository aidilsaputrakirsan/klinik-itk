<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ObatController extends Controller
{
    public function index(Request $request)
    {
        $query = Obat::query();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_obat', 'like', "%{$search}%")
                  ->orWhere('kode_obat', 'like', "%{$search}%");
            });
        }

        $obats = $query->orderBy('nama_obat')->paginate(15);

        return Inertia::render('Obat/Index', [
            'obats' => $obats,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_obat' => 'required|string|max:20|unique:obats,kode_obat',
            'nama_obat' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'stok' => 'required|integer|min:0',
            'harga' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['is_active'] = true;

        Obat::create($validated);

        return redirect()->route('obat.index')
            ->with('success', 'Obat berhasil ditambahkan.');
    }

    public function update(Request $request, Obat $obat)
    {
        $validated = $request->validate([
            'kode_obat' => 'required|string|max:20|unique:obats,kode_obat,' . $obat->id,
            'nama_obat' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'stok' => 'required|integer|min:0',
            'harga' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $obat->update($validated);

        return redirect()->route('obat.index')
            ->with('success', 'Obat berhasil diperbarui.');
    }

    public function destroy(Obat $obat)
    {
        $obat->delete();

        return redirect()->route('obat.index')
            ->with('success', 'Obat berhasil dihapus.');
    }
}
