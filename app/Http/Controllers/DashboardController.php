<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Laravel\Facades\Telegram;

use App\Models\Brand;
use App\Models\Image;
use App\Models\Product;
use App\Models\Category;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::whereNotNull('position')->orderBy('position')->get();
        $productsDiscounted = Product::where('discounted',1)->get();
        $sliders = Image::where('dashboard',1)->get();
        $promotions = Image::where('dashboard',2)->get();

        return view('index', [
            'categories' => $categories,
            'productsDiscounted' => $productsDiscounted,
            'sliders' => $sliders,
            'promotions' => $promotions
        ]);
    }

    public function contact()
    {
        return view('contact');
    }

    public function sendRequestContact(Request $request)
    {
        // Validazione
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required'
        ], [
            'name.required' => 'Il campo nome è obbligatorio',
            'email.required' => 'Il campo email è obbligatorio',
            'email.email' => 'Inserisci un indirizzo email valido',
            'subject.required' => 'Il campo oggetto è obbligatorio',
            'message.required' => 'Il campo messaggio è obbligatorio'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try{

            $chatId = '-5268274429';
            $text = "📄 <b>Richiesta di Contatto!</b>\n<b>Nome:</b> " . $request->name .  "\n<b>Email:</b> " . $request->email .  "\n<b>Oggetto:</b> " . $request->subject . "\n<b>Messaggio:</b> " . $request->message;

            $response = Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML'
            ]);

        } catch(\Exception $e){
            return redirect()->route('contact-complete')->with('error', 'Errore invio richiesta!');
        }
        return redirect()->route('contact-complete')->with('success', 'Richiesta inviata correttamente!');
    }

    public function contactComplete(Request $request)
    {
        if (!$request->session()->has('success') && !$request->session()->has('error')) {
            return redirect()->route('index');
        }


        return view('contact-complete');
    }

    public function brand(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $brands = $query
            ->orderBy('name')
            ->get()
            ->groupBy(function ($brand) {
                return strtoupper(substr($brand->name, 0, 1));
            });

        // Se è chiamata AJAX ritorna solo la tabella
        if ($request->ajax()) {
            return view('brands.partials.table', compact('brands'))->render();
        }

        return view('brand', compact('brands'));
    }
}
