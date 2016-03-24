<?php

class Userkendaraan extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id_kendaraan;

    /**
     *
     * @var integer
     */
    public $id_user;

    /**
     *
     * @var string
     */
    public $jenis_kendaraan;

    /**
     *
     * @var string
     */
    public $plat_kendaraan;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'userkendaraan';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Userkendaraan[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Userkendaraan
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
