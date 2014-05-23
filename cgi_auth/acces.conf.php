<?

/*
 * EZPROXY : CGI authentication tools.
 * Multi CAS/LDAP authentication Filter. 
 * Configuration file
 */
/*
 * R�pertoire contenant dirclasse bdd_vision.class.php pour l'acc�s aux Bases de Donnees.
 * Directory containing bdd_vision.class.php to database access
 */
$dirClasses=$dirLibs= dirname(__FILE__)."/../libs";
/*
How to get user identity 
Mode de r�cup�ration de l'identit� de l'interlocuteur. 
  serveur : by http service deamon (apache) / par le serveur HTTP (apache)
  form : generally by form / par formulaire g�n�r�
*/
$__VAcces_mode = 'form';

/**
 * Accept anonymous access ? L acc�s anonyme est-il permis ? (true /  false)
*/
$__VAcces_Anonyme = false;


/* 
 * $__VA_identifieurs :
 * Tableau des r�pertoire ou services identifieurs :
A chacun (chaque " entr�e "), correspond :
- un libell� utilis� pour faire un menu des r�pertoires pour donner le choix du r�pertoire � l usager.
- un type : indiquant le protocole � utiliser pour l'interroger et le tableau de param�tres � utiliser.
- nom dans le tableaux de param�tres de ceux du type indiqu�. (serveur, port, ...)
- des droits (automatiques) attribu�s aux utilisateurs de ce r�pertoire.
- une des clauses de recherches d'informations suppl�mentaires :
  * sources : liste des sources des tableaux  $__VAcces_serveurs_{protocole} pouvant �tre utilis�es
    (avec le login fourni) pour compl�ter les informations et LES DROITS de l'usager.
    Chacune est d�crite par " nom d entree " => protocole ;
    indique l entr�e utilis�e dans le tableau de protocole indiqu�.
  * est_source : indique que le r�pertoire est un annuaire et que toutes les informations suppl�mentaire
                 sont obtenues par une requ�te suppl�mentaire vers lui, uniquement.


 * List of identity providers
Every entry is the label of a provider associated with :
- 'libelle' = a long name to be used in a select list
- 'type' = could be 'CAS' , 'LDAP' , 'BdD'  but should be 'CAS'
- 'nom' = label of provider in the typed list below  
- "droits_auto" = access for all users of this provider (let to 'default')
-  "est_source" = true only for LDAP and BdD service . Says that informations can 
         be extracted by this source 
         if false, force the access to the 'sources' below.
- "sources" = list of label => type of other directory to request and get informations about users
          of this provider. ex. an organisation having CAS SSO coupled with LDAP Directory service 
          have to have CAS as identity provider and LDAP as complementary informations source.   
*/
$__VA_identifieurs =
    array ("IDT_S1" =>
              array ("libelle"=> "Long name of the first identity service"
                    ,"type" => 'CAS'
                    ,"nom" => "CAS_IS1"
                    ,"droits_auto" => "default"
                    ,"est_source" => false
                    ,"sources" => array ('LDAP_IS_1'=>'LDAP')
                    )

           ,"IDT_S2" =>
              array ("libelle"=> "Long name of the second identity service"
                    ,"type" => 'LDAP'
                    ,"nom" => "LDAP_IS2"
                    ,"droits_auto" => "default2" 
                    ,"est_source" => true
                    )                    
           ) ;

/*
  If valued with the label of an identity provider, forces the use of this Identity 
  provider and shunt choice of user .
  Variable permettant de forcer l'identifieur pour �viter un WAYF artisannal
  (si elle est non vide) :
*/
$__VA_identifieur = "";  //"CAS_IS1";

/**
 * List of CAS IP (cannot be source in IP list)
 * properties are 
 * 'serv'=> URI of CAS,
 * 'port'=> port to use
 * 'version'=> CAS version 
 * 'URI' => For other use, URI if a page is to be called after authentification. 
 *  	Here it's better to let it empty.
 * 'NouvSession'=> for other could be turned to true to force the open of a new session 
 *       every times auth is requested. Here let it to false.
 *       
 * Ne permet pas d extraire des informations sur l usager => une autre source est n�cessaire pour les
 *  r�pertoires de ce type.
 * 'serv'=> adresse du serveur requis,
 * 'port'=> port � utiliser
 * 'version'=> version utilis�e
 * 'URI' => adresse de la page � rappeler syst�matiquement - laisser vide car risque d interf�rence avec
 *          valis_acces - Laisser vide
 * 'NouvSession'=> indique qu il faut forcer une ouverture de session Laisser � false.
 */
 // Include n�cessaire d�s qu il y a un CAS utilis� pour la m�taconstante version
