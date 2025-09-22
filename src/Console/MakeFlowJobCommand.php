<?php

namespace Webtize\Flows\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeFlowJobCommand extends Command
{
    protected $signature = 'flows:job {name : The Job class name (e.g., OrderSync)}';

    protected $description = 'Create a Job with FlowAPI lifecycle (markInProcess/markComplete/markError)';

    public function handle(): int
    {
        $name = trim($this->argument('name'));
        if ($name === '') {
            $this->error('Name is required.');
            return 1;
        }

        $class = Str::studly($name);

        $filesystem = new Filesystem();
        $jobsDir = 'app'.DIRECTORY_SEPARATOR.'Jobs';
        $namespace = 'app\\Jobs';

        if (!$filesystem->isDirectory($jobsDir)) {
//            $filesystem->makeDirectory($jobsDir, 0755, true);
        }

        $path = $jobsDir . DIRECTORY_SEPARATOR . $class . '.php';

        if ($filesystem->exists($path)) {
            $this->error("Job already exists: {$namespace}\\{$class}");
            return 1;
        }

        $contents = $this->buildJobStub($namespace, $class);
        $filesystem->put($path, $contents);


        return 0;
    }

    protected function buildJobStub(string $namespace, string $class): string
    {
        $stub = <<<'PHP'
<?php

namespace {{namespace}};

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webtize\Flows\Service\FlowAPI;

class {{class}} implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The Flow ID this job processes.
     */
    public int $flowId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $flowId)
    {
        $this->flowId = $flowId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Mark flow as in-process
        FlowAPI::markInProcess($this->flowId);

        try {
            // TODO: Implement your job logic here

            // Mark as completed when done
            FlowAPI::markComplete($this->flowId);
        } catch (\Throwable $e) {
            $error = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];

            // Mark as error with message and stack trace
            FlowAPI::markError($this->flowId, $error);

            // Re-throw so the queue worker can handle retries, etc.
            throw $e;
        }
    }
}
PHP;

        return str_replace(['{{namespace}}', '{{class}}'], [$namespace, $class], $stub);
    }
}
