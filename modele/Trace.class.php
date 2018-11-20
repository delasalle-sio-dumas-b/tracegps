<?php
// Projet TraceGPS
// fichier : modele/Trace.class.php
// Rôle : la classe Trace représente une trace ou un parcours
// Dernière mise à jour : 17/7/2018 par Dylan VALLÉE

include_once('PointDeTrace.class.php');

class Trace
{
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Attributs privés de la classe -------------------------------------
    // ------------------------------------------------------------------------------------------------------

    private $id;                // identifiant de la trace
    private $dateHeureDebut;        // date et heure de début
    private $dateHeureFin;        // date et heure de fin
    private $terminee;            // true si la trace est terminée, false sinon
    private $idUtilisateur;        // identifiant de l'utilisateur ayant créé la trace
    private $lesPointsDeTrace;        // la collection (array) des objets PointDeTrace formant la trace

    // ------------------------------------------------------------------------------------------------------
    // ----------------------------------------- Constructeur -----------------------------------------------
    // ------------------------------------------------------------------------------------------------------

    public function Trace($unId, $uneDateHeureDebut, $uneDateHeureFin, $terminee, $unIdUtilisateur)
    {
        $this->setId($unId);
        $this->setDateHeureDebut($uneDateHeureDebut);
        $this->setDateHeureFin($uneDateHeureFin);
        $this->setTerminee($terminee);
        $this->setIdUtilisateur($unIdUtilisateur);
        $this->lesPointsDeTrace = array();
    }

    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------------- Getters et Setters ------------------------------------------
    // ------------------------------------------------------------------------------------------------------

    public function getId()
    {
        return $this->id;
    }

    public function setId($unId)
    {
        $this->id = $unId;
    }

    public function getDateHeureDebut()
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut($uneDateHeureDebut)
    {
        $this->dateHeureDebut = $uneDateHeureDebut;
    }

    public function getDateHeureFin()
    {
        return $this->dateHeureFin;
    }

    public function setDateHeureFin($uneDateHeureFin)
    {
        $this->dateHeureFin = $uneDateHeureFin;
    }

    public function getTerminee()
    {
        return $this->terminee;
    }

    public function setTerminee($terminee)
    {
        $this->terminee = $terminee;
    }

    public function getIdUtilisateur()
    {
        return $this->idUtilisateur;
    }

    public function setIdUtilisateur($unIdUtilisateur)
    {
        $this->idUtilisateur = $unIdUtilisateur;
    }

    public function getLesPointsDeTrace()
    {
        return $this->lesPointsDeTrace;
    }

    public function setLesPointsDeTrace($lesPointsDeTrace)
    {
        $this->lesPointsDeTrace = $lesPointsDeTrace;
    }

    // Fournit une chaine contenant toutes les données de l'objet
    public function toString()
    {
        $msg = "Id : " . $this->getId() . "<br>";
        $msg .= "Utilisateur : " . $this->getIdUtilisateur() . "<br>";
        if ($this->getDateHeureDebut() != null) {
            $msg .= "Heure de début : " . $this->getDateHeureDebut() . "<br>";
        }
        if ($this->getTerminee()) {
            $msg .= "Terminée : Oui  <br>";
        } else {
            $msg .= "Terminée : Non  <br>";
        }
        $msg .= "Nombre de points : " . $this->getNombrePoints() . "<br>";
        if ($this->getNombrePoints() > 0) {
            if ($this->getDateHeureFin() != null) {
                $msg .= "Heure de fin : " . $this->getDateHeureFin() . "<br>";
            }
            $msg .= "Durée en secondes : " . $this->getDureeEnSecondes() . "<br>";
            $msg .= "Durée totale : " . $this->getDureeTotale() . "<br>";
            $msg .= "Distance totale en Km : " . $this->getDistanceTotale() . "<br>";
            $msg .= "Dénivelé en m : " . $this->getDenivele() . "<br>";
            $msg .= "Dénivelé positif en m : " . $this->getDenivelePositif() . "<br>";
            $msg .= "Dénivelé négatif en m : " . $this->getDeniveleNegatif() . "<br>";
            $msg .= "Vitesse moyenne en Km/h : " . $this->getVitesseMoyenne() . "<br>";
            $msg .= "Centre du parcours : " . "<br>";
            $msg .= "   - Latitude : " . $this->getCentre()->getLatitude() . "<br>";
            $msg .= "   - Longitude : " . $this->getCentre()->getLongitude() . "<br>";
            $msg .= "   - Altitude : " . $this->getCentre()->getAltitude() . "<br>";
        }
        return $msg;
    }

    public function getNombrePoints()
    {
        return sizeof($this->getLesPointsDeTrace());
    }

