<?php
    abstract class AbstractService
    {
        protected $connection;
        protected $table;
        protected $modelName;

        public function __construct()
        {
            $this->connection = Doctrine_Manager::connection();

            // try connect when create so that any connection errors can be detected early
            if(!$this->connection->isConnected())
                $this->connection->connect();

            $this->table = $this->connection->getTable($this->modelName);
        }

        /**
         * Saves an Aerial model - if it does not exist (no identifier provided) then a new one will be created
         *
         * @param $object array|stdClass            The object to save
         * @param bool $returnCompleteObject        Whether or not to return the complete graph of the saved object
         * @param bool $mapToModel                  Whether or not to "serialize" the incoming object to its Doctrine equivalent (internal)
         *
         * @return int|Aerial_Record
         */
        public function save($object, $returnCompleteObject = true, $mapToModel = true)
        {
            if($mapToModel)
                $object = ModelMapper::mapToModel($this->modelName, $object, true);

            $result = $object->trySave();

            if($result === true)
            {
                return $returnCompleteObject ? $object : $object->getIdentifier();
            }
            else
            {
                $object->save();
            }

        }

        /**
         * Alias of "save" function
         *
         * @param $object array|stdClass            The object to save
         * @param bool $returnCompleteObject        Whether or not to return the complete graph of the saved object
         * @param bool $mapToModel                  Whether or not to "serialize" the incoming object to its Doctrine equivalent (internal)
         *
         * @return int|Aerial_Record
         */
        public function update($object, $returnCompleteObject = true, $mapToModel = true)
        {
            return self::save($object, $returnCompleteObject, $mapToModel);
        }

        /**
         * Saves an Aerial model - the identifier will be ignored and a new record will be created
         *
         * @param $object array|stdClass            The object to save
         * @param bool $returnCompleteObject        Whether or not to return the complete graph of the saved object
         * @param bool $mapToModel                  Whether or not to "serialize" the incoming object to its Doctrine equivalent (internal)
         *
         * @return int|Aerial_Record
         */
        public function insert($object, $returnCompleteObject = true, $mapToModel = true)
        {
            if($mapToModel)
                $object = ModelMapper::mapToModel($this->modelName, $object);

            // unset the primary key values if one is set to insert a new record
            foreach($object->table->getIdentifierColumnNames() as $primaryKey)
                unset($object->$primaryKey);

            $result = $object->trySave();

            if($result === true)
            {
                return $returnCompleteObject ? $object : $object->getIdentifier();
            }
            else
            {
                $object->save();
            }

        }

        /**
         * Deletes an Aerial model
         *
         * @param $object array|stdClass            The object to delete
         * @param bool $mapToModel                  Whether or not to "serialize" the incoming object to its Doctrine equivalent (internal)
         *
         * @return mixed
         */
        public function drop($object, $mapToModel = true)
        {
            if($mapToModel)
                $object = ModelMapper::mapToModel($this->modelName, $object, true);

            return $object->delete();
        }


        private function find($criteria, $limit, $offset, $sort, $relations, $hydrateAMF = true)
        {
            $q = Doctrine_Query::create()->from("$this->modelName r");

            //========================  Selects / Joins ==========================
            if($relations)
            {
                //Merge the relations into a single tree; validate all paths start with the root table.
                $mergedRelations = array();
                foreach($relations as $path)
                {
                    list($dirty_key) = explode(".", $path, 2);
                    if(Aerial_Relationship::key($dirty_key) <> $this->modelName) $path = $this->modelName . "." . $path;
                    $mergedRelations = Aerial_Relationship::merge($mergedRelations, $path);
                }

                //Build the DQL 'leftJoin' and 'Select' parts.
                $relationParts = Aerial_Relationship::relationParts($mergedRelations);
                foreach($relationParts["joins"] as $join)
                    $q->leftJoin($join);

                $q->select($relationParts["selects"]);
            }

            //============================  Criteria =============================
            if($criteria)
            {
                foreach($criteria as $key=> $value)
                    $q->addWhere("r.$key =?", $value);
            }

            if($relationParts && !$relationParts["criteria"])
                $relationParts["criteria"] = array();

            if($relationParts)
            {
                foreach($relationParts["criteria"] as $criteria)
                    $q->addWhere($criteria);
            }


            //============================   Order  ===============================
            if($sort)
            {
                foreach($sort as $key=> $value)
                {
                    $q->addOrderBy("$key $value");
                }
            }

            //==========================  Pagination  ==============================
            if($limit) $q->limit($limit);
            if($offset) $q->offset($offset);

            $q->setHydrationMode($hydrateAMF ? Aerial_Core::HYDRATE_AMF_COLLECTION : Aerial_Core::HYDRATE_RECORD);
            $results = $q->execute();

            return $results;

        }

        /**
         * Count the number of records of the related table to this service
         *
         * @return int
         */
        public function count()
        {
            return $this->table->count();
        }

        private function query($properties)
        {
            $q = Doctrine_Query::create();
            foreach($properties as $property)
            {
                $method = $property["key"];
                call_user_func_array(array($q, $method), $property["value"]);
            }

            $q->setHydrationMode(Aerial_Core::HYDRATE_AMF_COLLECTION);
            return $q->execute();
        }
    }

?>