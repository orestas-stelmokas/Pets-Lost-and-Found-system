<?php
//nustatymai.php
define("DB_SERVER", getenv("DB_HOST") ?: "localhost");
define("DB_USER", getenv("DB_USER") ?: "stud");
define("DB_PASS", getenv("DB_PASSWORD") ?: "stud");
define("DB_NAME", getenv("DB_NAME") ?: "gyvunu_paieska");
define("TBL_USERS", "users");

// Vartotojų profiliai
$user_roles = array(
    "Administratorius" => "9",
    "Registruotas naudotojas" => "4",
    "Kontrolierius" => "5",
); 
// automatiškai galioja ir vartotojas "guest",rolė "Svečias",  userlevel=0
//   jam irgi galima nurodyti leidžiamas operacijas

define("DEFAULT_LEVEL","Registruotas naudotojas");  // kokia rolė priskiriama kai registruojasi
define("ADMIN_LEVEL","Administratorius");  // jis turi vartotojų valdymo teisę per "Administratoriaus sąsaja"
define("UZBLOKUOTAS","255");      // vartotojas negali prisijungti kol administratorius nepakeis rolės
$uregister="both";  // kaip registruojami vartotojai:
					// self - pats registruojasi, admin - tik ADMIN_LEVEL, both - abu atvejai
define("ZINUCIU_DIENOS_LIMITAS", 3);
// Operacijų meniu
// Automatiškai rodomi punktai "Redaguoti paskyrą" ir "Atsijungti", 
//  							o Administratoriui dar "Administratoriaus sąsaja"
// Kitų operacijų meniu aprašomas kintamuoju $usermenu:
// operacijos pavadinimas
// kokioms rolėms rodoma
// operacijos modulis

$usermenu=array(
    ["Skelbimų peržiūra", [0,4,5,9], "/animals"],
    ["Skelbimo įvedimas", [4,5,9], "/create-listing"],
    ["Skelbimų kontrolė", [5,9], "/moderation"],
    ["Mano žinutės", [4,5,9], "/messages"],
    ["Mano skelbimai", [4,5,9], "/my-listings"]
			  ); 

// karkaso vaizdavimą paredaguokite savo stiliumi keisdami top.png (pradinis yra 1027x122, read teisė visiems)
// ir styles.css.
// top.png pageidautina matyti sistemos pavadinimą ir autorių.

