<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Http\Message;

use CuyZ\Valinor;
use Psr\Http\Message;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;

/**
 * UrlMetadataFactory
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Autoconfigure(public: true)]
final readonly class UrlMetadataFactory
{
    private const REQUEST_HEADER = 'X-Warming-Request-Claim';
    private const RESPONSE_HEADER = 'X-Warming-Url-Metadata';

    private Valinor\Mapper\TreeMapper $mapper;

    public function __construct()
    {
        $this->mapper = $this->createMapper();
    }

    /**
     * Create url metadata object from authorized request.
     */
    public function createForRequest(Message\RequestInterface $request): ?UrlMetadata
    {
        // Early return if request header is missing (this usually happens on "normal"
        // frontend requests, which were not triggered by EXT:warming)
        if (!$request->hasHeader(self::REQUEST_HEADER)) {
            return null;
        }

        $requestUrl = $this->decryptHeaderValue($request, self::REQUEST_HEADER);

        // Early return if request is invalid (encrypted header value does not match request url)
        if ($requestUrl !== (string)$request->getUri()) {
            return null;
        }

        return new UrlMetadata();
    }

    /**
     * Decrypt and hydrate url metadata object from given response.
     */
    public function createFromResponse(Message\ResponseInterface $response): ?UrlMetadata
    {
        if (!$response->hasHeader(self::RESPONSE_HEADER)) {
            return null;
        }

        $headerValue = $this->decryptHeaderValue($response, self::RESPONSE_HEADER);

        if ($headerValue === null) {
            return null;
        }

        try {
            $source = new Valinor\Mapper\Source\JsonSource($headerValue);

            return $this->mapper->map(UrlMetadata::class, $source);
        } catch (Valinor\Mapper\MappingError|Valinor\Mapper\Source\Exception\InvalidSource) {
            return null;
        }
    }

    /**
     * Decrypt and hydrate url metadata object from given response headers.
     *
     * @param array<string> $headers
     */
    public function createFromResponseHeaders(array $headers): ?UrlMetadata
    {
        $response = new Core\Http\Response(headers: $this->parseHeaderLines($headers));

        return $this->createFromResponse($response);
    }

    /**
     * Enrich (prepare) given request for further url metadata enrichment.
     *
     * @template T of Message\RequestInterface
     * @param T $request
     * @return T
     */
    public function enrichRequest(Message\RequestInterface $request): Message\RequestInterface
    {
        return $request->withHeader(
            self::REQUEST_HEADER,
            $this->encryptHeaderValue((string)$request->getUri()),
        );
    }

    /**
     * Enrich given response with decrypted url metadata.
     *
     * @template T of Message\ResponseInterface
     * @param T $response
     * @return T
     */
    public function enrichResponse(
        Message\ResponseInterface $response,
        UrlMetadata $metadata,
    ): Message\ResponseInterface {
        return $response->withHeader(self::RESPONSE_HEADER, $this->encryptHeaderValue($metadata));
    }

    /**
     * Enrich given response with decrypted url metadata.
     */
    public function enrichException(
        Core\Http\ImmediateResponseException|Core\Error\Http\StatusException $exception,
        UrlMetadata $metadata,
    ): void {
        if ($exception instanceof Core\Http\ImmediateResponseException) {
            $this->injectViaReflection(
                $exception,
                $this->enrichResponse($exception->getResponse(), $metadata),
                'response',
            );
        } else {
            $statusHeaders = $exception->getStatusHeaders();
            $statusHeaders[] = sprintf('%s: %s', self::RESPONSE_HEADER, $this->encryptHeaderValue($metadata));

            $this->injectViaReflection($exception, $statusHeaders, 'statusHeaders');
        }
    }

    private function encryptHeaderValue(string|\JsonSerializable $value): string
    {
        if ($value instanceof \JsonSerializable) {
            $value = (string)json_encode($value);
        }

        if (class_exists(Core\Crypto\HashService::class)) {
            $hashValue = Core\Utility\GeneralUtility::makeInstance(Core\Crypto\HashService::class)->appendHmac(
                $value,
                self::class,
            );
        } else {
            // @todo Remove once support for TYPO3 v12 is dropped
            /* @phpstan-ignore classConstant.deprecatedClass, method.deprecatedClass */
            $hashValue = Core\Utility\GeneralUtility::makeInstance(Extbase\Security\Cryptography\HashService::class)->appendHmac(
                $value,
            );
        }

        return base64_encode($hashValue);
    }

    private function decryptHeaderValue(Message\MessageInterface $message, string $headerName): ?string
    {
        $value = base64_decode($message->getHeader($headerName)[0] ?? '', true);

        if ($value === false || $value === '') {
            return null;
        }

        try {
            if (class_exists(Core\Crypto\HashService::class)) {
                return Core\Utility\GeneralUtility::makeInstance(Core\Crypto\HashService::class)
                    ->validateAndStripHmac($value, self::class)
                ;
            }

            // @todo Remove once support for TYPO3 v12 is dropped
            /* @phpstan-ignore classConstant.deprecatedClass, method.deprecatedClass */
            return Core\Utility\GeneralUtility::makeInstance(Extbase\Security\Cryptography\HashService::class)
                ->validateAndStripHmac($value)
            ;
        } catch (Core\Exception) {
            return null;
        }
    }

    /**
     * @param array<string> $statusHeaders
     * @return array<string, list<string>>
     */
    private function parseHeaderLines(array $statusHeaders): array
    {
        $headers = [];

        foreach ($statusHeaders as $headerLine) {
            if (str_contains($headerLine, ':')) {
                [$headerName, $headerValue] = explode(':', $headerLine, 2);
                $headers[$headerName] ??= [];
                $headers[$headerName][] = trim($headerValue);
            }
        }

        return $headers;
    }

    private function injectViaReflection(object $object, mixed $value, string $propertyName): void
    {
        $reflectionObject = new \ReflectionObject($object);
        $reflectionProperty = $reflectionObject->getProperty($propertyName);
        $reflectionProperty->setValue($object, $value);
    }

    /**
     * @return Valinor\Mapper\TreeMapper
     */
    private function createMapper(): Valinor\Mapper\TreeMapper
    {
        return (new Valinor\MapperBuilder())
            ->mapper()
        ;
    }
}
