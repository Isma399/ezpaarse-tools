#!/usr/bin/php
<?php
/*
 * Implantation :
 */
error_reporting(E_ALL);
$ce_repertoire = dirname(__FILE__);
$racinePhys  = preg_replace (":/traitements\$:","",$ce_repertoire);
$libs = $racinePhys."/libs";

/**
 * 
 * gcsv_tri.php V 0.1
 * Script qui sert � trier les ligne de plusieurs fichiers de logs (CSV) ou de l'entr�e standard (utilisable
 * en pipe). Le tri s'effectue sur une ou plusieurs colonne des donn�es source et cette cl� est pr�sente 
 * en t�te de chaque ligne du r�sultat. 
 * Les lignes peuvent �tre compl�t�es par :
- des colonnes indiqu�e (-col). Il y aura autant de ligne de m�me cl� que de valeurs pour ces colonnes. 
- le nombre de lignes de l'original contenant la cl� en t�te (-cpt)
- en l'absence de -col et -cpt, la liste des couples fichier_source:numero_ligne o� se trouve la cl�.  
Si aucun fichier n'est indiqu� en source (-src), c'est l'entr�e standard qui est utilis�e.
Si aucun fichier n'est indiqu� en r�sultat (-res), c'est la sortie standard qui est utilis�e.
Si aucun fichier n'est indiqu� pour les lignes non int�ressante (-rej), elles sont oubli�es.
 * 
 *
 *  V0.1 : 
 *  	- utilisation de l'entr�e standard pour un usage en pipe possible.
 * performance : 2s pour 17970 lignes triees donnant 3613 cl�s formees de deux de ses colonnes.
 * V0.2 :
 *      - possibilit� d'utiliser des noms de colonnes comme dans les autres outils. 
 */
