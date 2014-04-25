<?
/*
 * EZPROXY : CGI authentication tools.
 * Self information page for Users see their groups. 
 * Can help admin to confirm to user, they can access ( or not ) a resource.   
 */
/**
 * To be compared with verif.php or index.php . 
 * This utility provide a self test method for user to see wich groups they are linked to 
 * after authentication. Mode Debug:Diagnostic. Can be ignored / deleted is unused.
 * Don't request it in ezproxy user.txt file.
 *  
 * Module d'appel du programme d'identification des usagers principal.php en mode Debug/diagnostic. 
 * permet � un usager d'avoir un diagnostic de droit d'acc�s aux ressources.  
 * Il n'est pas sens� �tre requis ni recevoir de ticket de la part du reverse mais permet 
 * de v�rifier le r�sultat en terme de groupes d'acc�s rendus par principal.php
 */

session_start();
/*
 * reverse proxy = URL:port 
 */
$reverse = "http://my-proxy.univ-fed.edu";
/*
 * Report Key given in user.txt for this CGI
 *  
 * Cl� li� � la m�thode d'encodage � reporter dans user.txt de la configuration de EzProxy
 */
$cle_ticket = 'secretKeyForEZProxy';

/*
 * Allows or Disables Anonymous access   
 */
$__VAcces_Anonyme = false;
/*
 * Force an IdP -  For�age de l'identifieur
 * 
 */
//$__VA_identifieur = "IDT_S1";

/*
 * custumized WAYF/login-pass page - module de fonctions de personnalisation    
 */
//include_once 'page_identifieur.php';

$Debug = 'diagnostic' ;
$__VAcces_init=true;
include ("./principal.php");		
exit();
?>