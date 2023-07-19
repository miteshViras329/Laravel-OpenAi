<?php

namespace App\Http\Controllers;

use OpenAI;
use Illuminate\Support\Arr;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\OpenAi as OpenAiModel;
use App\Http\Resources\OpenAIResponceCollection;

class OpenAiController extends Controller
{
    public $client;
    public function __construct()
    {
        $this->client = OpenAI::client(config('openai.api_key'));
    }

    public function chat()
    {

        $model = 'gpt-3.5-turbo-16k';
        $response = $this->client->chat()->create([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'you are data analyst expert & working as a data analyst over 10+ years.'],
                ['role' => 'system', 'content' => "Transaction Json Encoded Data ``` " . $this->getTransactions()." ``` " ],
                ['role' => 'user', 'content' => 'list of top 5 most spent amounts on category.'],
                ['role' => 'user', 'content' => 'Todo : 1. return the output in json format'],
                ['role' => 'user', 'content' => '<debit_credit_type describes the amount is spent or income. if requested debited transaction details convert its value to positive value.>'],
                ['role' => 'user', 'content' => '<desire format : [{
                    "id": <transaction id>
                    "plain_name": <plain name>
                    "transaction_type": <transaction type>
                    "category": <category name>
                    "credit_debit_type": <credit_debit_type>
                    "transaction_date": <transaction date>
                    "amount": <amount>
                }]>'],
                ['role' => 'user', 'content' => 'if any of the case is failed or not getting output return ``` {}``` empty json object'],
                ['role' => 'user', 'content' => 'note. i want data in json format only.'],
            ]

        ]);

        OpenAiModel::create(['response' => json_encode($response), 'tokens' => $response->usage->totalTokens, 'model' => $model]);
        dd($response);
    }

    public function getChats()
    {
        dD(UserTransaction::get()->sum('geolocation_id') / 100000);
        return OpenAiModel::latest()->first()->response->choices[0]->message;
        return OpenAiModel::orderBy('id', 'desc')->get()->pluck('response.choices')->toArray();
    }

    public function responseBreaker()
    {
        // return response()->json(OpenAIResponceCollection::collection(),200);
        dd(request()->all());
    }

    public function getTransactions(int $user_id = null)
    {
        return UserTransaction::select('id', 'plain_updated_name as plain_name', 'transaction_type', 'primary_category_title as category', 'credit_debit_type', 'transaction_date')->selectRaw('geolocation_id / 100000 as amount')->get()->take(25)->toJson();
    }

    public function getCountries()
    {
        return DB::table('countries')->orderBy('id', 'desc')->take(25)->get()->toJson();
    }
}