function montre_usage ($mess=''){
	echo $mess."\n";
	?>
	
Usage : 
./gcsv_tri.php [-aide|-help|-h] [(+|-)test] [-max int][-tmax int]  
	[-par parentheses_filename] [-sep "split_char"]  [-glu "glu_char"] 
	[(+|-)hd[1]] -colt namenum [namenum ...] [(-col namenum [namenum ...] [-multi] [-u] | -cpt)]       
     [-res result_filename] {-src source_filename [source_filename ...] | source_filename}
<?php 
	if ($mess) return(false);
?>               
Traite le(s) fichier(s) CSV source_filenameS en triant le r�sultat sur les colonnes d�sign�es par colt. 
Au niveau des fichiers CSV source, il y deux parenth�seurs de valeur complexe ( i.e. colonne dont les valeurs peuvent contenir le s�parateur de colonne), ce sont la double quote(") ou le couple crochet ouvrant/fermant ([]). 
Pour changer ces parenth�seurs, utiliser l'argument par suivi du nom d'un fichier contenant sur chaque ligne, deux caract�res s�par�s par un espace, qui marquent le d�but et la fin d'une valeur complexe. 
Le s�parateur de base est l'espace qui peut-�tre modifi� par le param�tre sep suivi d'un caract�re quot�. 
Le tri s'effectue sur une ou plusieurs colonne des donn�es source (-colt) et cette cl� est pr�sente en t�te de chaque ligne du r�sultat.
 Les lignes peuvent �tre compl�t�es par :
- des colonnes indiqu�es (-col). Il y aura autant de ligne de m�me cl� que de valeurs pour ces colonnes. 
- le nombre de lignes de l'original contenant la cl� en t�te (-cpt)
- en l'absence de -col et -cpt, la liste des couples fichier_source:numero_ligne o� se trouve la cl�.  
Si aucun fichier n'est indiqu� en source (-src), c'est l'entr�e standard qui est utilis�e.
Si aucun fichier n'est indiqu� en r�sultat (-res), c'est la sortie standard qui est utilis�e.
Si aucun fichier n'est indiqu� pour les lignes non int�ressante (-rej), elles sont oubli�es.  
Arguments :
    -aide , -help, -h : simple affichage de l'usage
	(+|-)test : Indique les arguments	reconnus et pris en compte. Avec le signe + mode bavard.
	-max n : nombre n de lignes � traiter. 
		Si non pr�sent, l'int�gralit� des fichiers source est trait�e.
	-tmax n : Dur�e max du traitement en secondes. 
		Si non pr�sent, l'int�gralit� des fichiers source est trait�e.
   	-sep char : pr�cise le s�parateur de colonne char � utiliser.
   		Mettre  t pour tabulations, s pour toutes les caract�res invisibles. 
		(ou espaces g�n�raux)
   	-glu char : pr�cise le s�parateur de colonne char � utiliser dans le resultat.
   		Mettre  t pour tabulations, n retour a la ligne r  pour retour chariot. 
	-par file : fichier de ligne contenant chacune deux chaines s�par�es 
		par un espace. La premi�re repr�sente la marque de d�but
		d'une valeur et la seconde la marque de la fin d'une valeur. 
		Les lignes non conformes sont ignor�es.
	-xtrt PHP_filename : contient une fonction a_lecture_ligne($ligne_lue) 
	    qui corrige et retourne la ligne � traiter � partir de la ligne 
	    originale fournie en param�tre. 
    (+|-)hd,(+|-)hd1  : indique la presence de la ligne d'entete a conserver ou non dans le resultat. 
    	Si le chiffre 1 est present, seul le premier fichier source contient cette ligne. 
       Si elle existe sans etre declaree, elle sera traitee comme une autre ligne, elle ne peut �tre 
       omise si les colonnes sont designees par leur nom (de cette entete).
	-colt c1 c2 ... : les cn d�signent les colonnes servant au tri. 
		L'ordre des colonne sera celui utilis� pour cr�er les cl�s de tri des lignes. 
	**	R�sultat associ� � chaque cl�
    -col cr1 cr2 ... : colonnes formant autant de valeurs associ�es � chaque occurrence de
		la cl�.  Toutefois, si -u est utilis�, on ne retient qu'un exemplaire de chaque 
		combinaison diff�rente de ces n colonnes. 
    -cpt : pour n'obtenir que le nombre de lignes contenant  la cl� dans le r�sultat. 
        Ne peut etre employe avec -col ou un des multiplexeurs suivants.
    sans -col ni cpt, c'est le couple fichier:ligne qui est associ� a chaque fois que la cl�
    	est trouv�e
    -u : avec -col, une combinaison des colonnes associ�e � une cl� n'est report� qu'une
    	fois dans le r�sultat (unicit� des valeurs).   
		i.e. : parmi les valeurs associ�es � chaque cl�, certaines sont identiques car les 
		colonnes les formant ont les m�mes valeurs, dans ce cas, une seule occurrence 
		de la cl� associ�e � ces valeurs sera retenue  
    	En d'autre terme , avec -multi, il n'y a pas en r�sultat deux lignes strictement
    	identiques.
    	
	**	Pr�sentation des cl�s et valeurs associ�es
    -uk : pour cl� unique = seule la premi�re valeur compte, i.e. 
    	si la cl� d'une ligne source a �t� deja ete rencontr�e,  on ignore la ligne.
    	Option incompatible avec -cpt.
    -multi : indique que le resultat doit contenir autant de ligne, pour une cl�, 
    	que de valeurs associ�es � cette cl� sinon elles seront toutes ajout�es derri�re la 
    	cl� sur une m�me ligne. Cette option est incompatible avec -cpt.
    sans -uk ni -multi, chaque cl� est sur une seule ligne, en t�te de ligne, suivie de
    	toutes les valeurs qui lui sont associ�es. Le resultat n'est plus un gcsv. 

    Ainsi, une cl� qui appara�t sur N lignes ayant C combinaisons differentes de n 
        colonnes designees par -col aura en resultat, selon le cas
        - ni -multi ni -uk, une ligne avec la cl� suivie de 
        	- C*n colonnes avec -u,
        	- N*n colonnes sans -u. 
        - avec -multi, des lignes avec la cl� suivie de n colonnes 
        	- au nombre de C avec -u,
        	- au nombre de N sans -u.
        - avec -uk, une ligne avec les n colonnes ayant la valeur trouv�e sur la premi�re
        	ligne contenant la cl�.   
    	
    -res result_filename : pr�cise le chemein du fichier � cr�er. Si cet argument
        est absent, c'est la sortie standard qui affiche les lignes tri�es.
    -src source_filenames : liste des fichiers � traiter. Si on en designe pas, c'est l'entree standard 
         qui fait office de source.
Exemples :
     ./gcsv_tri.php -test -colt 2 3 -res trie -src journal.txt.0
     ./gcsv_tri.php -colt 2 3 -sep t -res trie -src journal.txt.0
<?php
	return(true);
}

function  traite_ligne_header_mess ($no,$p){
	switch ($no){
		case '2DefCol' :
			return ("Double definition de la colonne $p dans l'entete.\n");
		case 'NomInv' : 
			return ("Nom de colonne invalide $p.");
		case 'EntAna' :
			return ("Entete analysee $p.");
		} 
}

function message ($no,$p) {
	if (is_array($p) && count($p)<5){
		$p0=$p[0];
		if (count($p)>1) $p1=$p[1];
		if (count($p)>2) $p2=$p[2];
		if (count($p)>3) $p3=$p[3];
	}
	switch ($no){
		case 'ComInv' :
			return ("invalide : $p\n  fournir pour le moins, cl� de tri , source, ... \n ".
					"... pour pr�cision mettre aide");	
		case 'ArgInc' :
			return ("Argument $p0 hors liste $p1.\n");
		case 'par=' :
			return ("Parametre = $p\n");
		case 'par_cpt' :
			return (" Memo du compte d occurrence\n");	
		case '-u+-uk' :
			return ("ERR : -u et -uk exclusif\n");
		case 'ArgInc' :
			return ("Argument inconnu $p.\n");
		case 'VProc' : 
			return (" $p0 pour $p1\n");
		case 'maxInv':
			return ("Le maximum de ligne max n'est pas un entier $p\n");
		case 'max=' :
			return (" Maximum de lignes traitees $p.\n");
		case 'tmaxInv':
			return ("Le maximum temps n'est pas un entier $p\n");
		case 'tmax=' :
			return (" Duree maximum des traitements $p.\n");
		case 'sep=':
			return (" Le separateur est \"$p\".\n");
		case 'sepDef' :
			return ("Separateurs par defaut, espaces, tabulation.\n");
		case 'glu=' :
			return(" Recoller avec \"$p\".\n");
		case 'res#autres':
			return ("Le fichier resultat doit etre different des autres fichiers (source, parentheseurs). \n");
		case '1Res' : 
			return("Ne donner qu'un seul fichier resultat. $p ignore.\n");
		case 'res=' :
			return (" Fichier resultat $p.\n");
		case 'src#autres' :
			return ("le fichier source $p est deja designe par ailleurs.\n");
		case 'src=' :
			return (" Fichier source $p.\n");
		case 'xtrt#autres' :
			return ("le fichier de traitement $p est deja designe par ailleurs.\n");
		case 'xtrt=' :
			return (" Fichier de traitement externe $p\n");
		case 'par#autres' :
			return ("le fichier de parenth�seurs $p est deja designe par ailleurs.\n");
		case 'FicPar=' :
			return (" Fichier de parenth�seurs $p.\n"); 
		case 'DbleCol':
			return ("Double emploi de la colonne $p\n");
		case 'ColTri' :
			return (" Colonne(s) de tri $p.\n");
		case 'ColRes' :
			return (" Colonne(s) resultat $p.\n");
		case 'ParInc' : 
			return ("Valeur inaffectable $p1 car parametre invalide $p0.\n");
		case 'ValSsPar' :
			return ("Valeur inaffectable $p.\n");
		case '-cpt+-multiOU-uk' :
			return ("ERR Emploi de -cpt exclue celui de -multi, -u ou -uk.\n");
		case '-multi+-uk' :
			return ("ERR Emploi de -multi exclue celui de -uk.\n");
		case '-cpt+-col' :
			return ("ERR Emploi de -col et -cpt simultanement.\n");
		case '-uSs-col' :
			return ("ERR Emploi de -u sans colonne.\n");
		case 'StopErr' :
			return ("Arret sur erreur : \n$p");
		case 'FicParInv' :
			return ("Fichier de parenth�seur invalide $p.\n");
		case 'estMulti':
			return ("- Mettre autant de ligne que de valeur associee a une cle.\n");
		case 'est-u':
			return ("- Ne pas repeter les lignes identiques.\n");
		case 'est-cpt' :
			return ("- Associer a chaque cle le nombre de ses occurrences.\n");
		case 'ImpOuvSrc' :
			return ("Pb ouverture de la source $p");
		case 'TMaxFait' :
			return ("Temps maximum de $p secondes atteint. \n");
		case 'ColCleAbs' :
			return ("ERR ligne sans cle $p0:$p1 : colonne $p2 absente. \n");
		case 'ColResAbs' :
			return ("ATT ligne $p0:$p1 sans colonne $p2. \n");
		case 'Conc1' :
			return ("$p0 fichiers lus de $p1 lignes.\n");
		case 'ImpRes' :
			return ("Impossible de creer le fichier resultat $p.\n");			
		case 'Conc' :
			return ("Lignes lue: $p0 ; lignes ecrite: $p1\n");
	}
}
$ce_repertoire = dirname(__FILE__);

include_once ("$ce_repertoire/gcsv_tri.corps.php");
?>