<h1>Beranda Admin</h1>
<p>halo, {{ auth()->user()->nama }}</p>
<form method="POST" action="{{ route('logout') }}"> 
    @csrf
    <button type="submit">keluar</button>
</form>