<?php

namespace App\Command;

use App\Entity\Config;
use App\Entity\SearchTerms;
use App\Repository\ConfigRepository;
use App\Repository\SearchTermsRepository;
use App\Service\SimpleCurlService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeCommand extends Command
{
    const TO_REMOVE = ' | Adzuna';
    const GOOGLE_SHEETS_URL = 'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s?key=%s';

    protected static $defaultName = 'app:scrape';
    private $configRepository;
    private $entityManager;
    private $searchTermsRepository;
    private $simpleCurlService;
    private $logger;

    public function __construct(ConfigRepository $configRepository, SearchTermsRepository $searchTermsRepository, EntityManagerInterface $entityManager, SimpleCurlService $simpleCurlService, LoggerInterface $logger)
    {
        $this->configRepository = $configRepository;
        $this->simpleCurlService = $simpleCurlService;
        $this->searchTermsRepository = $searchTermsRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info(sprintf('Started facebook post at time %s', (new \DateTime('now'))->format('Y-m-d H:i:s')));

        $config = $this->configRepository->findAll();
        if (empty($config)) {
            throw new \Exception('Unable to find required value');
        }

        $config = $config[0];

        $googleSheetRows = $this->getGoogleSheet($config);
        $searchTerms = $this->searchTermsRepository->findAll();
        if (empty($searchTerms)) {
            // first entry
            $firstEntry = array_reverse($googleSheetRows);
            $firstEntry = array_pop($firstEntry);
            $searchTerms = new SearchTerms();
            $searchTerms->setSearched($firstEntry);
            $this->entityManager->persist($searchTerms);
            $this->entityManager->flush();

            $postContent = $this->scrape($firstEntry);
        } else {
            $lastSearchTerm = $searchTerms[0]->getSearched();
            $lastId = $searchTerms[0]->getId();

            $googleSheetRows = array_reverse(array_reverse($googleSheetRows));
            $key = array_search($lastSearchTerm, $googleSheetRows);
            $nextKey = $key + 1;
            $searchTerm = $googleSheetRows[$nextKey];
            $this->searchTermsRepository->update($lastId, $searchTerm);
            $postContent = $this->scrape($searchTerm);
        }

        $this->postToFacebook($postContent, $config);

        return 0;
    }

    private function postToFacebook($postContent, $config)
    {
        /**
         * @var $config Config
         */
        $url = sprintf('https://graph.facebook.com/%s/feed?message=%s&access_token=%s', $config->getFacebookPageId(), urlencode($postContent), $config->getFacebookPageToken());
        $this->simpleCurlService
            ->curlReset()
            ->setOpt(CURLOPT_URL, $url)
            ->setOpt(CURLOPT_CUSTOMREQUEST, "POST")
            ->setOpt(CURLOPT_RETURNTRANSFER, true)
            ->setOpt(CURLOPT_TIMEOUT, 30)
            ->getResponse();
    }

    private function scrape($searchTerm)
    {
        $url = "https://www.adzuna.co.za/search?q=" . urlencode($searchTerm);
        $response = $this->simpleCurlService
            ->curlReset()
            ->setOpt(CURLOPT_URL, $url)
            ->setOpt(CURLOPT_CUSTOMREQUEST, "GET")
            ->setOpt(CURLOPT_RETURNTRANSFER, true)
            ->setOpt(CURLOPT_TIMEOUT, 30)
            ->getResponse();

        $crawler = new Crawler($response);
        $crawler = $crawler->filterXPath('//title')->text();
        $crawler = str_replace(self::TO_REMOVE, '', $crawler);

        return sprintf( '%s: %s', $crawler, $url);
    }

    private function getGoogleSheet($config)
    {
        $googleSheetsUrl = parse_url($config->getGoogleSheetsReference(), PHP_URL_PATH);
        $googleSheetsReference = array_values(array_filter(explode('/', $googleSheetsUrl)))[2];

        /**
         * @var $config Config
         */
        $url = sprintf(self::GOOGLE_SHEETS_URL, $googleSheetsReference, $config->getGoogleSheetsSheetName(), $config->getGoogleSheetsApiKey());

        $response = $this->simpleCurlService
            ->curlReset()
            ->setOpt(CURLOPT_URL, $url)
            ->setOpt(CURLOPT_CUSTOMREQUEST, "GET")
            ->setOpt(CURLOPT_RETURNTRANSFER, true)
            ->setOpt(CURLOPT_TIMEOUT, 30)
            ->getResponse();

        $response = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($response['values'])) {
            throw new \Exception('Google sheets response not valid!');
        }

        $response = $response['values'];

        // unset the first row
        unset($response[0]);
        // flatten the array
        $googleSheetRows = [];
        array_walk_recursive($response, function ($value, $key) use (&$googleSheetRows) {
            $googleSheetRows[] = (trim($value));
        });

        return $googleSheetRows;
    }
}
