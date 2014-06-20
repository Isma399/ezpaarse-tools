#!/usr/bin/env php
<?php
/**
 * gcsv_extrait.php V1.1.1
 * 
 * 
 * Script qui sert � supprimer ou � extraire de logs complets fournis par un service Web les lignes et colonnes 
 * jug�es (in)utiles (exemple les r�cup�rations d'ic�nes, de javascripts, ...; les colonnes 
 * de session ou de referer ou ... les donn�es concernant un site/ un utilisateur, ...)
 * 
 * Usage : gcsv_extrait.php [(+|-)test | -v | -rapport | -status)]
 * 			[-xtrt PHP_filename] [-max int] [(-tmax|-d) int] [-sep char] [-glu char] 
              [( (+|-)hd[1] | -format sormat_str ]           
              [-colt colnamenum {+|-}ctriteria] [-colt ....]]              
              ([{+|-}col colnamenum [colnamenum ...] ]|-allcol) 
              [-{colca|colcip|colda|coldip} colnamenum [colnamenum ...]]
<<<<<<< HEAD
              [-colurl colnamenum +colu(scheme|host|hostrv|port|path|query|fragment) [+colu(scheme|...)...]]
=======
              [-colurl colnamenum +colu(scheme|host|port|path|query|fragment) [+colu(scheme|...)...]]
>>>>>>> ee89e4d44c2ffe383bb7c5ba0726dd87b67865e9
              [-res result_filename] [-rej reject_filename] 
               {src source_filenames| source_filename}
 * where ctriteria =
        	std|img|js
        	f|fh filename [filename [...]]
            s string [string[...]]
            eq|ne|ge|gt|le|lt rationnal_value
            be|oo interval (e.g. relationnal,relationnal) 
            teq|tne|tge|tgt|tle|tlt datetime_value (as YYYY-MM-DD.hh.mm.ss)
            tbe|too datetime_interval (e.g. datetime,datetime)
            
 *  Une ligne est retenue si elle contient une des valeurs recherch�es et ne contient pas de valeur exclue 
 *  dans la colonne correspondant au crit�re .
 *  Les colonnes s�lectionn�es pour le r�sultat peuvent �tre r�ordonn�es et encod�.
 *  Une colonne contenant une URL peut �tre �clat�e en plusieurs correspondants aux �l�ments choisi 
<<<<<<< HEAD
 *  formant l'URL d'origine (e.g. host,  path, query V. parse_url :: http://www.php.net/manual/fr/function.parse-url.php)
 *  et hostrv qui est le host renvers� pour l'avoir dans un ordre logique.
=======
 *  formant l'URL d'origine (e.g. host, path, query V. parse_url :: http://www.php.net/manual/fr/function.parse-url.php
>>>>>>> ee89e4d44c2ffe383bb7c5ba0726dd87b67865e9
 *
 *  Le CSV peut �tre un fichier de logs dont les valeurs d'une colonne peuvent contenir le caract�re
 *  servant � s�parer par ailleurs les colonnes souvent l'espace). 
 *  Ces colonnes � valeur complexes sont reconnues par le parenth�sage de leurs valeurs. 
 *  Par d�faut, il y deux parenth�seurs (utilis� classiquement dans les logs) : 
 *  la double quote("") ou le couple crochet ouvrant/fermant ([]).
 *  Pour changer les parenth�seurs, utiliser -par fichier_parentheses. Le contenu du fichier �crase les
 *  parenth�seurs par d�faut.
 *  Au niveau des lignes retenues/exclues , cinq moyens permettent de les d�finir :
 *   - des valeurs fournies directement au niveau de la commande -s chaine1 chaine2...
 *   - des valeurs standards bas�e sur des logs classiques (images et scripts)
 *   - des fichiers de valeurs (ASCII contenant une valeur par ligne)
 *   - des fichiers de host EzProxy (dont on ne retient que les host domain hj ou dj)
 *   - des valeurs num�riques test�es sur des colonnes qui leur sont sp�cifiques.
 *   Si aucun fichier n'est indiqu� en source (-src), c'est l'entr�e standard qui est utilis�e.
 *   Si aucun fichier n'est indiqu� en r�sultat (-res), c'est la sortie standard qui est utilis�e.
 *   Si aucun fichier n'est indiqu� pour les lignes non int�ressante (-rej), elles sont oubli�es.
 *    	Moins de 3s avec seulement la cha�ne invalidante pour 17970 lignes retenues.
 *  ... V1.1.1 correction d'une erreur li� � une s�lection positive multiple sur l'ensemble de la ligne et
 *     et en conservant dans le r�sultat toutes les colonnes. Typiquement +f utilis� sans +/-col . Le test n'�tait tout
 *     simplement pas fait.
 *  ... V1.1 (15/05/2013)
 *      . adjonction de la possibilit� de d�finir des valeurs commen�ant par le signe + ou -
 *  ... V1.0 (6/05/2013)
 *  	.am�lioration des performences en cas de test sur colonne 
 *      .d�finition syst�matique des colonnes test�es sauf pour l'ensemble de la ligne pour les
 *       param�tres s f et fh (qui peuvent �tre employ�s sans pr�cision de colonne)     
 *  
 *  ... V0.3 (2013.04.09) 
 *      . adjonction du param�tre format 
 *   ... V0.2 (2013.03.01) :
 *     - test num�riques  sur des colonnes diff�rentes des tests textuels
 *      avec intervalle de valeurs (utiles pour les code de retour HTTP, les taille en octets des �changes) 
 *     - usage possible d'expressions r�guli�res comme cha�ne d'exclusion ou de s�lection des lignes 
 *       soit en ligne de commande (param�tre s) soit dans les fichiers de valeurs (param�tre f) 
 *     - decodage symetrique du codage. Permet de retrouver les donn�es originales.
 *     - acc�l�ration du traitement des colonnes cod�es (anonymisation).
 *     - traitement avec retenue ou suppression de la ligne d'ent�te qui precise le contenu des colonnes.
 *   gcsv_extrait.php V0.0 
 *     - introduction des tests de date/heure
 *     - tous les tests se font sur une colonne pr�cise (et non plus liste de colonnes test�es)
 *     - r�elle introduction des intervalles 
 *     - correction de l agestion de la ligne d'ent�te. 
 *  ... V0.1 
 *     - les crit�res de s�lection sont cumulatifs sur des colonnes diff�rentes :
 *       ... -colt C1 +critere1 -colt C2 +critere2 -colt C -critere3...
 *       pour �tre retenue, une ligne doit v�rifier les deux crit�res critere1 dans et C1 et critere2 dans 
 *       C2 et mais pas critere3 dans C (o� C peut �tre C1 ou C2). Mais plusieurs valeurs sur une m�me colonne
 *       sont prises comme des alternatives.
 *       ... -colt C1 +critere1 +critere2
 *   ... V0.0   
 *  	- am�lioration d'analyse des cha�nes � exclure ou retenir qui contiennent des caract�res non alphanum.
 *  	- utilisation de l'entr�e standard pour un usage en pipe possible.
 *  ... extrait_ezp_logs.php V0.1 (2013.02.06)  : 
 *    performance : 55s pour traiter 46300 lignes avec  4336 valeurs validantes et 1 invalidante soit en moyenne
 *    	plus de 1160 tests par ligne.
 *      229s pour traiter 102340 lignes de log avec s�lection des lignes ayant un host 
 *      parmi 2858 sauf celle contenant en plus la r�f�rence � un host exclu puis 
 *      explosion d'url, et encodage de login pour les 645 v�rifiant les crit�res.  
 *      
 */
