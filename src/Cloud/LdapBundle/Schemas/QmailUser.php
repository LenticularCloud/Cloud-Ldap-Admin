<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * olcObjectClasses: {0}( 1.3.6.1.4.1.7914.1.2.2.1 NAME 'qmailUser' DESC 'QMail-L
 * DAP User' SUP top AUXILIARY
 * MUST mail
 * MAY ( uid $ mailMessageStore $ homeDire
 * ctory $ userPassword $ mailAlternateAddress $ qmailUID $ qmailGID $ mailHost
 * $ mailForwardingAddress $ deliveryProgramPath $ qmailDotMode $ deliveryMode $
 * mailReplyText $ accountStatus $ qmailAccountPurge $ mailQuotaSize $ mailQuot
 * aCount $ mailSizeMax ) )
 * @LDAP\Schema()
 */
class QmailUser
{
    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $mail;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $uid;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $homeDirectory;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $userPassword;

    /**
     * @var ArrayCollection
     *
     * @LDAP\Attribute(type="array")
     */
    private $mailAlternateAddress;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $qmailUID;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $mailHost;

    /**
     * @var ArrayCollection
     *
     * @LDAP\Attribute(type="array")
     */
    private $mailForwardingAddress;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $deliveryProgramPath;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $qmailDotMode;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $deliveryMode;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $mailReplyText;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $accountStatus;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $qmailAccountPurge;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $mailQuotaSize;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $mailQuotaCount;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $mailSizeMax;

    /**
     * @return string
     */
    public function getMailReplyText()
    {
        return $this->mailReplyText->get();
    }

    /**
     * @param string $mailReplyText
     * @return QmailUser
     */
    public function setMailReplyText($mailReplyText)
    {
        $this->mailReplyText->set($mailReplyText);
        return $this;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid->get();
    }

    /**
     * @param string $uid
     * @return QmailUser
     */
    public function setUid($uid)
    {
        $this->uid->set($uid);
        return $this;
    }

    /**
     * @return string
     */
    public function getHomeDirectory()
    {
        return $this->homeDirectory->get();
    }

    /**
     * @param string $homeDirectory
     * @return QmailUser
     */
    public function setHomeDirectory($homeDirectory)
    {
        $this->homeDirectory->set($homeDirectory);
        return $this;
    }

    /**
     * @return string
     */
    public function getUserPassword()
    {
        return $this->userPassword->get();
    }

    /**
     * @param string $userPassword
     * @return QmailUser
     */
    public function setUserPassword($userPassword)
    {
        $this->userPassword->set($userPassword);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getMailAlternateAddresss()
    {
        return $this->mailAlternateAddress->map(function($attr){return $attr->get();})->getValues();
    }

    /**
     * @param string $mailAlternateAddress
     * @return QmailUser
     */
    public function addMailAlternateAddress($mailAlternateAddress)
    {
        $this->mailAlternateAddress->add(new Attribute($mailAlternateAddress));
        return $this;
    }

    /**
     * @param string $mailAlternateAddress
     * @return QmailUser
     */
    public function removeMailAlternateAddress($mailAlternateAddress)
    {
        foreach ($this->mailAlternateAddress as $_mailAlternateAddres) {
            if($_mailAlternateAddres->get()===$mailAlternateAddress) {
                $this->mailAlternateAddress->removeElement($_mailAlternateAddres);
                return $this;
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getQmailUID()
    {
        return $this->qmailUID->get();
    }

    /**
     * @param string $qmailUID
     * @return QmailUser
     */
    public function setQmailUID($qmailUID)
    {
        $this->qmailUID->set($qmailUID);
        return $this;
    }

    /**
     * @return string
     */
    public function getQmailGID()
    {
        return $this->qmailGID->get();
    }

    /**
     * @param string $qmailGID
     * @return QmailUser
     */
    public function setQmailGID($qmailGID)
    {
        $this->qmailGID->set($qmailGID);
        return $this;
    }

    /**
     * @return string
     */
    public function getMailHost()
    {
        return $this->mailHost->get();
    }

    /**
     * @param string $mailHost
     * @return QmailUser
     */
    public function setMailHost($mailHost)
    {
        $this->mailHost->set($mailHost);
        return $this;
    }

    /**
     * @return string
     */
    public function getMailForwardingAddress()
    {
        return $this->mailForwardingAddress->get();
    }

    /**
     * @param string $mailForwardingAddress
     * @return QmailUser
     */
    public function setMailForwardingAddress($mailForwardingAddress)
    {
        $this->mailForwardingAddress->set($mailForwardingAddress);
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryProgramPath()
    {
        return $this->deliveryProgramPath->get();
    }

    /**
     * @param string $deliveryProgramPath
     * @return QmailUser
     */
    public function setDeliveryProgramPath($deliveryProgramPath)
    {
        $this->deliveryProgramPath->set($deliveryProgramPath);
        return $this;
    }

    /**
     * @return string
     */
    public function getQmailDotMode()
    {
        return $this->qmailDotMode->get();
    }

    /**
     * @param string $qmailDotMode
     * @return QmailUser
     */
    public function setQmailDotMode($qmailDotMode)
    {
        $this->qmailDotMode->set($qmailDotMode);
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryMode()
    {
        return $this->deliveryMode->get();
    }

    /**
     * @param string $deliveryMode
     * @return QmailUser
     */
    public function setDeliveryMode($deliveryMode)
    {
        $this->deliveryMode->set($deliveryMode);
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountStatus()
    {
        return $this->accountStatus->get();
    }

    /**
     * @param string $accountStatus
     * @return QmailUser
     */
    public function setAccountStatus($accountStatus)
    {
        $this->accountStatus->set($accountStatus);
        return $this;
    }

    /**
     * @return string
     */
    public function getQmailAccountPurge()
    {
        return $this->qmailAccountPurge->get();
    }

    /**
     * @param string $qmailAccountPurge
     * @return QmailUser
     */
    public function setQmailAccountPurge($qmailAccountPurge)
    {
        $this->qmailAccountPurge->set($qmailAccountPurge);
        return $this;
    }

    /**
     * @return string
     */
    public function getMailQuotaSize()
    {
        return $this->mailQuotaSize->get();
    }

    /**
     * @param string $mailQuotaSize
     * @return QmailUser
     */
    public function setMailQuotaSize($mailQuotaSize)
    {
        $this->mailQuotaSize->set($mailQuotaSize);
        return $this;
    }

    /**
     * @return string
     */
    public function getMailQuotaCount()
    {
        return $this->mailQuotaCount->get();
    }

    /**
     * @param string $mailQuotaCount
     * @return QmailUser
     */
    public function setMailQuotaCount($mailQuotaCount)
    {
        $this->mailQuotaCount->set($mailQuotaCount);
        return $this;
    }

    /**
     * @return string
     */
    public function getMailSizeMax()
    {
        return $this->mailSizeMax->get();
    }

    /**
     * @param string $mailSizeMax
     * @return QmailUser
     */
    public function setMailSizeMax($mailSizeMax)
    {
        $this->mailSizeMax->set($mailSizeMax);
        return $this;
    }

    /**
     * @return string
     *
     * @Assert\NotBlank()
     */
    public function getMail()
    {
        return $this->mail->get();
    }

    /**
     * @param string $mail
     * @return QmailUser
     */
    public function setMail($mail)
    {
        $this->mail->set($mail);
        return $this;
    }
}