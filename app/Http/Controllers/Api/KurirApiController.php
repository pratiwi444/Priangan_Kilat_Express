<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatusPaket;
use App\Http\Controllers\Controller;
use App\Models\{Paket, User};
use App\Services\PaketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KurirApiController extends Controller
{
    public function login(Request $r)
    {
        $r->validate(['kode_kurir' => 'required', 'password' => 'required']);

        $user = User::where('username', $r->kode_kurir)->where('role', 'kurir')->first();
        if (!$user || !Hash::check($r->password, $user->password)) {
            return response()->json(['message' => 'Kode kurir atau password salah.'], 422);
        }
        if (!$user->kurir || !$user->kurir->aktif) {
            return response()->json(['message' => 'Akun kurir tidak aktif.'], 403);
        }

        return [
            'token'                => $user->createToken('apk')->plainTextToken,
            'nama'                 => $user->nama,
            'wajib_ganti_password' => $user->wajib_ganti_password,
        ];
    }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()->delete();
        return ['message' => 'Berhasil keluar.'];
    }

    public function gantiPassword(Request $r)
    {
        $r->validate(['password_baru' => 'required|min:8'], [
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min'      => 'Password baru minimal 8 karakter.',
        ]);
        $r->user()->update(['password' => $r->password_baru, 'wajib_ganti_password' => false]);
        return ['message' => 'Password berhasil diganti.'];
    }

    /** Paket harus milik kurir yang sedang login; selain itu 403. */
    private function milik(Request $r, Paket $paket): Paket
    {
        abort_unless((int) $paket->kurir_id === (int) $r->user()->kurir->id, 403, 'Paket ini bukan milik Anda.');
        return $paket;
    }

    public function paketTugas(Request $r)
    {
        $k = $r->user()->kurir;
        $paket = $k->paket()->where('status', 'dikirim')->orderBy('id')->get();

        return [
            'jumlah' => $paket->count(),
            'batas'  => 20,
            'data'   => $paket->map(fn ($p) => $this->ringkas($p)),
        ];
    }

    public function detail(Request $r, Paket $paket)
    {
        return $this->ringkas($this->milik($r, $paket));
    }

    public function terkirim(Request $r, Paket $paket, PaketService $svc)
    {
        $svc->ubahStatus($this->milik($r, $paket), StatusPaket::Terkirim, $r->user(), $r->input('catatan'));
        return ['message' => 'Paket Terkirim.'];
    }

    public function gagalKirim(Request $r, Paket $paket, PaketService $svc)
    {
        $r->validate(['catatan' => 'required'], ['catatan.required' => 'Catatan wajib diisi untuk Gagal Kirim.']);
        $svc->ubahStatus($this->milik($r, $paket), StatusPaket::GagalKirim, $r->user(), $r->catatan);
        return ['message' => 'Paket dicatat Gagal Kirim.'];
    }

    public function cod(Request $r)
    {
        $paket = $r->user()->kurir->paket()->with('setoran')
            ->where('is_cod', true)->where('status', 'terkirim')->orderBy('id')->get();

        return [
            'total_belum_disetor' => (int) $paket->filter(fn ($p) => !$p->setoran)->sum('nilai_cod'),
            'data' => $paket->map(fn ($p) => [
                'id'        => $p->id,
                'no_resi'   => $p->no_resi,
                'nilai_cod' => $p->nilai_cod,
                'disetor'   => (bool) $p->setoran,
            ]),
        ];
    }

    private function ringkas(Paket $p): array
    {
        return [
            'id'               => $p->id,
            'no_resi'          => $p->no_resi,
            'status'           => $p->status->value,
            'nama_penerima'    => $p->nama_penerima,
            'telepon_penerima' => $p->telepon_penerima,
            'alamat_penerima'  => $p->alamat_penerima,
            'kota_tujuan'      => $p->kota_tujuan,
            'berat'            => (float) $p->berat,
            'layanan'          => $p->layanan,
            'is_cod'           => $p->is_cod,
            'nilai_cod'        => $p->nilai_cod,
        ];
    }
}