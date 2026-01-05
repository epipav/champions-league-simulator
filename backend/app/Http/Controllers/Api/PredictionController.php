<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PredictionService;
use Illuminate\Http\JsonResponse;

class PredictionController extends Controller
{
    private PredictionService $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    /**
     * Get championship predictions.
     * Only available from week 4 onwards.
     */
    public function index(): JsonResponse
    {
        $predictions = $this->predictionService->getPredictions();

        if ($predictions === null) {
            return response()->json([
                'message' => 'Predictions are only available from week 4 onwards',
            ], 400);
        }

        return response()->json($predictions);
    }
}
