@extends('layouts.app')

@section('content')
<h2 class="text-2xl font-bold mb-6">Edit Kategori: {{ $kategori->nama_kategori }}</h2>

<form action="{{ route('kategori.update', $kategori->id) }}" method="POST" class="bg-white shadow rounded-lg p-6">
    @csrf
    @method('PUT') @if ($errors->any())
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
        <input type="text" name="nama_kategori" id="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori) }}" class="w-full border rounded p-2 mt-1" required autofocus>
    </div>

    <div class="mt-6 border-t pt-3 flex justify-left">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Update
        </button>
        <a href="{{ route('kategori.index') }}" class="ml-3 text-gray-600 hover:underline py-2 px-4">Batal</a>
    </div>

</form>
@endsection