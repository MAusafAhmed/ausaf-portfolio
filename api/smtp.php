<?php
/**
 * SimpleSMTP -- a tiny SMTP client. No library or Composer required.
 * Works with the SMTP servers of Gmail, Outlook, Hostinger, cPanel and others.
 */
class SimpleSMTP
{
    private $host;
    private $port;
    private $secure;      // 'tls' | 'ssl' | ''
    private $user;
    private $pass;
    private $verify;
    private $timeout;
    private $sock = null;
    private $transcript = array();

    public function __construct($host, $port, $secure, $user, $pass, $verify = true, $timeout = 20)
    {
        $this->host    = $host;
        $this->port    = (int) $port;
        $this->secure  = strtolower((string) $secure);
        $this->user    = $user;
        $this->pass    = $pass;
        $this->verify  = (bool) $verify;
        $this->timeout = (int) $timeout;
    }

    public function getTranscript()
    {
        return implode("\n", $this->transcript);
    }

    /**
     * @return array(ok => bool, error => string)
     */
    public function send($fromEmail, $fromName, $toEmail, $subject, $textBody, $htmlBody = '', $replyTo = '')
    {
        try {
            $this->connect();
            $this->ehlo();

            if ($this->secure === 'tls') {
                $this->cmd('STARTTLS', 220);
                $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                    $crypto |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                }
                if (!@stream_socket_enable_crypto($this->sock, true, $crypto)) {
                    throw new Exception('STARTTLS handshake fail. Agar localhost par ho to config.php me SMTP_VERIFY_CERT ko false kar ke dekho.');
                }
                $this->ehlo();   // EHLO must be repeated after TLS starts
            }

            if ($this->user !== '') {
                $this->authenticate();
            }

            $this->cmd('MAIL FROM:<' . $fromEmail . '>', 250);
            $this->cmd('RCPT TO:<' . $toEmail . '>', 250);
            $this->cmd('DATA', 354);

            $data = $this->buildMessage($fromEmail, $fromName, $toEmail, $subject, $textBody, $htmlBody, $replyTo);
            $this->write($data . "\r\n.\r\n");
            $this->expect(250);

            $this->cmd('QUIT', 221, true);
            $this->close();

            return array('ok' => true, 'error' => '');
        } catch (Exception $ex) {
            $this->close();
            return array('ok' => false, 'error' => $ex->getMessage());
        }
    }

    /* ------------------------------------------------------------ */

    private function connect()
    {
        $target = ($this->secure === 'ssl' ? 'ssl://' : '') . $this->host . ':' . $this->port;

        $ctx = stream_context_create(array('ssl' => array(
            'verify_peer'       => $this->verify,
            'verify_peer_name'  => $this->verify,
            'allow_self_signed' => !$this->verify,
        )));

        $errno = 0;
        $errstr = '';
        $this->sock = @stream_socket_client($target, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $ctx);

        if (!$this->sock) {
            throw new Exception('SMTP connection failed (' . $this->host . ':' . $this->port . '): ' . ($errstr ?: 'the server did not respond') . '. Check your firewall and port settings.');
        }

        stream_set_timeout($this->sock, $this->timeout);
        $this->expect(220);
    }

    private function ehlo()
    {
        $name = isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] !== '' ? $_SERVER['SERVER_NAME'] : 'localhost';
        try {
            $this->cmd('EHLO ' . $name, 250);
        } catch (Exception $ex) {
            $this->cmd('HELO ' . $name, 250);
        }
    }

    private function authenticate()
    {
        try {
            $this->cmd('AUTH LOGIN', 334);
            $this->cmd(base64_encode($this->user), 334);
            $this->cmd(base64_encode($this->pass), 235);
        } catch (Exception $ex) {
            throw new Exception('SMTP login failed: ' . $ex->getMessage() . ' -- Gmail does not accept a regular password, use an App Password.');
        }
    }

    private function buildMessage($fromEmail, $fromName, $toEmail, $subject, $text, $html, $replyTo)
    {
        $boundary = 'bnd_' . bin2hex(random_bytes(8));

        $headers   = array();
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'From: ' . $this->encodeName($fromName) . ' <' . $fromEmail . '>';
        $headers[] = 'To: <' . $toEmail . '>';
        if ($replyTo !== '') {
            $headers[] = 'Reply-To: <' . $replyTo . '>';
        }
        $headers[] = 'Subject: ' . $this->encodeHeader($subject);
        $headers[] = 'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . $this->host . '>';
        $headers[] = 'MIME-Version: 1.0';

        if ($html === '') {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $headers[] = 'Content-Transfer-Encoding: base64';
            $body = chunk_split(base64_encode($text));
        } else {
            $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
            $body = "--$boundary\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode($text)) . "\r\n"
                . "--$boundary\r\n"
                . "Content-Type: text/html; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode($html)) . "\r\n"
                . "--$boundary--\r\n";
        }

        return implode("\r\n", $headers) . "\r\n\r\n" . $this->dotStuff($body);
    }

    /* A line starting with "." must be escaped to ".." or the message ends there */
    private function dotStuff($body)
    {
        $body = str_replace(array("\r\n", "\r", "\n"), "\r\n", $body);
        return preg_replace('/^\./m', '..', $body);
    }

    private function encodeHeader($value)
    {
        if (preg_match('/^[\x20-\x7E]*$/', $value)) {
            return $value;
        }
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function encodeName($name)
    {
        if (preg_match('/^[\x20-\x7E]*$/', $name)) {
            return '"' . str_replace('"', '', $name) . '"';
        }
        return $this->encodeHeader($name);
    }

    private function cmd($command, $expected, $ignoreFailure = false)
    {
        $this->write($command . "\r\n");
        $this->transcript[] = '> ' . (stripos($command, 'AUTH') === 0 ? 'AUTH ***' : $command);
        try {
            return $this->expect($expected);
        } catch (Exception $ex) {
            if ($ignoreFailure) {
                return '';
            }
            throw $ex;
        }
    }

    private function write($payload)
    {
        if (!$this->sock || fwrite($this->sock, $payload) === false) {
            throw new Exception('The SMTP connection was lost while writing.');
        }
    }

    private function expect($code)
    {
        $reply = '';
        while (($line = fgets($this->sock, 515)) !== false) {
            $reply .= $line;
            // Multi-line reply: "250-..." continue, "250 ..." last line
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $meta = stream_get_meta_data($this->sock);
        if (!empty($meta['timed_out'])) {
            throw new Exception('The SMTP server did not respond in time.');
        }

        $this->transcript[] = '< ' . trim($reply);

        if ((int) substr($reply, 0, 3) !== (int) $code) {
            throw new Exception('SMTP ne ' . $code . ' expect kiya, mila: ' . trim($reply));
        }
        return $reply;
    }

    private function close()
    {
        if ($this->sock) {
            @fclose($this->sock);
            $this->sock = null;
        }
    }
}
