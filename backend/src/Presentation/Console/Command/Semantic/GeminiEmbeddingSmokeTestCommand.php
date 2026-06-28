<?php

declare(strict_types=1);

namespace App\Presentation\Console\Command\Semantic;

use App\Domain\Semantic\ChunkText;
use App\Infrastructure\Semantic\GeminiEmbeddingProvider;
use App\Infrastructure\Semantic\GeminiEmbeddingTransportInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'semantic:embedding:smoke-test',
    description: 'Manually verify Gemini embedding generation (requires GEMINI_API_KEY)',
)]
final class GeminiEmbeddingSmokeTestCommand extends Command
{
    public function __construct(
        private readonly GeminiEmbeddingTransportInterface $transport,
        private readonly string $apiKey,
        private readonly string $model,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'text',
            InputArgument::REQUIRED,
            'Text to embed (e.g. "Roman Empire")',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ('' === trim($this->apiKey)) {
            $output->writeln('<error>GEMINI_API_KEY is not configured.</error>');

            return Command::FAILURE;
        }

        $rawText = $input->getArgument('text');
        if (!is_string($rawText)) {
            $output->writeln('<error>Text argument must be a string.</error>');

            return Command::FAILURE;
        }

        $text = trim($rawText);
        if ('' === $text) {
            $output->writeln('<error>Text argument cannot be empty.</error>');

            return Command::FAILURE;
        }

        $provider = new GeminiEmbeddingProvider(
            $this->transport,
            $this->apiKey,
            $this->model,
        );

        $vector = $provider->generateEmbedding(ChunkText::fromString($text));

        /** @var list<float> $values */
        $values = $vector->values();
        $sampleValues = array_slice($values, 0, 5);
        $formattedSample = implode(', ', array_map(
            static fn (float $value): string => sprintf('%.4f', $value),
            $sampleValues,
        ));

        $output->writeln('provider: gemini');
        $output->writeln(sprintf('model: %s', $this->model));
        $output->writeln(sprintf('dimension: %d', $vector->dimension()));
        $output->writeln(sprintf('sample values: [%s]', $formattedSample));

        return Command::SUCCESS;
    }
}
