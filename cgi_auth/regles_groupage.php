<?php

/**
 * @version du 5/03/2014 avec s�paration code / configuration.
 * Configuration of 
 * 1/ access right to resources.
 * 2/ stat categories to qualify consultation requests.
 * Both can use host IP and/or user informations gotten out LDAP or DB users directory.
 *  
 * Configuration 
 * 1/ des acc�s et
 * 2/ des cat�gories statistiques (faci�s) qualifiant les requ�tes faites 
 * 
 */
/**
 * Special group name for anonymous access $GdPublic (used in ezproxy config.txt file)
 * Nom du groupe pour acc�s anonyme (utilis� en config.txt de ezproxy)
 */
$GroupAnonyme = "GdPublic";
$LoginAnonyme = "anonyme";

/**
 * Distinction between on site and off site access :
 * Group names used to grant access to one resource at least
 *   $OnOffPass = access granted every where 
 *   $OnOnlyPass =  with condition the users IP host is known (on Campus only access)   
 * Groupes utilis�s pour signifier un prime acc�s (� au moins une ressource) 
 *    pr�cis� par l'appartenance � d'autres groupe.
 *   $OnOffPass = acc�s a
 */
$OnOffPass = "OOG";
$OnOnlyPass = "OCG";

/*
 * Group names used to grant administration access of CGI suite 
 * Noms des groupes ouvrant � l'administration du CGI 
 */
$AdminList = array('my_admin1','my_admin2');


/**
 * Log file name - Nom du journal propre au filtre
 */
$nom_journal = "journal.txt";



/**
 * 
 * $regles_groupage_LDAP 
 * @var array = 
 * 
 * Array associating user attribute/property name with a list of rules to apply for each 
 * value of the property given many values gathered with '+' sign as in config.txt file.
 * Each rule has the form S1=>S2 . S1 must be read as the first string of a 
 * preg_match (PHP) and is to be searched in every target value vN of the property.
 * S2 is a result string that is read as the second string of the preg_replace if it doesn't
 * begin with ==, So if searched string is found,  
 *   * if S2 begins with ==, use the string following == as result 
 *   * else use  preg_replace (S1,S2,vN) 
 * Ex: 
 * Property values 'aabbcc', 'aaabbbccc'
 * Rules for the property : 
 *   '/ab(bc)/'=>'$1', 
 *   '/abbb/'=>'=>'==GR1+GR2'
 * First value match first search (abbc found in aabbcc) and the replacement of abbc with bc
 * give a first result abcc (with the 'a' before th match and the 'c' after the match in the
 * value). Then, the second rule is ignored on the first value.
 * Second value doesn't match 'abbc' . But it matches 'abbb'. So the value GR1+GR2 is added 
 * to the result.
 * Finally, for this property, user get 'abcc+GR1+GR2' groups.
 * Note that the result value is a part of group used by ezproxy, you can use the + sign in
 * a result value to give name of two groups.
 *  
 * Tableau de r�gle de r��criture pour l'attribution de groupes pour ezproxy � partir
 * des valeurs d'attributs LDAP.
 * Chaque entr�e porte le nom de l'attribut LDAP qu'elle concerne. 
 * Elle contient un tableau de valeurs recherch�e dans CHAQUE valeurs de l'attribut.
 * A chaque valeur recherch�e est associ�e une cha�ne pr�c�d�e (commen�ant) ou non de "==".
 * Si une valeur de l'attribut correspond � une valeur recherch�e, elle ajoute aux groupes 
 * de l'usager la cha�ne associ�e si elle commence par "==" ou calcule la cha�ne en rempla�ant
 * dans la valeur de l'attribut, cha�ne recherch�e par la valeur qui lui est associ�e.
 * (selon le principe des expressions r�guli�res)
 * Les attributs utilisables sont ceux d�clar�s dans acces.conf.php pour la source d'informationdans 
 * La recherche se faisant sur chaque valeur de l'attribut, un attribut peut fournir plusieurs 
 * valeurs en r�sultat.  
 * Les valeurs sont assembl�s en une seule valeur, o� ils sont s�par�s par des '+',
 * mais une valeur r�sultat d'une r�gle peut d�signer plusieurs groupes si elle contient des 
 * signes + pour s�parer leurs noms.
 * Ex: 
 * Valeurs de l'attribut 'aabbcc', 'aaabbbccc'
 * R�gles de l'attribut : 
 *   '/ab(bc)/'=>'$1', 
 *   '/abbb/'=>'==GR1+GR2'
 * La premi�re valeur v�rifie la premi�re r�gle (elle contient 'abbc') et fournit en r�sultat
 * la valeur 'abcc' obtenue avec le 'a' avant et le 'c' apr�s la valeur recherch�e.
 * La seconde valeur ne correspond qu'� la seconde r�gle et fournit au r�sultat 'GR1+GR2'
 * Au final, cette propri�t� fournit la cha�ne des trois groupes : 'abcc+GR1+GR2'

 */
