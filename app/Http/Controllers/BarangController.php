<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        //$rsetBarang = Barang::with('kategori')->latest()->paginate(10);
        
        if ($request->search){
            //query builder
            $rsetBarang = DB::table('barang')->select('kategori')
                                            ->join('kategori', 'barang.kategori_id', '=', 'kategori.id')
                                            ->select('barang.id','barang.merk','barang.seri','barang.spesifikasi','barang.stok','barang.kategori_id',DB::raw('ketKategori(kategori.kategori) as kat'))
                                            ->where('barang.id','like','%'.$request->search.'%')
                                            ->orWhere('barang.merk','like','%'.$request->search.'%')
                                            ->paginate(10);
                                        
        }else {
            $rsetBarang = DB::table('barang')->select('kategori')
                                            ->join('kategori', 'barang.kategori_id', '=', 'kategori.id')
                                            ->select('barang.id','barang.merk','barang.seri','barang.spesifikasi','barang.stok','barang.kategori_id',DB::raw('ketKategori(kategori.kategori) as kat'))
                                            ->paginate(10);
        }
        
        //return $rsetBarang;

        return view('barang.index', compact('rsetBarang'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $akategori = Kategori::all();
        return view('barang.create',compact('akategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //return $request;
        //validate form
        $request->validate([
            'merk'          => 'required',
            'seri'          => 'required',
            'spesifikasi'   => 'required',
            'stok'          => 'required',
            'kategori_id'   => 'required',

        ]);

        //create post
        // Barang::create([
        //     'merk'             => $request->merk,
        //     'seri'             => $request->seri,
        //     'spesifikasi'      => $request->spesifikasi,
        //     'stok'             => $request->stok,
        //     'kategori_id'      => $request->kategori_id,
        // ]);

        try {
            DB::beginTransaction(); // <= Mulai transaksi
        
            // Simpan data barang
            $barang = new Barang();
            $barang->merk = $request->merk;
            $barang->seri = $request->seri;
            $barang->spesifikasi = $request->spesifikasi;
            $barang->stok = 0;
            $barang->kategori_id = $request->kategori_id;
            $barang->save();
        
            DB::commit(); // <= Commit perubahan
        } catch (\Exception $e) {
            report($e);
        
            DB::rollBack(); // <= Rollback jika terjadi kesalahan
            // return redirect()->route('barang.index')->with(['error' => 'gagal menyimpan data.']);
        }

        //redirect to index
        return redirect()->route('barang.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rsetBarang = Barang::find($id);

        //return $rsetBarang;

        //return view
        return view('barang.show', compact('rsetBarang'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    $akategori = Kategori::all();
    $rsetBarang = Barang::find($id);
    $selectedKategori = Kategori::find($rsetBarang->kategori_id);

    return view('barang.edit', compact('rsetBarang', 'akategori', 'selectedKategori'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'merk'        => 'required',
            'seri'        => 'required',
            'spesifikasi' => 'required',
            'stok'        => 'required',
            'kategori_id' => 'required',
        ]);

        $rsetBarang = Barang::find($id);

            //update post without image
            $rsetBarang->update([
                'merk'          => $request->merk,
                'seri'          => $request->seri,
                'spesifikasi'   => $request->spesifikasi,
                'stok'          => $request->stok,
                'kategori_id'   => $request->kategori_id,
            ]);

        // Redirect to the index page with a success message
        return redirect()->route('barang.index')->with(['success' => 'Data Berhasil Diubah!']);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (DB::table('barangmasuk')->where('barang_id', $id)->exists()) {
        return redirect()->route('barang.index')->with(['gagal' => 'Gagal dihapus']);
    } elseif (DB::table('barangkeluar')->where('barang_id', $id)->exists()) {
        return redirect()->route('barang.index')->with(['gagal' => 'Gagal dihapus']);
    } else {
        $rsetBarang = Barang::find($id);
        $rsetBarang->delete();
        return redirect()->route('barang.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}

    // API
    // [invent-01] Semua Barang
    function getAPIBarang(){
        $barang = Barang::all();
        $data = array("data"=>$barang);

        return response()->json($data);
    }

    // [invent-02] Buat Barang Baru
    function createAPIBarang(Request $request)
    {
        // Validasi data yang diterima dari request
        $validatedData = $request->validate([
            'merk'          => 'required|string|max:30',
            'seri'          => 'required|string|max:40',
            'spesifikasi'   => 'required|string',
            // 'stok'          => 'required|integer',
            'kategori_id'   => 'nullable|exists:kategori_id'
        ]);

        // Buat kategori baru menggunakan data yang sudah divalidasi
        $barang = Barang::create([
            'id' => $validatedData['id'],
            'merk' => $validatedData['merk'],
            'seri' => $validatedData['seri'],
            'spesifikasi' => $validatedData['spesifikasi'],
            // 'stok' => $validatedData['stok'],
            'kategori_id' => $validatedData['kategori_id']
        ]);

        // Mengembalikan respons JSON dengan data barang yang baru dibuat
        return response()->json([
            'data' => [
                'id' => $barang->id,
                'merk' => $barang->merk,
                'seri' => $barang->seri,
                'spesifikasi' => $barang->spesifikasi,
                // 'stok' => $barang->stok,
                'kategori_id' => $barang->kategori_id,
                'created_at' => $barang->created_at,
                'updated_at' => $barang->updated_at
            ]
        ], 200); // Status 200 Created
    }

    // [invent-03] Salah Satu Barang
    public function showAPIBarang($id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json(['status' => 'Barang tidak ditemukan'], 404);
        }


        return response()->json(['data' => $barang], 200);
    }

    // [invent-04] Hapus Barang
    public function deleteAPIBarang(string $id)
    {
        if (DB::table('barang')->where('kategori_id', $id)->exists()){
            // Menambahkan return response dengan status 500
            return response()->json(['error' => 'kategori tidak dapat dihapus'], 500);
        } else {
            $rseBarang = Barang::find($id);
            if ($rseBarang) {
                $rseBarang->delete();
                return response()->json(['success' => 'Berhasil dihapus'], 200);
            } else {
                return response()->json(['error' => 'Barang tidak ditemukan'], 404);
            }
        }
    }

    // [invent-05] Update Salah Satu Barang
    function updateAPIBarang(Request $request, string $id) {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json(['status' => 'Barang tidak ditemukan'], 404);
        }


        $barang->merk=$request->merk;
        $barang->seri=$request->seri;
        $barang->spesifikasi=$request->spesifikasi;
        $barang->kategori_id=$request->kategori_id;
        $barang->save();


        return response()->json(['status' => 'Barang berhasil diubah'], 200);          
    }

}