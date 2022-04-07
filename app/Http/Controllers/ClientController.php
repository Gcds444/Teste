<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Validator;
class ClientController extends Controller
{
    public function index()
    {
        //
    }

    public function store(StoreClientRequest $request)
    {
        $validated = $request->validated();
        $client = Client::create($validated);
        $client->save();
        return $client;
    }

    public function show(Client $client)
    {
        //
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        //
    }

    public function destroy(Client $client)
    {
        //
    }
}
