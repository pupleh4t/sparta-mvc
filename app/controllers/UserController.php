<?php
use Phalcon\Http\Response;
class UserController extends \Phalcon\Mvc\Controller
{

    public function registerAction()
    {
        $json_data = $this->request->getJsonRawBody();
        $name = $json_data->name;
        $email = $json_data->email;
        $password = $json_data->password;
        $uuid = uniqid('',true);

        // Make hashcode and salt string
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted_password = base64_encode(sha1($password . $salt, true) . $salt);

        // Check user is on database or not
        $conditions = "email = :email:";
        $parameters = array("email"=>$email);
        $userRegistered = Usercredential::find(array($conditions, "bind" => $parameters));

        $response = new Response();
        /**
         * Check if user has been on database or not
         */
        if(count($userRegistered) > 0){
            /**
             * User already exists
             */
            $response->setStatusCode(409, 'Conflicted');
            $response->setJsonContent(array(
                'error' => true ,
                'error_msg' => $email." has been registered"
            ));
        }
        else{
            // User is not on database, So it valid to be created
            // Followed by insertion a user into database
            $user = new Usercredential();
            $user->unique_id = $uuid;
            $user->name = $name;
            $user->email = $email;
            $user->encrypted_password = $encrypted_password;
            $user->salt = $salt;
            $user->created_at = date('Y-m-d H:i:s');
            $user->updated_at = date('Y-m-d H:i:s');


            // If User registration succeed
            if($user->save() == true)
            {
                $response->setStatusCode(201, "Created");
                $response->setJsonContent(
                    array(
                        'error' => false,
                        'uid' => $user->unique_id,
                        'user' => array(
                            'name'  => $user->name,
                            'email' => $user->email,
                            'created_at' => $user->created_at
                        )
                    )
                );
            }
            else {
                // If User regitration failed
                $response->setStatusCode(409, "Conflict");
                $error_msg[]= "error broo";
                foreach($user->getMessages() as $message){
                    $error_msg[] = $message->getMessage();
                }
                $response->setJsonContent(
                    array(
                        'error' => true,
                        'error_msg' => $error_msg
                    )
                );
            }
        }
        return $response;
    }

    public function loginAction()
    {
        $json_data = $this->request->getJsonRawBody();
        $email = $json_data->email;
        $password = $json_data->password;

        $response = new Response();

        // Searching record usercredential based on email
        $conditions = "email = :email:";
        $parameters = array("email"=>$email);
        $userExisted = Usercredential::findFirst(array($conditions, "bind"=>$parameters));

        if($userExisted == null){
            $response->setJsonContent(array(
                'error' => true,
                'error_msg' => 'Combination Username or Password is Incorrect'
            ));

        }else{
            // Check if post parameter data meets a usercredential record
            $encrypted_password = base64_encode(sha1($password . $userExisted->salt, true) . $userExisted->salt);
            if ($userExisted->encrypted_password == $encrypted_password) {
                $response->setJsonContent(array(
                    'error'     => false,
                    'uid'       => $userExisted->unique_id,
                    'user'      => array(
                        'name'      => $userExisted->name,
                        'email'     => $userExisted->email,
                        'created_at'=> $userExisted->created_at,
                        'updated_at'=> $userExisted->updated_at
                    )
                ));
            }
            else {
                $response->setJsonContent(array(
                    'error' => true,
                    'error_msg' => 'Combination Username or Password is Incorrect'
                ));
            }
        }
        return $response;
    }
}

