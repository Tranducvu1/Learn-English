<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DictionaryEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DictionaryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DictionaryEntry::query();

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(function ($builder) use ($q) {
                $builder->where('hanzi', 'like', $q)
                    ->orWhere('pinyin', 'like', $q)
                    ->orWhere('vietnamese', 'like', $q);
            });
        }

        $perPage = min((int) $request->input('per_page', 50), 200);
        $paginated = $query->orderBy('hanzi')->paginate($perPage);

        return response()->json([
            'entries' => $paginated->getCollection()->map(fn (DictionaryEntry $e) => [
                'hanzi' => $e->hanzi,
                'pinyin' => $e->pinyin,
                'vietnamese' => $e->vietnamese,
                'hsk' => $e->hsk,
                'pos' => $e->pos,
                'examples' => $e->examples,
            ]),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }
}
