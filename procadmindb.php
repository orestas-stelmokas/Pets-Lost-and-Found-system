<?php
// procadmindb.php   admino nurodytus pakeitimus padaro DB
// $_SESSION['ka_keisti'] kuriuos vartotojus, $_SESSION['pakeitimai'] į kokį userlevel
	
session_start();

include("include/nustatymai.php");
include("include/functions.php");

if (
    empty($_SESSION['user']) ||
    !isset($_SESSION['ulevel']) ||
    (int) $_SESSION['ulevel'] !== (int) $user_roles[ADMIN_LEVEL] ||
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    empty($_SESSION['ka_keisti']) ||
    !isset($_SESSION['pakeitimai'])
) {
    header("Location: index.php");
    exit;
}

$_SESSION['prev'] = "procadmindb";

$db=mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
$i=0;$levels=$_SESSION['pakeitimai'];
foreach ($_SESSION['ka_keisti'] as $user)
  {$level=$levels[$i++];
   if ($level == -1) {
    $sql = "DELETE FROM ". TBL_USERS. "  WHERE  username='$user'";
				         if (!mysqli_query($db, $sql)) {
                   echo " DB klaida šalinant vartotoją: " . $sql . "<br>" . mysqli_error($db);
		               exit;}
   } else {
    $sql = "UPDATE ". TBL_USERS." SET userlevel='$level' WHERE  username='$user'";
				         if (!mysqli_query($db, $sql)) {
                   echo " DB klaida keičiant vartotojo įgaliojimus: " . $sql . "<br>" . mysqli_error($db);
		               exit;}
  }}
$_SESSION['message']="Pakeitimai atlikti sėkmingai";
header("Location:admin.php");exit;