$langue = 'fr';
function echo_usage (){
?>
Usage : ./gcsv_extrait.php -aide | (
              [(+|-)test | -v | -rapport | -status)] 
              [-xtrt PHP_filename] [-max int] [(-tmax|-d) int] [-sep char] [-glu char] 
              [( (+|-)hd[1] | -format format_str ]           
              [-colt (colnamenum|"*") {+|-}ctriteria [(+|-)criteria] [-colt ....]] [-strin]  
              [-cold colnamenum:format (+|-)datecriteria] [-cold ...] [-strin]  
              [-{colca|colcip|colda|coldip} colnamenum [colnamenum ...]]
              [(-allcol | (+|-)col colnamenum [colnamenum ...])] [+colf colnamenum:value [colnamenum:value ...]]  
<<<<<<< HEAD
              [-colurl colnamenum +colu(scheme|host|hostrv|port|path|query|fragment) [+colu(scheme|...)...]]
=======
              [-colurl colnamenum +colu(scheme|host|port|path|query|fragment) [+colu(scheme|...)...]]
>>>>>>> ee89e4d44c2ffe383bb7c5ba0726dd87b67865e9
              [-res result_filename] [-rej reject_filename] 
              [-src source_filenames [source_filename ...]]
              )
    ctriteria =
        	std|img|js
        	f|fh filename [filename [...]]
            s string [string[...]]
            eq|ne|ge|gt|le|lt rationnal (as d+[.d+] where d+ is one or more digits) 
            be|oo rationnal,rationnal 
    datectriteria =
            teq|tne|tge|tgt|tle|tlt datetime (as [[[YYYY-]M[M]-]D[D]][.h[h][:m[m][:s[s]]]])
            tbe|too datetime,datetime
    special values (string, numerical, filename) beginning with signs plus(+) or minus(-) must be
    preceded with a dot(.) . Ex: To search +col string, use .+col argument value.  
	<?php 
}


