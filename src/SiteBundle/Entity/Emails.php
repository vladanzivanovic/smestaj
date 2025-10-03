<?php

namespace SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Emails
 *
 * @ORM\Table(name="emails")
 * @ORM\Entity(repositoryClass="SiteBundle\Repository\EmailsRepository")
 */
class Emails
{
    const EMAIL_SUCCESS = 'SENT';
    const EMAIL_FAILED = 'FAILED';
    const EMAIL_SEEN = 'SEEN';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fromEmail", type="string", length=255, nullable=false)
     */
    private $fromemail;

    /**
     * @var string
     *
     * @ORM\Column(name="toEmail", type="string", length=255, nullable=false)
     */
    private $toemail;

    /**
     * @var string
     *
     * @ORM\Column(name="rawData", type="text", nullable=true)
     */
    private $rawdata;

    /**
     * @var string
     *
     * @ORM\Column(name="errorMessage", type="text", length=65535, nullable=true)
     */
    private $errormessage;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=150, nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="script", type="string", length=200, nullable=false)
     */
    private $script;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sysCreatedUTC", type="datetime", nullable=true)
     */
    private $syscreatedutc;

    /**
     * @var string|null
     *
     * @ORM\Column(name="code", type="string", length=5)
     */
    private $code;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fromemail
     *
     * @param string $fromemail
     *
     * @return Emails
     */
    public function setFromemail($fromemail)
    {
        $this->fromemail = $fromemail;

        return $this;
    }

    /**
     * Get fromemail
     *
     * @return string
     */
    public function getFromemail()
    {
        return $this->fromemail;
    }

    /**
     * Set toemail
     *
     * @param string $toemail
     *
     * @return Emails
     */
    public function setToemail($toemail)
    {
        $this->toemail = $toemail;

        return $this;
    }

    /**
     * Get toemail
     *
     * @return string
     */
    public function getToemail()
    {
        return $this->toemail;
    }

    /**
     * Set rawdata
     *
     * @param string $rawdata
     *
     * @return Emails
     */
    public function setRawdata($rawdata)
    {
        $this->rawdata = $rawdata;

        return $this;
    }

    /**
     * Get rawdata
     *
     * @return string
     */
    public function getRawdata()
    {
        return $this->rawdata;
    }
    /**
     * Set errormessage
     *
     * @param string $errormessage
     *
     * @return Emails
     */
    public function setErrormessage($errormessage)
    {
        $this->errormessage = $errormessage;

        return $this;
    }

    /**
     * Get errormessage
     *
     * @return string
     */
    public function getErrormessage()
    {
        return $this->errormessage;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Emails
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set script
     *
     * @param string $script
     *
     * @return Emails
     */
    public function setScript($script)
    {
        $this->script = $script;

        return $this;
    }

    /**
     * Get script
     *
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Set syscreatedutc
     *
     * @param \DateTime $syscreatedutc
     *
     * @return Emails
     */
    public function setSyscreatedutc($syscreatedutc)
    {
        $this->syscreatedutc = $syscreatedutc;

        return $this;
    }

    /**
     * Get syscreatedutc
     *
     * @return \DateTime
     */
    public function getSyscreatedutc()
    {
        return $this->syscreatedutc;
    }

    /**
     * Set code
     *
     * @param string|null $code
     *
     * @return Emails
     */
    public function setCode(?string $code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }
}
