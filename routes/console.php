<?php

use App\Console\Commands\CleanupTempFilesCommand;

Schedule::command(CleanupTempFilesCommand::class, ['--hours' => 24])->daily();
