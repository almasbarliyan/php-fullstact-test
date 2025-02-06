<?php

namespace App\Http\Controllers;

use App\Models\MyClient;
use Illuminate\Http\Request;

class MyClientController extends Controller
{
    public function index()
    {
        $clients = MyClient::all();
        return response()->json($clients);
    }

    public function show($id)
    {
        $client = Cache::get('client:' . $id);

        if (!$client) {
            $client = MyClient::find($id);
            if ($client) {
                Cache::put('client:' . $client->slug, $client->toJson(), now()->addDays(30));
            }
        }

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        return response()->json($client);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'slug' => 'required|string|max:100|unique:my_client',
            'is_project' => 'required|in:0,1',
            'self_capture' => 'required|in:0,1',
            'client_prefix' => 'required|string|max:4',
            'client_logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $logoFile = $request->file('client_logo'); // Mendapatkan file gambar

        $client = MyClient::createOrUpdateClient($validated, $logoFile);

        return response()->json($client, 201);
    }

    public function update(Request $request, $id)
    {
        $client = MyClient::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:250',
            'slug' => 'nullable|string|max:100|unique:my_client,slug,' . $client->id,
            'is_project' => 'nullable|in:0,1',
            'self_capture' => 'nullable|in:0,1',
            'client_prefix' => 'nullable|string|max:4',
            'client_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $logoFile = $request->file('client_logo');

        $client = MyClient::createOrUpdateClient($validated, $logoFile);

        return response()->json($client);
    }

    public function destroy($id)
    {
        $client = MyClient::softDeleteClient($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        return response()->json(['message' => 'Client soft deleted and cache removed']);
    }

    public function softDelete($id)
    {
        $client = MyClient::softDeleteClient($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        return response()->json(['message' => 'Client deleted successfully', 'client' => $client]);
    }
}
