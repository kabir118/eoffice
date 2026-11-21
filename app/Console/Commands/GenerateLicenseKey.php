<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LicenseService;

class GenerateLicenseKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:generate {domain : Target domain or host name (e.g. eoffice.ustb.edu.pk or localhost)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a cryptographic System License Key for a specific domain or host name';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->argument('domain');
        $key = LicenseService::generateKeyForDomain($domain);

        $this->info("=================================================");
        $this->info(" USTB e-Office Cryptographic License Key Generator ");
        $this->info("=================================================");
        $this->line("Target Domain / Host : <fg=yellow>{$domain}</>");
        $this->line("System License Key   : <fg=green;bg=black;options=bold> {$key} </>");
        $this->info("=================================================");
        $this->comment("Add the following line to your .env file on the target server:");
        $this->line("<fg=cyan>SYSTEM_LICENSE_KEY={$key}</>");
        $this->info("=================================================");
        
        return Command::SUCCESS;
    }
}
