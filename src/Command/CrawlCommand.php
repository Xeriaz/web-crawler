<?php

namespace App\Command;

use App\Services\CrawlerService;
use App\Services\ResponseRetrieverService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    public function __construct(ResponseRetrieverService $retrieverService, CrawlerService $crawler)
    {
        parent::__construct(null);
        $this->retrieverService = $retrieverService;
        $this->crawler = $crawler;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Crawl given web')
            ->addArgument('url', InputArgument::OPTIONAL, 'Url of web to crawl')
            ->addOption('option1', null, InputOption::VALUE_REQUIRED, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $input->getArgument('url');

        $io = new SymfonyStyle($input, $output);
        $io->note(sprintf('Crawling started on: %s', $url));

        $html = $this->retrieverService->getResponseContent($url);

        [$inner, $outer] = $this->crawler->crawl($html, $url);

        dump($inner, $outer);
        $io->success('Crawling over!');

        return 0;
    }
}
