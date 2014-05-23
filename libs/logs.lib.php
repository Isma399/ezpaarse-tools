<?php
/**
 * 
 * Fonction qui tout en eclatant la ligne sur le s�parateur principal, tient compte de caract�res 
 * parenth�sants.  
 * Les parenth�ses permettent aux valeurs de comporter LE caract�re s�parateur de colonne. 
 * Ex : si le s�parateur est le plus g�n�rique espace, on peut avoir comme parenth�ses
 * les crochets pour la date soit [AAAA/MM/JJ � HH:mm:ss]  
 * @param string $ligne_entree : ligne � d�couper 
 * @param char $sep : s�parateur de colonnes
 * @param array $col_testees : liste (en cl�) des colonnes devant �tre test�es. 
 * 		La colonne C porte un crit�re	 si $col_testees[C] existe.
 * @uses global $limites_valeurs : tableau 
 * @return mixed array = tableau des valeurs contenues dans la ligne 
 * 				 string = message d'erreur
 */
$ce_repertoire = dirname(__FILE__);

require_once ($ce_repertoire."/parentheses.lib.php");
function explose_ligne ($ligne_entree,$sep,$col_testees=array(),$ote_parentheses=false){
	global $limites_valeurs;
	if (!$limites_valeurs){
		set_parentheses(array(array('"','"'),array('[',']')));
	}
	$a_test = count($col_testees);
	$ligne_entree=trim($ligne_entree);
	if (isset($col_testees["*"])) {
		$r = valeur_col_valide($ligne_entree, "*");
		if ($r) return ($r);
	}
	$prem_tab = preg_split('/(\\'.$sep.')/', $ligne_entree,-1,PREG_SPLIT_DELIM_CAPTURE);
	$tab_res = array(); 
	$limit_val=$val_retenue="";
	/* analyse de chaque morceau */
	for ($ipos=0;$ipos<count($prem_tab);$ipos+=2) {
		/* si on est en cours de valeur parenth�s�e */
		$morceau=$prem_tab[$ipos];
		if ($limit_val) {
			// si fin de parenth�se m�morisation et init pour la suite
			if (substr($morceau,-$l_limit_val) ==$limit_val) {
				if ($ote_parentheses) $morceau = substr($morceau,0,-$l_limit_val);
				$limit_val="";
			} elseif ($ipos == count($prem_tab)-1){
				$limit_val="";
			}
			$val_retenue.=$dersep.$morceau;
			if ($ipos<count($prem_tab)-1) $dersep = $prem_tab[$ipos+1];
			if (!$limit_val){
				$tab_res[]=$val_retenue;
				if (isset($col_testees[count($tab_res)])) {
					$r = valeur_col_valide($val_retenue, count($tab_res));
					if ($r) return ($r);
				} 
				$val_retenue=$dersep="";
			} 				
			continue;
		} 
		/* on N'est pas dans une valeur parenth�s�e. On recherche si c'en est le d�but */
		foreach ($limites_valeurs as $def){
			$deb = substr($morceau,0,strlen($def[0]));
			$fin = substr($morceau,-strlen($def[1]));
			if ($deb==$def[0]) {				
				if ($fin!=$def[1]) {
					$limit_val=$def[1];
					$l_limit_val=strlen($limit_val);
					if ($ote_parentheses) $morceau = substr($morceau,strlen($def[0]));
				} else {
					$morceau = substr($morceau,strlen($deb),-strlen($fin));
				}
				break;
			} 
		}
		if ($limit_val) {
			$val_retenue=$morceau; if ($ipos<count($prem_tab)-1) $dersep = $prem_tab[$ipos+1];
		} else {
			$tab_res[]=$morceau;$val_retenue=$dersep="";
			if ($a_test && isset($col_testees[count($tab_res)])) {
				$r = valeur_col_valide($morceau, count($tab_res));
				if ($r) return ($r);
			}
		}		
	}
	return ($tab_res);
}
