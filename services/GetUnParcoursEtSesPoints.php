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


