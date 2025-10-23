<?php
session_start();
$USERS_FILE = __DIR__ . '/users.json';
$PINS_FILE  = __DIR__ . '/pins.json';
if(!file_exists($USERS_FILE)) file_put_contents($USERS_FILE, json_encode([]));
if(!file_exists($PINS_FILE))  file_put_contents($PINS_FILE, json_encode([]));

function read_json($f){ $d=json_decode(@file_get_contents($f),true); return is_array($d)?$d:[]; }
function write_json($f,$a){ file_put_contents($f, json_encode($a, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); }

$users = read_json($USERS_FILE);
$pins  = read_json($PINS_FILE);

function &find_user(&$arr,$id){ foreach($arr as &$u){ if($u['id']===$id) return $u; } $null=null; return $null; }

if(!isset($_SESSION['user'])){
  echo "<script>alert('Please log in.'); window.location='login.php';</script>"; exit;
}

$me =& find_user($users, $_SESSION['user']['id']);
if(!$me){ session_destroy(); echo "<script>alert('Session expired.'); window.location='login.php';</script>"; exit; }

// Update profile
$msg = "";
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='update_profile'){
  $name = trim($_POST['name']??"");
  $bio  = trim($_POST['bio']??"");
  if($name){ $me['name']=$name; $me['bio']=$bio; write_json($USERS_FILE,$users); $msg="Profile updated."; }
}

// helper to map pins by id
$pinById=[];
foreach($pins as $p){ $pinById[$p['id']]=$p; }
$myPins = array_values(array_filter($pins, fn($p)=>$p['authorId']===$me['id']));
$savedPins = [];
foreach($me['savedPins'] as $pid){ if(isset($pinById[$pid])) $savedPins[] = $pinById[$pid]; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Profile • PinsPark</title>
<style>
  :root{--bg:#0e0f12;--card:#16181d;--muted:#9aa3af;--accent:#ff3b7a;--accent2:#7c3aed;--text:#e5e7eb}
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:var(--text);font-family:ui-sans-serif,system-ui,Segoe UI,Arial}
  header{display:flex;align-items:center;gap:14px;padding:14px 18px;border-bottom:1px solid #23252b;position:sticky;top:0;background:#0e0f12aa;backdrop-filter:blur(10px)}
  .brand{display:flex;align-items:center;gap:10px;font-weight:800;cursor:pointer}
  .brand .logo{width:30px;height:30px;background:conic-gradient(from 210deg,var(--accent),var(--accent2));border-radius:8px}
  .btn{padding:10px 12px;border-radius:12px;border:0;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;cursor:pointer}
  .ghost{background:#22252b;border:1px solid #2a2f36}
  .wrap{max-width:1100px;margin:16px auto;padding:0 16px;display:grid;grid-template-columns:360px 1fr;gap:16px}
  .panel{background:#16181d;border:1px solid #262a31;border-radius:16px;padding:16px}
  label{display:block;margin:10px 0 6px}
  input,textarea{width:100%;padding:10px 12px;border-radius:12px;border:1px solid #2a2f36;background:#1f232b;color:#e5e7eb}
  textarea{min-height:90px;resize:vertical}
  .msg{color:#a7f3d0;margin-top:6px}
  .feed{columns:3 260px; column-gap:14px}
  .pin{break-inside:avoid;background:#14161b;border:1px solid #20232a;border-radius:16px;margin:0 0 14px;overflow:hidden}
  .pin img{width:100%;display:block}
  .pin .meta{padding:10px 12px}
  .small{color:var(--muted);font-size:12px}
  .topbar{display:flex;gap:8px}
</style>
</head>
<body>
<header>
  <div class="brand" onclick="go('home.php')"><div class="logo"></div> PinsPark</div>
  <div style="margin-left:auto" class="topbar">
    <button class="btn ghost" onclick="go('home.php')">Home</button>
    <button class="btn ghost" onclick="go('logout.php')">Logout</button>
  </div>
</header>

<div class="wrap">
  <section class="panel">
    <h2><?php echo htmlspecialchars($me['name']); ?></h2>
    <div class="small" style="margin-bottom:10px"><?php echo htmlspecialchars($me['email']); ?></div>
    <form method="post">
      <input type="hidden" name="action" value="update_profile">
      <label>Name</label>
      <input name="name" value="<?php echo htmlspecialchars($me['name']); ?>" required>
      <label>Bio</label>
      <textarea name="bio" placeholder="Write something about you..."><?php echo htmlspecialchars($me['bio']); ?></textarea>
      <button class="btn" style="margin-top:8px">Save Profile</button>
      <?php if($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    </form>
    <hr style="border-color:#23252b;margin:16px 0">
    <h3>Your Boards</h3>
    <div style="display:flex;flex-wrap:wrap;gap:8px">
      <?php foreach($me['boards'] as $b) echo "<div class='small' style='background:#22252b;border:1px solid #2a2f36;border-radius:999px;padding:6px 10px'>".htmlspecialchars($b)."</div>"; ?>
    </div>
  </section>

  <section>
    <div class="panel">
      <h3>Your Pins</h3>
      <div class="feed">
        <?php if(empty($myPins)): ?>
          <div class="small">You haven't uploaded any pins yet. Go to Home and upload one!</div>
        <?php else: foreach(array_reverse($myPins) as $p): ?>
          <div class="pin">
            <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="">
            <div class="meta">
              <b><?php echo htmlspecialchars($p['title']); ?></b>
              <div class="small"><?php echo htmlspecialchars($p['category']); ?></div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <div class="panel" style="margin-top:16px">
      <h3>Saved Pins</h3>
      <div class="feed">
        <?php if(empty($savedPins)): ?>
          <div class="small">No saved pins yet. Click “Save” on any pin in Home.</div>
        <?php else: foreach(array_reverse($savedPins) as $p): ?>
          <div class="pin">
            <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="">
            <div class="meta">
              <b><?php echo htmlspecialchars($p['title']); ?></b>
              <div class="small"><?php echo htmlspecialchars($p['category']); ?></div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </section>
</div>

<script>
  function go(u){ window.location.href=u; }
</script>
</body>
</html>
