<?php
// Projet TraceGPS - services web
// fichier : services/GetTousLesUtilisateurs.php
// Dernière mise à jour : 23/11/2018 par Leilla

// Rôle : ce service permet à un utilisateur authentifié d'obtenir la liste de tous les utilisateurs qu'il autorise à consulter ses parcours.
// Le service web doit recevoir 3 paramètres :
//     pseudo : le pseudo de l'utilisateur
//     mdpSha1 : le mot de passe de l'utilisateur hashé en sha1
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
// Le service retourne un flux de données XML ou JSON contenant un compte-rendu d'exécution

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/GetTousLesUtilisateurs.php?pseudo=callisto&mdpSha1=13e3668bbee30b004380052b086457b014504b3e&lang=xml

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/GetTousLesUtilisateurs.php

// connexion du serveur web à la base MySQL
include_once ('../modele/DAO.class.php');
$dao = new DAO();
$lesUtilisateursAutorises = null;

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true)  $pseudo = "";  else   $pseudo = $_REQUEST ["pseudo"];
if ( empty ($_REQUEST ["mdpSha1"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if ( empty ($_REQUEST ["lang"]) == true) $lang = "";  else $lang = strtolower($_REQUEST ["lang"]);
// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// initialisation du nombre de réponses
$nbReponses = 0;
$lesUtilisateurs = array();

// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" )
{	$msg = "Erreur : données incomplètes.";
}
else
{	if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
    $msg = "Erreur : authentification incorrecte.";
    else
    {	// récupération de l'id de l'utilisateur
        $idUtilisateur = $dao->getUnUtilisateur($pseudo)->getId();
        
        // récupération de la liste des utilisateurs autorises à l'aide de la méthode getLesUtilisateursAutorises de la classe DAO
        $lesUtilisateursAutorises = $dao->getLesUtilisateursAutorises($idUtilisateur);
        
        // mémorisation du nombre d'utilisateurs autorisés
        $nbReponses = sizeof($lesUtilisateursAutorises);
        
        if ($nbReponses == 0)
            $msg = "Aucune autorisation accordée par " . $pseudo . ".";
            else
                $msg = $nbReponses . " autorisation(s) accordée(s) par " . $pseudo . ".";
    }
}
// ferme la connexion à MySQL
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    creerFluxXML($msg, $lesUtilisateursAutorises);
}
else {
    creerFluxJSON($msg, $lesUtilisateursAutorises);
}

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;

// création du flux XML en sortie
function creerFluxXML($msg, $lesUtilisateurs)
{
    /* Exemple de code XML
         <?xml version="1.0" encoding="UTF-8"?>
    <!--Service web GetLesUtilisateursQueJautorise - BTS SIO - Lycée De La Salle - Rennes-->
    <data>
      <reponse>2 autorisation(s) accordée(s) par neon.</reponse>
      <donnees>
        <lesUtilisateurs>
            <utilisateur>
              <id>12</id>
              <pseudo>oxygen</pseudo>
              <adrMail>delasalle.sio.eleves@gmail.com</adrMail>
              <numTel>44.55.66.77.88</numTel>
              <niveau>1</niveau>
              <dateCreation>2018-11-03 21:46:44</dateCreation>
              <nbTraces>2</nbTraces>
              <dateDerniereTrace>2018-01-19 13:08:48</dateDerniereTrace>
            </utilisateur>
            <utilisateur>
              <id>13</id>
              <pseudo>photon</pseudo>
              <adrMail>delasalle.sio.eleves@gmail.com</adrMail>
              <numTel>44.55.66.77.88</numTel>
              <niveau>1</niveau>
              <dateCreation>2018-11-03 21:46:44</dateCreation>
              <nbTraces>0</nbTraces>
            </utilisateur>
        </lesUtilisateurs>
      </donnees>
    </data>
     */
    
    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();
    
    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';
    
    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web GetLesUtilisateursQueJautorise - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);
    
    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    
    // place l'élément 'reponse' dans l'élément 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);
    
    // traitement des utilisateurs
    if (sizeof($lesUtilisateurs) > 0 && ! $lesUtilisateurs == null) {
        // place l'élément 'donnees' dans l'élément 'data'
        $elt_donnees = $doc->createElement('donnees');
        $elt_data->appendChild($elt_donnees);
        
        // place l'élément 'lesUtilisateurs' dans l'élément 'donnees'
        $elt_lesUtilisateurs = $doc->createElement('lesUtilisateurs');
        $elt_donnees->appendChild($elt_lesUtilisateurs);
        
        foreach ($lesUtilisateurs as $unUtilisateur)
        {
            // crée un élément vide 'utilisateur'
            $elt_utilisateur = $doc->createElement('utilisateur');
            // place l'élément 'utilisateur' dans l'élément 'lesUtilisateurs'
            $elt_lesUtilisateurs->appendChild($elt_utilisateur);
            
            // crée les éléments enfants de l'élément 'utilisateur'
            $elt_id         = $doc->createElement('id', $unUtilisateur->getId());
            $elt_utilisateur->appendChild($elt_id);
            
            $elt_pseudo     = $doc->createElement('pseudo', $unUtilisateur->getPseudo());
            $elt_utilisateur->appendChild($elt_pseudo);
            
            $elt_adrMail    = $doc->createElement('adrMail', $unUtilisateur->getAdrMail());
            $elt_utilisateur->appendChild($elt_adrMail);
            
            $elt_numTel     = $doc->createElement('numTel', $unUtilisateur->getNumTel());
            $elt_utilisateur->appendChild($elt_numTel);
            
            $elt_niveau     = $doc->createElement('niveau', $unUtilisateur->getNiveau());
            $elt_utilisateur->appendChild($elt_niveau);
            
            $elt_dateCreation = $doc->createElement('dateCreation', $unUtilisateur->getDateCreation());
            $elt_utilisateur->appendChild($elt_dateCreation);
            
            $elt_nbTraces   = $doc->createElement('nbTraces', $unUtilisateur->getNbTraces());
            $elt_utilisateur->appendChild($elt_nbTraces);
            
            if ($unUtilisateur->getNbTraces() > 0)
            {   $elt_dateDerniereTrace = $doc->createElement('dateDerniereTrace', $unUtilisateur->getDateDerniereTrace());
            $elt_utilisateur->appendChild($elt_dateDerniereTrace);
            }
        }
    }
    
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    echo $doc->saveXML();
    return;
}

// création du flux JSON en sortie
function creerFluxJSON($msg, $lesUtilisateurs)
{
    /* Exemple de code JSON
     {
         "data": {
             "reponse": "2 autorisation(s) accord\u00e9e(s) par neon.",
             "donnees": {
                 "lesUtilisateurs": [
                    {
                        "id": "12",
                        "pseudo": "oxygen",
                        "adrMail": "delasalle.sio.eleves@gmail.com",
                        "numTel": "44.55.66.77.88",
                        "niveau": "1",
                        "dateCreation": "2018-11-03 21:46:44",
                        "nbTraces": "2",
                        "dateDerniereTrace": "2018-01-19 13:08:48"
                    },
                    {
                        "id": "13",
                        "pseudo": "photon",
                        "adrMail": "delasalle.sio.eleves@gmail.com",
                        "numTel": "44.55.66.77.88",
                        "niveau": "1",
                        "dateCreation": "2018-11-03 21:46:44",
                        "nbTraces": "0"
                    }
                 ]
             }
         }
     }
     */
    
    
    if (sizeof($lesUtilisateurs) == 0) {
        // construction de l'élément "data"
        $elt_data = ["reponse" => $msg];
    }
    else {
        // construction d'un tableau contenant les utilisateurs
        $lesObjetsDuTableau = array();
        foreach ($lesUtilisateurs as $unUtilisateur)
        {	// crée une ligne dans le tableau
            $unObjetUtilisateur = array();
            $unObjetUtilisateur["id"] = $unUtilisateur->getId();
            $unObjetUtilisateur["pseudo"] = $unUtilisateur->getPseudo();
            $unObjetUtilisateur["adrMail"] = $unUtilisateur->getAdrMail();
            $unObjetUtilisateur["numTel"] = $unUtilisateur->getNumTel();
            $unObjetUtilisateur["niveau"] = $unUtilisateur->getNiveau();
            $unObjetUtilisateur["dateCreation"] = $unUtilisateur->getDateCreation();
            $unObjetUtilisateur["nbTraces"] = $unUtilisateur->getNbTraces();
            if ($unUtilisateur->getNbTraces() > 0)
            {   $unObjetUtilisateur["dateDerniereTrace"] = $unUtilisateur->getDateDerniereTrace();
            }
            $lesObjetsDuTableau[] = $unObjetUtilisateur;
        }
        // construction de l'élément "lesUtilisateurs"
        $elt_utilisateur = ["lesUtilisateurs" => $lesObjetsDuTableau];
        
        // construction de l'élément "data"
        $elt_data = ["reponse" => $msg, "donnees" => $elt_utilisateur];
    }
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    echo json_encode($elt_racine, JSON_PRETTY_PRINT);
    return;
}
?>
