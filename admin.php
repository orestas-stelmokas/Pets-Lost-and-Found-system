<?php
// admin.php
// vartotojų įgaliojimų keitimas ir naujo vartotojo registracija, jei leidžia nustatymai
// galima keisti vartotojų roles, tame tarpe uzblokuoti ir/arba juos pašalinti
// sužymėjus pakeitimus į procadmin.php, bus dar perklausta

session_start();
include("include/nustatymai.php");
include("include/functions.php");
// cia sesijos kontrole
if (!isset($_SESSION['prev']) || ($_SESSION['ulevel'] != $user_roles[ADMIN_LEVEL]))   { header("Location: logout.php");exit;}

// Apsauga nuo nepageidaujamų formos užklausų
if (empty($_SESSION['leidimu_token'])) {
    $_SESSION['leidimu_token'] = bin2hex(random_bytes(32));
}

// Skelbimų publikavimo leidimo keitimas
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['keisti_skelbimo_leidima'])) {

    $token = $_POST['leidimu_token'] ?? '';

    if (!is_string($token) ||
        !hash_equals($_SESSION['leidimu_token'], $token)) {
        http_response_code(403);
        exit("Neteisinga užklausa.");
    }

    $userid = $_POST['userid'] ?? '';
    $reiksme = $_POST['gali_skelbti'] ?? '';

    if (!is_string($userid) ||
        !preg_match('/^[a-f0-9]{32}$/i', $userid) ||
        !in_array($reiksme, ['0', '1'], true)) {
        http_response_code(400);
        exit("Neteisingi leidimo duomenys.");
    }

    $leisti = (int)$reiksme;

    $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

    $stmt = $db->prepare(
        "UPDATE users
         SET gali_skelbti = ?
         WHERE userid = ?
         AND userlevel IN (4,5,9)"
    );

    $stmt->bind_param("is", $leisti, $userid);
    $stmt->execute();
    $stmt->close();
    $db->close();

    $_SESSION['message'] = $leisti
        ? "Leidimas skelbti suteiktas."
        : "Leidimas skelbti atšauktas.";

    header("Location: admin.php");
    exit;
}

$_SESSION['prev']="admin";
date_default_timezone_set("Europe/Vilnius");
?>

<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=9; text/html; charset=utf-8">
        <title>Administratoriaus sąsaja</title>
        <link href="include/styles.css" rel="stylesheet" type="text/css" >
    </head>
    <body>
        <table class="center" ><tr><td>
            <center><img src="include/top.png"></center>
            </td></tr><tr><td>
		<center><font size="5">Vartotojų registracija, peržiūra ir įgaliojimų keitimas</font></center></td></tr></table> <br>
		<center><b><?php echo $_SESSION['message']; ?></b></center>
		<form name="vartotojai" action="procadmin.php" method="post">
	    <table class="center" style=" width:75%; border-width: 2px; border-style: dotted;">
		         <tr><td width=30%><a href="index.php">[Atgal]</a></td><td width=30%> 
	<?php
		   if ($uregister != "self") echo "<a href=\"register.php\"><b>Registruoti naują vartotoją<b></a><td>";
		   else echo "</td>";
	?>
		   
			<td width="30%">Atlikite reikalingus pakeitimus ir</td><td width="10%"> <input type="submit" value="Vykdyti"></td></tr></table> <br> 
<?php
    
	$db=mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	$sql = "SELECT username,userlevel,email,timestamp "
            . "FROM " . TBL_USERS . " ORDER BY userlevel DESC,username";
	$result = mysqli_query($db, $sql);
	if (!$result || (mysqli_num_rows($result) < 1))  
			{echo "Klaida skaitant lentelę users"; exit;}
?>
    <table class="center"  border="1" cellspacing="0" cellpadding="3">
    <tr><td><b>Vartotojo vardas</b></td><td><b>Rolė</b></td><td><b>E-paštas</b></td><td><b>Paskutinį kartą aktyvus</b></td><td><b>Šalinti?</b></td></tr>
<?php
        while($row = mysqli_fetch_assoc($result)) 
	{	 
	    $level=$row['userlevel']; 
	  	$user= $row['username'];
	  	$email = $row['email'];
      	$time = date("Y-m-d G:i", strtotime($row['timestamp']));
      	echo "<tr><td>".$user. "</td><td>";
    	echo "<select name=\"role_".$user."\">";
      	$yra=false;
		foreach($user_roles as $x=>$x_value)
  			{echo "<option ";
        	 if ($x_value == $level) {$yra=true;echo "selected ";}
             echo "value=\"".$x_value."\" ";
         	 echo ">".$x."</option>";
        	 }
		if (!$yra)
        {echo "<option selected value=".$level.">Neegzistuoja=".$level."</option>";}
        $UZBLOKUOTAS=UZBLOKUOTAS; echo "<option ";
        if ($level == UZBLOKUOTAS) echo "selected ";
          echo "value=".$UZBLOKUOTAS." ";
        echo ">Užblokuotas</option>";      // papildoma opcija
      echo "</select></td>";
          
      echo "<td>".$email."</td><td>".$time."</td>";
      echo "<td><input type=\"checkbox\" name=\"naikinti_".$user."\">";
   }
?>
        </table>
        <br> <input type="submit" value="Vykdyti">
        </form>
		<h2>Skelbimų publikavimo leidimai</h2>

		<?php
		$naudotojai = $db->query(
			"SELECT userid, username, userlevel, gali_skelbti
			FROM users
			WHERE userlevel IN (4,5,9)
			ORDER BY username"
		);
		?>

		<table class="center" border="1" cellpadding="8">
			<tr>
				<th>Naudotojas</th>
				<th>Dabartinis leidimas</th>
				<th>Veiksmas</th>
			</tr>

			<?php while ($u = $naudotojai->fetch_assoc()): ?>
				<?php $leista = (int)$u['gali_skelbti'] === 1; ?>

				<tr>
					<td>
						<?= htmlspecialchars(
							$u['username'], ENT_QUOTES, 'UTF-8'
						) ?>
					</td>

					<td><?= $leista ? 'Leista' : 'Neleista' ?></td>

					<td>
						<form method="post" action="admin.php">
							<input type="hidden" name="leidimu_token"
								value="<?= htmlspecialchars(
									$_SESSION['leidimu_token'],
									ENT_QUOTES, 'UTF-8'
								) ?>">

							<input type="hidden" name="userid"
								value="<?= htmlspecialchars(
									$u['userid'], ENT_QUOTES, 'UTF-8'
								) ?>">

							<input type="hidden" name="gali_skelbti"
								value="<?= $leista ? '0' : '1' ?>">

							<button type="submit"
									name="keisti_skelbimo_leidima"
									value="1">
								<?= $leista
									? 'Atšaukti leidimą'
									: 'Suteikti leidimą' ?>
							</button>
						</form>
					</td>
				</tr>
			<?php endwhile; ?>
		</table>
    </body></html>
