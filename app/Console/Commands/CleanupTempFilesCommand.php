<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupTempFilesCommand extends Command
{
    protected $signature = 'cleanup:temp-files {--hours=24 : Files older than [HOURS] will be deleted} {--debug : Show debug information} {--force : Delete all files regardless of age}';

    protected $description = 'Clean up temporary uploaded files older than specified hours';

    public function handle(): int
    {
        $hours = $this->option('hours');
        $debug = $this->option('debug');
        $force = $this->option('force');

        $cutoff = $force ? Carbon::now()->addYear() : Carbon::now()->subHours($hours);

        $directories = ['har-files', 'ip-imports'];
        $totalDeleted = 0;

        if ($force) {
            $this->warn('⚠️  FORCE MODE: Deleting ALL temporary files...');
            if (! $this->confirm('Are you sure you want to continue?')) {
                $this->info('Operation cancelled by user.');

                return 0;
            }
        } else {
            $this->info("Cleaning up temporary files older than {$hours} hours...");
        }

        if ($debug) {
            $this->info("Cutoff time: {$cutoff->format('Y-m-d H:i:s')}");
        }
        $this->newLine();

        // First pass: count total files to be deleted for progress bar
        $filesToDelete = [];
        $totalFiles = 0;

        foreach ($directories as $directory) {
            if ($debug) {
                $this->info("Checking directory: {$directory}");
            }

            // Check if directory exists
            if (! Storage::disk('local')->exists($directory)) {
                if ($debug) {
                    $this->warn("Directory {$directory} does not exist, skipping...");
                }
                $filesToDelete[$directory] = [];

                continue;
            }

            $files = Storage::disk('local')->files($directory);
            $filesToDelete[$directory] = [];

            if ($debug) {
                $this->info('Found '.count($files)." files in {$directory}");
            }

            foreach ($files as $file) {
                try {
                    $lastModified = Carbon::createFromTimestamp(Storage::disk('local')->lastModified($file));

                    if ($debug) {
                        $this->line("  File: {$file}");
                        $this->line("    Last modified: {$lastModified->format('Y-m-d H:i:s')}");
                        $this->line('    Will delete: '.($lastModified->lt($cutoff) ? 'YES' : 'NO'));
                    }

                    if ($lastModified->lt($cutoff)) {
                        $filesToDelete[$directory][] = $file;
                        $totalFiles++;
                    }
                } catch (Exception $e) {
                    if ($debug) {
                        $this->error("Error processing file {$file}: ".$e->getMessage());
                    }
                }
            }

            if ($debug) {
                $this->info("Files to delete in {$directory}: ".count($filesToDelete[$directory]));
                $this->newLine();
            }
        }

        if ($totalFiles === 0) {
            $this->info('No files to clean up.');

            return 0;
        }

        // Create progress bar (skip if debug mode for cleaner output)
        if (! $debug) {
            $progressBar = $this->output->createProgressBar($totalFiles);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
            $progressBar->setMessage('Starting cleanup...');
            $progressBar->start();
        }

        // Second pass: delete files with progress tracking
        foreach ($directories as $directory) {
            $directoryDeleted = 0;

            foreach ($filesToDelete[$directory] as $file) {
                $filename = basename($file);

                if ($debug) {
                    $this->info("Attempting to delete: {$file}");
                }

                if (! $debug) {
                    $progressBar->setMessage("Deleting from {$directory}/: {$filename}");
                }

                try {
                    $deleteResult = Storage::disk('local')->delete($file);

                    if ($debug) {
                        $this->info('Delete result: '.($deleteResult ? 'SUCCESS' : 'FAILED'));
                    }

                    if ($deleteResult) {
                        $directoryDeleted++;
                        $totalDeleted++;
                    }
                } catch (Exception $e) {
                    if ($debug) {
                        $this->error("Failed to delete {$file}: ".$e->getMessage());
                    }
                }

                if (! $debug) {
                    $progressBar->advance();
                    usleep(10000); // 0.01 seconds
                }
            }

            if ($directoryDeleted > 0) {
                if (! $debug) {
                    $progressBar->setMessage("✅  Completed {$directory}/ - deleted {$directoryDeleted} files");
                } else {
                    $this->info("✅  Completed {$directory}/ - deleted {$directoryDeleted} files");
                }
            }
        }

        if (! $debug) {
            $progressBar->setMessage('✅  Cleanup completed!');
            $progressBar->finish();
            $this->newLine(2);
        }

        // Summary
        $this->info('Summary:');
        foreach ($directories as $directory) {
            $deletedCount = count($filesToDelete[$directory]);
            if ($deletedCount > 0) {
                $this->line("  • {$directory}/: {$deletedCount} files deleted");
            } else {
                $this->line("  • {$directory}/: No files to delete");
            }
        }

        $this->newLine();
        $this->info("📄  Total files deleted: {$totalDeleted}");

        return 0;
    }
}