// in example below, assume 'OOG' is generic OnOff campus Grant and 'admin' (ezproxy admin) 
$regles_groupage_LDAP =
			// Special property login - propri�t� d'office = l'identifiant
                    // Special admin users - Acteurs particuliers
	array 	('login'=>
				array 	('/user1/'=>'==OOG+admin'
				// as login is exactly matched, can use : 
						,'/user2/'=>'OOG+admin'  
						)
/* if otherCategories is a list of businessCategory, definitions for otherCategories
 * will then used for businessCategory. OOG group given for granted categories users.
 * or OCG if only on Campus access.
 * 						
 * 'businessCategory' est bas� sur le m�me principe que 'otherCategories' et sera 
 * d�clar�e comme telle ensuite.
 * Timbrage OOG pour les membres � cat�gorie valide partout et OCG en local uniquement 
*/
			,'otherCategories'=>
				array 	('/Cat1/'=>'OOG'  
						,'/Cat2/'=>'OOG' 
						,'/Cat3/'=>'OCG' // Vacataire d'enseignement
						,'/Cat4/'=>'OCG' // Vacataire administratif ou technique
						)
						// valeur attribu� � un groupe particulier		
			,'OrgGroup'=>
				array	('/app:ezproxy:edoc-access/'=>'==OOG' 
						)						
		/* 
		 * Affectations codes granting access to specific resources. Need OOG/OCG beside 
		 * Codes d'affectations ouvrant l'acc�s � des ressources sp�ciales
		 *  n�cessite OOG/OCG par ailleurs 
		 */		
			,'Affectations'=>
				array 	('/......([A-L]).../'=>'Edu$1'
						,'/......([M-Z]).../'=>'Res$1'
						,'/......([0-9]).../'=>'Other$1'
						,'/Old.....([A-L]).*/'=>'alumni$1'
						)
			);
// Even rules to be used for properties having the same set of values
// D�finition-copie d'attributs synonymes ayant des valeurs comprises et similaires
// en sens que les valeurs d'un attribut d�j� cit� : 			
$regles_groupage_LDAP['businessCategory']='udlCategories';			

/**
 * Rules for profiles reported in logs file of CGI to be used for statistics.
 * Different of ezproxy granting access rules:
 * 		List of target-values to create. Every one is associated with its source users properties i.e.
 * every of its values is the result of a unique value of a unique property as :
 * a result of a function  :: 
 * 		'property_name'=>array('function'=>'function_name')
 * a set of "deduire_de_regles function" rules :: 
 * 		'property_name'=>array('rules'=>array(...))
 * simply the values of property  ::
 * 		'property_name'=>array()
 *  
 * Regles de d�finition des profiles statistiques :
 * Interpr�tation des attributs LDAP pour construire des donn�es dites cibles. 
 * Toutefois, chacune utilise pour �tre d�finies, les attributs de mani�re individuelle i.e.
 * les donn�e extraites (forg�es) sont chacune d�duite d'un seul attribut par
 * application d'une fonction  :: 
 * 		'attribut1'=>array('fonction'=>'nom_de_fonction')
 * usage de r�gles de r��criture/remplacement (V. fonction deduire_de_regles) :: 
 * 		'attribut2'=>array('regles'=>array(...))
 * simple copie des valeurs  ::
 * 		'attribut3'=>array()
 *  
 */
$regles_profiles_LDAP = 
    array ('general'=> array	('Categories'=>
									array 	('rules'=> array ('/(.+)/'=>'Cat$1'))
								,'Affectations'=>
									array 	('rules'=> array 	('/.LOC.(.....)/'=>'$1'))
								,'login'=>
									array 	('regles'=>
										array 	('/user1/'=>'testeur'
												,'/user2/'=>'testeur'
												)
											)
								,'Groups'=>
									array	('rules'=>array 	('/doc-access/'=>'==parGrouper')
											)
								)
			,'MainCategory' => array('businessCategory'=>		 
										array 	('rules'=> array ('/(.+)/'=>'Cat$1'))
									)
			,'MainAffect'=>array('MainAffectation'=>
									array('rules'=> array ('/.LOC.(.....)/'=>'$1'))
								)
			,'niveau'=>array('YearInscription'=>
								array 	('function'=>'niveau_etude')
							)
			);


/* 
 * Gathering order of qualifications
 * 
 * Ordre d'assemblage des qualificatifs 
 */			
$ordre_formation_profiles = array('BC','AllBC','Affect','AllAff','special','manual');
/*
 * Default value of statistic for  none qualification users
 * Valeur statistique par d�faut si aucun qualificatif ne peut �tre fourni
 */
$profiles_defaut = '-----';


/*
 * Administration test customization of extracted properties display produced by verif.php. 
 * Adaptation de l'affichage des attributs extraits de LDAP (ou d'une base de donn�es)
 * dans l'outil d'administration et verifivation verif.php
 */	  	
$regles_affichage_LDAP = 
// to merge businessCategory info categories  for example
// fusion de propi�t�s ex.  businessCategory est affich� dans categories
	array('reecrire'=>array('businessCategory'=>'categories'
							,'mainAffectation'=>'Affectation'
							)
// information grouping line by line
// groupement par ligne des information							
		 ,'ligne'=>array(array('Nom','Prenom','login','identifieur')
		 				,array('cn','Adrel')
		 				,array('groups','categories','Affectation')
		 				)
// header / title to use if different of property name
// (Re)Nommage des attributs pour l'affichage. 
		 ,'titre'=>array('Nom' => 'Name'
		 				,'Prenom'=>'First name'
		 				,'identifieur'=>'IdP Organisation'
		 				,'Adrel'=>'Email address'
		 				,'groups'=>'Groups'
		 				,'categories'=>"Business Categories"
		 				,'Affectation'=>"Affectations"
		 				)
		);



/**
 * 
 * @var array $regles_groupage_IP = est un tableau qui fait SUIVRE un n� IP ou un intervalle 
 * de n� (premier-dernier),  des groupes � attribuer � l'usager utilisant ce poste.
 * D�s qu'une correspondsance est trouv�e, le test est termin� 
 * Le groupe particulier GdPublic a le m�me effet que AutoLoginIP auparavant pour les 
 * postes publics
 *   
 */
$regles_groupage_IP =
	array	(
			'1.1.1.1-256.256.256.256'=>'WorldGroup'
			);

