<?php
// Projet TraceGPS - services web
// fichier :  services/CreerUnUtilisateur.php
// Dernière mise à jour : 27/11/2018 par Jim

//  Rôle : ce service web permet à un utilisateur de demander un nouveau mot de passe s'il l'a oublié.
//  Le service web doit recevoir 4 paramètres :
//  pseudo : le pseudo de l'utilisateur
//  lang : le langage utilisé pour le flux de données ("xml" ou "json")

// Connexion du serveur web à la base MySQL
require_once('../modele/DAO.class.php');
$dao = new DAO();

// Récupération des données transmises
if (empty ($_REQUEST['pseudo']) == true) $pseudo = ""; else $pseudo = $_REQUEST['pseudo'];
if (empty ($_REQUEST['lang']) == true) $lang = ""; else $lang = $_REQUEST['lang'];

// On vérifie si lang comporte le langage JSON, si non, on active le XML par défaut
if ($lang != "json") $lang = "xml";

// Contrôle du pseudo

if ($pseudo == '') {
    $msg = "Erreur : données incomplètes ou incorrectes.";
} else {
    if ($dao->existePseudoUtilisateur($pseudo) == false) {
        $msg = "Erreur : pseudo inexistant.";
    } else if ($dao->existePseudoUtilisateur($pseudo)) {
        // Récupération de l'utilisateur concerné
        $utilisateur = $dao->getUnUtilisateur($pseudo);

        // Génération d'un mot de passe aléatoire de 8 caratères
        $password = Outils::creerMdp();
        $adrMail = $utilisateur->getAdrMail();

        // Modifie le mot de passe de l'utilisateur dans la base de données
        $succes = $dao->modifierMdpUtilisateur($pseudo, $password);

        if (!$succes) {
            $msg = "Erreur : problème lors de l'enregistrement.";
        } else {
            // Envoi du mail avec le mot de passe généré précedemment
            $sujet = "Mot de passe oublié - Système Trace GPS";
            $contenuMail = "Cher / Chere ". $pseudo. "\r\n";
            $contenuMail .= "Suite à votre demande de changement de mot passe dû a un oubli de celui-ci, nous vous ";
            $contenuMail .= "envoyons votre nouveau mot de passe ci-dessous :" . "\r\n";
            $contenuMail .= "Votre nouveau mot de passe : " . $password . " (nous vous conseillons de le changer par la suite)\n";

            $ok = Outils::envoyerMail($adrMail, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);
            if (!$ok) {
                // l'envoi de mail a échoué
                $msg = "L'envoi du courriel concernant la réinitialisation du mot de passe a rencontré un problème.";
            } else {
                // tout a bien fonctionné
                $msg = "Vous allez recevoir un courriel avec votre nouveau mot de passe.";
            }
        }
    }
}

// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    creerFluxXML($msg);
}
else {
    creerFluxJSON($msg);
}

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;

// création du flux XML en sortie
function creerFluxXML($msg)
{
    /* Exemple de code XML
        <?xml version="1.0" encoding="UTF-8"?>
        <!--Service web CreerUnUtilisateur - BTS SIO - Lycée De La Salle - Rennes-->
        <data>
          <reponse>Erreur : pseudo trop court (8 car minimum) ou déjà existant .</reponse>
        </data>
     */

    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();

    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';

    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web DemanderMdp - BTS SIO - Lycée De La Salle - Rennes');
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
                "reponse": "Erreur : pseudo trop court (8 car minimum) ou déjà existant."
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
