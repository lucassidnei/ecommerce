<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model {
	

//Função para listar os Usuários
	public static function listAll(){

		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
    }
    
  //Função para criar categoria banco de dados
	public function save(){

		$sql = new Sql();
		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));
		$this->setData($results[0]);
    }
    

    public function get($idcategory){

        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
            ':idcategory'=>$idcategory
        ]);

        $this->setData($results[0]);
    }

    public function delete(){

        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory ", [
            ':idcategory'=>$this->getidcategory()
        ]);
    }

}
 ?>