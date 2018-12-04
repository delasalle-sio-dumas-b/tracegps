<?php
include_once ('../modele/DAO.class.php');
$dao = new DAO();

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true)  $pseudo = "";  else   $pseudo = $_REQUEST ["pseudo"];
if ( empty ($_REQUEST ["mdpSha1"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if ( empty ($_REQUEST ["idTrace"]) == true) $idTrace = ""; else $idTrace = $_REQUEST ["idTrace"];
if ( empty ($_REQUEST ["lang"]) == true) $lang = "";  else $lang = strtolower($_REQUEST ["lang"]);
// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

$uneTrace = null;
$lesPoints = null;

// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" || $idTrace == "" )
{	$msg = "Erreur : données incomplètes.";
}
else
{	if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
        $msg = "Erreur : authentification incorrecte.";
    else
    {	
        $uneTrace = $dao->getUneTrace($idTrace); 
        
        if ($uneTrace == null) {
            $msg = "Erreur : parcours inexistant.";
        } else {
            $idDemandeur = $dao->getUnUtilisateur($pseudo)->getId();
            $idProprietaire = $uneTrace->getIdUtilisateur();
            
            if ($idDemandeur != $idProprietaire && $dao->autoriseAConsulter($idProprietaire, $idDemandeur) == false) {
                $msg = "Erreur : vous n'êtes pas autorisé par le propriétaire du parcours.";
            } else {
                $msg = "Données de la trace demandée.";
                $lesPoints = $dao->getLesPointsDeTrace($idTrace);
                $uneTrace->setLesPointsDeTrace($lesPoints);
            }
        }
    }
}

// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    creerFluxXML($msg, $uneTrace);
} else {
    creerFluxJSON($msg, $uneTrace);
}

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;
    
// création du flux XML en sortie
function creerFluxXML($msg, $uneTrace)
{
    /* Exemple de code XML
        <?xml version="1.0" encoding="UTF-8"?>
        <!--Service web GetUnParcoursEtSesPoints - BTS SIO - Lycée De La Salle - Rennes-->
        <data>
          <reponse>Données de la trace demandée.</reponse>
          <donnees>
            <trace>
              <id>2</id>
              <dateHeureDebut>2018-01-19 13:08:48</dateHeureDebut>
              <terminee>1</terminee>
              <dateHeureFin>2018-01-19 13:11:48</dateHeureFin>
              <idUtilisateur>2</idUtilisateur>
            </trace>
            <lesPoints>
              <point>
                <id>1</id>
                <latitude>48.2109</latitude>
                <longitude>-1.5535</longitude>
                <altitude>60</altitude>
                <dateHeure>2018-01-19 13:08:48</dateHeure>
                <rythmeCardio>81</rythmeCardio>
              </point>
               .....................................................................................................
              <point>
                <id>10</id>
                <latitude>48.2199</latitude>
                <longitude>-1.5445</longitude>
                <altitude>150</altitude>
                <dateHeure>2018-01-19 13:11:48</dateHeure>
                <rythmeCardio>90</rythmeCardio>
              </point>
            </lesPoints>
          </donnees>
        </data>

     */
    
    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();
    
    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';
    
    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web GetUnParcoursEtSesPoints - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);
    
    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    
    // place l'élément 'reponse' dans l'élément 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);

    
    // traitement des utilisateurs
    if ($uneTrace != null) {
        // place l'élément 'donnees' dans l'élément 'data'
        $elt_donnees = $doc->createElement('donnees');
        $elt_data->appendChild($elt_donnees);
        
        // place l'élément 'lesUtilisateurs' dans l'élément 'donnees'
        $elt_trace = $doc->createElement('trace');
        
        $elt_idTrace = $doc->createElement('id', $uneTrace->getId());
        $elt_trace->appendChild($elt_idTrace);
        
        $elt_dateHeureDebut = $doc->createElement('dateHeureDebut', $uneTrace->getDateHeureDebut());
        $elt_trace->appendChild($elt_dateHeureDebut);
        
        $elt_terminee = $doc->createElement('terminee', $uneTrace->getTerminee());
        $elt_trace->appendChild($elt_terminee);
        
        $elt_dateHeureFin = $doc->createElement('dateHeureFin', $uneTrace->getDateHeureFin());
        $elt_trace->appendChild($elt_dateHeureFin);
        
        $elt_idUtilisateur = $doc->createElement('idUtilisateur', $uneTrace->getIdUtilisateur());
        $elt_trace->appendChild($elt_idUtilisateur);
        
        $elt_donnees->appendChild($elt_trace);
        
        foreach ($uneTrace->getLesPointsDeTrace() as $unPoint)
        {
            // crée un élément vide 'utilisateur'
            $elt_lesPoints = $doc->createElement('lesPoints');
            
            $elt_point = $doc->createElement('point');
            
            $elt_idPoint = $doc->createElement('id', $unPoint->getId());
            
            // place l'élément 'point' dans l'élément 'lesPoints'
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
function creerFluxJSON($msg, $uneTrace)
{
    /* Exemple de code JSON
{
    "data": {
        "reponse": "Données de la trace demandée.",
        "donnees": {
            "trace": {
                "id": "2",
                "dateHeureDebut": "2018-01-19 13:08:48",
                "terminee: "1",
                "dateHeureFin: "2018-01-19 13:11:48",
                "idUtilisateur: "2"
            },
            "lesPoints": [
                {
                    "id": "1",
                    "latitude": "48.2109",
                    "longitude": "-1.5535",
                    "altitude": "60",
                    "dateHeure": "2018-01-19 13:08:48",
                    "rythmeCardio": "81"
                },
                ..................................
                {
                    "id": "10",
                    "latitude": "48.2199",
                    "longitude": "-1.5445",
                    "altitude": "150",
                    "dateHeure": "2018-01-19 13:11:48",
                    "rythmeCardio": "90"
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
