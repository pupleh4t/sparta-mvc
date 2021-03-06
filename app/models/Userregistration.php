<?php

use Phalcon\Mvc\Model\Validator\Email as Email;

class Userregistration extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id_temp_user;

    /**
     *
     * @var string
     */
    public $unique_id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $type;

    /**
     *
     * @var string
     */
    public $email;

    /**
     *
     * @var string
     */
    public $encrypted_password;

    /**
     *
     * @var string
     */
    public $salt;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var string
     */
    public $activation_token;

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $this->validate(
            new Email(
                array(
                    'field'    => 'email',
                    'required' => true,
                )
            )
        );

        if ($this->validationHasFailed() == true) {
            return false;
        }

        return true;
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'userregistration';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Userregistration[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Userregistration
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
