<?php 
	require ("config/Database.php");
	$con=new Database();
	$req=$con->query("show tables");
	$resultat = $req->fetchAll();
	//For pour le parcourt des tables contenues dans la bd
	for($i=0; $i< count($resultat); $i++){
		//Pour Chaque Table
		$tableB=$resultat[$i][0];
		
		
		echo "<br/>".$tableB."<br/>";
		$file=fopen($tableB.".php", "w");
		fwrite($file,"<?php\n");
		fwrite($file,"require_once(\"Database.php\");\n");
		$class=ucwords($tableB);
		fwrite($file,"public class ".$class." {\n");
		fwrite($file,"\tprivate static \$base;\n");
		$reqforTable="select COLUMN_NAME, DATA_TYPE,IS_NULLABLE, COLUMN_KEY from information_schema.columns where table_name='".$tableB."' AND `columns`.`TABLE_SCHEMA`='".Database::$dataBase."'";
		$req=$con->query($reqforTable);
		$resul=$req->fetchAll();
		$colum_id="";
		$mot="";
		$a=0;

		//Deuxieme for pour le parcourt des attributs contenue dans une table
		for($j=0; $j< count($resul); $j++){

			$mot=$mot.$resul[$j]["COLUMN_NAME"].",";
			// Utilisation de la boucle if pour le remplissage des attributs contenue dans chaque Table
		    if($resul[$j]["DATA_TYPE"]=="int" || $resul[$j]["DATA_TYPE"]=="tinyint"){
				fwrite($file,"\tprivate \$".$resul[$j]["COLUMN_NAME"]."=0;\n");
			}
			else {
				fwrite($file,"\tprivate \$".$resul[$j]["COLUMN_NAME"]."=\"\";\n");
			}
			if($resul[$j]["COLUMN_KEY"]=="PRI"){
			     $colum_id=$resul[$j]["COLUMN_NAME"];
			}
					


		$param=substr($mot,0,(strlen($mot)-1));
		$param1=str_word_count($mot,1);
		$const="";
		$popu="";
		$miseAjour="";
		$findB="";
		//Troisieme for permettant le parcourt pour la creation des methodes 
		for($a=0;$a<str_word_count($mot);$a++){
			$popu=$popu."\$row['".$param1[$a]."'],";
			//$rpopu est la variable declare pour la metode pupulate
			$rpopu=substr($popu,0,(strlen($popu)-1));


			// Utilisation de la boucle if pour le remplissage des elements contenues dans la variable Construct
			if ($a<str_word_count($mot)-1) {
				$const=$const."\$this->".$param1[$a]."=\$".$param1[$a].";\n\t\t";
			}
			else{
				$const=$const."\$this->".$param1[$a]."=\$".$param1[$a].";";
			}

			// Utilisation de la boucle if pour le remplissage des elements contenues dans la variable Update
			if ($a<str_word_count($mot)-1) {
				$miseAjour=$miseAjour."\n\t\t\t\t\t\t'".$param1[$a]."'=>\$this->".$param1[$a].",";

			}
			else{
				$miseAjour=$miseAjour."\n\t\t\t\t\t\t'".$param1[$a]."'=>\$this->".$param1[$a].");";
			}

			// Utilisation de la boucle if pour le remplissage des elements contenues dans la variable findByValue
			if ($resul[$j]["DATA_TYPE"]=="int" || $resul[$j]["DATA_TYPE"]=="tinyint") {
				
				$findB=$findB."if(\$this->".$param1[$a]."!=0)\$string =\$string.\"AND '".$param1[$a]."'=\".\$this->".$param1[$a].";\n\t\t";
			}
			else{
				$findB=$findB."if(\$this->".$param1[$a]."!=\"\")\$string=\$string.\"AND'".$param1[$a]."'='\".\$this->".$param1[$a].".\"'\";\n\t\t";

			}

		}
			
		}
		/**
		*@$construct: est la variable qui permet de creer la methode du constructeur
		*Elle permet d'initialiser les elements d'une class donne
		*/
		$construct="\tpublic function __construct(".$param."){\n\t\t".$const."\n\t}";

		/**
		*@$populate: est la variable qui permet de creer la methode populate
		*Elle permet de 
		*/
		$populate="\tstatic function populate(\$resutset){\n\t\t\$object=array();\n \t\t\$i=0;\n\t\tforeach (\$resutset as \$row) {\n\t\t\t\$object[\$i]=new $class(".$rpopu.");\n\t\t\$i++;\n\t\t}\n\t\tself::\$base=null;\n\t\treturn \$object;\n\t}";

		/**
		*@$update:est la variable qui permet de creer la methode update
		*Elle permet la mise a jour des attributs 
		*/
		$update="\tpublic function update(){\n\t\tself::\$base=new Database();\n\t\tself::\$base->connect();\n\t\t\$values = array(".$miseAjour."\n\t\tself::\$base->update('".$colum_id."',\$values,'".$colum_id."',\$this->".$colum_id.");\n\t\tself::\$base=null;\n\t}";
		
		/**
		* @$findByValue: est la variable qui permet de creer la methode findByValue
		* Elle permet d'afficher 
		*/

		$findByValue="\tfunction findByValue(){\n\t\tself::\$base = new Database();\n\t\t\$"."string1=\"select *from '".$tableB."'\";\n\t\t\$string=\" \";\n\t\t".$findB."\$string1=\$string1.preg_replace('/AND/', 'WHERE', \$string, 1);\n\t\treturn self::populate(self::\$base->query(\$string1));\n\t}";
		/**
		* @$findBykey: est la variable qui permet de creer la methode findBykey
		* Elle permet d'afficher 
		*/
		
		$findBykey="\tstatic function findBykey(\$key){\n\t\tself::\$base=new Database();\n\t\t\$tab=self::populate(self::\$base->query(\"select * from '".$tableB."' WHERE `".$colum_id."`=\".\$key));\n\t\treturn \$tab[0];\n\t}\n";

		/**
		*@$findAll: est la variable qui permet de creer la methode FindAll
		*Elle permet d'afficher le contenu d'dun table de façon detaille
		*/
		$findAll="\tstatic function findAll(){\n\t\tself::\$base=new Database();\n\t\treturn self::populate(self::\$base->query(\"select * from '".$tableB."'\"));\n\t}\n";
		/**
		*@$delete : est la variable qui permet de creer la methode Delete 
		*Elle permet de faire la suppresion
		*/
		$delete="\tpublic function delete(){\n\t\tself::\$base=new Database();\n\t\tself::\$base->delete('".$tableB."', '".$colum_id."', \$this->".$colum_id.");\n\t\tself::\$base=null;\n\t}\n";





		fwrite($file,"\n\n".$construct."\n\n");
		fwrite($file,$findBykey."\n\n");
		fwrite($file,$delete."\n\n");
		fwrite($file,$findAll."\n\n");
	
	    #Completer ces Méthodes dans la boucle et enlever les comments
		fwrite($file,$populate."\n\n");
		fwrite($file,$findByValue."\n\n");
		fwrite($file,$update."\n\n");
		
		
		
		fwrite($file,"}\n");
		fwrite($file,"?>");
		fclose($file);
	
	}
	
?>