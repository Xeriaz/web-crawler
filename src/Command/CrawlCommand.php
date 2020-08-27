<?php

namespace App\Command;

use App\Services\CrawlerService;
use App\Services\ResponseRetrieverService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\LockFactory;

class CrawlCommand extends Command
{
    protected static $defaultName = 'app:crawl';

    /**
     * @var ResponseRetrieverService
     */
    private $retrieverService;

    /**
     * @var CrawlerService
     */
    private $crawler;

    /**
     * @var LockFactory
     */
    private $lockFactory;

    /**
     * @param ResponseRetrieverService $retrieverService
     * @param CrawlerService $crawler
     * @param LockFactory $lockFactory
     */
    public function __construct(
        ResponseRetrieverService $retrieverService,
        CrawlerService $crawler,
        LockFactory $lockFactory
    ) {
        $this->retrieverService = $retrieverService;
        $this->crawler = $crawler;
        $this->lockFactory = $lockFactory;
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Crawl given web')
            ->addArgument('url', InputArgument::REQUIRED, 'Url of web to crawl')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $input->getArgument('url');

        $io = new SymfonyStyle($input, $output);
        $io->note(sprintf('Crawling started on: %s', $url));

        $lock = $this->lockFactory->createLock('crawler-service');

        if (!$lock->acquire()) {
            $io->warning('Crawler service is currently locked');

            return self::FAILURE;
        }

        $this->crawler->crawl($url);

        $io->success('Crawling over!');

        $lock->release();

        return self::SUCCESS;
    }
}
