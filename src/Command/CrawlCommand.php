<?php

namespace App\Command;

use App\Services\CrawlerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrawlCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:crawl';

    /**
     * @var CrawlerService
     */
    private $crawler;

    /**
     * @param CrawlerService $crawler
     */
    public function __construct(CrawlerService $crawler) {
        $this->crawler = $crawler;
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

        $lockSuccessful = $this->lock('crawler-service');

        if ($lockSuccessful === false) {
            $io->warning('Crawler service is currently locked');

            return self::FAILURE;
        }

        try {
            $this->crawler->crawl($url);

            $io->success('Crawling over!');
        } catch (\Throwable $e) {
            dump($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        } finally {
            $this->lock->release();
        }

        return self::SUCCESS;
    }
}
