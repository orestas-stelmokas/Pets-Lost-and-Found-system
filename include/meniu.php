<?php
// meniu.php  rodomas meniu pagal vartotojo rolę

if (!isset($_SESSION)) { header("Location: logout.php");exit;}
require_once("include/nustatymai.php");
$user=$_SESSION['user'];
$userlevel=$_SESSION['ulevel'];
if ($_SESSION['ulevel'] == 0)$role="Svečias";else $role="";

$galiSkelbti = false;

if ($userlevel != 0 && !empty($_SESSION['userid'])) {
    $dbLeidimai = new mysqli(
        DB_SERVER, DB_USER, DB_PASS, DB_NAME
    );

    $stmt = $dbLeidimai->prepare(
        "SELECT gali_skelbti
         FROM users
         WHERE userid = ?
         AND userlevel IN (4,5,9)
         LIMIT 1"
    );

    $userid = (string)$_SESSION['userid'];

    $stmt->bind_param("s", $userid);
    $stmt->execute();
    $stmt->bind_result($leidimas);

    if ($stmt->fetch()) {
        $galiSkelbti = (int)$leidimas === 1;
    }

    $stmt->close();
    $dbLeidimai->close();
}

{foreach($user_roles as $x=>$x_value)
			      {if ($x_value == $userlevel) $role=$x;}
} 

     echo "<table width=100% border=\"0\" cellspacing=\"1\" cellpadding=\"3\" class=\"meniu\">";
        echo "<tr><td>";
        echo "Prisijungęs vartotojas: <b>".$user."</b>     Rolė: <b>".$role."</b> <br>";
        echo "</td></tr><tr><td>";
        if ($userlevel != 0) echo "[<a href=\"useredit.php\">Redaguoti paskyrą</a>] &nbsp;&nbsp;";
       
		foreach ($usermenu as $x) {
            if ($x[2] === "operacija2.php" && !$galiSkelbti) {
                continue;
            }
			foreach ($x[1] as $menulevel) {
				if($menulevel==$userlevel){
					echo "[<a href=\"".$x[2]."\">".$x[0]."</a>] &nbsp;&nbsp;";
				}
			}
		}
 
        //Administratoriaus sąsaja rodoma tik administratoriui
        if ($userlevel == $user_roles[ADMIN_LEVEL] ) {
            echo "[<a href=\"admin.php\">Administratoriaus sąsaja</a>] &nbsp;&nbsp;";
        }
        echo "[<a href=\"logout.php\">Atsijungti</a>]";
      echo "</td></tr></table>";
?>       
