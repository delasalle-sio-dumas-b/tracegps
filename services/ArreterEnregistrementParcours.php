<?php
// Projet TraceGPS - services web
// fichier : services/ArreterEnregistrementParcours.php
// Dernière mise à jour : 27/11/2018 par VALLÉE Dylan

// Rôle : ce service web permet à un utilisateur de terminer l'enregistrement d'un parcours.
// Le service web doit recevoir 3 paramètres :
//     pseudo : le pseudo de l'utilisateur
//     mdpSha1 : le mot de passe hashé en sha1
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
//     idTrace : l'id de la trace à terminer
// Le service retourne un flux de données XML ou JSON contenant un compte-rendu d'exécution

// connexion du serveur web à la base MySQL
include_once('../modele/DAO.class.php');
$dao = new DAO();

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if (empty ($_REQUEST ["pseudo"]) == true) $pseudo = ""; else   $pseudo = $_REQUEST ["pseudo"];
if (empty ($_REQUEST ["mdpSha1"]) == true) $mdpSha1 = ""; else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if (empty ($_REQUEST ["lang"]) == true) $lang = ""; else $lang = strtolower($_REQUEST ["lang"]);
if (empty ($_REQUEST ["idTrace"]) == true) $idTrace = ""; else $idTrace = $_REQUEST ["idTrace"];

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// Contrôle de la présence des paramètres
if ($pseudo == "" || $mdpSha1 == "") {
    $msg = "Erreur : données incomplètes.";
} else {
    $niveauConnexion = $dao->getNiveauConnexion($pseudo, $mdpSha1);

    if ($niveauConnexion == 0) {
        $msg = "Erreur : authentification incorrecte.";
    } else {
        $uneTrace = $dao->getUneTrace($idTrace);
        if ($uneTrace == null) {
            $msg = "Erreur : parcours inexistant.";
        } else {
            $idUtilisateur = $dao->getUnUtilisateur($pseudo)->getId();
            if ($idUtilisateur != $uneTrace->getIdUtilisateur()) {
                $msg = "Erreur : le numéro de trace ne correspond pas à cet utilisateur.";
            } else {
                if ($uneTrace->getTerminee() == true) {
                    $msg = "Erreur : cette trace est déjà terminée.";
                } else {
                    $ok = $dao->terminerUneTrace($idTrace);
                    if (!$ok) {
                        $msg = "Erreur : problème lors de la fin de l'enregistrement de la trace.";
                    } else {
                        $msg = "Enregistrement terminé.";
                    }
                }
            }
        }
    }
}

// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    creerFluxXML($msg);
} else {
    creerFluxJSON($msg);
}

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;

// création du flux XML en sortie
function creerFluxXML($msg)
{
    /* Exemple de code XML
         <?xml version="1.0" encoding="UTF-8"?>
         <!--Service web Connecter - BTS SIO - Lycée De La Salle - Rennes-->
         <data>
            <reponse>Erreur : données incomplètes.</reponse>
         </data>
     */

    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();

    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';

    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web Connecter - BTS SIO - Lycée De La Salle - Rennes');
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
             "data":{
                "reponse": "authentification incorrecte."
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
