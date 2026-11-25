<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Decorind</title>
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.style')
    <style>.login-card { max-width:400px; margin:9vh auto; }</style>
</head>
<body>
<div class="login-card card-box">
    <div class="logo justify-content-center mb-3"><span>DECORIND</span><i class="logo-dot"></i></div>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="lbl" for="username">username</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" class="form-control" placeholder="Masukan Username" required autofocus>
            @error('username')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="lbl" for="password">Password</label>
            <input id="password" type="password" name="password" class="form-control" placeholder="Masukan Password" required>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Ingat saya</label>
        </div>
        <button class="btn btn-primary-x w-100 py-2" type="submit">Login</button>
    </form>
    <div class="footer p-0 pt-4">{{ config('decorind.footer') }}</div>
</div>
</body>
</html>
