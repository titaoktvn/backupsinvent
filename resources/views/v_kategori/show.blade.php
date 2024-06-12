@extends('layouts.adm-main')

@section('content')
<div class="container">
        <div class="pull-left">
            <h2>TAMPILKAN KATEGORI</h2>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow rounded">
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <td>DESKRIPSI</td>
                                <td>{{ $rsetKategori->deskripsi }}</td>
                            </tr>
                            <tr>
                                <td>KATEGORI</td>
                                <td>{{ $rsetKategori->kategori }}</td>
                            </tr>
                          
                        </table>
                    </div>
               </div>
            </div>

           
        </div>
        <div class="row">
            <div class="col-md-12  text-center">
                

                <a href="{{ route('kategori.index') }}" class="btn btn-md btn-primary mb-3">Back</a>
            </div>
        </div>
    </div>
@endsection