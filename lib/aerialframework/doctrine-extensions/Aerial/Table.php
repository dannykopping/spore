<?php
    class Aerial_Table extends Doctrine_Table
    {

        /**
         * Adds a column to the schema.
         *
         * This method does not alter the database table; @see export();
         *
         * @see                                                $_columns;
         * @param string $name      column physical name
         * @param string $type      type of data
         * @param integer $length   maximum length
         * @param mixed $options
         * @param boolean $prepend  Whether to prepend or append the new column to the column list.
         *                          By default the column gets appended.
         * @throws Doctrine_Table_Exception     if trying use wrongly typed parameter
         * @return void
         */
        public function setColumn($name, $type = null, $length = null, $options = array(), $prepend = false)
        {
            $maintainNames = $this->getConnection()->getAttribute(Aerial_Core::ATTR_YAML_MAINTAIN_COLUMN_NAMES);

            if(is_string($options))
            {
                $options = explode('|', $options);
            }

            foreach($options as $k => $option)
            {
                if(is_numeric($k))
                {
                    if(!empty($option))
                    {
                        $options[$option] = true;
                    }
                    unset($options[$k]);
                }
            }

            // extract column name & field name
            if(stripos($name, ' as '))
            {
                if(strpos($name, ' as '))
                {
                    $parts = explode(' as ', $name);
                } else
                {
                    $parts = explode(' AS ', $name);
                }

                if(count($parts) > 1)
                {
                    $fieldName = $parts[1];
                } else
                {
                    $fieldName = $parts[0];
                }

                $name = $maintainNames ? $parts[0] : strtolower($parts[0]);
            } else
            {
                $fieldName = $name;

                if(!$maintainNames)
                    $name = strtolower($name);
            }

            $name = trim($name);
            $fieldName = trim($fieldName);

            if($prepend)
            {
                $this->_columnNames = array_merge(array($fieldName => $name), $this->_columnNames);
                $this->_fieldNames = array_merge(array($name => $fieldName), $this->_fieldNames);
            } else
            {
                $this->_columnNames[$fieldName] = $name;
                $this->_fieldNames[$name] = $fieldName;
            }

            $defaultOptions = $this->getAttribute(Doctrine_Core::ATTR_DEFAULT_COLUMN_OPTIONS);

            if(isset($defaultOptions['length']) && $defaultOptions['length'] && $length == null)
            {
                $length = $defaultOptions['length'];
            }

            if($length == null)
            {
                switch($type)
                {
                    case 'integer':
                        $length = 8;
                        break;
                    case 'decimal':
                        $length = 18;
                        break;
                    case 'string':
                    case 'clob':
                    case 'float':
                    case 'integer':
                    case 'array':
                    case 'object':
                    case 'blob':
                    case 'gzip':
                        //$length = 2147483647;

                        //All the DataDict driver classes have work-arounds to deal
                        //with unset lengths.
                        $length = null;
                        break;
                    case 'boolean':
                        $length = 1;
                    case 'date':
                        // YYYY-MM-DD ISO 8601
                        $length = 10;
                    case 'time':
                        // HH:NN:SS+00:00 ISO 8601
                        $length = 14;
                    case 'timestamp':
                        // YYYY-MM-DDTHH:MM:SS+00:00 ISO 8601
                        $length = 25;
                }
            }

            $options['type'] = $type;
            $options['length'] = $length;

            foreach($defaultOptions as $key => $value)
            {
                if(!array_key_exists($key, $options) || is_null($options[$key]))
                {
                    $options[$key] = $value;
                }
            }

            if($prepend)
            {
                $this->_columns = array_merge(array($name => $options), $this->_columns);
            } else
            {
                $this->_columns[$name] = $options;
            }

            if(isset($options['primary']) && $options['primary'])
            {
                if(isset($this->_identifier))
                {
                    $this->_identifier = (array) $this->_identifier;
                }
                if(!in_array($fieldName, $this->_identifier))
                {
                    $this->_identifier[] = $fieldName;
                }
            }
            if(isset($options['default']))
            {
                $this->hasDefaultValues = true;
            }
        }
    }
