<?php
session_start();
$USERS_FILE = __DIR__ . '/users.json';
$PINS_FILE  = __DIR__ . '/pins.json';
$UPLOAD_DIR = __DIR__ . '/uploads';
if(!file_exists($USERS_FILE)) file_put_contents($USERS_FILE, json_encode([]));
if(!file_exists($PINS_FILE))  file_put_contents($PINS_FILE, json_encode([]));
if(!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0775, true);

function read_json($f){ $d=json_decode(@file_get_contents($f),true); return is_array($d)?$d:[]; }
function write_json($f,$a){ file_put_contents($f, json_encode($a, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); }

function get_user(&$users,$id){
  foreach($users as &$u){ if($u['id']===$id) return $u; }
  return null;
}

$users = read_json($USERS_FILE);
$pins  = read_json($PINS_FILE);
$me = null;
if(isset($_SESSION['user'])) $me = get_user($users, $_SESSION['user']['id']);

// Handle create board
if($me && isset($_POST['action']) && $_POST['action']==='create_board'){
  $b = trim($_POST['board']??"");
  if($b && !in_array($b,$me['boards'])){ $me['boards'][]=$b; write_json($USERS_FILE,$users); }
  echo "<script>alert('Board added.'); window.location='home.php';</script>"; exit;
}

// Handle save pin to board
if($me && isset($_POST['action']) && $_POST['action']==='save_pin'){
  $pinId = $_POST['pin_id']??"";
  $board = $_POST['board']??"";
  if($pinId && $board){
    if(!in_array($board,$me['boards'])) $me['boards'][]=$board;
    if(!in_array($pinId,$me['savedPins'])) $me['savedPins'][]=$pinId;
    write_json($USERS_FILE,$users);
  }
  echo "<script>alert('Saved to board!'); window.location='home.php';</script>"; exit;
}

// Handle upload pin
$uploadMsg = "";
if($me && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='upload_pin'){
  $title = trim($_POST['title']??"");
  $desc  = trim($_POST['description']??"");
  $cat   = trim($_POST['category']??"");
  if($title && isset($_FILES['image']) && $_FILES['image']['error']===UPLOAD_ERR_OK){
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if(in_array($ext,['jpg','jpeg','png','webp','gif'])){
      $id = uniqid('p_');
      $dest = $UPLOAD_DIR . "/$id.$ext";
      if(move_uploaded_file($_FILES['image']['tmp_name'], $dest)){
        $url = "uploads/$id.$ext";
        $pins[] = [
          'id'=>$id,'title'=>$title,'description'=>$desc,'category'=>$cat?:'General',
          'image'=>$url,'authorId'=>$me['id'],'authorName'=>$me['name'],'createdAt'=>time(),'saves'=>0
        ];
        // link to user myPins
        if(!in_array($id,$me['myPins'])) $me['myPins'][] = $id;
        write_json($PINS_FILE,$pins);
        write_json($USERS_FILE,$users);
        echo "<script>alert('Pin uploaded!'); window.location='home.php';</script>"; exit;
      } else $uploadMsg="Failed to save image.";
    } else $uploadMsg="Only JPG, PNG, WEBP, GIF allowed.";
  } else $uploadMsg="Please provide title and image.";
}

// Search & filters
$q = trim($_GET['q'] ?? "");
$category = trim($_GET['category'] ?? "");
$allCategories = ['All','Fashion','Art','Food','Travel','DIY','Home','Nature','Tech','General'];
$feed = array_reverse($pins); // newest first
$feed = array_values(array_filter($feed, function($p) use($q,$category){
  $ok=true;
  if($q!=="") $ok = stripos($p['title'],$q)!==false || stripos($p['description'],$q)!==false || stripos($p['category'],$q)!==false;
  if($ok && $category && $category!=="All") $ok = strcasecmp($p['category'],$category)===0;
  return $ok;
}));

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Home • PinsPark</title>
<style>
  :root{--bg:#0e0f12;--card:#16181d;--muted:#9aa3af;--accent:#ff3b7a;--accent2:#7c3aed;--text:#e5e7eb;--chip:#22252b;}
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:var(--text);font-family:ui-sans-serif,system-ui,Segoe UI,Arial}
  header{display:flex;align-items:center;gap:14px;padding:14px 18px;border-bottom:1px solid #23252b;position:sticky;top:0;background:#0e0f12aa;backdrop-filter:blur(10px)}
  .brand{display:flex;align-items:center;gap:10px;font-weight:800}
  .brand .logo{width:30px;height:30px;background:conic-gradient(from 210deg,var(--accent),var(--accent2));border-radius:8px}
  .search{flex:1;display:flex;gap:8px}
  .search input, .search select{flex:1;padding:10px 12px;border-radius:12px;border:1px solid #2a2f36;background:#1f232b;color:#e5e7eb}
  .btn{padding:10px 12px;border-radius:12px;border:0;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;cursor:pointer}
  .ghost{background:#22252b;border:1px solid #2a2f36}
  .wrap{max-width:1200px;margin:16px auto;padding:0 16px;display:grid;grid-template-columns:280px 1fr;gap:16px}
  .panel{background:#16181d;border:1px solid #262a31;border-radius:16px;padding:16px}
  h3{margin:0 0 10px}
  .feed{columns: 4 260px; column-gap: 14px}
  .pin{break-inside:avoid;background:#14161b;border:1px solid #20232a;border-radius:16px;margin:0 0 14px;overflow:hidden}
  .pin img{width:100%;display:block}
  .pin .meta{padding:10px 12px}
  .small{font-size:12px;color:var(--muted)}
  .row{display:flex;gap:8px;align-items:center}
  .chip{background:#22252b;border:1px solid #2a2f36;padding:6px 10px;border-radius:999px;color:#cbd5e1;font-size:12px}
  .uploader form{display:grid;gap:8px}
  input[type="text"], textarea, select{width:100%;padding:10px 12px;border-radius:12px;border:1px solid #2a2f36;background:#1f232b;color:#e5e7eb}
  textarea{min-height:80px;resize:vertical}
  .msg{color:#fca5a5;margin-top:6px}
  .side-list{display:flex;flex-wrap:wrap;gap:8px}
  .side-list .chip{cursor:pointer}
  .bar{display:flex;gap:8px}
  .right-actions{display:flex;gap:8px}
</style>
</head>
<body>
<header>
  <div class="brand" onclick="go('index.php')" style="cursor:pointer"><div class="logo"></div> PinsPark</div>
  <form class="search" method="get">
    <input type="text" name="q" placeholder="Search pins (title, description, category)" value="<?php echo htmlspecialchars($q); ?>">
    <select name="category">
      <?php foreach($allCategories as $c){ $sel = ($category===$c) ? 'selected' : ''; echo "<option $sel>".htmlspecialchars($c)."</option>"; } ?>
    </select>
    <button class="btn">Search</button>
    <button type="button" class="btn ghost" onclick="go('home.php')">Reset</button>
  </form>
  <div class="right-actions">
    <button class="btn ghost" onclick="go('profile.php')">Profile</button>
    <?php if($me): ?>
      <button class="btn ghost" onclick="go('logout.php')">Logout</button>
    <?php else: ?>
      <button class="btn" onclick="go('login.php')">Sign in</button>
    <?php endif; ?>
  </div>
</header>

<div class="wrap">
  <aside class="panel">
    <h3>Explore</h3>
    <div class="side-list">
      <?php foreach($allCategories as $c): ?>
        <div class="chip" onclick="filterCat('<?php echo $c; ?>')"><?php echo $c; ?></div>
      <?php endforeach; ?>
    </div>
    <hr style="border-color:#23252b;margin:16px 0">
    <h3>Your Boards</h3>
    <div class="side-list">
      <?php if($me){ foreach($me['boards'] as $b) echo "<div class='chip'>".htmlspecialchars($b)."</div>"; } else { echo "<div class='small'>Sign in to manage boards.</div>"; } ?>
    </div>
    <?php if($me): ?>
      <form method="post" style="margin-top:10px">
        <input type="hidden" name="action" value="create_board">
        <input name="board" placeholder="New board name" required>
        <div class="bar" style="margin-top:8px"><button class="btn">Add Board</button></div>
      </form>
    <?php endif; ?>
    <hr style="border-color:#23252b;margin:16px 0">
    <div class="small">Tip: Click “Save” on any pin to add it to a board.</div>
  </aside>

  <main>
    <?php if($me): ?>
    <div class="panel uploader">
      <h3>Upload a Pin</h3>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_pin">
        <input type="text" name="title" placeholder="Title" required>
        <textarea name="description" placeholder="Description (optional)"></textarea>
        <div class="bar">
          <select name="category">
            <?php foreach($allCategories as $c){ if($c==='All') continue; echo "<option>".htmlspecialchars($c)."</option>"; } ?>
          </select>
          <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.gif" required>
          <button class="btn">Upload</button>
        </div>
        <?php if($uploadMsg): ?><div class="msg"><?php echo htmlspecialchars($uploadMsg); ?></div><?php endif; ?>
      </form>
    </div>
    <?php endif; ?>

    <div class="feed">
      <?php if(empty($feed)): ?>
        <div class="panel"><div class="small">No pins found. Try different search or upload your first pin.</div></div>
      <?php else: foreach($feed as $p): ?>
        <div class="pin">
          <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="">
          <div class="meta">
            <div class="row" style="justify-content:space-between">
              <b><?php echo htmlspecialchars($p['title']); ?></b>
              <span class="chip"><?php echo htmlspecialchars($p['category']); ?></span>
            </div>
            <div class="small" style="margin:6px 0"><?php echo htmlspecialchars($p['description'] ?: ''); ?></div>
            <div class="row" style="justify-content:space-between;margin-top:6px">
              <div class="small">by <?php echo htmlspecialchars($p['authorName']); ?></div>
              <?php if($me): ?>
              <form method="post" class="row" style="gap:6px">
                <input type="hidden" name="action" value="save_pin">
                <input type="hidden" name="pin_id" value="<?php echo htmlspecialchars($p['id']); ?>">
                <select name="board" required>
                  <?php foreach($me['boards'] as $b){ echo "<option>".htmlspecialchars($b)."</option>"; } ?>
                </select>
                <button class="btn">Save</button>
              </form>
              <?php else: ?>
                <button class="btn ghost" onclick="go('login.php')">Save</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </main>
</div>

<script>
  function go(u){ window.location.href=u; }
  function filterCat(c){
    const params = new URLSearchParams(window.location.search);
    if(c==='All'){ params.delete('category'); } else { params.set('category', c); }
    window.location.search = params.toString();
  }
</script>
</body>
</html>
