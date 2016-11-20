<?php

namespace App\Http\Controllers\Web;

use App\Models\User\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Auth;
use League\Csv\Writer;
use SplTempFileObject;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * User authenticated via web interface.
     * @return User
     */
    protected function user()
    {
        return Auth::guard('web')->user();
    }

    /**
     * @return Writer
     */
    protected function createCsvWriter() : Writer
    {
        //the CSV file will be created using a temporary File
        $writer = Writer::createFromFileObject(new SplTempFileObject);
        //the delimiter will be the tab character
        $writer->setDelimiter(",");
        //use windows line endings for compatibility with some csv libraries
        $writer->setNewline("\r\n");
        return $writer;
    }
}
