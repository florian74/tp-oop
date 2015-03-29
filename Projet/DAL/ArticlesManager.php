<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 23/03/2015
 * Time: 16:36
 */

class ArticlesManager {

    public $TableName;
    public $SchemaName;
    public $PhpClass;
    public $primaryKey;


    // NB: Toutes les colonnes de la bdd doivent être mapper, mais ils peut y avoir plus de propriété dans la classe Entry que de champs dans la BDD,
    // seuls les champs mappés seront enregistrés.
    // le mapping se fait via un tableau avec des entrées: ENTRY_PROPERTY => BDD_COLUMN
    public $propertyMap;

    public $NbProperties;

    public $reflector;


    public function  ArticlesManager($table_name)
    {
        date_default_timezone_set("Zulu");

        $conf = simplexml_load_file(__DIR__ . "/conf.xml");

        foreach($conf->table as $table)
        {

            if ($table->table_name == $table_name)
            {
                $this->primaryKey = (String) $table->phpClass_primarykey;

                $this->PhpClass = (String)$table->phpClass;

                $this->TableName = (String)$table->table_name;

                $this->SchemaName = (String)$table->schema_name;

                $this->propertyMap = array();
                foreach($table->mapping as $mapping)
                {
                    foreach($mapping->children() as $key) {

                        $class_field = "";
                        $db_field = "";
                        foreach($key->attributes() as $sub_key => $sub_value)
                        {
                            if ($sub_key == "Class_field")
                                $class_field = $sub_value;
                            if ($sub_key == "DB_field")
                                $db_field = $sub_value;

                        }

                        $this->propertyMap[(String) $class_field] = (String) $db_field;


                    }

                }

                $this->reflector = new ReflectionClass($this->PhpClass);
                $this->NbProperties = count($this->propertyMap);

                break;
            }
        }
    }

    public function clean()
    {
        $stmt = Connection::getConnection()->prepare("TRUNCATE TABLE " . $this->TableName);
        $stmt->execute();
    }


    public function getEntriesByProperty($property, $value)
    {
        $stmt = Connection::getConnection()->prepare("SELECT * FROM " . $this->TableName . " WHERE `" . $this->propertyMap[$property] . "` = ?;");
        $stmt->bindParam(1, $value);
        $stmt->execute();

         $articles = array();
        while ($row = $stmt->fetch()) {

            $article = new Entry();


            foreach ($this->reflector->getProperties() as $property ) {
                if ( isset ($this->propertyMap[$property->getName()]))
                    $property->setValue($article,$row[$this->propertyMap[ $property->getName() ] ]);
            }

            $articles[] = $article;
        }

        return $articles;
    }

    public function getAllEntries() {

        $stmt = Connection::getConnection()->prepare("SELECT * FROM " . $this->TableName);
        $stmt->execute();


        $articles = array();
        while ($row = $stmt->fetch()) {

            $article = new Entry();


            foreach ($this->reflector->getProperties() as $property ) {
                if ( isset ($this->propertyMap[$property->getName()]))
                 $property->setValue($article,$row[$this->propertyMap[ $property->getName() ] ]);
            }

            $articles[] = $article;
        }

        return $articles;

    }

    public function insertEntry( $anArticle) {

        //préparer la requête
        $props = array($this->NbProperties);
        $cpt = 0;
        $sql = "";
        $arg = "";
        foreach ($this->reflector->getProperties() as $property)
        {
            if (isset ($this->propertyMap[ $property->getName() ] )) {

                if ($sql == "")
                    $sql = $this->propertyMap[$property->getName()];
                else
                    $sql = $sql . "," . $this->propertyMap[$property->getName()];

                if ($arg == "")
                    $arg = "?";
                else
                    $arg = $arg . ",?";

                if (isset($anArticle->{$property->getName()})) {
                    $props[$cpt] = $anArticle->__get($property->getName());
                } else
                    $props[$cpt] = 0;


                $cpt++;
            }
        }

        //écrire la requête
        $stmt = Connection::getConnection()->prepare("INSERT INTO `" . $this->SchemaName . "`.`" . $this->TableName . "` (" . $sql .") VALUES (" . $arg . ");");


        //binder  les valeurs
        $cpt = 0;
        foreach ($this->reflector->getProperties() as $property) {

            if (isset ($this->propertyMap[ $property->getName() ] )) {
                $stmt->bindParam($cpt + 1, $props[$cpt]);
                $cpt++;
            }

        }

        //executer
        $stmt->execute();
    }

    public function insertEntries( $Articles ) {

        foreach($Articles as $Article)
            try {
                $this->insertEntry($Article);
            }
            catch (PDOException $e)
            {
               // echo $e->getMessage();
            }

    }

    public function mergeEntry( $anArticle ) {

        //préparer la requête
        $props = array($this->NbProperties);
        $cpt = 0;
        $sql = "";
        foreach ($this->reflector->getProperties() as $property)
        {
            if (isset ($this->propertyMap[ $property->getName() ] )) {

                if ($sql == "")
                    $sql = $this->propertyMap[$property->getName()] . " = ? ";
                else
                    $sql = $sql . "," . $this->propertyMap[$property->getName()] . "= ?";

                if (isset($anArticle->{$property->getName()})) {
                    $props[$cpt] = $anArticle->__get($property->getName());
                } else
                    $props[$cpt] = 0;


                $cpt++;
            }
        }

        //écrire la requête
        $stmt = Connection::getConnection()->prepare("UPDATE `" . $this->SchemaName . "`.`" . $this->TableName . "` SET " . $sql . " WHERE " . $this->primaryKey . " = ?" . ";");


        //binder  les valeurs
        $cpt = 0;
        foreach ($this->reflector->getProperties() as $property) {

            if (isset ($this->propertyMap[ $property->getName() ] )) {
                $stmt->bindParam($cpt + 1, $props[$cpt]);
                $cpt++;
            }

        }
        $stmt->bindParam($cpt + 1, $anArticle->{$this->primaryKey});

        //executer
        $stmt->execute();


    }


    public function mergeWithPKChange( $anArticle , $ancienKey)
    {
       $this->Delete($this->primaryKey, $ancienKey);
       $this->insertEntry($anArticle);
    }

    public function mergeEntries( $Articles ) {
        foreach($Articles as $Article)
            try {
                $this->mergeEntry($Article);
            }
            catch (PDOException $e)
            {
               // echo $e->getMessage();
            }
    }


    public function DeleteEntry( $anArticle)
    {
        $this->Delete($this->primaryKey, $anArticle->{$this->primaryKey});
    }

    public function DeleteEntries( $Articles) {

        $primaryKey = $this->primaryKey;
        foreach($Articles as $Article) {
            try {
                $this->Delete($primaryKey, $Article->{$primaryKey});
            } catch (PDOException $e) {
             //   echo $e->getMessage();
            }
        }
    }

    public function Delete( $parameter , $value)
    {
        if (isset ($this->propertyMap[$parameter])) {
            $stmt = Connection::getConnection()->prepare(" DELETE FROM `" . $this->SchemaName . "`.`" . $this->TableName . "` WHERE " . $this->propertyMap[$parameter] . " = ?;");
            $stmt->bindValue(1, $value);
            $stmt->execute();
        }
    }


}