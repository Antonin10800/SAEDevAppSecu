<?php

namespace netvod\action;

use netvod\Auth\Auth;
use netvod\db\ConnectionFactory;
use netvod\user\Utilisateur;
use netvod\video\episode\Serie;
use netvod\video\lists\ListeSerie;

class Favoris implements Action
{
    public function execute(): string
    {

        $html = "";
        //on récupere l'utilisateur
        $utilisateur = unserialize($_SESSION['user']);
        //on récupere l'id User et l'id Serie
        $idUser = $utilisateur->IDuser;
        $idSerie = filter_var($_GET['idSerie'],FILTER_SANITIZE_NUMBER_INT);

        //on récupere la liste des séries
        $listeSerie = ListeSerie::getInstance();
        $series = $listeSerie->getSeries();

        //on parcours les séries et si la série est la même c'est celle la:
        foreach ($series as $a) {
            if($a->IDserie == $idSerie){
                $serie = $a;
                break;
            }
        }

        //on vérifie si la série est deja en favoris
        if(self::pasDeFavoris())
        {
            $query = "INSERT INTO Favoris VALUES(?,?)";

            $utilisateur->ajouterFavoris($serie);
        }
        else
        {
            $query = "DELETE FROM Favoris WHERE IDUser = ? AND IDSerie= ?";
            $utilisateur->supprimerFavoris($serie);
        }
        $_SESSION['user'] = serialize($utilisateur);
        //on redirige vers notre série:
        //on execute la query
        $db = ConnectionFactory::makeConnection();
        $statement = $db->prepare($query);
        $statement->bindParam(1,$idUser);
        $statement->bindParam(2,$idSerie);
        $statement->execute();
        header('Location: ?action=afficher-serie&idSerie=' . $idSerie);
        return $html;
    }

    /**
     * méthode qui vérifie si la série n'est pas en favoris
     * @return bool  true ou false
     */
    public static function pasDeFavoris():bool
    {
        //on récupere l'utilisateur
        $utilisateur = unserialize($_SESSION['user']);
        //on récupere l'id de l'utilisateur
        $idUser = $utilisateur->IDuser;
        //on récupere l'id de la série dans le lien GET:
        $idSerie = filter_var($_GET['idSerie'], FILTER_SANITIZE_NUMBER_INT);

        //on select dans favoris:
        $query = "SELECT * FROM Favoris WHERE IDUser = ? AND IDSerie = ?";
        $db = ConnectionFactory::makeConnection();
        $statement = $db->prepare($query);
        $statement->bindParam(1,$idUser);
        $statement->bindParam(2,$idSerie);
        $statement->execute();
        //si row === false alors la requete n'est pas dans la BD.
        $row = $statement->fetch();
        if($row === false)
        {
            //retourne vrai si n'est pas dans la bd
            return true;
        }
        //sinon retourne false
        return false;
    }

    /**
     * méthode qui permet d'ajouter les favoris dans la liste de favoris de l'utilisateur
     * @param Utilisateur $user l'utilisateur
     * @return Utilisateur on retourne cette utilisateur apres la mise a jours
     */
    public static function remplirFavoris(Utilisateur $user): Utilisateur
    {
        //on récupere l'id User
        $idUser = $user->IDuser;
        //on recupere les favoris de l'utilisateur
        $query = "SELECT * FROM Favoris WHERE IDUser = ?";
        $db = ConnectionFactory::makeConnection();
        $statement = $db->prepare($query);
        $statement->bindParam(1,$idUser);
        $statement->execute();
        //on recupere les séries :
        $listeSerie = ListeSerie::getInstance();
        $series = $listeSerie->getSeries();
        //on fetch les favoris
        $row = $statement->fetchAll();
        foreach ($row as $item)
        {
            foreach ($series as $a) {
                if($a->IDserie == $item['IDSerie']){
                    $serie = $a;
                    break;
                }
            }
            $user->ajouterFavoris($serie);
        }
        return $user;
    }
}