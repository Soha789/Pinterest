<?php
session_start();
$USERS_FILE = __DIR__ . '/users.json';
if(!file_exists($USERS_FILE)) file_put_contents($USERS_FILE, json_encode([]));
function read_users($f){ $d=json_decode(@file_get_contents($f),true); return is_array($d)?$d:[]; }

$msg = "";
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = strtolower(trim($_POST['email']??""));
  $pass = $_POST['password']??"";
  $users = read_users($USERS_FILE);
  foreach($users as $u){
    if($u['email']===$email && password_verify($pass,$u['password'])){
      $_SESSION['user'] = ['id'=>$u['id'],'email'=>$u['email']];
      echo "<script>alert('Welcome back!'); window.location='home.php';</script>"; exit;
    }
  }
  $msg = "Invalid credentials.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login • PinsPark</title>
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
  <h1>Welcome back</h1>
  <p>Log in to save and upload pins.</p>
  <form method="post">
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <div class="row">
      <button type="submit" class="primary">Log in</button>
      <button type="button" class="ghost" onclick="go('signup.php')">Create account</button>
    </div>
    <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
  </form>
</div>
<script>
  function go(u){ window.location.href=u; }
</script>
</body>
</html>
