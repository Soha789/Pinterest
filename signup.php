<?php
session_start();
$USERS_FILE = __DIR__ . '/users.json';
if(!file_exists($USERS_FILE)) file_put_contents($USERS_FILE, json_encode([]));

function read_users($f){ $d=json_decode(@file_get_contents($f),true); return is_array($d)?$d:[]; }
function write_users($f,$a){ file_put_contents($f, json_encode($a, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); }

$msg = "";
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name = trim($_POST['name']??"");
  $email = strtolower(trim($_POST['email']??""));
  $pass = $_POST['password']??"";
  if($name && filter_var($email,FILTER_VALIDATE_EMAIL) && strlen($pass)>=6){
    $users = read_users($USERS_FILE);
    foreach($users as $u){ if($u['email']===$email){ $msg="Email already registered."; break; } }
    if(!$msg){
      $users[] = [
        'id'=>uniqid('u_'), 'name'=>$name, 'email'=>$email,
        'password'=>password_hash($pass, PASSWORD_BCRYPT),
        'bio'=>'',
        'boards'=>['Ideas','Inspiration','Wish List'],
        'savedPins'=>[], // array of pin ids
        'myPins'=>[]     // array of pin ids
      ];
      write_users($USERS_FILE,$users);
      $msg="Account created! Please login.";
      echo "<script>alert('Signup successful! Please log in.'); window.location='login.php';</script>"; exit;
    }
  }else{ $msg="Please fill all fields. Password ≥ 6 chars."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Sign up • PinsPark</title>
<style>
  body{margin:0;background:#0e0f12;color:#e5e7eb;font-family:ui-sans-serif,system-ui,Segoe UI,Arial}
  .wrap{max-width:420px;margin:60px auto;padding:24px;background:#16181d;border:1px solid #262a31;border-radius:16px}
  h1{margin:0 0 10px}
  p{color:#9aa3af;margin-top:0}
  label{display:block;margin:14px 0 6px}
  input{width:100%;padding:12px 14px;border-radius:12px;border:1px solid #2a2f36;background:#1f232b;color:#e5e7eb}
  .row{display:flex;gap:10px;margin-top:14px}
  button{flex:1;padding:12px 14px;border-radius:12px;border:0;font-weight:700;cursor:pointer}
  .primary{background:linear-gradient(135deg,#ff3b7a,#7c3aed);color:white}
  .ghost{background:#22252b;color:#e5e7eb;border:1px solid #2a2f36}
  .msg{color:#fca5a5;margin-top:10px;min-height:20px}
  .top{display:flex;align-items:center;justify-content:space-between;max-width:420px;margin:20px auto}
  .link{background:#1f232b;border:1px solid #2a2f36;color:#e5e7eb;padding:10px 12px;border-radius:12px;cursor:pointer}
</style>
</head>
<body>
<div class="top"><div><b>PinsPark</b></div><div class="link" onclick="go('index.php')">← Back</div></div>
<div class="wrap">
  <h1>Create your account</h1>
  <p>Save ideas to boards and upload your own pins.</p>
  <form method="post">
    <label>Name</label>
    <input name="name" required>
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Password</label>
    <input type="password" name="password" minlength="6" required>
    <div class="row">
      <button type="submit" class="primary">Sign up</button>
      <button type="button" class="ghost" onclick="go('login.php')">Log in</button>
    </div>
    <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
  </form>
</div>
<script>
  function go(u){ window.location.href=u; }
</script>
</body>
</html>