    public function getCentre()
    {
        if ($this->getNombrePoints() == 0) {
            return null;
        } else {
            $premierPoint = $this->lesPointsDeTrace[0];
            $latitudeMin = $premierPoint->getLatitude();
            $latitudeMax = $premierPoint->getLatitude();
            $longitudeMin = $premierPoint->getLongitude();
            $longitudeMax = $premierPoint->getLongitude();


            $centre = new Point(0, 0, 0);

            for ($i = 0; $i <= sizeof($this->lesPointsDeTrace) - 1; $i++) {
                $lePoint = $this->lesPointsDeTrace[$i];

                if ($lePoint->getLatitude() <= $latitudeMin)
                    $latitudeMin = $lePoint->getLatitude();

                if ($lePoint->getLatitude() >= $latitudeMax)
                    $latitudeMax = $lePoint->getLatitude();

                if ($lePoint->getLongitude() <= $longitudeMin)
                    $longitudeMin = $lePoint->getLongitude();

                if ($lePoint->getLongitude() >= $longitudeMax)
                    $longitudeMax = $lePoint->getLongitude();
            }

            $latitudeMoy = ($latitudeMin + $latitudeMax) / 2;
            $longitudeMoy = ($longitudeMin + $longitudeMax) / 2;

            $centre->setLatitude($latitudeMoy);
            $centre->setLongitude($longitudeMoy);

            return $centre;
        }
    }

    public function getDenivele()
    {
        if ($this->getNombrePoints() == 0) {
            return null;
        } else {
            $premierPoint = $this->lesPointsDeTrace[0];
            $altitudeMin = $premierPoint->getAltitude();
            $altitudeMax = $premierPoint->getAltitude();
            $ecart = 0;

            for ($i = 0; $i <= sizeof($this->lesPointsDeTrace) - 1; $i++) {
                $lePoint = $this->lesPointsDeTrace[$i];

                if ($lePoint->getAltitude() <= $altitudeMin) {
                    $altitudeMin = $lePoint->getAltitude();
                }
                if ($lePoint->getAltitude() >= $altitudeMax) {
                    $altitudeMax = $lePoint->getAltitude();
                }
            }

            return $ecart = $altitudeMax - $altitudeMin;
        }
    }

    public function getDureeEnSecondes()
    {
        if ($this->getNombrePoints() == 0) {
            return 0;
        } else {
            $point = $this->lesPointsDeTrace[$this->getNombrePoints() - 1];
            return $point->getTempsCumule();
        }
    }

    public function getDureeTotale()
    {
        if ($this->getNombrePoints() == 0) {
            return null;
        } else {
            $heures = (int)$this->getDureeEnSecondes() / 3600;
            $minutes = (int)($this->getDureeEnSecondes() % 3600) / 60;
            $secondes = ($this->getDureeEnSecondes() % 3600) % 60;

            return sprintf("%02d", $heures) . ":" . sprintf("%02d", $minutes) . ":" . sprintf("%02d", $secondes);
        }
    }

    public function getDistanceTotale()
    {
        if ($this->getNombrePoints() == 0) {
            return null;
        } else {
            $point = $this->lesPointsDeTrace[$this->getNombrePoints() - 1];
            return $point->getDistanceCumulee();

        }
    }

    public function getDenivelePositif()
    {
        $denivele = 0;
        // parcours de tous les couples de points
        for ($i = 0; $i < sizeof($this->lesPointsDeTrace) - 1; $i+=1)
        {
            $lePoint1 = $this->lesPointsDeTrace[$i];
            $lePoint2 = $this->lesPointsDeTrace[$i + 1];
            // on teste si ça monte
            if ( $lePoint2->getAltitude() > $lePoint1->getAltitude() )
                $denivele += $lePoint2->getAltitude() - $lePoint1->getAltitude();
        }
        return $denivele;
    }


    public function getDeniveleNegatif()
    {
        $denivele = 0;
        // parcours de tous les couples de points
        for ($i = 0; $i < sizeof($this->lesPointsDeTrace) - 1; $i+=1)
        {
            $lePoint1 = $this->lesPointsDeTrace[$i];
            $lePoint2 = $this->lesPointsDeTrace[$i + 1];
            // on teste si ça descend
            if ( $lePoint1->getAltitude() > $lePoint2->getAltitude() )
                $denivele += $lePoint1->getAltitude() - $lePoint2->getAltitude();
        }
        return $denivele;
    }

    public function getVitesseMoyenne()
    {
        if ($this->getNombrePoints() == 0) {
            return 0;
        } else {
            $vitesseEnKmH = $this->getDistanceTotale() / ($this->getDureeEnSecondes() / 3600);
            return $vitesseEnKmH;
        }
    }


    public function ajouterPoint(PointDeTrace $unPoint)
    {
        if ($this->getNombrePoints() == 0) {
            $unPoint->setDistanceCumulee(0);
            $unPoint->setTempsCumule(0);
            $unPoint->setVitesse(0);
        } else {
            $pointPrecedent = $this->lesPointsDeTrace[$this->getNombrePoints() - 1];
            $unPoint->setDistanceCumulee($pointPrecedent->getDistanceCumulee() + Point::getDistance($pointPrecedent, $unPoint));

            $temps = strtotime($unPoint->getDateHeure()) - strtotime($pointPrecedent->getDateHeure());
            $unPoint->setTempsCumule($pointPrecedent->getTempsCumule() + $temps);

            $unPoint->setVitesse(Point::getDistance($pointPrecedent, $unPoint) / ($temps / 3600));

        }
        $this->lesPointsDeTrace[] = $unPoint;
    }

    public function viderListePoints() {
        unset($this->lesPointsDeTrace);
    }
} // fin de la classe Trace

// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!
