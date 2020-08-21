<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Company;
use App\PDF;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function company_users(Request $r, $country_name){
        $companies = Company::with('country')->whereHas('country', function($q) use($country_name){
            $q->where('country_name', '=', $country_name);
        })->get();

        return view('welcome',[
            'companies' => $companies,
            'country_name' => $country_name,
        ]);
    }

    public function check_pdf_upload(Request $request){
        $extension= $request->file("file")->getClientOriginalExtension();
        if($extension != 'pdf'){
            return Response::Json([
                'errors' => "File type not match",
            ], 422);
        }
        // Parse pdf file and build necessary objects.
        $parser = new \Smalot\PdfParser\Parser();
        $pdf    = $parser->parseFile('dummy.pdf');
        
        $text = $pdf->getText();

        if (strpos($text, 'Proposal') !== false) {
            $title= $request->input('title');
            $size = $request->file('file')->getSize();
            $extension= "pdf";
            $stringPaperFormat=str_replace(" ", "", $request->input('title'));
            $fileName= $stringPaperFormat.".".$extension;
            $FileEnconded=  File::get($request->file);
            Storage::disk('local')->put('public/Pdf'.$fileName, $FileEnconded);
            
            $pdf_exist = PDF::where([['file', $fileName], ['size',$size] ])->first();
            if ($pdf_exist !== null) {
                    $pdf_exist->delete();
            }

            $pdf = new PDF;
            $pdf->title = $title;
            $pdf->file = $filename; 
            $pdf->size = $size;
            if($pdf->save()){
                return Response::Json([
                    'msg' => "success",
                ], 200);
            }else{
                return Response::Json([
                    'msg' => "not success",
                ], 200);
            }
        }
        else{
            return Response::Json([
                'errors' => "File type not match",
            ], 422);
        }
    }
}
