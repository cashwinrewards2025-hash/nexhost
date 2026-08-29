<?php

namespace App\Http\Controllers\API\Core;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    /**
     * List client servers
     */
    public function listServers(Client $client): JsonResponse
    {
        $servers = $client->servers()
            ->with('monitoringSource', 'networkInfo')
            ->paginate(15);

        return response()->json($servers);
    }

    /**
     * Get server details
     */
    public function getServer(Server $server): JsonResponse
    {
        return response()->json(
            $server->load(['client', 'monitoringSource', 'networkInfo', 'metrics', 'reports'])
        );
    }

    /**
     * Create server
     */
    public function createServer(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'description' => 'nullable|string',
            'monitoring_source_id' => 'nullable|exists:monitoring_sources,id',
            'tags' => 'nullable|array',
        ]);

        $server = $client->servers()->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Server created successfully',
            'server' => $server,
        ], 201);
    }

    /**
     * Update server
     */
    public function updateServer(Request $request, Server $server): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'monitoring_source_id' => 'nullable|exists:monitoring_sources,id',
            'tags' => 'nullable|array',
        ]);

        $server->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Server updated successfully',
            'server' => $server,
        ]);
    }

    /**
     * Delete server
     */
    public function deleteServer(Server $server): JsonResponse
    {
        $server->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Server deleted successfully',
        ]);
    }
}