include_once ('CAS.php');

$__VAcces_serveurs_CAS =
    array ('CAS_IS1'=>
              array ('serv'=>'auth.my-cas.edu'
                    ,'port'=> 443
                    ,'version'=> CAS_VERSION_2_0
                    ,'URI' => ''
                    ,'NouvSession'=> false
                    )

/*
          ,'CAS_IS2'=> ...
*/
           );
/**
 * 
 * List of LDAP service as Identity and/or Informations Providers 
 * Every labeled service as the following properties :
 *   'serv' = space separated list of URL:port of different miroring LDAP service 
 *            responding for the same directory. The port can be precised for 
 *            every one or to all with the property:
 *   'port'= port to be useed for every of these services. 
 *   'dn'=> searching root DN 
 *   'audn' et 'aupw' = DN and password of uid to use to bind and request service.
 *          If not set, anonymous bind is done 
 *   'filtre'= LDAP filter to select nodes from root to search 
 *   'nom'= long name to use in message 
 *       --- LDAP attributes for std user properties
 *   'attnom'= generaly 'sn' last name
 *   'attprenom'='givenname' first name
 *   'attadr'='postalAddress' postal address
 *   'attadrel'=>'mail' email address
 *   ,'atts'= list of 'filter' attributes used to define users role(s). Could be :
 *               'attributename'=> array('value1' => 'rightstring','value2'=>... )
 *               OR
 *               'attributename'=> 'rightstring'
 *   ,'atts_lus'= list of other users attributes to read 
 * 
 * D�finition des serveurs LDAP r�pertoire et �ventuellement annuaire
 *  qui permettent d extraire des informations sur l usager et de d�duire SES DROITS (peuvent servir de
 *  "source")
 * Chaque entr�e est d�crite par les attributs suivants :
 *   'serv'=>'liste des URL:port (s�parateur=l espace) des serveurs � interroger successivement'
 *            dans le cas d une seule URL on peut sp�cifier le port par :
 *   'port'=> n� de port utilis�
 *   'dn'=> DN de la racine � interroger'
 *   'audn' et 'aupw' => DN et mot de passe de l'usager autoris� � interroger. 
 *   		si absent, bind anonyme
 *   'filtre'=>'filtre LDAP � utiliser sur les sous-objet de cette branche'
 *   'nom'=>'affichage en clair en tant que source'
 *   'attnom'=>'sn' ou attribut contenant le patronyme de l usager
 *   'attprenom'=>'givenname' ou attribut contenant le pr�nom de l usager
 *   'attadr'=>'postalAddress'  ou attribut contenant l adresse postale de l usager
 *   'attadrel'=>'mail' ou attribut contenant l adresse �lectronique de l usager
 *   'atts'=> tableau des attributs ouvrant des droit sous la forme :
 *               'attribut'=>[ array('valeur'=>array(droits)) || droit ]
 *                           le second cas se basant sur l existence non nulle de l attribut pour l usager
 *    'atts_lus'=> liste d'autres attributs lus et donn�s en r�sultat 
 */

$__VAcces_serveurs_LDAP =
  array('LDAP_IS_1' =>
           array('serv'=> 'ldaps://ldapw1.univ1.edu/ ldaps://ldapw2.univ1.edu/'
                ,'dn'=>'dc=univ1,dc=uk'
                ,'filtre'=>'(objectclass=eduPerson)'
                ,'nom'=>'member Univ1'
                ,'audn'=>'cn=app-idty,ou=system,dc=univ1,dc=uk'
                ,'aupw'=>'verysecretword'
                ,'attnom'=>'sn'
                ,'attprenom'=>'givenname'
                ,'attadr'=>'postalAddress'
                ,'attadrel'=>'mail'
                ,'atts'=>
                 array ('mail'=>array
                               ('user.first@univ1.uk'=>'sp_rights'
                               ,'user.second@univ1.uk'=>'sp_rights'
                               ,'user.admin@univ1.uk'=>'admin_VA' 
                               )
						)
                ,'atts_lus'=> array('cn','myGroupsAtt'
                					,'businessCategory','Categories'
                					,'Affectation','mainAffectation'
                					)
                )
                
        ,'LDAP_IS2' =>
           array('serv'=>'s1.univ2.uk s2.univ2.uk'
                ,'dn'=>'dc=univ2,dc=uk'
                ,'filtre'=>'(objectclass=eduPerson)'
                ,'nom'=>'member Univ2'
                ,'attnom'=>'sn'
                ,'attprenom'=>'givenname'
                ,'attadr'=>'postalAddress'
                ,'attadrel'=>'mail'
                ,'atts'=>
                 array ('mail'=>array
                               ('fn.ln@univ2.uk'=>'admin_VA'
                               )
						)
                ,'atts_lus'=> array('cn')
                )
        );

