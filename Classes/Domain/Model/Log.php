<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Domain\Model;

use DateTimeInterface;
use EliasHaeussler\Typo3Warming\Domain;
use TYPO3\CMS\Extbase;

/**
 * Log
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @final
 */
class Log extends Extbase\DomainObject\AbstractEntity
{
    final public const TABLE_NAME = 'tx_warming_domain_model_log';

    protected string $requestId = '';
    protected ?DateTimeInterface $date = null;
    protected string $url = '';
    protected string $message = '';
    protected ?Domain\Type\StateType $state = null;
    protected ?string $sitemap = null;
    protected ?int $site = null;
    protected ?int $siteLanguage = null;

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function setRequestId(string $requestId): self
    {
        $this->requestId = $requestId;
        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getState(): ?Domain\Type\StateType
    {
        return $this->state;
    }

    public function setState(?Domain\Type\StateType $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getSitemap(): ?string
    {
        return $this->sitemap;
    }

    public function setSitemap(?string $sitemap): self
    {
        $this->sitemap = $sitemap;

        return $this;
    }

    public function getSite(): ?int
    {
        return $this->site;
    }

    public function setSite(?int $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getSiteLanguage(): ?int
    {
        return $this->siteLanguage;
    }

    public function setSiteLanguage(?int $siteLanguage): self
    {
        $this->siteLanguage = $siteLanguage;

        return $this;
    }
}
