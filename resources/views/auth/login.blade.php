<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Brundo International PLC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v={{ filemtime(public_path('css/theme.css')) }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh; font-family: 'Inter', system-ui, sans-serif;
            display: flex; align-items: center; justify-content: center;
            padding: 40px 20px; position: relative; overflow-x: hidden;
            background:
                radial-gradient(70% 60% at 100% 0%, rgba(45, 212, 191, .32), transparent 60%),
                radial-gradient(60% 50% at 0% 100%, rgba(20, 184, 166, .18), transparent 60%),
                linear-gradient(150deg, #0c1d2b 0%, #0f2f3c 60%, #0b5f5a 100%);
        }
        body::before {
            content: ''; position: fixed; inset: 0; pointer-events: none; opacity: .5;
            background-image: radial-gradient(rgba(255, 255, 255, .08) 1px, transparent 1px);
            background-size: 22px 22px;
            mask-image: linear-gradient(180deg, transparent, #000 40%, transparent);
        }

        .stage { position: relative; z-index: 1; width: 100%; max-width: 420px; }
        .brand {
            display: flex; align-items: center; justify-content: center; gap: 13px;
            margin-bottom: 30px; text-decoration: none;
        }
        .brand-icon {
            width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center;
            font: 800 21px 'Plus Jakarta Sans', sans-serif; color: #fff;
            background: linear-gradient(135deg, #2dd4bf, #0b7f78);
            box-shadow: 0 12px 32px -8px rgba(45, 212, 191, .7), inset 0 1px 0 rgba(255, 255, 255, .3);
        }
        .brand-name { font: 750 20px 'Plus Jakarta Sans', sans-serif; letter-spacing: -.02em; color: #fff; }
        .brand-sub { font-size: 11.5px; color: rgba(255, 255, 255, .55); letter-spacing: .06em; text-transform: uppercase; }

        .card {
            background: #fff; border-radius: 20px; padding: 38px 36px;
            box-shadow: 0 30px 70px -20px rgba(0, 0, 0, .5);
        }
        .card h2 { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 26px; letter-spacing: -.02em; margin-bottom: 6px; color: var(--text, #1c2933); }
        .card .sub { color: var(--muted, #6b7d88); font-size: 14px; margin-bottom: 26px; }
        .form-field { margin-bottom: 18px; }
        .form-field label { display: block; font-size: 13px; font-weight: 600; color: #2d4458; margin-bottom: 7px; }
        .form-field input {
            width: 100%; min-height: 46px; padding: 11px 14px; font: inherit; font-size: 14.5px;
            color: var(--text, #1c2933); border: 1px solid var(--border-2, #d7dee3);
            border-radius: 11px; background: #fbfcfd; transition: border-color .15s, box-shadow .15s;
        }
        .form-field input:focus {
            outline: none; border-color: #0b7f78; background: #fff;
            box-shadow: 0 0 0 4px rgba(11, 127, 120, .12);
        }
        .remember { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #4b5c68; margin: 4px 0 22px; }
        .remember input { width: 16px; height: 16px; }
        .btn-login {
            width: 100%; min-height: 48px; font-size: 15px; border-radius: 11px;
            display: inline-flex; align-items: center; justify-content: center;
            background: #0b7f78; color: #fff; border: none; cursor: pointer;
            font-family: inherit; font-weight: 600; transition: background .15s, transform .15s;
            box-shadow: 0 10px 24px -10px rgba(11, 127, 120, .6);
        }
        .btn-login:hover { background: #086059; transform: translateY(-1px); }
        .alert { margin-bottom: 18px; }

        .cred { margin-top: 26px; padding-top: 22px; border-top: 1px solid var(--border, #e5eaee); }
        .cred p { font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--muted, #6b7d88); margin-bottom: 12px; }
        .cred-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .cred-btn {
            text-align: left; padding: 9px 12px; border-radius: 10px; border: 1px solid var(--border, #e5eaee);
            background: #fbfcfd; cursor: pointer; font: inherit; transition: border-color .15s, background .15s;
        }
        .cred-btn:hover { border-color: #0b7f78; background: #effaf8; }
        .cred-btn strong { display: block; font-size: 13px; color: var(--text, #1c2933); }
        .cred-btn span { font-size: 11.5px; color: var(--muted, #6b7d88); }

        .foot { text-align: center; margin-top: 24px; font-size: 12.5px; color: rgba(255, 255, 255, .5); }
        .foot a { color: #8ef0e0; text-decoration: none; font-weight: 600; }
        .foot a:hover { text-decoration: underline; }

        @media (max-width: 480px) {
            body { padding: 28px 14px; }
            .card { padding: 30px 24px; }
            .cred-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="stage">
    <a href="{{ route('home') }}" class="brand">
        <div class="brand-icon">B</div>
        <div>
            <div class="brand-name">Brundo International PLC</div>
            <div class="brand-sub">Ethiopian Spices · Operations</div>
        </div>
    </a>

    <div class="card">
        <h2>Welcome back</h2>
        <p class="sub">Sign in to continue to your workspace.</p>

        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@company.com">
            </div>
            <div class="form-field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            </div>
            <label class="remember"><input type="checkbox" name="remember"> Keep me signed in</label>
            <button type="submit" class="btn-login">Sign in</button>
        </form>

        @if(app()->environment('local'))
        <div class="cred">
            <p>Test accounts · password “password”</p>
            <div class="cred-grid">
                @foreach(['Admin' => 'admin@erp.local', 'Store' => 'stock@erp.local', 'Production' => 'production@erp.local', 'Cashier' => 'cashier@erp.local', 'HR' => 'hr@erp.local'] as $role => $email)
                <button type="button" class="cred-btn" data-email="{{ $email }}"><strong>{{ $role }}</strong><span>{{ $email }}</span></button>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="foot">
        &copy; {{ date('Y') }} Brundo International PLC &nbsp;·&nbsp; <a href="{{ route('home') }}">&larr; Back to site</a>
    </div>
</div>

<script>
document.querySelectorAll('.cred-btn').forEach(b => b.addEventListener('click', () => {
    document.getElementById('email').value = b.dataset.email;
    document.getElementById('password').value = 'password';
    document.getElementById('password').focus();
}));
</script>
</body>
</html>
