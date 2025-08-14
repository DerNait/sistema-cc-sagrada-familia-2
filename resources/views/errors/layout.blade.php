<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Error')</title>
  <style>
    :root{
      --brand-green:#86B877;
      --brand-navy:#0b2a3a;
      --text:#0f172a;
      --muted:#475569;
    }
    *{box-sizing:border-box}
    html,body{height:100%;margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,'Noto Sans',sans-serif;color:var(--text)}
    .wrap{display:grid;grid-template-columns:1fr 1fr;min-height:100vh}
    .left{background:#fff;display:flex;flex-direction:column;justify-content:center;padding:40px 8vw;position:relative}
    .logo{position:absolute;top:22px;left:32px;height:44px}
    .code{font-size:142px;line-height:1;font-weight:800;color:var(--brand-green);letter-spacing:2px}
    .msg{font-size:30px;margin-top:18px;color:var(--muted)}
    .btn{display:inline-flex;gap:10px;align-items:center;margin-top:28px;background:var(--brand-navy);color:#fff;text-decoration:none;padding:14px 22px;border-radius:14px;box-shadow:0 8px 20px rgba(0,0,0,.12)}
    .btn:focus,.btn:hover{filter:brightness(.95)}
    .right{position:relative;overflow:hidden}
    .bg{position:absolute;inset:0;background-size:cover;background-position:center}
    .scrim::after{content:"";position:absolute;inset:0;background:rgba(0,0,0,.48)}
    @media (max-width: 980px){ .wrap{grid-template-columns:1fr} .right{height:40vh} }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="left">
      @hasSection('logo')
        <img class="logo" src="@yield('logo')" alt="Logo">
      @endif

      <div class="code">@yield('error_code', '404')</div>
      <div class="msg">@yield('error_message', 'Not Found')</div>

      <a class="btn" href="@yield('back_url', url('/'))">
        <span>←</span> <span>@yield('button_text', 'Regresar')</span>
      </a>
    </div>

    <div class="right scrim">
      <div class="bg" style="background-image:url('@yield('bg_image', asset('images/error-bg.jpg'))')"></div>
    </div>
  </div>
</body>
</html>
