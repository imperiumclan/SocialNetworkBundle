<?php

namespace ICS\SocialNetworkBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractSocialClient
{
    protected $client;

    protected $container;

    protected $headers;

    protected $cookie;

    protected function __construct(ContainerInterface $container, string $socialNetworkName, HttpClientInterface $client)
    {
        $store = new Store($container->getParameter('kernel.project_dir') . '/var/cache/WebServices/' . $socialNetworkName . '/');
        $this->client = $client;

        $this->headers = [
            'headers' => [
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Accept-Language' => 'fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                // 'TE' => 'TE	Trailers',
                // 'Upgrade-Insecure-Requests' => '1',
                // 'Host' => 'www.instagram.com',
                // 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:83.0) Gecko/20100101 Firefox/83.0',
                'User-Agent' => 'Mozilla/5.0 (Linux; U; Android 4.4.2; en-us; SCH-I535 Build/KOT49H) ',
                'Referer ' => 'https://www.instagram.com',
                'Cookie' => $this->cookie,
            ],
        ];

        $this->client = new CachingHttpClient($this->client, $store);
        $this->container = $container;
    }

    public function getApiUrl($url, $requestOptions = [], bool $raw = false)
    {
        $options = '';
        if (count($requestOptions) > 0) {
            $options = '?';
            foreach ($requestOptions as $key => $opt) {
                $options .= $key . '=' . $opt . '&';
            }
            $options = substr($options, 0, strlen($options) - 1);
        }

        $response = $this->client->request('GET', $url . $options, [
            'max_redirects' => 5,
        ]);
        $contentType = $response->getHeaders()['content-type'][0];

        $this->cookie = '';

        if ($raw) {
            return $response->getContent();
        } elseif (200 == $response->getStatusCode() && 'application/json; charset=utf-8' == $contentType) {
            return json_decode($response->getContent());
        } elseif (200 == $response->getStatusCode() && 'text/html; charset=utf-8' == $contentType) {
            $publicPath = $this->container->getParameter('kernel.project_dir') . '/public/apiError/';
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0775, true);
            }

            $fileCount = count(scandir($publicPath));
            $fileCount++;
            $content = $response->getContent();
            $filePath = $publicPath . '/Error_' . $fileCount . '.html';
            file_put_contents($filePath, $url . $options . (string)$content);

            echo "Error HTML response. See @Url: /apiError/" . basename($filePath) . "\n";
            return null;
        }



        return null;
    }

    abstract public function search(string $seacrh, $verifiedOnly = false);
}
