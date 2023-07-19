<?php

namespace App\Http\Controllers;

use App\Http\Resources\OpenAIResponceCollection;
use OpenAI;
use App\Models\OpenAi as OpenAiModel;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OpenAiController extends Controller
{
    public $client;
    public function __construct()
    {
        $this->client = OpenAI::client(config('openai.api_key'));
    }

    public function chat()
    {
        // ['role' => 'user', 'content' => 'countries json encoded string given, fetch data from above encoded string.'],
        $model = 'gpt-3.5-turbo-16k';
        $response = $this->client->chat()->create([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => "Transaction Json Encoded Data : " . $this->getTransactions()],
                ['role' => 'system', 'content' => 'return data as php array with tansaction ID, we call transaction_date as date '],
                ['role' => 'user', 'content' => 'list all transactions date between 2022-09-15 to 2023-04-26.'],
            ]
        ]);

        OpenAiModel::create(['response' => json_encode($response), 'tokens' => $response->usage->totalTokens, 'model' => $model]);
        dd($response);
    }

    public function getChats()
    {
        return OpenAiModel::latest()->first()->response->choices[0]->message;
        return OpenAiModel::orderBy('id','desc')->get()->pluck('response.choices')->toArray();
    }

    public function responseBreaker()
    {
        // return response()->json(OpenAIResponceCollection::collection(),200);
        dd(request()->all());
    }

    public function getTransactions(int $user_id = null)
    {
        return UserTransaction::select('plain_updated_name as plain_name', 'transaction_type', 'primary_category_title as category', 'credit_debit_type', 'transaction_date')->selectRaw('geolocation_id / 100000 as amount')->get()->toJson();
    }

    public function getCountries()
    {
        return DB::table('countries')->orderBy('id', 'desc')->take(25)->get()->toJson();
    }
}
