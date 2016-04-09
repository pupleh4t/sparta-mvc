<?php
use Phalcon\Http\Response;
use Phalcon\Mvc\Url;
require APP_PATH."/app/library/vendor/autoload.php";
use Mailgun\Mailgun;
use Http\Adapter\Guzzle6\Client;

class MailController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

    }

    public function createBodyActivationMail($username, $created_at, $link_confirmation, $htmlOrNot)
    {
        $emailBody = null;
        if($htmlOrNot){
            $emailBody = "<html><body>"."<h3>Welcome $username </h3>"
                ."Your email has recently been registered as a SPARTA account at $created_at. Please click this <a href='$link_confirmation'>link</a> to activate your account. <br><br>"

                ."If you have any trouble, please don't mind to ask us by replying this email. Thank you."
                ."</body></html>";
        }else{
            $emailBody = "Welcome $username \nYour email has recently as a SPARTA account at $created_at. Please click the activation link below to activate your account.\n\n"
                ."$link_confirmation \n\n"
                ."If you have any trouble, please don't mind to ask us by replying this email. Thank you.";
        }

        return $emailBody;
    }

    public function createBodyReactivationMail($username, $link_confirmation, $htmlOrNot)
    {
        $emailBody = null;
        if($htmlOrNot){
            $emailBody = "<html><body>"."<h3>Hello $username </h3>"
                ."You've just requested activation link for your SPARTA account. Please click this <a href='$link_confirmation'>link</a> to activate your account. <br><br>"

                ."If you have any trouble, please don't mind to ask us by replying this email. Thank you."
                ."</body></html>";
        }else{
            $emailBody = "Hello $username \nYou've just requested activation link for your SPARTA account. Please click the activation link below to activate your account.\n\n"
                ."$link_confirmation \n\n"
                ."If you have any trouble, please don't mind to ask us by replying this email. Thank you.";
        }

        return $emailBody;
    }

    public function sendActivationMail($username, $created_at, $email, $link_confirmation)
    {
        $config = include APP_PATH . "/app/config/config.php";

        $APIkey = $config->mailgun->apiKey;
        $client = new Client();
        $mail = new Mailgun($APIkey, $client);
        $domain = $config->mailgun->domain;

        $mail->sendMessage($domain, array(
                'from'=>$config->mailgun->fromEmail,
                'to'=> $email,
                'subject' => 'Welcome to SPARTA',
                'text' => $this->createBodyActivationMail($username, $created_at, $link_confirmation, false),
                'html' => $this->createBodyActivationMail($username, $created_at, $link_confirmation, true)
            )
        );
    }

    public function resendActivationMail($username, $email, $link_confirmation)
    {
        $config = include APP_PATH . "/app/config/config.php";

        $APIkey = $config->mailgun->apiKey;
        $client = new Client();
        $mail = new Mailgun($APIkey, $client);
        $domain = $config->mailgun->domain;

        $mail->sendMessage($domain, array(
                'from'=>$config->mailgun->fromEmail,
                'to'=> $email,
                'subject' => 'Account Activation',
                'text' => $this->createBodyReactivationMail($username, $link_confirmation, false),
                'html' => $this->createBodyReactivationMail($username, $link_confirmation, true)
            )
        );
    }
}

