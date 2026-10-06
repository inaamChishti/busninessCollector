<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sign in · Business Email Collector</title>
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/app.css')); ?>">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="brand auth-brand">
        <span class="brand-mark">BC</span>
        <span>Business Collector</span>
    </div>
    <h1>Welcome back</h1>
    <p>Sign in to manage collection.</p>

    <?php if(session('success')): ?>
        <div class="alert success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert danger"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo e(route('login')); ?>">
        <label>
            Email
            <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus autocomplete="email">
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
<?php /**PATH C:\xampp\htdocs\business collector - Copy\resources\views/auth/login.blade.php ENDPATH**/ ?>