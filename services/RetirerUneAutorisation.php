<?php
// Rôle : ce service web permet à un utilisateur de supprimer une autorisation qu'il avait accordée à un autre utilisateur.
// Le service web doit recevoir 4 paramètres :
//     pseudo : le pseudo de l'administrateur
//     mdpSha1 : le mot de passe hashé en sha1 de l'administrateur
//     pseudoARetirer : le pseudo de l'utilisateur à qui on veut retirer l'autorisation
//     texteMessage : le texte d'un message accompagnant la suppression
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
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
if (empty ($_REQUEST ["pseudoARetirer"]) == true) $pseudoDestinataire = ""; else $pseudoARetirer = $_REQUEST ["pseudoARetirer"];
if (empty ($_REQUEST ["texteMessage"]) == true) $texteMessage = ""; else $texteMessage = $_REQUEST ["texteMessage"];
if (empty ($_REQUEST ["lang"]) == true) $lang = ""; else $lang = strtolower($_REQUEST ["lang"]);
// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// Contrôle de la présence des paramètres
if ($pseudo == "" || $mdpSha1 == "" || $pseudoARetirer == "") {
    $msg = "Erreur : données incomplètes.";
} else {
    if ($dao->getNiveauConnexion($pseudo, $mdpSha1) == 0) {
        $msg = "Erreur : authentification incorrecte.";
    } else {
        $utilisateur = $dao->getUnUtilisateur($pseudoARetirer);

        if ($utilisateur == null) {
            $msg = "Erreur : pseudo utilisateur inexistant.";
        } else {
            $autorisant = $dao->getUnUtilisateur($pseudo)->getId();

            $autorise = $dao->getUnUtilisateur($pseudoARetirer)->getId();

            if (!$dao->autoriseAConsulter($autorisant, $autorise)) {
                $msg = "Erreur : l'autorisation n'était pas accordée.";
            } else {
                $ok = $dao->supprimerUneAutorisation($autorisant, $autorise);
                if (!$ok) {
                    $msg = "Erreur : problème lors de la suppression de l'utilisateur.";
                } else {
                    $utilisateur = $dao->getUnUtilisateur($pseudo);
                    $numTelUtilisateur = $utilisateur->getNumTel();
<<<<<<< HEAD
                   $adrMailDemandeur = $utilisateur->getAdrMail();
                    $msg = 'Autorisation supprimée.';
                    if ($dao->existePseudoUtilisateur($pseudoARetirer) == false) {
                        $msg = 'Erreur : pseudo du destinataire inexistant.';
=======
                }
                $adrMailDemandeur = $utilisateur->getAdrMail();
                $msg = 'Autorisation supprimée.';
                if ($dao->existePseudoUtilisateur($pseudoARetirer) == false) {
                    $msg = 'Erreur : pseudo du destinataire inexistant.';
                } else {
                    $destinataire = $dao->getUnUtilisateur($pseudoARetirer);
                    $idDestinataire = $destinataire->getId();
                    $adrMailDestinataire = $destinataire->getAdrMail();
                    $lien1 = "http://localhost/ws-php-leilla/tracegps/services/ValiderDemandeAutorisation.php?a=" . $mdpSha1 . "&b=" . $pseudo . "&c=" . $pseudoARetirer . "&d=1";
                    $lien2 = "http://localhost/ws-php-leilla/tracegps/services/ValiderDemandeAutorisation.php?a=" . $mdpSha1 . "&b=" . $pseudo . "&c=" . $pseudoARetirer . "&d=0";
                    $msg = "Autorisation supprimée ; " . $pseudoARetirer . " va recevoir un courriel de notification.";

                    $sujetMail = "Suppression d'autorisation de la part d'un utilisateur du système TraceGPS";
                    $contenuMail = "Cher ou chère " . $pseudoARetirer . "\n\n";
                    $contenuMail .= "L'utilisateur " . $pseudo . " du système TraceGPS vous retire l'autorisation de suivre ses parcours.\n\n";

                    if (!$texteMessage === "") {
                        $contenuMail .= "Son message : " . $texteMessage . "\n\n";
>>>>>>> branch 'master' of https://github.com/delasalle-sio-dumas-b/tracegps.git
                    } else {
                        $contenuMail .= "Il n'a pas laissé de message précisant les raisons de cette action.\n\n";
                    }

                    $contenuMail .= "Cordialement " . "\n\n";
                    $contenuMail .= "L'administrateur du système TraceGPS";
                    $ok = Outils::envoyerMail($adrMailDemandeur, $sujetMail, $contenuMail, $adrMailDestinataire);
                }
            }
        }
    }
}

// ferme la connexion à MySQL
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    creerFluxXML($msg);
} else {
    creerFluxJSON($msg);
}

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;

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

?>