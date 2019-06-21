<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {
	const SESSION = "User";
	const SECRET = "SidneiLucas_Secret";


	// Pega o usuário da sessão
public static function getFromSession(){

	$user = new User();

	if(isset($_SESSION[User::SESSION])&& (int)$_SESSION[User::SESSION]['iduser'] >0){

		$user->setData($_SESSION[User::SESSION]);

	}
	return $user;
}

public static function checkLogin($inadmin = true){

		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		) {
			
			//Não está logado
			return false;
		
		} else {
		
			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {
				return true;
		
			} else if ($inadmin === false) {
				return true;
		
			} else {
				return false;
		
			}
		}
	}

//Função de validar login
	public static function login($login, $password){

		$sql = new Sql(); 
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			"LOGIN"=>$login
		));

		// Estoura uma excessão pelo usuário não existir
		if(count($results)===0){
				throw  \Exception("Usuário inexistente ou senha inválida");
				
			}

			// Se existe seta os dados na variável $data
			$data = $results[0];

			if 
				(password_verify($password, $data["despassword"]) === true){
				$user = new User();
				$user->setData($data);
				// Define uma sessão com o nome do usuário que conseguiu logar
				$_SESSION[User::SESSION] = $user->getValues();
				return $user;


			}

				 else{

					// Estoura uma excessão pela senha ser inválida
					throw new \Exception("Usuário inexistente ou senha inválida");
				 }
	} 

//Verifica o login do usuario
	public static function verifyLogin($inadmin = true)
	{
		if (!User::checkLogin($inadmin)) {
			header("Location: /admin/login");
			exit;
		}
		
	}

///Função de deslogin
	public static function logout(){
		$_SESSION[User::SESSION] = NULL;
	}

//Função para listar os Usuários
	public static function listAll(){
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_users a INNER  JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
	}

//Função para salvar no banco de dados
	public function save(){
		$sql = new Sql();
		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
		$this->setData($results[0]);
	}


	public function get($iduser)
	{

		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));
		$this->setData($results[0]);
	}

//Função pra fazer update no banco de dados
	public function update()
	{
		$sql = new Sql();
		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
	}

//Função para deletar dados
	public function delete(){

		$sql = new Sql();
		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
	}

//Função de recuperação de senha
public static function getForgot($email, $inadmin = true){

	$sql = new Sql();

	$results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :EMAIL", array(
		":EMAIL"=>$email
	));

	
	if(count($results) === 0){

		throw new \Exception("E-Mail não cadastrado.");

	}else{

		$data = $results[0];

		$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:IDUSER, :DESIP)", array(
			":IDUSER"=>$data["iduser"],
			":DESIP"=>$_SERVER["REMOTE_ADDR"]
		));

		if(count($results2) === 0){

		   throw new \Exception("Não foi possível recuperar a senha");

		}else{

			$dataRecovery = $results2[0];

			$iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
			$code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);
			$result = base64_encode($iv.$code);

			if($inadmin === true){

				$link = "http://www.xesquecommerce.com.br/admin/forgot/reset?code=$result";
				
			}else{

				$link = "http://www.xesquecommerce.com.br/forgot/reset?code=$result";

			}

			$mailer = new Mailer(
				$data["desemail"], 
				$data["desperson"], 
				"Redefinir senha", 
				"forgot", 
					array(
						"name"=>$data["desperson"],
						"link"=>$link
					)
			);

			$mailer->send();

			return $link;
		}
	}
	
}

public static function validForgotDecryt($result){
	
	$result = base64_decode($result);
    $code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');
    $iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');
    $idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);
	$sql = new Sql();
            $results = $sql->select("SELECT *
                FROM tb_userspasswordsrecoveries a
                INNER JOIN tb_users b USING(iduser)
                INNER JOIN tb_persons c USING(idperson)
                WHERE
                a.idrecovery = :idrecovery
                AND
                a.dtrecovery IS NULL
                AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();",
				array(
					":idrecovery"=>$idrecovery
				));

		if (count ($results) === 0){

			throw new \Exception("Não foi possível recuperar a senha.");
			
		}else {

			return $results[0];
		}
}

public static function setForgotUsed($idrecovery){
	$sql = new Sql();
	$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
		":idrecovery"=>$idrecovery
	));
}


public function setPassword($password){

	$sql = new Sql();
	$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
		":password"=>$password,
		":iduser"=>$this->getiduser()

	));
}

}
 ?>