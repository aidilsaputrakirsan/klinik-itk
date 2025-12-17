<?php

namespace App\Http\Controllers;

use App\Models\Tindakan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TindakanController extends Controller
{
    public function index(Request $request)
    {
        $query = Tindakan::query();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_tindakan', 'like', "%{$search}%")
                  ->orWhere('kode_tindakan', 'like', "%{$search}%");
            });
        }

        $tindakans = $query->orderBy('nama_tindakan')->paginate(15);

        return Inertia::render('Tindakan/Index', [
            'tindakans' => $tindakans,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_tindakan' => 'required|string|max:20|unique:tindakans,kode_tindakan',
            'nama_tindakan' => 'required|string|max:255',
            'tarif' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['is_active'] = true;

        Tindakan::create($validated);

        return redirect()->route('tindakan.index')
            ->with('success', 'Tindakan berhasil ditambahkan.');
    }

    public function update(Request $request, Tindakan $tindakan)
    {
        $validated = $request->validate([
            'kode_tindakan' => 'required|string|max:20|unique:tindakans,kode_tindakan,' . $tindakan->id,
            'nama_tindakan' => 'required|string|max:255',
            'tarif' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $tindakan->update($validated);

        return redirect()->route('tindakan.index')
            ->with('success', 'Tindakan berhasil diperbarui.');
    }

    public function destroy(Tindakan $tindakan)
    {
        $tindakan->delete();

        return redirect()->route('tindakan.index')
            ->with('success', 'Tindakan berhasil dihapus.');
    }
}
