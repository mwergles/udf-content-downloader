<?php

namespace Downloader\Service;

use Downloader\Exception\UnavailableException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Created by PhpStorm.
 * User: wergles
 * Date: 13/01/17
 * Time: 14:46
 */
class DownloadService
{

    const BASE_URL = 'https://arquivos.cruzeirodosulvirtual.com.br/presenter/';

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $outputDir;

    /**
     * @var string
     */
    protected $viewer;

    /**
     * DownloadService constructor.
     * @param Client $httpClient
     */
    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $path
     * @throws UnavailableException
     * @throws \RuntimeException
     */
    public function download(string $path)
    {
        $dataPath = $path . DS . 'data';
        if (!@mkdir($dataPath, 0777, true) && !is_dir($dataPath)) {
            throw new \RuntimeException('Could not create ' . $dataPath);
        }

        $this->downloadViewer($dataPath);
        $this->downloadData($dataPath);
        $this->downloadStaticFiles($path);
    }

    /**
     * @param $path
     * @throws \Downloader\Exception\UnavailableException
     * @throws \RuntimeException
     */
    private function downloadViewer($path)
    {
        $dataUrl = self::BASE_URL . $path . '/viewer.xml';
        $file = ROOT . DS . $path . DS . 'viewer.xml';
        $this->downloadFile($dataUrl, $file);
        $this->viewer = file_get_contents($file);
    }

    /**
     * @param string $path
     * @throws \Downloader\Exception\UnavailableException
     * @throws \RuntimeException
     */
    private function downloadStaticFiles(string $path)
    {
        $url = self::BASE_URL . $path . '/';

        $files = [
            'loadflash.js',
            'viewer.swf',
            'components.swf'
        ];

        foreach ($files as $fileName) {
            $file = ROOT . DS . $path . DS . $fileName;
            $this->downloadFile($url . $fileName, $file);
        }
    }

    /**
     * @param $path
     * @throws \Downloader\Exception\UnavailableException
     * @throws \RuntimeException
     */
    private function downloadData($path)
    {
        foreach ($this->getDataFiles() as $fileName) {
            $url = self::BASE_URL . $path . '/' . $fileName;
            $file = ROOT . DS . $path . DS . $fileName;
            $this->downloadFile($url, $file);
        }
    }

    /**
     * @return array
     */
    private function getDataFiles()
    {
        // we don't care about xml, we just want the files.
        $data = [];

        $er = '#.*"(.*?\.(?:mp3|swf|flv))".*#iu';
        if (preg_match_all($er, $this->viewer, $matches)) {
            $data = array_unique($matches[1]);
        }

        return $data;
    }

    /**
     * @param string $url
     * @param string $file
     * @param bool $fromBody
     * @throws UnavailableException
     * @throws \RuntimeException
     */
    private function downloadFile(string $url, string $file, $fromBody = false)
    {
        try {
            echo 'downloading ' . $url . PHP_EOL;

            if ($fromBody) {
                $response = $this->httpClient->get($url, ['verify' => false]);
                file_put_contents($file, $response->getBody()->getContents());
                return;
            }

            $this->httpClient->get($url, ['verify' => false, 'sink' => $file]);
        } catch (ClientException $e) {
            throw new UnavailableException('failed to download ' . $url);
        }
    }
}
