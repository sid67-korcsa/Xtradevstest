<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;

#[Signature('app:generate-pdf')]
#[Description('Command description')]
class GeneratePdf extends Command
{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teszt PDF állomány generálása..';

    protected $pdfName = "hello-world.pdf";
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = 'Lorem ipsum dolor sit amet. Et rerum placeat qui fuga consequuntur
            est numquam nemo At ipsam esse ut voluptates nulla. Eos veritatis inventore
            qui quia voluptates aut voluptates cupiditate non repudiandae voluptatum in
            cupiditate recusandae rem obcaecati recusandae et tempore atque. Sit itaque
            error ut officia facilis et praesentium totam aut voluptatem quasi ad maiores
            perferendis. Ex veniam aspernatur ab mollitia repudiandae ea iusto blanditiis
            vel possimus omnis rem veritatis vitae in voluptatum laborum non ipsam
            doloremque.';

        Pdf::setOption(['dpi' => 300, 'defaultPaperSize' =>'a3', 'defaultFont' => 'sans-serif']);
        $pdf = Pdf::loadView(
            'pdf-view',
            ['data' => $data]
        );
        $pdf->save($this->pdfName);

        /**
         * run artisan command in console
         *
         * php artisan app:generate-pdf
         */
    }
}
