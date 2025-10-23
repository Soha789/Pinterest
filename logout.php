<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Logging out…</title>
<style>
  body{margin:0;display:grid;place-items:center;height:100vh;background:#0e0f12;color:#e5e7eb;font-family:ui-sans-serif,system-ui,Segoe UI,Arial}
  .card{background:#16181d;border:1px solid #262a31;border-radius:16px;padding:24px;min-width:280px;text-align:center}
  .btn{margin-top:10px;padding:10px 12px;border-radius:12px;border:0;background:linear-gradient(135deg,#ff3b7a,#7c3aed);color:#fff;font-weight:700;cursor:pointer}
</style>
</head>
<body>
<div class="card">
  <h3>Signed out</h3>
  <div>You have been logged out of PinsPark.</div>
  <button class="btn" onclick="go('index.php')">Go to Home</button>
</div>
<script>
  function go(u){ window.location.href=u; }
  setTimeout(()=>go('index.php'),800);
</script>
</body>
</html>
