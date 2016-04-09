<?php
use Phalcon\Http\Response;
class UserController extends \Phalcon\Mvc\Controller
{

    public function registrationAction()
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
         * Check if user has been in database or not
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
                        ),

                    )
                );
            }
            else {
                // If User regitration failed
                $response->setStatusCode(409, "Conflict");
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

    public function registerAction()
    {
        $json_data = $this->request->getJsonRawBody();
        $name = $json_data->name;
        $email = $json_data->email;
        $password = $json_data->password;
        $uuid = uniqid('',true);
        $type = null;

        $domain = explode("@", $email);
        $domain = $domain[1];
        if($domain == "ugm.ac.id"){
            $type = "lecturer";
        }elseif ($domain == "mail.ugm.ac.id"){
            $type = "student";
        }else{
            $type = "guest";
        }

        // Make hashcode and salt string
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted_password = base64_encode(sha1($password . $salt, true) . $salt);

        // Check user is on database or not
        $conditions = "email = :email:";
        $parameters = array("email"=>$email);
        $userRegistered = Usercredential::findFirst(array($conditions, "bind" => $parameters));
        $userNotValidated = Userregistration::findFirst(array($conditions, "bind" => $parameters));

        $response = new Response();
        /**
         * Check if user has been on database or not
         */
        if($userRegistered){
            /**
             * User already exists
             */
            $response->setStatusCode(409, 'Conflicted');
            $response->setJsonContent(array(
                'error' => true ,
                'error_msg' => $email." has been registered"
            ));
        }
        elseif ($userNotValidated){
            /**
             * User already exists
             */
            $response->setStatusCode(409, 'Conflicted');
            $response->setJsonContent(array(
                'error' => true ,
                'error_msg' => $email." waiting for activation via email"
            ));
        }
        else{
            // User is not on database, So valid to be created
            // Followed by insertion a user into database
            $activation_token = substr(sha1($name.$salt.$uuid), 0, 10) ;
            $user = new Userregistration();
            $user->unique_id = $uuid;
            $user->name = $name;
            $user->type = $type;
            $user->email = $email;
            $user->encrypted_password = $encrypted_password;
            $user->salt = $salt;
            $user->created_at = date('Y-m-d H:i:s');
            $user->activation_token = $activation_token;

            // If User registration succeed
            if($user->save() == true)
            {
                $link_activation = "smartcity.wg.ugm.ac.id/webapp/sparta3/user/activate/".base64_encode($email)."/$activation_token";
                $mailController = new MailController();
                $mailController->sendActivationMail($name,date('Y-m-d H:i:s'),$email, $link_activation);

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

    public function activateAction($email, $token)
    {
        try{
            $email = base64_decode($email);
            $conditions = "email = :email: AND activation_token = :token:";
            $parameters = array("email" => $email, "token"=>$token);
            $userNotValidated = Userregistration::find(array($conditions, "bind"=>$parameters));

            if(count($userNotValidated)>0){
                $userRegistered = new Usercredential();
                $userRegistered->unique_id = $userNotValidated->unique_id;
                $userRegistered->name = $userNotValidated->name;
                $userRegistered->email = $userNotValidated->email;
                $userRegistered->encrypted_password = $userNotValidated->encrypted_password;
                $userRegistered->salt = $userNotValidated->salt;
                $userRegistered->created_at = $userNotValidated->created_at;
                $userRegistered->updated_at = date('Y-m-d H:i:s');

                if($userRegistered->create() && $userNotValidated->delete()){
                    echo "Congratulations! $userNotValidated->email account has been activated.";
                }else{
                    echo "Oops, Something might go wrong";
                }
            }else{
                echo "This activation link is broken";
            }
        }catch (Exception $e){
            echo "Oops, something might go wrong";
        }
    }

    public function resendEmailAction($email)
    {
        $response = new Response();
        try{
            $email = base64_decode($email);
            $conditions = "email = :email:";
            $parameters = array("email"=>$email);
            $userNotValidated = Userregistration::findFirst(array($conditions, "bind"=>$parameters));

            $mailController = new MailController();

            if($userNotValidated){
                $username = $userNotValidated->name;
                $activation_token = $userNotValidated->activation_token;
                $link_activation = "smartcity.wg.ugm.ac.id/webapp/sparta3/user/activate/".base64_encode($email)."/$activation_token";
                $mailController->resendActivationMail($username,$email,$link_activation);
                $response->setJsonContent(
                    array(
                        'error' => false,
                        'error_msg' => null
                    )
                );
            }else{
                $response->setJsonContent(
                    array(
                        'error' => true,
                        'error_msg' => "$email has been activated or hasn't been registered yet"
                    )
                );
            }
        }catch (Exception $e){
            $response->setJsonContent(
                array(
                    'error' => true,
                    'error_msg' => "Oops, something might go wrong"
                )
            );
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

