<?php declare(strict_types=1);

namespace App\Command;

use App\Crawler\Crawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlRefactoredCommand extends Command
{
    protected static $defaultName = 'crawl';

    /**
     * @var Crawler
     */
    private $crawler;

    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('url', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');
        $this->crawler->crawl($url);

        return 0;
    }
}
