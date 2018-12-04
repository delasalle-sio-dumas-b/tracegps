<<<<<<< HEAD
<?php
// Projet TraceGPS - services web
// fichier : services/GetTousLesUtilisateurs.php
// Dernière mise à jour : 23/11/2018 par Leilla

// Rôle : ce service web permet à un utilisateur de demander une autorisation à un autre utilisateur.
// Le service web doit recevoir 3 paramètres :
//     pseudo : le pseudo de l'utilisateur
//     mdpSha1 : le mot de passe de l'utilisateur hashé en sha1
//     pseudoDestinataire : le pseudo de l'utilisateur à qui on demande l'autorisation
//     texteMessage : le texte d'un message accompagnant la demande
//     nomPrenom : le nom et le prénom du demandeur
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
// Le service retourne un flux de données XML ou JSON contenant un compte-rendu d'exécution

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/GetTousLesUtilisateurs.php?pseudo=callisto&mdpSha1=13e3668bbee30b004380052b086457b014504b3e&lang=xml

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/GetTousLesUtilisateurs.php

// connexion du serveur web à la base MySQL
include_once ('../modele/DAO.class.php');
$dao = new DAO();

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true)  $pseudo = "";  else   $pseudo = $_REQUEST ["pseudo"];
if ( empty ($_REQUEST ["mdpSha1"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if ( empty ($_REQUEST ["pseudoDestinataire"]) == true)  $pseudoDestinataire = "";  else   $pseudoDestinataire = $_REQUEST ["pseudoDestinataire"];
if ( empty ($_REQUEST ["texteMessage"]) == true)  $texteMessage = "";  else   $texteMessage = $_REQUEST ["texteMessage"];
if ( empty ($_REQUEST ["nomPrenom"]) == true)  $nomPrenom = "";  else   $nomPrenom = $_REQUEST ["nomPrenom"];
if ( empty ($_REQUEST ["lang"]) == true) $lang = "";  else $lang = strtolower($_REQUEST ["lang"]);
// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// initialisation du nombre de réponses
$nbReponses = 0;
$lesUtilisateurs = array();

// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" || $pseudoDestinataire == "" || $texteMessage == "" || $nomPrenom == "")
{	$msg = "Erreur : données incomplètes.";
}
else
{	if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
    $msg = "Erreur : authentification incorrecte.";
    else
    {	// Vérifie que le pseudo de l'utilisateur destinataire existe
        $destinataire = $dao->getUnUtilisateur($pseudoDestinataire);
        if ($destinataire == null)
        {   $msg = "Erreur : pseudo utilisateur inexistant.";
        
        }
        else {
            // Récupération de l'utilisateur demandeur
            $utilisateur = $dao->getUnUtilisateur($pseudo);
            $contenuMail = " Cher ou chère" . $pseudoDestinataire . "\r\n";
            $contenuMail .= " Un utilisateur du système TraceGPS vous demande l'autorisation de suivre vos parcours" . "\r\n";
            $contenuMail .= " Voici les données le concernant :" . "\r\n";
            $contenuMail .= " Son pseudo : " . $utilisateur->getPseudo() . "\n";
            $contenuMail .= " Son adresse mail : " + $utilisateur->getAdrMail(). "\n";
            $contenuMail .= "Son numéro de téléphone : " . $utilisateurDemandeur->getNumTel() . "\n";
            $contenuMail .= " Son nom et prénom : " . $dao->getUnUtilisateur($nomPrenom). "\n";
            $contenuMail .= " Son message : " . $texteMessage . "\n\n";
            $contenuMail .= " Pour accepter la demande, cliquez sur ce lien : ";
            $contenuMail .= "http://localhost/ws-php-leilla/tracegps/services/ValiderDemandeAutorisation.php?pseudo=".$pseudo;
            "&mdpSha1= .$mdpSha1 &pseudoDestinataire=oxygen
            &texteMessage=coucou&nomPrenom=charles-edouard&lang=xml". "\n\n";
            
            
        }
    }
}
// ferme la connexion à MySQL
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    creerFluxXML($msg, $lesUtilisateurs);
}
else {
    creerFluxJSON($msg, $lesUtilisateurs);
}

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;

// création du flux JSON en sortie
function creerFluxJSON($msg, $lesUtilisateurs)
{
      /* Exemple de code JSON
         {
             "data": {
                 "reponse": "Erreur : authentification incorrecte."
             }
         }
     */        
        // construction de l'élément "data"
        $elt_data = ["reponse" => $msg];
    }
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    echo json_encode($elt_racine, JSON_PRETTY_PRINT);
    return;
?>
=======
<?php
// Projet TraceGPS - services web
// fichier :  services/DemanderUneAutorisation.php
// Dernière mise à jour : 8/11/2018 par VALLÉE Dylan

// Rôle : ce service web permet à un utilisateur destinataire d'accepter ou de rejeter une demande d'autorisation provenant d'un utilisateur demandeur
// il envoie un mail au demandeur avec la décision de l'utilisateur destinataire

// Le service web doit être appelé avec 4 paramètres obligatoires dont les noms sont volontairement non significatifs :
//  pseudo : le pseudo de l'utilisateur qui demande l'autorisation
//  mdpSha1 : le mot de passe hashé en sha1 de l'utilisateur qui demande l'autorisation
//  pseudoDestinataire : le pseudo de l'utilisateur à qui on demande l'autorisation
//  texteMessage : le texte d'un message accompagnant la demande
//  nomPrenom : le nom et le prénom du demandeur
//  lang : le langage utilisé pour le flux de données ("xml" ou "json")

// connexion du serveur web à la base MySQL
include_once('../modele/DAO.class.php');
$dao = new DAO();

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if (empty ($_REQUEST ["pseudo"]) == true) $pseudo = ""; else   $pseudo = $_REQUEST ["pseudo"];
if (empty ($_REQUEST ["mdpSha1"]) == true) $mdpSha1 = ""; else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if (empty ($_REQUEST ["pseudoDestinataire"]) == true) $pseudoDestinataire = ""; else   $pseudoDestinataire = $_REQUEST ["pseudoDestinataire"];
if (empty ($_REQUEST ["texteMessage"]) == true) $texteMessage = ""; else   $texteMessage = $_REQUEST ["texteMessage"];
if (empty ($_REQUEST ["nomPrenom"]) == true) $nomPrenom = ""; else   $nomPrenom = $_REQUEST ["nomPrenom"];
if (empty ($_REQUEST ["lang"]) == true) $lang = ""; else   $lang = $_REQUEST ["lang"];
if ($lang != "json") $lang = "xml";

// Contrôle de la présence et de la correction des paramètres
if ($mdpSha1 == "" || $pseudo == "" || $pseudoDestinataire == "" || $texteMessage == "" || $nomPrenom == "") {
    $msg = "Erreur : données incomplètes";
} else {
    // test de l'authentification de l'utilisateur
    // la méthode getNiveauConnexion de la classe DAO retourne les valeurs 0 (non identifié) ou 1 (utilisateur) ou 2 (administrateur)

    if ($dao->getNiveauConnexion($pseudo, $mdpSha1) == 0) {
        $msg = "Erreur : authentification incorrecte.";
    } else {
        $utilisateur = $dao->getUnUtilisateur($pseudo);
        $numTelUtilisateur = $utilisateur->getNumTel();
        $adrMailDemandeur = $utilisateur->getAdrMail();

        if ($dao->existePseudoUtilisateur($pseudoDestinataire) == false) {
            $msg = 'Erreur : pseudo du destinataire inexistant.';
        } else {
            $destinataire = $dao->getUnUtilisateur($pseudoDestinataire);
            //$idDestinataire = $destinataire->getId();
            $adrMailDestinataire = $destinataire->getAdrMail();
            $lien1 = "http://localhost/ws-php-vallee/tracegps/services/ValiderDemandeAutorisation.php?a=" . $mdpSha1 . "&b=" . $pseudo . "&c=" . $pseudoDestinataire . "&d=1";
            $lien2 = "http://localhost/ws-php-vallee/tracegps/services/ValiderDemandeAutorisation.php?a=" . $mdpSha1 . "&b=" . $pseudo . "&c=" . $pseudoDestinataire . "&d=0";
            $msg = "Un courriel à été envoyé au destinataire.";

            $sujetMail = "Votre demande d'autorisation à un utilisateur du système TraceGPS";
            $contenuMail = "Cher ou chère " . $pseudoDestinataire . "\n\n";
            $contenuMail .= "Un utilisateur du sytème TraceGPS vous demande l'autorisation de suivre vos parcours.\n\n";
            $contenuMail .= "Voici les données le concernant :\n\n";
            $contenuMail .= "Pseudo : " . $pseudo . "\n";
            $contenuMail .= "Adresse mail : " . $adrMailDemandeur . "\n";
            $contenuMail .= "Numéro de téléphone : " . $numTelUtilisateur . "\n";
            $contenuMail .= "Nom et prénom : " . $nomPrenom . "\n";
            $contenuMail .= "Message : " . $texteMessage . "\n\n";
            $contenuMail .= "Pour accepter la demande, cliquez sur ce lien : \n" . $lien1 . "\n\n";
            $contenuMail .= "Pour refuser la demande, cliquez sur ce lien : \n" . $lien2;
            $ok = Outils::envoyerMail($adrMailDemandeur, $sujetMail, $contenuMail, $adrMailDestinataire);
        }
    }
}

unset($dao);   // ferme la connexion à MySQL

// création du flux en sortie
if ($lang == "xml") {
    creerFluxXML($msg);
} else {
    creerFluxJSON($msg);
}

function creerFluxXML($msg)
{
    /* Exemple de code XML
     <?xml version="1.0" encoding="UTF-8"?>
     <!--Service web ChangerDeMdp - BTS SIO - Lycée De La Salle - Rennes-->
     <data>
     <reponse>Erreur : authentification incorrecte.</reponse>
     </data>
     */

    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();

    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';

    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web DemanderUneAutorisation - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);

    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);

    // place l'élément 'reponse' juste après l'élément 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);

    // Mise en forme finale
    $doc->formatOutput = true;

    // renvoie le contenu XML
    echo $doc->saveXML();
    return;
}

// création du flux JSON en sortie
function creerFluxJSON($msg)
{
    /* Exemple de code JSON
     {
     "data": {
     "reponse": "Erreur : authentification incorrecte."
     }
     }
     */

    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg];

    // construction de la racine
    $elt_racine = ["data" => $elt_data];

    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    echo json_encode($elt_racine, JSON_PRETTY_PRINT);
    return;
}
>>>>>>> branch 'master' of https://github.com/delasalle-sio-dumas-b/tracegps.git
