<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>PinsPark — Discover & Save Ideas</title>
<style>
  :root{
    --bg:#0e0f12; --card:#16181d; --muted:#9aa3af; --accent:#ff3b7a; --accent2:#7c3aed; --ok:#22c55e; --bad:#ef4444;
    --text:#e5e7eb; --chip:#22252b; --chipText:#cbd5e1;
  }
  *{box-sizing:border-box}
  body{margin:0;background:radial-gradient(1200px 600px at 80% -10%, rgba(124,58,237,.25), transparent),
       radial-gradient(900px 500px at -10% 10%, rgba(255,59,122,.20), transparent),
       var(--bg); color:var(--text); font-family:ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial;}
  header{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;position:sticky;top:0;background:rgba(14,15,18,.6);backdrop-filter: blur(8px);border-bottom:1px solid #23252b}
  .brand{display:flex;align-items:center;gap:10px;font-weight:800;letter-spacing:.3px}
  .brand .logo{width:36px;height:36px;background:conic-gradient(from 210deg, var(--accent), var(--accent2));border-radius:10px;box-shadow:0 6px 20px rgba(255,59,122,.35)}
  nav button{background:#1f232b;border:1px solid #2a2f36;color:var(--text);padding:10px 14px;border-radius:12px;cursor:pointer;font-weight:700}
  nav button.primary{background:linear-gradient(135deg,var(--accent), var(--accent2)); border:0}
  .hero{display:grid;grid-template-columns:1.15fr .85fr;gap:28px;max-width:1150px;margin:32px auto;padding:0 20px}
  .hero h1{font-size:44px;line-height:1.1;margin:12px 0}
  .hero p{color:var(--muted);font-size:18px}
  .chips{display:flex;flex-wrap:wrap;gap:10px;margin:18px 0}
  .chip{background:var(--chip);color:var(--chipText);padding:8px 12px;border-radius:999px;border:1px solid #2a2f36;font-weight:600}
  .cta{display:flex;gap:12px;margin-top:10px}
  .panel{background:var(--card);border:1px solid #262a31;border-radius:18px;padding:18px}
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
  .tile{position:relative;border-radius:14px;overflow:hidden;height:160px;background:#222}
  .tile::after{content:"";position:absolute;inset:0;box-shadow:inset 0 -80px 80px rgba(0,0,0,.45)}
  .tile b{position:absolute;bottom:10px;left:12px;z-index:2}
  footer{max-width:1150px;margin:20px auto 60px;color:#8b93a0;padding:0 20px}
  .note{margin-top:10px;font-size:14px;color:#9aa3af}
</style>
</head>
<body>
<header>
  <div class="brand"><div class="logo"></div> <span>PinsPark</span></div>
  <nav>
    <?php if(isset($_SESSION['user'])): ?>
      <button onclick="go('home.php')">Open Home</button>
    <?php else: ?>
      <button onclick="go('login.php')">Log in</button>
      <button class="primary" onclick="go('signup.php')">Sign up</button>
    <?php endif; ?>
  </nav>
</header>

<section class="hero">
  <div>
    <h1>Discover, save, and organize ideas you love.</h1>
    <p>Create boards, upload your own pins, and collect inspiration across Fashion, Art, Food, Travel, DIY, and more.</p>
    <div class="chips">
      <span class="chip">⚡ Trending Feed</span>
      <span class="chip">📌 Save to Boards</span>
      <span class="chip">🔎 Smart Search & Filters</span>
      <span class="chip">🖼️ Upload Your Pins</span>
      <span class="chip">🧭 Explore Categories</span>
    </div>
    <div class="cta">
      <button class="primary" onclick="go('signup.php')">Create free account</button>
      <button onclick="go('home.php')">Browse as guest</button>
    </div>
    <p class="note">Tip: You can browse publicly, but saving/uploading needs an account.</p>
  </div>
  <div class="panel">
    <div class="grid">
      <div class="tile" style="background:url('https://images.unsplash.com/photo-1520975916090-3105956dac38?q=80&w=900&auto=format&fit=crop') center/cover"><b>Art</b></div>
      <div class="tile" style="background:url('https://images.unsplash.com/photo-1478147427282-58a87a120781?q=80&w=900&auto=format&fit=crop') center/cover"><b>Food</b></div>
      <div class="tile" style="background:url('https://images.unsplash.com/photo-1549880338-65ddcdfd017b?q=80&w=900&auto=format&fit=crop') center/cover"><b>Travel</b></div>
      <div class="tile" style="background:url('https://images.unsplash.com/photo-1520974729539-5cd8aee0d41a?q=80&w=900&auto=format&fit=crop') center/cover"><b>DIY</b></div>
      <div class="tile" style="background:url('https://images.unsplash.com/photo-1559563362-c667ba5f5480?q=80&w=900&auto=format&fit=crop') center/cover"><b>Fashion</b></div>
      <div class="tile" style="background:url('https://images.unsplash.com/photo-1496307042754-b4aa456c4a2d?q=80&w=900&auto=format&fit=crop') center/cover"><b>Home</b></div>
    </div>
  </div>
</section>

<footer>© <?php echo date('Y'); ?> PinsPark. Built with ❤️ — simple PHP + JSON storage. No external CSS/JS.</footer>

<script>
  function go(url){ window.location.href = url; }
</script>
</body>
</html>
