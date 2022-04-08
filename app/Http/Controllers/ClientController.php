<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;

class ClientController extends Controller
{
    public function store(StoreClientRequest $request)
    {
        $validated = $request->validated();
        $client = Client::create($validated);
        $client->save();
        return $client;
    }
}
