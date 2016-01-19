<?php
namespace IP\Code\Strata\Model;

use IP\Code\Strata\Model\Option;
use Exception;

abstract class Mailer
{
    abstract public function setDestination($destination);
    abstract protected function loadOptions();

    protected $options = array();
    protected $title;
    protected $contents;
    protected $attachedFile;
    protected $headers = array('Content-Type: text/html; charset=UTF-8');
    protected $to = array();
    protected $bcc = array();

    public function __construct()
    {
        $this->loadOptions();
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setContent($contents)
    {
        $this->contents = $contents;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function attachFile($filePath)
    {
        $this->attachedFile = $filePath;
    }

    public function send()
    {
        $headers = array_merge(
            $this->headers,
            $this->buildBCCList()
        );

        $this->setHtmlEmails();
        $status = wp_mail($this->to, $this->title, $this->contents, $headers, $this->attachedFile);
        $this->setHtmlEmails(false);

        return $status;
    }

    public function setHtmlContentType()
    {
        return 'text/html';
    }

    private function setHtmlEmails($enable = true)
    {
        if ($enable) {
            add_filter('wp_mail_content_type', array($this, 'setHtmlContentType'));
        } else {
            remove_filter('wp_mail_content_type', array($this, 'setHtmlContentType'));
        }
    }

    protected function getOptionKey($optionKey)
    {
        $optionValue = $this->options->{$optionKey};

        if (!empty($optionValue)) {
            return $optionValue;
        }
    }

    private function buildBCCList($separator = ";")
    {
        $adresses = array();

        if (count($this->bcc)) {
            foreach ($this->bcc as $email) {
                $adresses[] = 'Bcc: ' . trim($email);
            }
        }

        return $adresses;
    }
}
