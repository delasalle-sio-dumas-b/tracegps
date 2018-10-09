<?php
// Projet TraceGPS
// fichier : modele/Utilisateur.class.php
// Rôle : la classe Utilisateur représente les utilisateurs de l'application
// Dernière mise à jour : 18/7/2018 par Dylan VALLÉE 卍

include_once ('Outils.class.php');

class Utilisateur
{
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Attributs privés de la classe -------------------------------------
    // ------------------------------------------------------------------------------------------------------

    private $id;	// identifiant de l'utilisateur (numéro automatique dans la BDD)
    private $pseudo;	// pseudo de l'utilisateur
    private $mdpSha1;	// mot de passe de l'utilisateur (hashé en SHA1)
    private $adrMail;	// adresse mail de l'utilisateur
    private $numTel;	// numéro de téléphone de l'utilisateur
    private $niveau;	// niveau d'accès : 1 = utilisateur (pratiquant ou proche)    2 = administrateur
    private $dateCreation;	// date de création du compte
    private $nbTraces;	// nombre de traces stockées actuellement
    private $dateDerniereTrace;	// date de début de la dernière trace

    // ------------------------------------------------------------------------------------------------------
    // ----------------------------------------- Constructeur -----------------------------------------------
    // ------------------------------------------------------------------------------------------------------

    public function Utilisateur($id, $pseudo, $mdpSha1, $adrMail, $numTel, $niveau, $dateCreation, $nbTraces, $dateDerniereTrace)
    {
        $this->id = $id;
        $this->pseudo = $pseudo;
        $this->mdpSha1 = $mdpSha1;
        $this->adrMail = $adrMail;
        $this->numTel = Outils::corrigerTelephone($numTel);
        $this->niveau = $niveau;
        $this->dateCreation = $dateCreation;
        $this->nbTraces = $nbTraces;
        $this->dateDerniereTrace = $dateDerniereTrace;
    }

    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------------- Getters et Setters ------------------------------------------
    // ------------------------------------------------------------------------------------------------------

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * @param mixed $pseudo
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;
    }

    /**
     * @return mixed
     */
    public function getMdpSha1()
    {
        return $this->mdpSha1;
    }

    /**
     * @param mixed $mdpSha1
     */
    public function setMdpSha1($mdpSha1)
    {
        $this->mdpSha1 = $mdpSha1;
    }

    /**
     * @return mixed
     */
    public function getAdrMail()
    {
        return $this->adrMail;
    }

    /**
     * @param mixed $adrMail
     */
    public function setAdrMail($adrMail)
    {
        $this->adrMail = $adrMail;
    }

    /**
     * @return mixed
     */
    public function getNumTel()
    {
        return $this->numTel;
    }

    /**
     * @param mixed $numTel
     */
    public function setNumTel($numTel)
    {
        $this->numTel = Outils::corrigerTelephone($numTel);
    }

    /**
     * @return mixed
     */
    public function getNiveau()
    {
        return $this->niveau;
    }

    /**
     * @param mixed $niveau
     */
    public function setNiveau($niveau)
    {
        $this->niveau = $niveau;
    }

    /**
     * @return mixed
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @param mixed $dateCreation
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;
    }

    /**
     * @return mixed
     */
    public function getNbTraces()
    {
        return $this->nbTraces;
    }

    /**
     * @param mixed $nbTraces
     */
    public function setNbTraces($nbTraces)
    {
        $this->nbTraces = $nbTraces;
    }

    /**
     * @return mixed
     */
    public function getDateDerniereTrace()
    {
        return $this->dateDerniereTrace;
    }

    /**
     * @param mixed $dateDerniereTrace
     */
    public function setDateDerniereTrace($dateDerniereTrace)
    {
        $this->dateDerniereTrace = $dateDerniereTrace;
    }

    // ------------------------------------------------------------------------------------------------------
    // -------------------------------------- Méthodes d'instances ------------------------------------------
    // ------------------------------------------------------------------------------------------------------

    public function toString() {
        $msg = 'id : ' . $this->id . '<br>';
        $msg .= 'pseudo : ' . $this->pseudo . '<br>';
        $msg .= 'mdpSha1 : ' . $this->mdpSha1 . '<br>';
        $msg .= 'adrMail : ' . $this->adrMail . '<br>';
        $msg .= 'numTel : ' . $this->numTel . '<br>';
        $msg .= 'niveau : ' . $this->niveau . '<br>';
        $msg .= 'dateCreation : ' . $this->dateCreation . '<br>';
        $msg .= 'nbTraces : ' . $this->nbTraces . '<br>';
        $msg .= 'dateDerniereTrace : ' . $this->dateDerniereTrace . '<br>';
        return $msg;
    }


} // fin de la classe Utilisateur
