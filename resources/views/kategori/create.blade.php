@extends('layouts.app')

@section('content')
<h2 class="text-2xl font-bold mb-6">Tambah Kategori Baru</h2>

<form action="{{ route('kategori.store') }}" method="POST" class="bg-white shadow rounded-lg p-6">
    @csrf
    
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4">
        <label for="nama_kategori" class="block text-sm font-medium text-gray-700">Nama Kategori</label>
        <input type="text" name="nama_kategori" id="nama_kategori" value="{{ old('nama_kategori') }}" class="w-full border rounded p-2 mt-1" required autofocus>
    </div>

    <div class="mt-6">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
        <a href="{{ route('kategori.index') }}" class="ml-3 text-gray-600 hover:underline">Batal</a>
    </div>
</form>
@endsection