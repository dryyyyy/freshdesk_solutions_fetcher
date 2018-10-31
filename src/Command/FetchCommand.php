<?php

namespace App\Command;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Services\FreshdeskSDK;

class FetchCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:fetch';

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');

        $login_question = new Question('Login: ');

        $password_question = new Question('Password: ');
        $password_question->setHidden(true);
        $password_question->setHiddenFallback(false);

        $login = $helper->ask($input, $output, $login_question);
        $password = $helper->ask($input, $output, $password_question);
        $fdk = new FreshdeskSDK($login, $password);
        $fdk->initCategories();

        $categorySelect_question = new ChoiceQuestion(
            'Please select category to extract',
            $fdk->getCategoriesNames()
        );
        $category = $helper->ask($input, $output, $categorySelect_question);

        $languageCode_question = new Question('Please enter language code for articles (defaults to en): ', 'en');
        $languageCode = $helper->ask($input, $output, $languageCode_question);

        $filename_question = new Question('Please enter desired filename: ', 'data');
        $filename = $helper->ask($input, $output, $filename_question);

        $output->writeln('Extracting ' . $category);

        $fdk->fetchArticles($fdk->getCategories()[$category], $languageCode);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Topic');
        $sheet->setCellValue('B1', 'Question');
        $sheet->setCellValue('C1', 'Answer');
        $sheet->setCellValue('D1', 'HTML');
        $index = 1;
        foreach ($fdk->getAllArticles() as $articles) {
            foreach ($articles as $article) {
                $index++;
                $sheet->setCellValue('A' . $index, $article['topic']);
                $sheet->setCellValue('B' . $index, $article['query']);
                $sheet->setCellValue('C' . $index, $article['response']);
                $sheet->setCellValue('D' . $index, $article['html']);
            }
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save("$filename.xlsx");
        $io->success('Your file ' . $filename . '.xlsx is in: ' . getcwd());
    }
}
