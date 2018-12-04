<?php
require_once('../modele/DAO.class.php');
$dao = new DAO();
    // préparation de la requête de recherche
    $txt_req = "Select idTrace, id, latitude, longitude, altitude, dateHeure, rythmeCardio";
    $txt_req .= " from tracegps_points";
    $txt_req .= "  where idTrace = :idTrace";
    $txt_req = $txt_req . " order by id";
    
    $req = $this->cnx->prepare($txt_req);
    $req->bindValue("idTrace", $idTrace, PDO::PARAM_INT);
    // extraction des données
    $req->execute();
    $uneLigne = $req->fetch(PDO::FETCH_OBJ);
    
    // construction d'une collection d'objets pointDeTrace
    $lesPointDeTraces = array();
    // tant qu'une ligne est trouvée :
    while ($uneLigne) {
        // création d'un objet Utilisateur
        $unIdTrace = utf8_encode($uneLigne->idTrace);
        $unId = utf8_encode($uneLigne->id);
        $uneLatitude = utf8_encode($uneLigne->latitude);
        $uneLongitude = utf8_encode($uneLigne->longitude);
        $uneAltitude = utf8_encode($uneLigne->altitude);
        $uneDateHeure = utf8_encode($uneLigne->dateHeure);
        $unRythmeCardio = utf8_encode($uneLigne->rythmeCardio);
        
        $unPointDeTrace = new PointDeTrace($unIdTrace, $unId, $uneLatitude, $uneLongitude, $uneAltitude, $uneDateHeure, $unRythmeCardio, 0, 0, 0);
        // ajout d'un pointDeTrace à la collection
        $lesPointDeTraces[] = $unPointDeTrace;
        // extrait la ligne suivante
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
    }
    // libère les ressources du jeu de données
    $req->closeCursor();
    // fourniture de la collection
    return $lesPointDeTraces;


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