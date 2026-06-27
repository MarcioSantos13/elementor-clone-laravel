<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Page Builder') - {{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f5f5f5; color: #333; }
        .navbar { background: #fff; border-bottom: 1px solid #ddd; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .navbar-brand a { text-decoration: none; font-weight: 700; color: #333; font-size: 1.2rem; }
        .navbar-menu { display: flex; gap: 1rem; }
        .nav-link { text-decoration: none; color: #666; }
        .nav-link-primary { background: #007bff; color: #fff; padding: .4rem 1rem; border-radius: 4px; }
        .main-content { padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .btn { display: inline-block; padding: .5rem 1rem; border-radius: 4px; text-decoration: none; font-size: .9rem; border: none; cursor: pointer; transition: background .2s; }
        .btn-primary { background: #007bff; color: #fff; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; color: #fff; }
        .btn-success:hover { background: #1e7e34; }
        .btn-secondary { background: #6c757d; color: #fff; }
        .btn-secondary:hover { background: #545b62; }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn-danger:hover { background: #c82333; }
        .btn-sm { padding: .25rem .5rem; font-size: .8rem; }
        .table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .table th, .table td { padding: .75rem 1rem; text-align: left; border-bottom: 1px solid #eee; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .th-actions, .td-actions { text-align: right; }
        .td-title { font-weight: 500; }
        .td-date { white-space: nowrap; color: #888; font-size: .85rem; }
        code { background: #f0f0f0; padding: .1rem .4rem; border-radius: 3px; font-size: .85rem; }
        .badge { display: inline-block; padding: .2rem .5rem; border-radius: 12px; font-size: .75rem; font-weight: 600; }
        .badge-draft { background: #fff3cd; color: #856404; }
        .badge-published { background: #d4edda; color: #155724; }
        .actions { display: flex; gap: .5rem; }
        .inline-form { display: inline; }
        .nav-link { text-decoration: none; color: #666; padding: .3rem .5rem; }
        .nav-link:hover { color: #333; }
        .nav-link-primary { background: #007bff; color: #fff !important; padding: .4rem 1rem; border-radius: 4px; }
        .nav-link-primary:hover { background: #0056b3; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); padding: 2rem; max-width: 640px; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-weight: 500; margin-bottom: .35rem; }
        .form-control { width: 100%; padding: .6rem .75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; transition: border-color .2s; }
        .form-control:focus { outline: none; border-color: #007bff; box-shadow: 0 0 0 3px rgba(0,123,255,.15); }
        .form-row { display: flex; gap: 1rem; }
        .form-row .form-group { flex: 1; }
        .form-actions { display: flex; gap: .75rem; align-items: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #eee; }
        .error { color: #dc3545; font-size: .85rem; }
        .required { color: #dc3545; }
        .optional { color: #999; font-weight: 400; font-size: .85rem; }
        .toast { position: fixed; top: 1.5rem; right: 1.5rem; padding: .9rem 1.2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,.15); display: flex; align-items: center; gap: .75rem; z-index: 1000; animation: slideInToast .3s ease; transition: opacity .4s; }
        .toast-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .toast-close { background: none; border: none; font-size: 1.3rem; cursor: pointer; color: inherit; margin-left: auto; padding: 0 .2rem; }
        @keyframes slideInToast { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .empty-state { text-align: center; padding: 4rem 2rem; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .empty-icon { font-size: 3rem; margin-bottom: 1rem; }
        .empty-state h2 { margin-bottom: .5rem; }
        .empty-state p { color: #888; margin-bottom: 1.5rem; }
        .pagination { margin-top: 1.5rem; text-align: center; }
        .pagination nav > div { display: flex; justify-content: center; gap: .25rem; }
        .pagination a, .pagination span { display: inline-block; padding: .4rem .75rem; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; background: #fff; }
        .pagination span[aria-current] { background: #007bff; color: #fff; border-color: #007bff; }
        h1 { font-size: 1.75rem; }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="{{ route('page-builder.pages.index') }}">{{ config('app.name') }}</a>
        </div>
        <div class="navbar-menu">
            <a href="{{ route('page-builder.pages.index') }}" class="nav-link">Pages</a>
            <a href="{{ route('page-builder.pages.create') }}" class="nav-link nav-link-primary">New Page</a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="nav-link" style="background:none;border:none;cursor:pointer;color:#666">Logout</button>
            </form>
        </div>
    </nav>

    <main class="main-content">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
