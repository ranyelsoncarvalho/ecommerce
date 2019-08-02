<?php

namespace Hcode;

//utilizar a classe PHP mailer
use Rain\Tpl;

//classe responsavel pelo envio de email
class Mailer {

    const USERNAME = "guilherme.eneas21@gmail.com"; //endereco de email
    const PASSWORD = "d-efeitos"; //senha
    const NAME_FROM = "Hcode Store"; //remetente

    private $mail;


    public function __construct($toAddress, $toName, $subject, $tplName, $data = array()){ //passando qual o endereco email e o nome do usuario

        //funcao de enviar o email
        //incluir o autoload
    
    //criar o template
     //configurar o template
     $config = array(
        "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/email/", //pasta para pegar os arquivos HTML, criar essas pastas no diretorio do projeto
        "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/", //pasta criada no diretorio do projeto
        "debug"         => false // set to false to improve the speed
       );

    Tpl::configure($config);
    
    //cria a variavel, para ser acessivel para as outras classes
    $tpl = new Tpl;

    //passar os dados para o template - cria as variaveis dentro do template
    foreach ($data as $key => $value) {
        $this->tpl->assign($key, $value); //atribuição de variaveis que irão aparecer no template
    }

    $html = $tpl->draw($tplName, true);





    //Create a new PHPMailer instance
    $this->mail = new \PHPMailer; //pois o PHPMailer esta no escopo principal para encontrar a classe
    //deixar a variavel como privado, para enviar o metodo "send" a parte

    //Tell PHPMailer to use SMTP
    $this->mail->isSMTP();

    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $this->mail->SMTPDebug = 0;

    //Set the hostname of the mail server
    $this->mail->Host = 'smtp.gmail.com';
    // use
    // $mail->Host = gethostbyname('smtp.gmail.com');
    // if your network does not support SMTP over IPv6

    //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $this->mail->Port = 587;

    //Set the encryption system to use - ssl (deprecated) or tls
    $this->mail->SMTPSecure = 'tls';

    //Whether to use SMTP authentication
    $this->mail->SMTPAuth = true;

    //Username to use for SMTP authentication - use full email address for gmail
    //$mail->Username = "guilherme.eneas21@gmail.com";
    $this->mail->Username = Mailer::USERNAME; //constante do nome de usuario

    //Password to use for SMTP authentication
    //$mail->Password = "d-efeitos";
    $this->mail->Password = Mailer::PASSWORD; //constante da senha
    //Set who the message is to be sent from
    $this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

    //Set an alternative reply-to address
    //$mail->addReplyTo('replyto@example.com', 'First Last');

    //para quem o e-mail sera enviado, dados que virão do metodo
    $this->mail->addAddress($toAddress, $toName);

    //Set the subject line
    $this->mail->Subject = $subject; //assunto do email

    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    $this->mail->msgHTML($html); //HTML da página que sera renderizada com o RAINTPL

    //Replace the plain text body with one created manually
    $this->mail->AltBody = 'This is a plain-text message body';

    //Attach an image file
    //$mail->addAttachment('images/phpmailer_mini.png');

    }
    

    //metodo para o envio do email
    public function send(){
        return $this->mail->send();
    }

}

?>