function detail_usage (){
?>               
gcsv_extrait.php V1.2 :
Outil de s�lection d'information issus de logs ou plus generalement de fichier CSV.
  La selection des informations a retenir en resultat se fait sur les lignes, et pour chaque ligne retenue,
sur les colonnes presentes dans le resultat.
Une ligne est retenue si elle contient une des valeurs recherch�es dans chacune des colonnes testees et ne 
contient aucune des valeur exclues des colonnes testees.  
Toutefois, une valeur peut etre recherchee, exclue sur toute la ligne sauf pour les criteres sur valeurs 
numeriques ou dates.
  Les colonnes s�lectionn�es pour le r�sultat peuvent �tre r�ordonn�es et leur contenu encod�/d�cod� ou 
eclate pour les URL et n'en retenir que des �l�ments constitutifs (host, path, query V. parse_url :: 
<<<<<<< HEAD
http://www.php.net/manual/fr/function.parse-url.php), plus une colonne hostrv qui contient le 
host dans un logique de ses composants de domaine.
=======
http://www.php.net/manual/fr/function.parse-url.php). 
>>>>>>> ee89e4d44c2ffe383bb7c5ba0726dd87b67865e9

  Le CSV peut �tre un fichier de logs et les valeurs de certaines colonnes (date/heure) peuvent contenir
le separateur de colonnes qui est l'espace. Pour d�limiter le contenu de ces colonnes, des couples de 
caracteres servent de parentheses.
Par d�faut, il y deux couples : la double quote("") ou les crochets ouvrant/fermant ([]).
Pour les changer, utiliser le parametre -par fichier_parentheses. Le contenu du fichier remplace les
couples par defaut.

  Cinq moyens permettent retenir ou d'exclure du resultat  des lignes :
 - fournir au niveau de la commande des chaines par le critere +/-s chaine1 chaine2...
 - utiliser les des valeurs standards bas�e sur des logs classiques (images et scripts)
 - des fichiers de valeurs (ASCII contenant une valeur par ligne)
 - des fichiers de host EzProxy (dont on ne retient que les host domain hj ou dj)
 - des valeurs num�riques test�es sur des colonnes qui leur sont sp�cifiques.
 Si aucun fichier n'est indiqu� en source (-src), c'est l'entr�e standard qui est utilis�e.
 Si aucun fichier n'est indiqu� en r�sultat (-res), c'est la sortie standard qui est utilis�e.
 Si aucun fichier n'est indiqu� pour les lignes non int�ressante (-rej), elles sont oubli�es.
 
 N.B. Un pretraitement d'une ligne lue, avant exploitation par l'extracteur, peut etre realis� sur chaque 
   ligne par la fonction a_lecture_ligne mise dans un fichier php.
   (function string a_lecture_ligne(string $ligne))
   De meme, un pretraitement d'ecriture par la fonction a_ecriture_ligne qui re�oit une ligne et un
   tableau s�quentiel des colonnes retenues pour le resultat.
   (function string a_ecriture_ligne(array $colonnes))

Arguments :
    -aide | -help | -h : simple affichage de l'usage
	-test : Indique aussi les arguments	reconnus et pris en compte. Le nombre de lignes traitees / ecrites.
	+test : en plus, analyse plus detaillee des arguments  
	-v    : ... voir tous les messages de cause de rejet des lignes exclues sur stderr.
	-max n : n = nombre maximum de lignes � traiter. 
		 Si non pr�sent, l'int�gralit� des fichiers source est trait�e.
	-tmax seconds | -d seconds : nombre de secondes maximum allou�es au traitement
	-sep char : pr�cise le s�parateur de colonne char � utiliser pour analyser la source
	-glu char : pr�cise le caract�re pour recoller les colonnes retenues en r�sultat
	-par file : fichier de ligne contenant chacune 
	     - deux chaines s�par�es par un espace, la premi�re est la chaine de debut, la seconde celle de fin 
	       de valeur.
	     - deux caract�res 
	     - un seul caractere servant de parenthese de debut et de fin (ex ")
	-xtrt PHP_filename : fichier PHP dont on utilise les fonctions a_lecture_ligne(string) et 
	     a_ecriture_ligne(array) qui retournent la ligne � traiter a partir d'une ligne lue en fichier 
	     ou celle a ecrire a partir des colonnes retenues pour etre ecrites. 
		   

    Arguments de CONSERVATION OU EXCLUSION des LIGNES/ des COLONNES. 
       Le (+/-) indique pour le plus, la conservation  ou l'exclusion de la donn�e (colonne ou ligne)
       Les colonnes peuvent �tre d�sign�es par leur num�ro (de 1 � N) ou par leur nom indique dans la
       premiere ligne a lire du(des) fichiers source derriere (+|-)hd[1] ou par le parametre -format

       ** Conservation / rejet des COLONNES ecrites en resultat :
      Si aucun de ces attribut n'est pr�cis�, la ligne est int�gralement conserv�e.         
    (+/-)col N [N2[...] : ne conserve que (+) ou supprime (-) les colonnes N [N2[...] du r�sultat.
       On ne peut utiliser les deux ensemble et -col doit �tre r�serv� au rejet simple d'informations 
       encombrantes. Les deux excluent l'usage de -allcol
    -col(c|d)(a|ip) N [N2[...] : 
       encode (+colc??) ou decode (+cold??) les colonnes N [N2[...] dans le r�sultat. 
       Le codage /decodage est cense traiter des IP (??=ip) ou les caract�res alphanum�riques(??=a) de
       la valeur a coder/decoder. L'argument col(c|d)?? N necessite +col N ou -allcol.
    +colf Nom:val [N2:val2 ...] :
       ajoute une colonne nommee Nom (resp N2) dans l'entete et qui a comme valeur val (resp val2 ...)
       sur les autres lignes.   
    -colurl N : indique la colonne N  en source contenant une URL a scinder dans le resultat. Les attributs
       suivants ne peuvent etre employes que si cette colonne est precisee 
<<<<<<< HEAD
    +colu(scheme|host|hostrv|port|path|query|fragment) : 
        ne peut �tre employ� qu'avec l'argument -colurl. Permet de positionner dans le resultat 
        scheme = le protocole (http), host = le serveur , port, path = la page demandee, 
        query=les parametres de la requete, fragment = paragraphe devant �tre pr�sent� dans la page resultat, 
        et hostrv  = le nom du serveur mais dans l'ordre logique du super domaine
        au sous-...-domaine. 
=======
    +colu(scheme|host|port|path|query|fragment) : 
        ne peut �tre employ� qu'avec l'argument -colurl. Permet de positionner dans le resultat 
        scheme = le protocole (http), host = le serveur , port, path = la page demandee, 
        query=les parametres de la requete, fragment = paragraphe devant �tre pr�sent� dans la page resultat.
>>>>>>> ee89e4d44c2ffe383bb7c5ba0726dd87b67865e9
    -allcol : indication rapide de r�cuperation de toutes les colonnes non encore designees par un
        +col??? ci-dessus.
    -strout : rejette toute ligne dont une colonne a ecrire est absente 

       ** Conservation / rejet des LIGNES  completes :
       * traitement de la ligne d'entete :
    (+|-)hd,(+|-)hd1  : indique la presence de la ligne d'entete a conserver dans le resultat. Si le chiffre 1 est present,
       seul le premier fichier source contient cette ligne. 
       Si elle existe sans etre declaree, elle sera traitee comme une autre ligne, elle ne peut �tre omise
       si les colonne sont designees par leur nom dans cette entete.
    -format "format_str" : indiquer le format symbolique employ� dans le reste de la commande pour designer 
       les colonnes ex "ip ses us url sta byt gps" decrit 7 colonnes.       

       ** Autres lignes :
       
    -colt n : analyse la colonne d�sign�e sur les crit�res textuels ou numeriques decrits ensuite 
       pour retenir ou rejeter une ligne. Impose un crit�re textuel ou numerique ensuite.
    -cold n:format : analyse la colonne d�sign�e selon le format est decrit sur les crit�res de date 
       qui le suivent pour retenir ou rejeter une ligne. Impose un (des)  crit�res date ensuite. 
    Chaque critere est precede du signe + (selection) pour retenir uniquement les lignes le verifiant, 
       ou du signe - (exclusion) pour exclure ces lignes.  
       Les crit�res de s�lection sont cumulatifs sur des colonnes diff�rentes :
        ... -colt C1 +critere1 -colt C2 +critere2
        pour �tre retenue, une ligne doit v�rifier critere1 dans C1 ET critere2 dans C2. 
        Si plusieurs crit�res de s�lection concernent la m�me colonne, ils sont pris comme des alternatives.
        ... -colt C1 +critere1 +critere2
        pour etre retenue, une ligne doit verifier critere1 OU critere2 en colonne C1
    -strin : rejette toute ligne dont une colonne testee est absente 
     
       * criteres TEXTUELS :  
    std : mettre comme string equivalentes '.js','.css','.gif','.jpg','.png','.ico' non suivi d'une lettre
    img : mettre comme string equivalentes '.gif','.jpg','.png','.ico' non suivi d'une lettre
    js  : mettre comme string equivalentes '.js','.css' non suivi d'une lettre
    s stringS : ne retient/exclue du r�sultat que les lignes dont les colonnes 
       analys�es contiennent une des chaines stringS. Si les premier et dernier caracteres sont identiques 
       et non alphanum�riques, l'int�gralit� est pris comme une expression r�guli�re. (Traitement plus long)
    fh filenameS : fichiers dont extrait les declaration host et domain au sens d'ezproxy On prend les cha�nes 
       de chaque ligne si en d�but de ligne, il y a d ou domain ou host ou h ou hj  ou dj 
       et que l'on traite comme les suivantes (argument f).
     f filename : fichier dont chaque ligne contient une cha�ne a rechercher (V. s).
       
       * criteres NUMERIQUES et de DATES si l'operateur est precede d'un t ex teq
     [t](eq|ne|ge|gt|le|lt) val : (egale|differente|superieure|sup strict|inferieure|inf strict) a la valeur
        val fournie.  sous la forme 
       [+|-]d[.d] pour les test num�riques (les d �tant un ou des chiffres), 
       [[[YYYY-]mm-]dd][.hh[:MM[:ss]]] pour les date/heure.    
     [t](be|oo) valmin,valmax :  idem pour les operateurs "entre" ou "hors de" qui demande un intervalle 
       fourni sous la forme vmin,vmax 
       
       * Format de colonne date (http://fr2.php.net/manual/en/function.strftime.php)
     Tout �l�ment d'information est symbolise par le signe % suivi d'une lettre. 
     Tout lettre non precedee de % ou autre caractere du format different de %, est interprete tel que.
     Dans la liste suivante, 
     litt = litteral / num = numerique / (n) fixe sur n lettres/chiffres. 
     Jour de la semaine :  %a = litt(3) %A = litt ; %u = num (1=lun-7=dim.) ; %w = num (0=dim-6=sam)
     Jour du mois : %d = num(2) ; %e = num ; de l'annee : %j = num(3) (001-366)
     Mois : %b = %h = litt(3) ; %B = litt ; %m = num(2)
     Annee : %y : num(2) ; %Y = num(4)
     Heure : %H : 00-23 ; %k = 0-23 ; %I = 01-12 ; %p = AM/PM ; %P = am/pm
     Minute : %M = 00-59 Secondes %S = 00-59 
     Horaire %r=%I:%M:%S %p ; %T = %H:%M:%S    
     Caracteres echappes  %% = % %, = ,     

	** Fichiers sources et resultat 
     -src source_filenames : liste des fichiers � traiter. S'il n'y en a pas, c'est l'entree standard
                    qui sert de source.
     -res result_filename : pr�cise le chemein du fichier � cr�er. Si cet argument
        			est absent, c'est la sortie standard qui affiche les lignes filtr�es.
     -rej reject_filename : pr�cise le chemein du fichier des rejetes � cr�er. 

** Recommandations :
  - Preferer les tests (moins precis) et resultats ne precisant pas de colonne car les traitements 
    sont plus rapides ...
  - Ne pas hesiter a obtenir le resultat en plusieurs commandes successives (pipees entre elles).

** Exemples d'emplois : 
 Le format d'entete issu de ezproxy, est mis dans un fichier ne comportant qu'une ligne obtenue par 
 copi� du format mis dans le fichier config.txt, puis renomm� pour avoir des colonnes � nom lisibles :
 %h %{ezproxy-session}i %u %t "%r" %s %b %{ezproxy-groups}i    
 devient dans le fichier format.txt
 IP session login date url code taille groupes 
  * faire de plusieurs fichiers � entete ezproxy, un seul fichier avec la nouvelle entete
./gcsv_extrait.php -src format.txt mes_log* +hd >final
  * coder des colonnes en pr�servant la premiere ligne d'entete :
./gcsv_extrait.php -src format.txt mes_log* -allcol +hd -colca login -colcip IP
  * supprimer des colonnes. L'entete est corrigee en consequence :
./gcsv_extrait.php -src format.txt mes_log* +hd -col login code  session
  * reordonner et coder des colonnes. Ne retenir que date, coder IP et login, ne retenir que le host url 
   et groupes  :
./gcsv_extrait.php -src format.txt mes_log* +hd -colurl url -colcip IP -colca login +col date IP login \\
+coluhost +col groupes
  * retirer d'un log toutes les lignes qui concerne un ensemble de domaines dont les noms sont dans 
        les fichiers nomme "dom_..."  :
./gcsv_extrait.php -src mes_logs* -hd -colt url -f dom_* 
  * extraire d'un log toutes les lignes qui concerne un editeur dont on conna�t les noms de domaine rassembl�
        dans le fichier dom1 et ne conserver que celle ne referant pas mon reverse my_server. 
        De ces lignes ne retenir que les �l�ments "utiles" : date, login cod�, la page (url.path) et la 
        requete (url.query) faite au serveur de l'editeur et les categorie statistiques de l'usager 
        (groupes)
./gcsv_extrait.php -src format.txt mes_logs* -hd -colurl url -colca login +col date login +colupath +coluquery \\
+col groupes -colt url +f dom1 -s my_server -res fichier_dom1
   ** Les deux dernieres sont plus rapide si la selection/exclusion de ligne se fait sans preciser de colonne 
   et en deux temps pour la seconde selection de ligne puis selection de colonne :
 ./gcsv_extrait.php -src mes_logs* -hd -colt "*" -f dom_*  
 ./gcsv_extrait.php -src mes_logs*  +hd -colt "*" +s dom1 -s my_server | \\
 ./gcsv_extrait.php -hd -colurl %r -colca %u +col %t %u +colupath +coluquery +col %{ezproxy-groups}i -res fichier_final
   * Ne conserver que les echanges entre 11h13 et 11h17 de code retour 200 ne referant pas mon reverse my_server
   mettre le r�sultat dans res1
 ./gcsv_extrait.php  -hd1 -src format.txt mes_logs*  -cold "date:[%Y/%m/%d %H:%M:%S]" +tbe .11:13,.11:16 \\
 -colt code +eq 200 -colt "*" -s my_server +col "*" >res1
 
  
  ...  
<?php 
}


function etat_mess (){
	global $maxval,$sources,$tmax;
			$echo_etat = "";
		if ($maxval>0) 
			$echo_etat.= "* Traiter un maximum de $maxval lignes ";
		else $echo_etat.= "* Traiter le contenu ";
		if ($sources)
			$echo_etat.= " des fichiers ".implode(',',$sources);
		else 
			$echo_etat .= " de l'entree standard";
		if ($tmax > 0) $echo_etat.= " en un maximum de $tmax secondes";
		$echo_etat.= ".\n";
	global $valide_ligne,$extrait,$extrait_RE,$extrait_num;		
		if ($valide_ligne) {
			if ($extrait || $extrait_RE) {
				$nv = 0; $nc=0;
				foreach ($extrait as $nocol=>$vals) {
					$nv += count($vals); $nc++;
				}
				foreach ($extrait_RE as $nocol=>$vals) {
					$nv += count($vals); $nc++;
				}
				$echo_etat.="* Ne retenir que les lignes contenant $nc valeurs parmi les $nv cherchees (une par col).\n";
			} 
			if ($extrait_num) {
				$n = count($extrait_num);
				$echo_etat.="* Ne retenir que les lignes verifiant les $n tests numeriques ou sur date/heure declares.\n";
			}
		}
	global $filtre_ligne,$filtres,$filtres_RE,$filtres_num;
		if ($filtre_ligne){
			if ($filtres || $filtres_RE){
				$nv = 0; $nc=0; 
				foreach ($filtres as $nocol=>$vals) {
					$nv += count($vals); $nc++; 
				}
				foreach ($filtres_RE as $nocol=>$vals) {
					$nv += count($vals); $nc++; 
				}
				$echo_etat.="* Ne pas retenir les lignes contenant une des $nv valeurs exclues (parmi $nc col).\n";
			}
			if ($filtres_num){
				$n = count($filtres_num);	
				$echo_etat.="* Ne pas retenir les lignes contenant une valeur exclue sur les $n colonnes testees sur nombre ou date/heure\n";
			}
		}
	global $sepaff,$gluaff,$format,$fic_res,$fic_rej;
		if ($sepaff)  
			$echo_etat .= " ... avec comme separateur \"$sepaff\"\n";
		if ($format){
			$echo_etat.= " ... et comme format de ligne \"$format\"\n";
		}
		if ($fic_res)
			$echo_etat.= "- Mettre le resultat dans $fic_res\n";
		else 
			$echo_etat.= "- Afficher le resultat\n";
	global $col_exclues,$col_ret,$col_code;			
		if ($col_exclues)
			$echo_etat.= " ... en retirer la(les) colonne(s) ".implode(', ',$col_exclues)."\n";
		if ($col_ret)
			$echo_etat.= " ... n'en retenir que la(les) colonne(s) ".implode(', ',$col_ret)."\n";
		if (count($col_code)>0)
			$echo_etat.= " ... et encoder/decoder les colonnes ".implode(', ',array_keys($col_code))."\n";
		if ($gluaff)  
			$echo_etat .= " ... avec comme glu \"$gluaff\"\n";
		if ($fic_rej) 
			$echo_etat.= "- Mettre les lignes rejetees dans $fic_rej\n";
		return ($echo_etat);
	
}
function traite_fichier_parentheses_mess($no_mess,$param=""){
	switch ($no_mess){
		case 'FINC' : 
			return ("Fichier de parenth�seur inconnu $param.\n");
		case 'FINV' : 
			return ("Fichier de parenth�seur $param invalide.\n");
		default: 
			if (is_array($param)) $param=print_r($param,true);
			return ("traite_fichier_parentheses_mess erreur inconnue $no pour $param.\n");
	}
}
function traite_fichier_mess ($no, $param=''){
	global $langue;
	switch ($no){
		case 'FINV': return ("Fichier invalide $param.\n");
		case 'TEST': $fin =$param[0]; $nb = $param[1];
			return ("...$fic contient $nb valeurs.\n");
		default: 
			if (is_array($param)) $param=print_r($param,true);
			return ("traite_fichier_mess erreur inconnue $no pour $param.\n");
	}
}
function converti_col_val_mess ($no,$param){
	switch ($no){
		case 'COLINV': return ("Colonne $param non trouvee dans l'entete des logs.\n");
		case 'DBLDEF': return ("Double definition de $param.\n");
		case 'LIGOR' : return ("Ligne format = $param \n");
		default: 
			if (is_array($param)) $param=print_r($param,true);
			return ("converti_col_val_mess erreur inconnue $no pour $param.\n");
		
	}
}
function ajoute_val_num_mess($no,$param){
	switch ($no){
		case 'NOOP': return ("$param n'est pas un operateur valide.\n");
		case 'NOINTV':
			$val = $param[0];$op=$param[1]; 
			return("$val n'est pas un intervalle pour $op.\n");
		case 'INVVAL': 
			return("Valeur invalide : ".$param);
		case 'INTVINV': 
			return("Bornes d'intervalle invalides $param.");
		case '1VAL': 
			return("Ne fournir qu'une valeur pour $param.");			
		default: 
			if (is_array($param)) $param=print_r($param,true);
			return ("ajoute_val_num_mess erreur inconnue $no pour $param.\n");
	}
}
function normalise_critere_num_mess ($no,$param){
	switch ($no){
		case 'VINV': 
			return ("Valeur numerique a tester invalide $param\n");
		case 'DTINV':
			return ("Date invalide $param a tester. Structure generale AAAA-MM-JJ.hh:mn:ss .\n");
		case 'ANNEEINV': 
			$v=$param[0];$vt = $param[1];
			return("Annee invalide $vt dans la date $v.\n");
		case 'MOISINV': 
			$v=$param[0];$vt = $param[1];
			return("Mois invalide $vt dans la date $v.\n");
		case 'JOURINV': 
			$v=$param[0];$vt = $param[1];
			return("Jour invalide $vt dans la date $v.\n");
		case 'HEURINV': 
			$v=$param[0];$vt = $param[1];
			return("Heure invalide $vt dans la date $v.\n");
		case 'MININV': 
			$v=$param[0];$vt = $param[1];
			return("Minutes invalides $vt dans la date $v.\n");
		case 'SECINV': 
			$v=$param[0];$vt = $param[1];
			return("Secondes invalides $vt dans la date $v.\n");
		default: 
			if (is_array($param)) $param=print_r($param,true);
			return ("normalise_critere_num_mess erreur inconnue $no pour $param.\n");
	} 
}
function valeur_col_valide_mess ($no,$param=''){
	global $langue;
	switch ($no){
		case 'SSVAL':
			return("Pas de valeur en colonne $param");
		case 'PRESVAL':
			$nocol = $param[0]; $une_val=$param[1];
			return("Presence de $une_val en $nocol.");
		case 'VALEXC':
			$nocol = $param[0]; $une_val=$param[1];
			return("Valeur exclue en colonne $nocol : $une_val.");
		case 'NOVAL'://
			$nocol = $param[0]; $une_val=$param[1];
			return("Colonne $nocol : aucune valeur recherchee presente dans : $une_val.");
			
		default: 
			if (is_array($param)) $param=print_r($param,true);
			return ("valeur_col_valide_mess erreur inconnue $no pour $param.\n");
	}
}

function message ($no,$param=''){
	global $langue;
	switch ($no){
		case 'ArgINC' : 
			return("Argument inconnu $param.\n");
		case 'ArgINAT' : 
			$liste = $param[0]; $arg=$param[1];
			return ("Argument attendu, un parmi $liste et non pas $arg.\n");
		case 'ValATT' : 
			$arglu = $param[0]; $arg_en_cours = $param[1];
			return ("Argument trouve $arglu alors que $arg_en_cours n'a pas de valeur.\n");
		case 'param' :
			return ("Argument $param ");
		case 'verbeux' : 
			return (" -- mode test plus.\n");
		case 'test' : 
			return (" -- mode test simple.\n");
		case 'col+-' :
			return ("+allcol et +col/-col incompatibles.\n");
		case 'PosCOLU' :
			$nom = $param[0];$pos=$param[1];
			return ("Colonne $nom retenue en $pos position.\n");
		case 'NoColt' :
			return ("Operateur sans colonne affectee : $param\n");
		case 'coldateINV' :
			return ("Colonne:format date invalide : $param\n");
		case 'colfixeINV' :
			return ("Colonne:contenu = colonne a contenu fixe invalide : $param\n");
		case 'argSig' :
			return ("Parametre $param trouve\n");
		case 'maxINV' :
			return ("Le maximum de ligne max n'est pas un entier\n");
		case 'maxOK' :
			return ("Nombre maximum de lignes a traiter $param. \n");
		case 'tmaxINV' :
			return("Duree maximum de traitement n'est pas un entier\n");
		case 'tmaxOK' : 
			if ($langue == 'fr') return ("Duree maximum du traitement $param secondes. \n");
		case 'sepOK' : 
			return ("Separateur a utiliser $param. \n");
		case 'gluOK' :
			return ("Recoller les colonnes resultantes avec $param.\n");
		case 'resultat' :
			return ("resultat");
		case 'rejet' :
			return ("rejet");
		case 'FicERRDiff' :
			return ("Les  fichiers resultat et de rejet doivent etre differents. \n");
		case '1Fic' :
			if ($langue='fr') return ("Ne donner qu'un seul fichier $param .\n");
		case 'ExistFIC' :
			$cas = $param[0];$fic=$param[1];
			return ("Le fichier $cas $fic existe deja.\n");
		case 'FicRES' :
			$cas = $param[0];$un_arg=$param[1];
			return ("Creer le fichier $cas : $un_arg. \n");
		case 'FicINEX' :
			return ("Fichier inexistant $param.\n");
		case 'source' :
			return ("source");
		case 'FicSOUR' :
			return ("Fichier source a traiter $param. \n");
		case 'par' :
			return("parentheseur");
		case 'xtrt':
			return ("script externe");
		case '1Usage' :
			$un_arg = $param[0]; $cas = $param[1]; 
			return ("Le fichier $un_arg, ou un fichier $cas, est deja designe par ailleurs.\n");
		case 'FicLU' :
			$un_arg = $param[0]; $cas = $param[1]; 
			return ("Fichier $cas $un_arg declare.\n");
		case 'fh' :
			return ("ezproxy");
		case 'f' :
			return ("chaines");
		case 'cas+' :
			return ("a chercher");
		case "cas-" :
			return ("a exclure");
		case '2format' :
			return ("Double definition de format.\n");
		case 'PrecCOL' :
			return ("Preciser une colonne pour $param et non *\n");
		case 'colurl' :
			return ("Colonne URL $param a fragmenter pour le resultat.\n");
		case 'colca' :
			return ("Colonne $param codee alpha.\n");
		case 'colcip' :
			return ("Colonne $param codage IP.\n");
		case 'colda' :
			return ("Colonne $param a decoder.\n");
		case 'coldip' :
			return ("Colonne $param - IP a decoder.\n");
		case 'colmixte' :
			return ("Colonne exclue (-col) et retenue (+col) $param\n");
		case 'col+' :
			return ("Colonne retenue $param.\n");
		case 'col-' :
			return ("Colonne exclue $param\n");
		case 'NumTCOL': //
			return ("Test arithmetique $param impossible sur toute la ligne.\n");
		case 'colTERR' :
			$col = $param[0];$crit=$param[1];$mess = $param[2];
			return ("Critere $crit en colonne $col erronne : $mess.\n");
		case 'colTVAL' :
			$col = $param[0];$crit=$param[1];$val = $param[2];
			return ("Critere $crit sur $val en colonne $col.\n");
		case 'quoi' :
			return ("Valeur $param trouvee alors qu'un argument etait attendu.\n");
		case 'NVAL' :
			return (" $param valeurs");
		case 'FormatINV' :
			return ("Format de colonne invalide : $param.\n");
		case 'Mod_nomINV' :
			return ("Colonnes designees par leur nom mais pas de ligne d'entete declaree.\n");
		case 'FicResNOP' ://
			return ("ERR ouverture impossible du resultat $param.\n");
		case 'FicRejNOP' ://
			return ("ERR ouverture impossible du fichier de rejet $param.\n");
		case 'FicSrcNOP' ://
			return ("ERR ouverture impossible du fichier source $param.\n");
		case 'FicSrcNOP' ://
			return ("\n===\n  Traitement de la source $param\n===\n\n");
		case 'MaxFait' :
			return ("Nombre maximum de lignes $param atteint.\n");
		case 'TMaxFait' :
			return ("Temps maximum de $param secondes atteint. \n");
		case 'AbsVal' :
			return ("Absence de $param sur la ligne.\n");
		case 'PresVal' :
			return ("Presence de $param sur la ligne.\n");
		case 'ligIncomp' :
			return ("Ligne incomplete $param.\n");
		case 'BilanSrc' :
			$source=$param[0];$cptlignefic=$param[1];
			return ("\n...\n  $source de $cptlignefic lignes traite===========\n\n");
		case 'final' :
			$cptlues=$param[0];$cptecrites=$param[1];$tmis=$param[2];
			return ("Lignes lue: $cptlues ; lignes ecrite: $cptecrites en $tmis\n");
		default: 
			if (is_array($param)) $param=print_r($param,true);
			return ("Cas d'erreur inconnu $no pour $param.\n");
	}
}
$ce_repertoire = dirname(__FILE__);

include_once ("$ce_repertoire/gcsv_extrait.corps.php");