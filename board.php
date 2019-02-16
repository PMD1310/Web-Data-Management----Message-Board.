<html>
<head><title>Message Board</title></head>
<body>
<form method="get" action="board.php">
    <input type="submit" name="logout" value="logout"/>
</form>
<form action =board.php method=POST>
<input type="text" id="newpost" name="newpost"/>
<input type="submit" name="submit" value="new post"/>
</form>
<?php
 session_start();
error_reporting(E_ALL);
ini_set('display_errors','On');
try {
  $dbconn = new PDO("mysql:host=127.0.0.1:3306;dbname=board","root","",array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
 // print_r($dbconn);
  if(isset($_GET['logout'])){
    session_destroy();
    header('Location: login.php');
    exit();
  }
  if(isset($_POST['username']) && isset($_POST['pwd'])){
    $get = 'SELECT username,password from USERS where username="'.$_POST['username'].'"';
    $resultset = $dbconn->query($get,PDO::FETCH_ASSOC);
    $resultset = $resultset->fetchAll();
    if($resultset[0]['password']== md5($_POST['pwd'])){
     
      $_SESSION["authenticentry"] = $resultset[0]['username'];
    }
    else{
      header('Location: login.php');
    exit();
    }
    //echo md5('12345');
  }
  if(isset( $_SESSION['authenticentry'])){
  if(isset($_POST["newpost"])){
  $insertQuery = 'INSERT INTO POSTS VALUES(:id,:replyto,:postedby,now(),:message)';
  $statement = $dbconn->prepare($insertQuery, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $statement->execute(array(':id' => uniqid(), ':replyto' => null, ':postedby'=> $_SESSION['authenticentry'],':message'=> $_POST['newpost']));
  }
   if(isset($_GET["replyto"])){
   $uniqueID = uniqid();
   $insertQuery = 'INSERT INTO POSTS VALUES(:id,:replyto,:postedby,now(),:message)';
   $statement = $dbconn->prepare($insertQuery, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
   $statement->execute(array(':id' => $uniqueID, ':replyto' => null, ':postedby'=> $_SESSION['authenticentry'],':message'=> $_GET['reply']));
   $update = 'UPDATE posts SET replyto=:replyid where id=:uid';
   $statement = $dbconn->prepare($update, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
   $statement->execute(array(':replyid' =>$_GET['replyto'] , ':uid'=>$uniqueID));
   }
  
   
   $sql = 'select * from posts inner join users where posts.postedby = users.username order by datetime DESC';
  
 
   print "<pre>";
  foreach ($dbconn->query($sql) as $row)  {
    // print_r($row);
     echo '<form>';
     echo '<input type=hidden name="replyto" value="'.$row['id'].'"/>';
     print'<b>Message Id: </b>'.$row['id']."\n";
     if($row['replyto']!=null)
		    print'<b>Username: </b>'.$row['username']."\n".'<b>Full Name: </b>'.$row['fullname']."\n";
		print'<b>Date and Time: </b>'.$row['datetime']."\n";
      print'<b>Replied to Message with message Id: </b>'.$row['replyto']."\n";
     print'<b>Message: </b>'.$row['message']."\n";
     echo '<input type="text" id="reply" name="reply"/>';
     echo '<button type="submit" formaction="board.php">Reply</button></form>';
     print "\n\n\n\n";
  }
   print "</pre>";
 }
 else{
  header('Location: login.php');
    exit();
 }
 } 
catch (PDOException $e) {
  print "Error!: " . $e->getMessage() . "<br/>";
  die();
}
?>

</body>

</html>