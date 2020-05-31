<?php

namespace App\Entity;

use App\Repository\ConfigRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConfigRepository::class)
 */
class Config
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $google_sheets_reference;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $google_sheets_sheet_name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $google_sheets_api_key;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $facebook_app_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $facebook_secret;

    /**
     * @ORM\Column(type="string", length=400, nullable=true)
     */
    private $facebook_long_lived_access_token;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $facebook_page_id;

    /**
     * @ORM\Column(type="string", length=400, nullable=true)
     */
    private $facebook_page_token;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoogleSheetsReference(): ?string
    {
        return $this->google_sheets_reference;
    }

    public function setGoogleSheetsReference(string $google_sheets_reference): self
    {
        $this->google_sheets_reference = $google_sheets_reference;

        return $this;
    }

    public function getGoogleSheetsSheetName(): ?string
    {
        return $this->google_sheets_sheet_name;
    }

    public function setGoogleSheetsSheetName(string $google_sheets_sheet_name): self
    {
        $this->google_sheets_sheet_name = $google_sheets_sheet_name;

        return $this;
    }

    public function getGoogleSheetsApiKey(): ?string
    {
        return $this->google_sheets_api_key;
    }

    public function setGoogleSheetsApiKey(string $google_sheets_api_key): self
    {
        $this->google_sheets_api_key = $google_sheets_api_key;

        return $this;
    }

    public function getFacebookAppId(): ?string
    {
        return $this->facebook_app_id;
    }

    public function setFacebookAppId(string $facebook_app_id): self
    {
        $this->facebook_app_id = $facebook_app_id;

        return $this;
    }

    public function getFacebookSecret(): ?string
    {
        return $this->facebook_secret;
    }

    public function setFacebookSecret(string $facebook_secret): self
    {
        $this->facebook_secret = $facebook_secret;

        return $this;
    }

    public function getFacebookLongLivedAccessToken(): ?string
    {
        return $this->facebook_long_lived_access_token;
    }

    public function setFacebookLongLivedAccessToken(?string $facebook_long_lived_access_token): self
    {
        $this->facebook_long_lived_access_token = $facebook_long_lived_access_token;

        return $this;
    }

    public function getFacebookPageId(): ?string
    {
        return $this->facebook_page_id;
    }

    public function setFacebookPageId(string $facebook_page_id): self
    {
        $this->facebook_page_id = $facebook_page_id;

        return $this;
    }

    public function getFacebookPageToken(): ?string
    {
        return $this->facebook_page_token;
    }

    public function setFacebookPageToken(?string $facebook_page_token): self
    {
        $this->facebook_page_token = $facebook_page_token;

        return $this;
    }
}
