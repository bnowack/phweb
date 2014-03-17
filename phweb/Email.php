<?php

namespace phweb;

use \PHPMailer;

/**
 * Class for sending mails
 *
 */
class Email {

    protected $app;

    public function __construct($app) {
        $this->app = $app;
    }
    
    protected function completeMessage($msg) {
        $msg['smtp-host'] = $this->app->config('smtp/host');
        $msg['smtp-port'] = $this->app->config('smtp/port');
		if (empty($msg['smtp-user'])) {
			$msg['smtp-user'] = $this->app->config('smtp/user');
		}
		if (empty($msg['smtp-pass'])) {
			$msg['smtp-pass'] = $this->app->config('smtp/password');
		}
		if (empty($msg['from'])) {
			$msg['from'] = array(
                'address' => $this->app->config('smtp/from-address'),
                'name' => $this->app->config('smtp/from-name')
            );
		}
		if (empty($msg['to'])) {
			$msg['to'] = array(
                array(
                    'address' => $this->app->config('smtp/to-address'),
                    'name' => $this->app->config('smtp/to-name')
                )
            );
		}
		if (empty($msg['plain-template'])) {
            $msg['plain-template'] = $this->app->config('smtp/plain-template');
        }
		if (empty($msg['html-template'])) {
            $msg['html-template'] = $this->app->config('smtp/html-template');
        }
        $msg['website-url'] = "http://{$this->app->request->host}{$this->app->request->base}";
		return $msg;
    }
	
	public function send($msg, $debug = false) {
        $msg = $this->completeMessage($msg);
		$mailer = new PHPMailer();
        $this->setHeaders($mailer, $msg);
        $this->setBody($mailer, $msg);
        
        $result = array(
            'debug' => $debug,
            'headers' => StringUtils::toUtf8($mailer->CreateHeader())
        );

        if ($debug) {
            $result['success'] = true;
		}
        else {
            $result['success'] = $mailer->Send();
            if (!$result['success']) {
                $result['error'] = $mailer->ErrorInfo;
            }
        }
        return $result;
    }
    
    protected function setHeaders($mailer, $msg) {
		$mailer->IsSMTP();
		$mailer->Host = $msg['smtp-host'];
		$mailer->Port = $msg['smtp-port'];
		$mailer->SMTPAuth = true;
		$mailer->Username = $msg['smtp-user'];
		$mailer->Password = $msg['smtp-pass'];
		//$mailer->SMTPSecure = 'tls';
		//$mailer->SMTPDebug  = 1;

		$mailer->From = $msg['from']['address'];
		$mailer->FromName = $msg['from']['name'];
		foreach ($msg['to'] as $to) {
			$mailer->AddAddress($to['address'], isset($to['name']) ? $to['name'] : null);
		}
		if (!empty($msg['replyTo'])) {
			$mailer->AddReplyTo($msg['replyTo']['address'], $msg['replyTo']['name']);
		}
		if (!empty($msg['cc'])) {
			foreach ($msg['cc'] as $cc) {
				$mailer->AddCC($cc['address'], isset($cc['name']) ? $cc['name'] : null);
			}
		}
		if (!empty($msg['bcc'])) {
			foreach ($msg['bcc'] as $bcc) {
				$mailer->AddBCC($bcc['address'], isset($bcc['name']) ? $bcc['name'] : null);
			}
		}
    }
    
    protected function setBody($mailer, $msg) {
        // subject
		$mailer->Subject = utf8_decode(StringUtils::toUtf8($msg['subject']));
        // body
		$mailer->WordWrap = 80;
        $htmlBody = $this->getHtmlBody($msg);
        $plainBody = $this->getPlainBody($msg);
		if (isset($msg['isHtml']) && $msg['isHtml']) {
    		$mailer->IsHTML(true);
    		$mailer->Body = $htmlBody;
            $mailer->AltBody = $plainBody;
        }
        else {
            $mailer->Body = $plainBody;
        }
	}
    
    protected function getHtmlBody($msg) {
		$msg['body'] = preg_replace('/[\r\n]+[ \t]+/', "\n",    // remove tabs and empty lines
            str_replace("\n", '<br />' . "\n", $msg['body'])    // replace line breaks with markup
        );
        $template = new Template($msg['html-template'], $msg);
        $template->execute();
        return utf8_decode(StringUtils::toUtf8($template->result));
    }
    
    protected function getPlainBody($msg) {
        $msg['body'] = preg_replace('/&nbsp;/', " ",    // replace nbsp entities
            preg_replace('/[\r\n]+[ \t]+/', "\n",       // remove tabs and empty lines after tag removal
                strip_tags($msg['body'])                // remove html markup
            )
        );
        $template = new Template($msg['plain-template'], $msg);
        $template->execute();
        return utf8_decode(StringUtils::toUtf8($template->result));
    }
    
}
