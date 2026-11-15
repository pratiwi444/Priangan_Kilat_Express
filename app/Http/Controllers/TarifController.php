<?php

namespace App\Http\Controllers;

use App\Models\Zona;
use Illuminate\Http\Request;

class TarifController extends Controller
{
    public function index() { return view('tarif.index', ['zona' => Zona::orderBy('id')->get()]); }

public function update(Request $r, Zona $zona)    // hanya rute manajer
{
    $d = $r->validate(['tarif_per_kg' => 'required|integer|min:1']);
    $zona->update($d);
    return back()->with('ok', 'Tarif diperbarui. Berlaku untuk paket yang dibuat sesudah ini.');
}
}
