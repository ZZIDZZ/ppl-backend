<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendLoginInfo extends Mailable
{
    use Queueable, SerializesModels;
    private $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = $this->data;

        // # Mailing
        // $username = 'api';
        // $password = '08eb705d76cb2f03cd6814014d05f1e5-787e6567-400f8423';
        // $URL = 'https://api.mailgun.net/v3/mail.zzidzz.tech/messages';
        // $ch = curl_init();
        // $output = curl_exec($ch);
        // $ch = curl_init();
        // $payload_email = view('otp-email')->with('otp', $requestToken)->render();
        // $post = [
        //     "from" => "Rayya Mandiri Sejahtera <gamer@mail.zzidzz.tech>",
        //     "to" => $input['email'],
        //     "subject" => 'OTP Login Koperasi RMS',
        //     "html" => $payload_email
        // ];
    
        // curl_setopt($ch, CURLOPT_URL,$URL);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        // curl_setopt($ch, CURLOPT_POST,1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    
        // $result=curl_exec ($ch);
        // $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // Log::channel('email')->debug(Carbon::now() . $result);
        // if(!$result){
        //     throw new CoreException(__("message.emailSendFailed"), 500);
        // }

        // curl_close ($ch);
        
        return $this->view('monark-login')->with('data', $data)->subject('MONARK - Info Login');
    }
}
