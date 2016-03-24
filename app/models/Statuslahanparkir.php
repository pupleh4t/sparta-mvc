<?php

class Statuslahanparkir extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id_status;

    /**
     *
     * @var integer
     */
    public $id_lahan;

    /**
     *
     * @var integer
     */
    public $slot_number;

    /**
     *
     * @var string
     */
    public $status;

    /**
     *
     * @var integer
     */
    public $id_kendaraan;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'statuslahanparkir';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Statuslahanparkir[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Statuslahanparkir
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
