<?php

declare(strict_types=1);

namespace Localizationteam\L10nmgr\Model\Dto;

use Exception;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EmConfiguration
{
    /**
     * @var bool
     */
    protected bool $enable_hidden_languages = false;

    // Enable settings
    /**
     * @var bool
     */
    protected bool $enable_notification = false;

    /**
     * @var bool
     */
    protected bool $enable_customername = false;

    /**
     * @var bool
     */
    protected bool $enable_ftp = false;

    /**
     * @var bool
     */
    protected bool $enable_stat_hook = false;

    /**
     * @var bool
     */
    protected bool $enable_neverHideAtCopy = true;

    /**
     * @var string
     */
    protected string $disallowDoktypes = '255, ---div---';

    /**
     * @var bool
     */
    protected bool $import_dontProcessTransformations = true;

    /**
     * @var string
     */
    protected string $l10nmgr_cfg = '';

    // Load L10N manager configration
    /**
     * @var string
     */
    protected string $l10nmgr_tlangs = '';

    /**
     * @var string
     */
    protected string $email_recipient = '';

    // Define email notification
    /**
     * @var string
     */
    protected string $email_recipient_import = '';

    /**
     * @var string
     */
    protected string $email_sender = '';

    /**
     * @var string
     */
    protected string $email_sender_name = '';

    /**
     * @var string
     */
    protected string $email_sender_organisation = '';

    /**
     * @var bool
     */
    protected bool $email_attachment = false;

    /**
     * @var string
     */
    protected string $ftp_server = '';

    // Define FTP server details
    /**
     * @var string
     */
    protected string $ftp_server_path = '';

    /**
     * @var string
     */
    protected string $ftp_server_downpath = '';

    /**
     * @var string
     */
    protected string $ftp_server_username = '';

    /**
     * @var string
     */
    protected string $ftp_server_password = '';

    /**
     * @var int
     */
    protected int $service_children = 3;

    // Import service
    /**
     * @var string
     */
    protected string $service_user = '';

    /**
     * @var string
     */
    protected string $service_pwd = '';

    /**
     * @var string
     */
    protected string $service_enc = '';

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {
        if (empty($configuration)) {
            try {
                $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
                $configuration = $extensionConfiguration->get('l10nmgr');
            } catch (Exception $exception) {
                // do nothing
            }
        }

        foreach ($configuration as $key => $value) {
            if (property_exists(__CLASS__, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @return bool
     */
    public function isEnableHiddenLanguages(): bool
    {
        return $this->enable_hidden_languages;
    }

    /**
     * @return bool
     */
    public function isEnableNotification(): bool
    {
        return $this->enable_notification;
    }

    /**
     * @return bool
     */
    public function isEnableCustomername(): bool
    {
        return $this->enable_customername;
    }

    /**
     * @return bool
     */
    public function isEnableFtp(): bool
    {
        return $this->enable_ftp;
    }

    /**
     * @return bool
     */
    public function isEnableStatHook(): bool
    {
        return $this->enable_stat_hook;
    }

    /**
     * @return bool
     */
    public function isEnableNeverHideAtCopy(): bool
    {
        return $this->enable_neverHideAtCopy;
    }

    /**
     * @return string
     */
    public function getDisallowDoktypes(): string
    {
        return $this->disallowDoktypes;
    }

    /**
     * @return bool
     */
    public function isImportDontProcessTransformations(): bool
    {
        return $this->import_dontProcessTransformations;
    }

    /**
     * @return string
     */
    public function getL10NmgrCfg(): string
    {
        return $this->l10nmgr_cfg;
    }

    /**
     * @return string
     */
    public function getL10NmgrTlangs(): string
    {
        return $this->l10nmgr_tlangs;
    }

    /**
     * @return string
     */
    public function getEmailRecipient(): string
    {
        return $this->email_recipient;
    }

    /**
     * @return string
     */
    public function getEmailRecipientImport(): string
    {
        return $this->email_recipient_import;
    }

    /**
     * @return string
     */
    public function getEmailSender(): string
    {
        return $this->email_sender;
    }

    /**
     * @return string
     */
    public function getEmailSenderName(): string
    {
        return $this->email_sender_name;
    }

    /**
     * @return string
     */
    public function getEmailSenderOrganisation(): string
    {
        return $this->email_sender_organisation;
    }

    /**
     * @return bool
     */
    public function isEmailAttachment(): bool
    {
        return $this->email_attachment;
    }

    /**
     * @return string
     */
    public function getFtpServerPath(): string
    {
        return $this->ftp_server_path;
    }

    /**
     * @return string
     */
    public function getFtpServerDownPath(): string
    {
        return $this->ftp_server_downpath;
    }

    /**
     * @return int
     */
    public function getServiceChildren(): int
    {
        return $this->service_children;
    }

    /**
     * @return string
     */
    public function getServiceUser(): string
    {
        return $this->service_user;
    }

    /**
     * @return string
     */
    public function getServicePwd(): string
    {
        return $this->service_pwd;
    }

    /**
     * @return string
     */
    public function getServiceEnc(): string
    {
        return $this->service_enc;
    }

    /**
     * @return bool
     */
    public function hasFtpCredentials(): bool
    {
        return
            !empty($this->getFtpServer())
            && !empty($this->getFtpServerUsername())
            && !empty($this->getFtpServerPassword());
    }

    /**
     * @return string
     */
    public function getFtpServer(): string
    {
        return $this->ftp_server;
    }

    /**
     * @return string
     */
    public function getFtpServerUsername(): string
    {
        return $this->ftp_server_username;
    }

    /**
     * @return string
     */
    public function getFtpServerPassword(): string
    {
        return $this->ftp_server_password;
    }
}