/*
 * List of DBs used as Information Providers (and could be eventually IdP)
 * Every labeled service as the following properties :
 *         'sgbd'=>'mysql' DB type de ODBC SQL server .
 *         'fct_mdp' => 'my_pwd_fct' = SQL function to use to search password validity. 
 *         'serv'=> 'mysql.my-univ-edu' = server CName to use 
 *         'base'=>'my_usersDB' = DB name 
 *         'utilbdd'=> 'admin_usersDB' = utilisateur et ...
 *         'mdpbdd'=> 'pass_admin_secret' = ... mot de passe de l utilisateur au vu de la BdD
 *         'table'=> 'users' = table name 
 *         'attident'=>'Login' = login attribute (column) 
 *         'attmdp'=>'Pass' = pass column 
 *         'attnom'=>'Name' = last name 
 *         'attprenom'=>'Firstname' = first name
 *         'attadr'=>array('Adr','Code','City','Country') = attribut list to form postal address
 *         'attadrel'=>'Email' = email 
 *         'atttel'=>'Tel' = telephon number 
 *         'attfax'=>'Fax' fax number
 *         'droit'=>'default' = basic rights granted to known DB user
 *         'reqdroit'=> request to use a.e. :
                "select role
                 from users , accessER
                 where users.login='!login!'" and users.pwd=my_pwd_fct('!mdp!') and 
                   accessER.login=users.login 

 * 
 * D�finition des serveurs base de donn�es r�pertoires et/ou annuaires
 *  qui permettent d extraire des informations sur l usager et de d�duire SES DROITS
 *   (peuvent servir de "source")
 * Chaque entr�e est d�crite par les attributs suivants :
 *         'sgbd'=>'mysql' ou type de serveur ODBC h�bergeant la base
 *         'fct_mdp' => 'ma_fct' nom de fonction � employer dans la requ�te SQL sur le mot de passe pour transcoder
 *                         le mot de passe fourni par un usager.(si gestion interne cod�e des MdP)
 *         'serv'=>adresse du serveur � invoquer
 *         'base'=>nom de la BdD
 *         'utilbdd'=> utilisateur et ...
 *         'mdpbdd'=> ... mot de passe de l utilisateur au vu de la BdD
 *         'table'=>'usagers' nom de la table contenant annuaires et d�f. d acc�s
 *         'attident'=>'LoginEnt' attribut de la table devant contenir le login (inutile pour annuaire source )
 *         'attmdp'=>'PassEnt' idem pour celui contenant la mot de passe
 *         'attnom'=>'NomInterlocEnt' attribut de la table devant contenir le patronyme de l usager
 *         'attprenom'=>'PrenomInterlocEnt' attribut de la table devant contenir lson pr�nom
 *         'attadr'=>array('AdrEnt','CpEnt','VilleEnt') attributs de la table d�finissant son adresse postale
 *         'attadrel'=>'EmailInterlocEnt' attribut de la table contenant son adresse �lectronique
 *         'atttel'=>'TelInterlocEnt' attribut de la table devant contenir son n� de t�l.
 *         'attfax'=>'FaxInterlocEnt' attribut de la table devant contenir son n� de t�l�copieur
 *         'droit'=>'p' droit par d�faut accord� si le login usager est connu OU ...
 *         'reqdroit'=> requ�te � utiliser pour d�finir les droits de l usager. Dans cette requ�te, on emploiera
 *                      le login de l usager en le notant '!login!' Ex :
                "select da.droit
                 from droit_acces da
                 where da.login='!login!'" and da.mdp=ma_fct('!mdp!') 

*/
$__VAcces_serveurs_BDD = array();
        
?>