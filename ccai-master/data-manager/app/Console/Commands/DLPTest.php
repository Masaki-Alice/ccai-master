<?php

namespace App\Console\Commands;

use App\Library\DLPService;
use Illuminate\Console\Command;

class DLPTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccai:dlp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $findings = DLPService::inspect('pawait-ccai-test', $this->text());
        // dd($findings);

        $redacted = DLPService::redact('pawait-ccai-test', $this->text());
        dd($redacted);
    }

    function text()
    {
        return "
            John Doe, born on January 15, 1985, resides at 123 Main Street, Nairobi, Kenya. His email address is john.doe@example.com, and his phone number is +254 (0) 712 345 678. He works as a software engineer at XYZ Corporation, located at 456 Elm Avenue, Nairobi. His bank account number is 1168019621. John will be 25 years old in 2 months.

            John's national ID number is 12345678, and his credit card number is 1234-5678-9012-3456. He holds a Master's degree in Computer Science from University of Nairobi, graduating in 2010. His current salary is KSh 1,000,000 per year. John identifies as Hispanic.

            Jane Smith, born on March 20, 1990, lives at 789 Oak Lane, Mombasa, Kenya. Her email is jane.smith@example.com, and her phone number is +254 (0) 712 987 654. She is a medical doctor specializing in cardiology at Coast General Hospital, located at 789 Palm Street, Mombasa. Jane's nationality is American.

            Jane's national ID number is 87654321, and her credit card number is 9876-5432-1098-7654. She graduated from University of Nairobi School of Medicine in 2015. Her annual income is KSh 2,000,000. Jane identifies as Luo. Jane's sister is 45 years old.

            Please note that this is just sample text for testing purposes. All the personally identifiable information (PII) and sensitive data contained within should be deidentified before using it in any real-world application.
        ";
    }
}
