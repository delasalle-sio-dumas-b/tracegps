<?php
namespace services;

use PDO;

class RetirerUneAutorisation
{
    public function RetirerUneAutorisation($idAutorisant, $idAutorise)
    {
        // préparation de la requete de suppression
        $txt_req = "DELETE from tracegps_autorisations where idAutorisant = :idAutorisant and idAutorise = :idAutorise ";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("idAutorisant", $idAutorisant, PDO::PARAM_STR);
        $req->bindValue("idAutorise", $idAutorise, PDO::PARAM_STR);
        // exécution de la requete
        $ok = $req->execute();
        
        // fourniture de la réponse
        return $ok;
    }
}

