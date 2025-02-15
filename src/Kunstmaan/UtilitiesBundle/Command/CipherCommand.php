<?php

namespace Kunstmaan\UtilitiesBundle\Command;

use Kunstmaan\UtilitiesBundle\Helper\Cipher\CipherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

final class CipherCommand extends Command
{
    /**
     * @var CipherInterface
     */
    private $cipher;

    private static $methods = [
        0 => 'Encrypt text',
        1 => 'Decrypt text',
        2 => 'Encrypt file',
        3 => 'Decrypt file',
    ];

    public function __construct(CipherInterface $cipher)
    {
        parent::__construct();

        $this->cipher = $cipher;
    }

    protected function configure(): void
    {
        $this->setName('kuma:cipher')->setDescription('Cipher utilities commands.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Please select the method you want to use',
            self::$methods,
            0
        );

        $question->setErrorMessage('Method %s is invalid.');
        $method = $helper->ask($input, $output, $question);
        $method = array_search($method, self::$methods, true);
        switch ($method) {
            case 0:
            case 1:
                $question = new Question('Please enter the text: ');
                $question->setValidator(function ($value) {
                    if (trim($value) === '') {
                        throw new \Exception('The text cannot be empty');
                    }

                    return $value;
                });
                $question->setMaxAttempts(3);
                $text = $helper->ask($input, $output, $question);
                $text = $method === 0 ? $this->cipher->encrypt($text) : $this->cipher->decrypt($text);
                $output->writeln(sprintf('Result: %s', $text));

                break;
            case 2:
            case 3:
                $fs = new Filesystem();

                $question = new Question('Please enter the input file path: ');
                $question->setValidator(function ($value) use ($fs) {
                    if (trim($value) === '') {
                        throw new \Exception('The input file path cannot be empty');
                    }

                    if (false === $fs->exists($value)) {
                        throw new \Exception('The input file must exists');
                    }

                    if (is_dir($value)) {
                        throw new \Exception('The input file cannot be a dir');
                    }

                    return $value;
                });
                $question->setMaxAttempts(3);
                $inputFilePath = $helper->ask($input, $output, $question);

                $question = new Question('Please enter the output file path: ');
                $question->setValidator(function ($value) {
                    if (trim($value) === '') {
                        throw new \Exception('The output file path cannot be empty');
                    }

                    if (is_dir($value)) {
                        throw new \Exception('The output file path cannot be a dir');
                    }

                    return $value;
                });
                $question->setMaxAttempts(3);
                $outputFilePath = $helper->ask($input, $output, $question);

                if ($method === 2) {
                    $this->cipher->encryptFile($inputFilePath, $outputFilePath);
                } else {
                    if (false === $fs->exists($outputFilePath)) {
                        $fs->touch($outputFilePath);
                    }
                    $this->cipher->decryptFile($inputFilePath, $outputFilePath);
                }

                $output->writeln(sprintf('Check "%s" to see result', $outputFilePath));

                break;
        }

        return 0;
    }
}
