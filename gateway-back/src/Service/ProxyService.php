<?php
declare(strict_types=1);

namespace photopro\gateway\Service;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProxyService
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly int $timeoutSeconds = 10
    ) {
    }

    /**
     * @return array{status:int, headers:array<string,array<int,string>>, body:string}
     */
    public function forward(ServerRequestInterface $request, string $baseUri, string $targetPath): array
    {
        $headers = $request->getHeaders();
        unset($headers['Host'], $headers['Content-Length']);

        $options = [
            'http_errors' => false,
            'headers' => $headers,
            'query' => $request->getQueryParams(),
            'timeout' => $this->timeoutSeconds,
        ];

        $uploadedFiles = $request->getUploadedFiles();
        if (!empty($uploadedFiles)) {
            // Supprimer Content-Type sans distinction de casse — Guzzle le régénère avec le bon boundary
            $options['headers'] = array_filter(
                $options['headers'],
                static fn (string $key): bool => strtolower($key) !== 'content-type',
                ARRAY_FILTER_USE_KEY
            );
            $options['multipart'] = $this->buildMultipart($request->getParsedBody(), $uploadedFiles);
        } else {
            $body = (string) $request->getBody();
            if ($body !== '') {
                $options['body'] = $body;
            }
        }

        $uri = rtrim($baseUri, '/') . '/' . ltrim($targetPath, '/');
        $upstreamResponse = $this->httpClient->request($request->getMethod(), $uri, $options);

        return [
            'status' => $upstreamResponse->getStatusCode(),
            'headers' => $upstreamResponse->getHeaders(),
            'body' => (string) $upstreamResponse->getBody(),
        ];
    }

    /**
     * @param array<string, mixed>|null $parsedBody
     * @param array<string, UploadedFileInterface|array> $uploadedFiles
     * @return array<int, array{name:string, contents:string, filename?:string, headers?:array<string,string>}>
     */
    private function buildMultipart(?array $parsedBody, array $uploadedFiles): array
    {
        $multipart = [];

        if (is_array($parsedBody)) {
            foreach ($parsedBody as $name => $value) {
                if (is_scalar($value) || $value === null) {
                    $multipart[] = [
                        'name' => (string) $name,
                        'contents' => (string) $value,
                    ];
                }
            }
        }

        foreach ($uploadedFiles as $name => $uploadedFile) {
            $this->appendUploadedFile($multipart, (string) $name, $uploadedFile);
        }

        return $multipart;
    }

    /**
     * @param array<int, array{name:string, contents:string, filename?:string, headers?:array<string,string>}> $multipart
     * @param UploadedFileInterface|array $uploadedFile
     */
    private function appendUploadedFile(array &$multipart, string $name, UploadedFileInterface|array $uploadedFile): void
    {
        if (is_array($uploadedFile)) {
            foreach ($uploadedFile as $subName => $subFile) {
                $this->appendUploadedFile($multipart, $name . '[' . $subName . ']', $subFile);
            }

            return;
        }

        $stream = $uploadedFile->getStream();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $part = [
            'name' => $name,
            'contents' => $stream->getContents(),
        ];

        $clientFileName = $uploadedFile->getClientFilename();
        if (is_string($clientFileName) && $clientFileName !== '') {
            $part['filename'] = $clientFileName;
        }

        $clientMediaType = $uploadedFile->getClientMediaType();
        if (is_string($clientMediaType) && $clientMediaType !== '') {
            $part['headers'] = ['Content-Type' => $clientMediaType];
        }

        $multipart[] = $part;
    }
}
