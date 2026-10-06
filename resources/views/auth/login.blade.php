<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sign in · Business Email Collector</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="brand auth-brand">
        <span class="brand-mark">BC</span>
        <span>Business Collector</span>
    </div>
    <h1>Welcome back</h1>
    <p>Sign in to manage collection.</p>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert danger">{{ $errors->first() }}</div>
    @endif

    <form method="post" action="{{ route('login') }}">
        <label>
            Email
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
        </label>
        <label>
            Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <label class="check">
            <input type="checkbox" name="remember" value="1"> Keep me signed in
        </label>
        <button class="btn primary full">Sign in</button>
    </form>
</div>
</body>
</html>
